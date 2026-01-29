<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HeldSalary;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Helpers\FinancialYearHelper;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HeldSalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = HeldSalary::with(['employee', 'releaser']);

        if ($request->filled('employee_name')) {
            $search = $request->employee_name;
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                     $sub->where('name', 'like', "%{$search}%")
                         ->orWhere('employee_id', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $activeHolds = (clone $query)->where('status', 'active')->orderBy('created_at', 'desc')->get();
        $heldSalaries = $query->orderBy('created_at', 'desc')->paginate(20);
        $employees = EmployeeBasicDetail::select('id', 'name', 'employee_id')->where('status', 'active')->get();

        return view('payroll.hold-salary.index', compact('heldSalaries', 'activeHolds', 'employees'));
    }

    public function create()
    {
        // Status 1 = Active
        $employees = EmployeeBasicDetail::select('id', 'name', 'employee_id')->where('status', 1)->get();
        return view('payroll.hold-salary.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employee_basic_details,id',
            'hold_type' => 'required|in:month,indefinite',
            'payout_month_year' => 'required_if:hold_type,month',
            'remarks' => 'nullable|string'
        ]);

        $month = null;
        $year = null;

        if ($request->hold_type === 'month' && $request->payout_month_year) {
            list($year, $month) = explode('-', $request->payout_month_year); // Input is YYYY-MM
            $month = (int)$month;
            $year = (int)$year;
        }

        HeldSalary::create([
            'employee_id' => $request->employee_id,
            'hold_type' => $request->hold_type,
            'payout_month' => $month,
            'payout_year' => $year,
            'remarks' => $request->remarks,
            'status' => 'active',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('hold-salary.index')->with('success', 'Salary put on hold successfully.');
    }

    public function edit($id)
    {
        $hold = HeldSalary::findOrFail($id);
        if ($hold->status !== 'active') {
            return redirect()->route('hold-salary.index')->with('error', 'Only active holds can be edited.');
        }
        $employees = EmployeeBasicDetail::select('id', 'name', 'employee_id')->where('status', 1)->get();
        return view('payroll.hold-salary.edit', compact('hold', 'employees'));
    }

    public function update(Request $request, $id)
    {
         $hold = HeldSalary::findOrFail($id);
        
        $request->validate([
            'hold_type' => 'required|in:month,indefinite',
            'payout_month_year' => 'required_if:hold_type,month',
            'remarks' => 'nullable|string'
        ]);

        $month = null;
        $year = null;

        if ($request->hold_type === 'month' && $request->payout_month_year) {
            list($year, $month) = explode('-', $request->payout_month_year);
            $month = (int)$month;
            $year = (int)$year;
        }

        $hold->update([
            'hold_type' => $request->hold_type,
            'payout_month' => $month,
            'payout_year' => $year,
            'remarks' => $request->remarks,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('hold-salary.index')->with('success', 'Hold details updated successfully.');
    }

    public function showReleaseForm($id)
    {
        $hold = HeldSalary::with('employee')->findOrFail($id);
        if ($hold->status !== 'active') {
             return redirect()->route('hold-salary.index')->with('error', 'This hold is already released.');
        }
        
        // Fetch current salary structure gross
        $employeeComponents = \App\Models\EmployeeSalaryComponent::where('emp_id', $hold->employee_id)
            ->whereHas('salaryComponent', function($q) {
                $q->where('type', 'earning')
                  ->where('status', '1');
            })
            ->get();
        $currentMonthlyGross = $employeeComponents->sum('value');

        return view('payroll.hold-salary.release', compact('hold', 'currentMonthlyGross'));
    }

    public function release(Request $request, $id)
    {
        $request->validate([
            'release_month_year' => 'required',
            'amount' => 'nullable|numeric|min:0',
            'exclude_statutory' => 'boolean'
        ]);
        
        $hold = HeldSalary::findOrFail($id);
        
        list($relYear, $relMonth) = explode('-', $request->release_month_year);
        $relMonth = (int)$relMonth;
        $relYear = (int)$relYear;

        // 1. Find Target Payroll Status
        // 1. Validation: "One Month" hold must be released in SAME month
        if ($hold->hold_type === 'month') {
            $holdDate = \Carbon\Carbon::createFromDate($hold->payout_year, $hold->payout_month, 1);
            $releaseDate = \Carbon\Carbon::createFromDate($relYear, $relMonth, 1);
            
            if (!$holdDate->isSameMonth($releaseDate)) {
                return back()->with('error', 'One Month hold for ' . $holdDate->format('M Y') . ' can only be released in the same month (' . $holdDate->format('M Y') . ').');
            }
        }

        // 2. Find Payout Month ID
        $payoutStatus = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $relMonth)
            ->where('payout_year', $relYear)
            ->where(function($q) {
                 $q->whereNull('location_id')->orWhereNotNull('location_id');
            })
            ->first();

        $employee = $hold->employee;
        if ($employee->location_id) {
             $locPayoutStatus = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $relMonth)
                ->where('payout_year', $relYear)
                ->where('location_id', $employee->location_id)
                ->first();
             if ($locPayoutStatus) {
                 $payoutStatus = $locPayoutStatus;
             }
        }
        
        if (!$payoutStatus) {
             $globalPayout = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $relMonth)
                ->where('payout_year', $relYear)
                ->whereNull('location_id')
                ->first();
             if ($globalPayout) $payoutStatus = $globalPayout;
        }

        if (!$payoutStatus) {
            return back()->with('error', 'Payroll for the release month (' . $request->release_month_year . ') has not been created yet. Please create it first.');
        }
        
        if ($payoutStatus->status === 'completed') {
             // Instead of blocking, we flag that we need to recalculate
             $isFinalized = true;
             // return back()->with('error', 'Payroll for the release month is already finalized. Cannot add arrears.');
        } else {
             $isFinalized = false;
        }

        // 2. Find Attendance Record
        $attendance = \App\Models\EmployeePayrollAttendance::where('payout_month_id', $payoutStatus->id)
            ->where('emp_id', $employee->id)
            ->first();

        if (!$attendance) {
            return back()->with('error', 'Attendance record for this employee in the release month not found. Please Save Attendance first.');
        }

        // --- NEW LOGIC: Distributed Release ---
        
        // If exclude_statutory is checked, we stick to the legacy "Lump Sum" method
        if ($request->exclude_statutory) {
             // 3. Determine Component (Legacy Path)
            $amountToRelease = $request->amount;
             
            if (is_null($amountToRelease) || $amountToRelease == 0) {
                 return back()->with('error', 'Amount is required when excluding statutory arrears.');
            }

            // Find or Create "Held Salary Release"
            $adhocComp = \App\Models\SalaryComponent::firstOrCreate(
                ['name' => 'Held Salary Release'],
                [
                    'type' => 'earning',
                    'status' => '1',
                    'is_residual' => 0
                ]
            );
            $componentId = $adhocComp->id;

            // 4. Create Override
            \App\Models\EmployeePayrollAttendanceSalaryComponentOverride::updateOrCreate(
                [
                    'emp_id' => $employee->id,
                    'payroll_attendance_id' => $attendance->id,
                    'salary_component_id' => $componentId
                ],
                [
                    'default_value' => 0,
                    'override_value' => $amountToRelease,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]
            );

        } else {
            // NEW PATH: Distribute amount across earning components
            
            // A. Fetch all active earning components for this employee
            $employeeComponents = \App\Models\EmployeeSalaryComponent::where('emp_id', $employee->id)
                ->whereHas('salaryComponent', function($q) {
                    $q->where('type', 'earning')
                      ->where('status', '1');
                })
                ->get();
            
            // Calculate Current Structure Gross
            $currentStructureGross = $employeeComponents->sum('value');

            // B. Determine Target Amount
            $targetAmount = $request->amount;

            // If input is empty, release ACTUAL salary (so Target = Current Structure)
            if (is_null($targetAmount) || $targetAmount === '') {
                $targetAmount = $currentStructureGross;
            }

            // Safety check
            if ($currentStructureGross <= 0) {
                 if ($targetAmount > 0) {
                        $adhocComp = \App\Models\SalaryComponent::firstOrCreate(
                            ['name' => 'Held Salary Release'],
                            ['type' => 'earning', 'status' => '1', 'is_residual' => 0]
                        );
                         \App\Models\EmployeePayrollAttendanceSalaryComponentOverride::updateOrCreate(
                            ['emp_id' => $employee->id, 'payroll_attendance_id' => $attendance->id, 'salary_component_id' => $adhocComp->id],
                            ['default_value' => 0, 'override_value' => $targetAmount, 'created_by' => auth()->id(), 'updated_by' => auth()->id()]
                        );
                 } else {
                     return back()->with('error', 'Employee has no salary structure defined. Cannot release actual salary.');
                 }
            } else {
                // C. Calculate Ratio
                $ratio = $targetAmount / $currentStructureGross;

                // D. Create Overrides for EACH component
                foreach ($employeeComponents as $empComp) {
                    $newValue = $empComp->value * $ratio;
                    
                    \App\Models\EmployeePayrollAttendanceSalaryComponentOverride::updateOrCreate(
                        [
                            'emp_id' => $employee->id,
                            'payroll_attendance_id' => $attendance->id,
                            'salary_component_id' => $empComp->salary_component_id
                        ],
                        [
                            'default_value' => $empComp->value, 
                            'override_value' => $newValue,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id()
                        ]
                    );
                }
            }
        }
        
        $finalReleasedAmount = $request->amount ?? ($currentStructureGross ?? 0);

        // 5. Update Held Salary Status
        $hold->update([
            'status' => 'released',
            'released_at' => now(),
            'released_by' => auth()->id(),
            'remarks' => $hold->remarks . " | Released " . number_format($finalReleasedAmount, 2) . " in " . $request->release_month_year . ". " . $request->remarks,
        ]);

        // 6. Recalculate if finalized
        if ($isFinalized) {
            $this->recalculateAttendance($attendance);
        }

        return redirect()->route('hold-salary.index')->with('success', 'Salary released successfully. ' . ($isFinalized ? 'Payroll updated for finalized month.' : 'Arrears added to payroll.'));
    }

    private function recalculateAttendance($attendance)
    {
        // 1. Fetch all overrides for this attendance
        $overrides = \App\Models\EmployeePayrollAttendanceSalaryComponentOverride::where('payroll_attendance_id', $attendance->id)->get();
        
        // 2. Get current Earnings JSON (as array)
        // FIX: If earnings is null (e.g. Zero Salary case), initialize it from Master Structure
        $earnings = $attendance->earnings;
        if (empty($earnings)) {
            $earnings = [];
            // Fetch Master Components
             $masterComponents = \App\Models\EmployeeSalaryComponent::where('emp_id', $attendance->emp_id)
                ->whereHas('salaryComponent', function($q) { $q->where('type', 'earning')->where('status', '1'); })
                ->with('salaryComponent')
                ->get();
                
            foreach($masterComponents as $mc) {
                $earnings[$mc->salary_component_id] = [
                    'name' => $mc->salaryComponent->name,
                    'value' => $mc->value,
                    'is_percentage' => $mc->salaryComponent->is_percentage,
                    'applicable' => true,
                    'overridden' => false
                ];
            }
        }
        
        $totalEarnings = 0;

        // 3. Update Earnings with Overrides
        // We need to loop through overrides and update the corresponding component in the earnings array
        foreach ($overrides as $override) {
            $compId = $override->salary_component_id;
            
            // If it exists in earnings, update it
            if (isset($earnings[$compId])) {
                $earnings[$compId]['value'] = $override->override_value;
                $earnings[$compId]['overridden'] = true;
            } else {
                // If it doesn't exist (e.g. adhoc component), add it
                // We need the component name for the JSON structure usually
                $comp = \App\Models\SalaryComponent::find($compId);
                if ($comp) {
                    $earnings[$compId] = [
                        'name' => $comp->name, // Assuming name is stored
                        'value' => $override->override_value,
                        'is_percentage' => false, // Override is absolute
                        'applicable' => true,
                        'overridden' => true
                    ];
                }
            }
        }

        // 4. Recalculate Total Earnings
        foreach ($earnings as $earning) {
            $val = isset($earning['value']) ? (float)$earning['value'] : 0;
            $totalEarnings += $val;
        }

        // 5. Update Deductions? 
        // For now, we are NOT automatically recalculating statutory deductions to avoid complex recursion issues 
        // unless explicit statutory logic is ported here. 
        // We assume 'Total Deductions' remains same or user manually adjusts if needed (but this is bulk release).
        // If strictly following logic, PF/ESI should update. 
        // *Risk*: If we update PF, we need to know the rule (12% of Basic + DA, capped at 15000 etc). 
        // Given constraint, we keep deductions as is, just update Gross and Net.
        
        $totalDeductions = $attendance->total_deductions; // keeping existing
        $netPay = $totalEarnings - $totalDeductions;

        // 6. Save
        $attendance->earnings = $earnings;
        $attendance->total_earnings = $totalEarnings;
        $attendance->net_pay = $netPay;
        $attendance->save();
    }

    public function processView(Request $request)
    {
        if ($request->has('month_year')) {
            $parts = explode('-', $request->month_year);
            if (count($parts) === 2) {
                $month = (int)$parts[1];
                $year = (int)$parts[0];
            } else {
                $month = $request->get('month', date('m'));
                $year = $request->get('year', date('Y'));
            }
        } else {
            $month = $request->get('month', date('m'));
            $year = $request->get('year', date('Y'));
        }

        // Find Payout Month ID
        $payoutStatus = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', (int)$month)
            ->where('payout_year', (int)$year)
             // We pick the first one matching - could be global or location based. 
             // To be safe for listing ALL, we might need a broader query or loop.
            ->first(); 
            
        // Fetch Held Salaries for this Month/Year (Held OR Released in this month)
        // Released in this month = released_at is in this month AND payout_month matches?
        // Actually, user wants to see "Employees who are Held" OR "Employees who were Held but Released for this payroll".
        
        $heldSalaryQuery = HeldSalary::where(function($q) use ($month, $year) {
                $q->where('payout_month', (int)$month)
                  ->where('payout_year', (int)$year);
            });
            
        if ($request->filled('hold_type')) {
            $heldSalaryQuery->where('hold_type', $request->hold_type);
        }

        $heldEmployees = $heldSalaryQuery->with('employee')->get();
            
        $designations = \App\Models\PositionType::pluck('position', 'id');
            
        // Filter for view
        // Ideally we want to show the Payroll Breakdown for these employees.
        // So we need their Attendance records for this month.
        
        $employeeIds = $heldEmployees->pluck('employee_id')->unique();
        
        $processAttendances = collect();
        if ($payoutStatus) {
             // Fetch attendances for these employees in this payroll month
             $processAttendances = \App\Models\EmployeePayrollAttendance::where('payout_month_id', $payoutStatus->id)
                ->whereIn('emp_id', $employeeIds)
                ->with([
                    'employee' => function($query) {
                        $query->with([
                            'salaryComponents' => function($q) { $q->withTrashed(); },
                            'statutoryComponents' => function($q) { $q->withTrashed(); },
                            'exitDetails',
                            'locationObj',
                            'bankDetail', // Added for consistency
                            'personalDetail' // Added for consistency
                        ]);
                    },
                    'salaryOverrides',
                    'statutoryOverrides'
                ])
                ->get();
        }

        // We also need components for headers
        $earningSalaryComponents = \App\Models\SalaryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = \App\Models\StatutoryComponent::where('type', 'earning')
            ->where('status', '1')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = \App\Models\StatutoryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = \App\Models\SalaryComponent::where('type', 'deduction')
            ->where('status', '1')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);
        
        // 1. Transform: Decode JSON, Calculate EPF Wage
        // We reuse logic from `salaryBreakdown` in PayrollController but simplified
        $epfComponentIds = [1, 2, 4]; // Basic, DA, etc. ids

        $processAttendances->transform(function ($att) use ($earningComponents, $deductionComponents, $epfComponentIds, $heldEmployees) {
            
            // Handle Earnings
            $earnings = is_string($att->earnings) ? json_decode($att->earnings, true) : $att->earnings;
            if (!is_array($earnings)) $earnings = [];
            
            // Calculate EPF Wage
            $epfWage = 0;
            foreach ($epfComponentIds as $cid) {
                if (isset($earnings[$cid]) && isset($earnings[$cid]['value'])) {
                    $epfWage += (float)$earnings[$cid]['value'];
                }
            }
            // Standard cap check (simplistic)
            if ($epfWage > 15000) $epfWage = 15000;
            $att->epfWage = $epfWage;

            // Ensure all components exist in array for view
            $attEarnings = [];
            $totalEarnings = 0;
            foreach ($earningComponents as $comp) {
                
                $val = 0;
                $isOverridden = false;
                $applicable = false;

                if (isset($earnings[$comp->id])) {
                   // This assumes ID collision isn't happening between Salary and Statutory components
                   // But PayrollController treats them as merged.
                   $data = $earnings[$comp->id];
                   $val = $data['value'] ?? 0;
                   $isOverridden = $data['overridden'] ?? false;
                   $applicable = $data['applicable'] ?? true;
                }
                
                $attEarnings[$comp->id] = [
                    'value' => $val,
                    'overridden' => $isOverridden,
                    'applicable' => $applicable
                ];
                if($applicable) $totalEarnings += $val;
            }
            $att->earnings = $attEarnings;
            
            // Handle Deductions
            $deductions = is_string($att->deductions) ? json_decode($att->deductions, true) : $att->deductions;
            if (!is_array($deductions)) $deductions = [];
            
            $attDeductions = [];
            $totalDeductions = 0;
            foreach ($deductionComponents as $comp) {
                 $val = 0;
                 $isOverridden = false;
                 $applicable = false;
                 
                if (isset($deductions[$comp->id])) {
                   $data = $deductions[$comp->id];
                   $val = $data['value'] ?? 0;
                   $isOverridden = $data['overridden'] ?? false;
                   $applicable = $data['applicable'] ?? true;
                }
                 $attDeductions[$comp->id] = [
                    'value' => $val,
                    'overridden' => $isOverridden,
                    'applicable' => $applicable
                ];
                if($applicable) $totalDeductions += $val;
            }
            
            // Advance
            if (isset($deductions['advance'])) {
                $attDeductions['advance'] = $deductions['advance'];
                if($deductions['advance']['applicable'] ?? false) {
                    $totalDeductions += ($deductions['advance']['value'] ?? 0);
                }
            }

            $att->deductions = $attDeductions;
            
            // Use derived totals for consistent display
            $att->totalEarnings = $totalEarnings;
            $att->totalDeductions = $totalDeductions;
            $att->netPay = $totalEarnings - $totalDeductions;

            // Re-apply flags
            $att->is_held = $heldEmployees->contains(function($h) use ($att) {
                return $h->employee_id == $att->emp_id && $h->status == 'active';
            });
            $att->is_released = $heldEmployees->contains(function($h) use ($att) {
                return $h->employee_id == $att->emp_id && $h->status == 'released';
            });
            
            // Early Salary
            $att->early_salary_processed = false; // logic placeholder
            
            return $att;
        });

        $isFinalized = $payoutStatus && $payoutStatus->status === 'completed';

        return view('payroll.hold-salary.process', compact('processAttendances', 'earningComponents', 'deductionComponents', 'month', 'year', 'payoutStatus', 'heldEmployees', 'designations', 'isFinalized'));
    }
}
