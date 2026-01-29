<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{
    EmployeeBasicDetail,
    EmployeePayrollAttendancePayoutMonthStatus,
    EmployeeOtDetail,
    EmployeeIncentiveDetail,
    EmployeeHolidayPayoutDetail,
    SalaryComponent,
    StatutoryComponent
};
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogService;
use App\Helpers\FinancialYearHelper;


class OtIncentiveController extends Controller
{
    public function index(Request $request)
    {
        // Get financial year context
        $fyContext = FinancialYearHelper::getFinancialYearContext();
        $selectedFY = $fyContext['selectedFinancialYear'];
        
        // Handle form submission
        if ($request->filled('month') && $request->filled('type')) {
            list($month, $year) = explode('-', $request->month);
            $type = $request->type;
            
            if ($type === 'ot') {
                return redirect()->route('ot-incentive.ot', [$month, $year]);
            } elseif ($type === 'incentive') {
                return redirect()->route('ot-incentive.incentive', [$month, $year]);
            }
        }
        
        // Get available months from payroll status table with financial year filtering
        $availableMonthsQuery = EmployeePayrollAttendancePayoutMonthStatus::select('payout_month', 'payout_year', 'status');
        
        // Filter by selected financial year
        if ($selectedFY) {
            $availableMonthsQuery = FinancialYearHelper::filterPayrollBySelectedFinancialYear($availableMonthsQuery);
        }
        
        $availableMonths = $availableMonthsQuery
            ->groupBy('payout_year', 'payout_month', 'status')  // Add status to GROUP BY
            ->orderByDesc('payout_year')
            ->orderByDesc('payout_month')
            ->get()
            ->map(function ($item) {
                return [
                    'month' => $item->payout_month,
                    'year' => $item->payout_year,
                    'status' => $item->status,
                    'label' => Carbon::createFromDate($item->payout_year, $item->payout_month, 1)->format('M-Y')
                ];
            });

        return view('ot-incentive.index', compact('availableMonths', 'fyContext'));
    }

    // New method to get month status via AJAX
    public function getMonthStatus(Request $request)
    {
        $request->validate([
            'month' => 'required|string'
        ]);

        list($month, $year) = explode('-', $request->month);
        
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->first();

        if (!$payoutMonth) {
            return response()->json([
                'success' => false,
                'message' => 'Payroll month not found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'ot_finalized' => $payoutMonth->ot_finalized,
                'incentive_finalized' => $payoutMonth->incentive_finalized,
                'ot_status' => $payoutMonth->ot_finalized ? 'Finalized' : 'Not Finalized',
                'incentive_status' => $payoutMonth->incentive_finalized ? 'Finalized' : 'Not Finalized'
            ]
        ]);
    }

    // public function showOtForm($month, $year)
    // {
    //     // Validate month/year combination exists in payroll status
    //     $exists = EmployeePayrollAttendancePayoutMonthStatus::where([
    //         'payout_month' => $month,
    //         'payout_year' => $year
    //     ])->exists();

    //     if (!$exists) {
    //         return redirect()->route('ot-incentive.index')->with('error', 'Invalid payroll month selected');
    //     }
    //     $employees = EmployeeBasicDetail::where('ot_status', 'yes')
    //         ->orderBy('employee_id')
    //         ->get();

    //     $existingOt = EmployeeOtDetail::where('payout_month', $month)
    //         ->where('payout_year', $year)
    //         ->get()
    //         ->keyBy('emp_id');

    //     $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
    //     $totalEmployees = $employees->count();
    //     $totalOtAmount = $existingOt->sum('total_amount');
    //     $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
    //         'payout_month' => $month,
    //         'payout_year' => $year
    //     ])->first();
    
    //     $isFinalized = $payoutMonth && $payoutMonth->ot_finalized;
    //     return view('ot-incentive.ot', compact(
    //         'employees', 'month', 'year', 'monthName', 'existingOt', 'totalEmployees', 'totalOtAmount', 'isFinalized'
    //     ));
    // }

    // OtIncentiveController.php

    public function showOtForm($month, $year)
    {
        $exists = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->exists();
    
        if (!$exists) {
            return redirect()->route('ot-incentive.index')->with('error', 'Invalid payroll month selected');
        }
    
        // OT Employees
        $otEmployees = EmployeeBasicDetail::where('ot_status', 'yes')
            ->orderBy('employee_id')
            ->get();
    
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth();
    
        // Holiday Work Employees
        $holidayEmployees = EmployeeBasicDetail::whereDate('date_of_joining', '<=', $endOfMonth)
            ->where(function ($query) use ($startOfMonth) {
                $query->whereNull('date_of_resignation') // Still working
                      ->orWhereDate('date_of_resignation', '>=', $startOfMonth); // Resigned after month started
            })
            ->orderBy('employee_id')
            ->get()
            ->each(function ($employee) use ($month, $year) {
                // Calculate total earnings and daily rate
                $earningsData = $this->calculateEarnings($employee);
                $employee->total_earnings = $earningsData['total_earnings'];
                $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
                $employee->daily_rate = $employee->total_earnings / $daysInMonth;
            });
    
        $existingOt = EmployeeOtDetail::where('payout_month', $month)
            ->where('payout_year', $year)
            ->get()
            ->keyBy('emp_id');
    
        $existingHoliday = EmployeeHolidayPayoutDetail::where('payout_month', $month)
            ->where('payout_year', $year)
            ->get()
            ->keyBy('emp_id');
    
        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $totalEmployees = $otEmployees->count() + $holidayEmployees->count();
        $totalOtAmount = $existingOt->sum('total_amount');
        $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;
    
        // Calculate holiday totals
        $totalHolidayAmount = $existingHoliday->sum('total_amount');
    
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->first();
    
        $isFinalized = $payoutMonth && ($payoutMonth->ot_finalized || $payoutMonth->holiday_work_payout_finalized);
    
        return view('ot-incentive.ot', compact(
            'otEmployees', 'holidayEmployees', 'month', 'year', 'monthName',
            'existingOt', 'existingHoliday', 'totalEmployees', 'totalOtAmount',
            'totalHolidayAmount', 'isFinalized', 'daysInMonth'
        ));
    }
    
    /**
     * Calculate total earnings for an employee, similar to joiningLetterPDF
     *
     * @param EmployeeBasicDetail $employee
     * @return array
     */
    private function calculateEarnings($employee)
    {
        // Get all earning components
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);
    
        // Create maps for component values
        $salaryComponentMap = [];
        $statutoryComponentMap = [];
    
        // Process salary components
        foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
            $salaryComponentMap[$component->salary_component_id] = $component->value;
        }
    
        // Process statutory components
        foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
            $statutoryComponentMap[$component->statutory_component_id] = $component->value;
        }
    
        // Calculate earnings
        $totalEarnings = 0;
        $earnings = [];
    
        foreach ($earningComponents as $component) {
            $value = 0;
            $isApplicable = false;
    
            if ($component instanceof \App\Models\SalaryComponent) {
                $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                $baseValue = $salaryComponentMap[$component->id] ?? 0;
            } else {
                $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                $baseValue = $statutoryComponentMap[$component->id] ?? 0;
            }
    
            if ($isApplicable) {
                $value = $baseValue;
                $totalEarnings += $value;
            }
    
            $earnings[$component->id] = [
                'value' => $value,
                'applicable' => $isApplicable,
                'name' => $component->name,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
            ];
        }
    
        return [
            'total_earnings' => $totalEarnings,
            'earnings' => $earnings
        ];
    }


    public function saveOtAndHoliday(Request $request, $month, $year)
    {
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->first();

        if ($payoutMonth && ($payoutMonth->ot_finalized || $payoutMonth->holiday_work_payout_finalized)) {
            return back()->with('error', 'OT or Holiday work for this month has been finalized and cannot be edited.');
        }

        $request->validate([
            'ot_hours' => 'required|array',
            'ot_hours.*' => 'required|numeric|min:0|max:200',
            'holiday_work_days' => 'required|array',
            'holiday_work_days.*' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = now();
            $daysInMonth = Carbon::createFromDate($year, $month, 1)->daysInMonth;

            // Save OT details
            foreach ($request->ot_hours as $empId => $hours) {
                $employee = EmployeeBasicDetail::findOrFail($empId);

                EmployeeOtDetail::updateOrCreate(
                    [
                        'emp_id' => $empId,
                        'payout_month' => $month,
                        'payout_year' => $year
                    ],
                    [
                        'ot_hours' => $hours,
                        'ot_rate' => $employee->ot_per_hour,
                        'total_amount' => round($hours * $employee->ot_per_hour),
                        'updated_by' => $userId,
                        'updated_at' => $now,
                        'created_by' => DB::raw("IF(created_by IS NULL, $userId, created_by)")
                    ]
                );
            }

            // Save holiday work details
            foreach ($request->holiday_work_days as $empId => $days) {
                if ($days > 0) {
                    $employee = EmployeeBasicDetail::findOrFail($empId);
                    // Recalculate earnings to ensure accuracy
                    $earningsData = $this->calculateEarnings($employee);
                    $dailyRate = $earningsData['total_earnings'] / $daysInMonth;
            
                    EmployeeHolidayPayoutDetail::updateOrCreate(
                        [
                            'emp_id' => $empId,
                            'payout_month' => $month,
                            'payout_year' => $year
                        ],
                        [
                            'holiday_work_days' => $days,
                            'holiday_work_rate' => $dailyRate,
                            'total_amount' => round($dailyRate * $days),
                            'updated_by' => $userId,
                            'updated_at' => $now,
                            'created_by' => DB::raw("IF(created_by IS NULL, $userId, created_by)")
                        ]
                    );
                }
            }

            // Finalize the month
            if (!$payoutMonth) {
                $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::create([
                    'payout_month' => $month,
                    'payout_year' => $year,
                    'ot_finalized' => true,
                    'holiday_work_payout_finalized' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                $payoutMonth->ot_finalized = true;
                $payoutMonth->holiday_work_payout_finalized = true;
                $payoutMonth->save();
            }

            // Log the OT and Holiday work save activity
            $otData = [];
            $holidayData = [];
            $totalOtHours = 0;
            $totalHolidayDays = 0;
            
            foreach ($request->ot_hours as $empId => $hours) {
                if ($hours > 0) {
                    $employee = EmployeeBasicDetail::find($empId);
                    $otData[] = [
                        'employee_id' => $empId,
                        'employee_name' => $employee ? $employee->name : "Employee ID: $empId",
                        'ot_hours' => $hours,
                        'ot_rate' => $employee ? $employee->ot_per_hour : 0,
                        'total_amount' => $hours * ($employee ? $employee->ot_per_hour : 0)
                    ];
                    $totalOtHours += $hours;
                }
            }
            
            foreach ($request->holiday_work_days as $empId => $days) {
                if ($days > 0) {
                    $employee = EmployeeBasicDetail::find($empId);
                    $holidayData[] = [
                        'employee_id' => $empId,
                        'employee_name' => $employee ? $employee->name : "Employee ID: $empId",
                        'holiday_days' => $days
                    ];
                    $totalHolidayDays += $days;
                }
            }

            ActivityLogService::log(
                'ot_holiday_save',
                'Saved OT and Holiday work details',
                "Saved OT and Holiday work for " . Carbon::createFromDate($year, $month, 1)->format('M Y') . " - {$totalOtHours} OT hours, {$totalHolidayDays} holiday days",
                [
                    'month' => $month,
                    'year' => $year,
                    'total_ot_hours' => $totalOtHours,
                    'total_holiday_days' => $totalHolidayDays,
                    'ot_employees_count' => count($otData),
                    'holiday_employees_count' => count($holidayData),
                    'ot_data' => $otData,
                    'holiday_data' => $holidayData,
                    'ot_finalized' => true,
                    'holiday_finalized' => true
                ]
            );

            DB::commit();
            return back()->with('success', 'OT and Holiday work details saved and finalized successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save: ' . $e->getMessage());
        }
    }

    public function showIncentiveForm($month, $year)
    {
        // Validate month/year combination exists in payroll status
        $exists = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->exists();

        if (!$exists) {
            return redirect()->route('ot-incentive.index')->with('error', 'Invalid payroll month selected');
        }

        $employees = EmployeeBasicDetail::where('incentive_status', 'yes')
            ->orderBy('employee_id')
            ->get();

        $existingIncentive = EmployeeIncentiveDetail::where('payout_month', $month)
            ->where('payout_year', $year)
            ->get()
            ->keyBy('emp_id');

        $monthName = Carbon::createFromDate($year, $month, 1)->format('F Y');
        $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        $totalEmployees = $employees->count();
        $totalIncentiveAmount = $existingIncentive->sum('total_amount');
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->first();
    
        $isFinalized = $payoutMonth && $payoutMonth->incentive_finalized;
        return view('ot-incentive.incentive', compact(
            'employees', 'month', 'year', 'monthName', 'existingIncentive', 'totalDays', 'totalIncentiveAmount', 'totalEmployees', 'isFinalized'
        ));
    }


    // public function saveOt(Request $request, $month, $year)
    // {
    //     $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
    //         'payout_month' => $month,
    //         'payout_year' => $year
    //     ])->first();

    //     if ($payoutMonth && $payoutMonth->ot_finalized) {
    //         return back()->with('error', 'OT for this month has been finalized and cannot be edited.');
    //     }

    //     $request->validate([
    //         'ot_hours' => 'required|array',
    //         'ot_hours.*' => 'required|numeric|min:0|max:200',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $userId = Auth::id();
    //         $now = now();

    //         foreach ($request->ot_hours as $empId => $hours) {
    //             $employee = EmployeeBasicDetail::findOrFail($empId);
                
    //             EmployeeOtDetail::updateOrCreate(
    //                 [
    //                     'emp_id' => $empId,
    //                     'payout_month' => $month,
    //                     'payout_year' => $year
    //                 ],
    //                 [
    //                     'ot_hours' => $hours,
    //                     'ot_rate' => $employee->ot_per_hour,
    //                     'total_amount' => round($hours * $employee->ot_per_hour),
    //                     'updated_by' => $userId,
    //                     'updated_at' => $now,
    //                     'created_by' => DB::raw("IF(created_by IS NULL, $userId, created_by)")
    //                 ]
    //             );
    //         }
            
    //         // Always finalize the OT when saving
    //         if (!$payoutMonth) {
    //             $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::firstOrCreate([
    //                 'payout_month' => $month,
    //                 'payout_year' => $year
    //             ]);
    //         }
            
    //         $payoutMonth->ot_finalized = true;
    //         $payoutMonth->save();
            
    //         DB::commit();
    //         return back()->with('success', 'OT details saved and finalized successfully! Editing is now disabled.');

    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         return back()->with('error', 'Failed to save OT: ' . $e->getMessage());
    //     }
    // }
    


   public function saveIncentive(Request $request, $month, $year)
    {
        $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
            'payout_month' => $month,
            'payout_year' => $year
        ])->first();
    
        if ($payoutMonth && $payoutMonth->incentive_finalized) {
            return back()->with('error', 'Incentive for this month has been finalized and cannot be edited.');
        }

        $totalDays = Carbon::createFromDate($year, $month, 1)->daysInMonth;
        
        $request->validate([
            'incentive_days' => 'required|array',
            'incentive_days.*' => 'required|numeric|min:0|max:' . $totalDays,
        ]);

        DB::beginTransaction();

        try {
            $userId = Auth::id();
            $now = now();

            // Save incentive details
            $incentiveData = [];
            $totalIncentiveDays = 0;
            $totalIncentiveAmount = 0;
            
            foreach ($request->incentive_days as $empId => $days) {
                $employee = EmployeeBasicDetail::findOrFail($empId);
                $dailyRate = ceil($employee->incentive_per_month / $totalDays);
                $totalAmount = min(round($dailyRate * $days), 5000);

                EmployeeIncentiveDetail::updateOrCreate(
                    [
                        'emp_id' => $empId,
                        'payout_month' => $month,
                        'payout_year' => $year
                    ],
                    [
                        'incentive_days' => $days,
                        'incentive_rate' => $employee->incentive_per_month,
                        // 'total_amount' => round($dailyRate * $days),
                        'total_amount' => $totalAmount,
                        'updated_by' => $userId,
                        'updated_at' => $now,
                        'created_by' => DB::raw("IF(created_by IS NULL, $userId, created_by)")
                    ]
                );

                if ($days > 0) {
                    $incentiveData[] = [
                        'employee_id' => $empId,
                        'employee_name' => $employee->name,
                        'incentive_days' => $days,
                        'daily_rate' => $dailyRate,
                        'total_amount' => $totalAmount
                    ];
                    $totalIncentiveDays += $days;
                    $totalIncentiveAmount += $totalAmount;
                }
            }

            // Finalize the payout month
            if (!$payoutMonth) {
                $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::create([
                    'payout_month' => $month,
                    'payout_year' => $year,
                    'incentive_finalized' => true,
                    'created_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now
                ]);
            } else {
                $payoutMonth->incentive_finalized = true;
                $payoutMonth->updated_at = $now;
                $payoutMonth->updated_by = $userId;
                $payoutMonth->save();
            }

            // Log the incentive save activity
            ActivityLogService::log(
                'incentive_save',
                'Saved and finalized incentive details',
                "Saved incentives for " . Carbon::createFromDate($year, $month, 1)->format('M Y') . " - {$totalIncentiveDays} total incentive days, ₹{$totalIncentiveAmount} total amount",
                [
                    'month' => $month,
                    'year' => $year,
                    'total_incentive_days' => $totalIncentiveDays,
                    'total_incentive_amount' => $totalIncentiveAmount,
                    'employees_count' => count($incentiveData),
                    'incentive_data' => $incentiveData,
                    'incentive_finalized' => true
                ]
            );

            DB::commit();
            return back()->with('success', 'Incentive details saved and finalized successfully! Editing is now disabled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save and finalize incentives: ' . $e->getMessage());
        }
    }

    public function finalizeOt($month, $year)
    {
        DB::beginTransaction();

        try {
            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year
            ])->firstOrFail();

            // Get OT details for logging
            $otDetails = EmployeeOtDetail::with('employee')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('ot_hours', '>', 0)
                ->get();

            $totalOtHours = $otDetails->sum('ot_hours');
            $totalOtAmount = $otDetails->sum('total_amount');
            $employeeCount = $otDetails->count();

            $payoutMonth->ot_finalized = true;
            $payoutMonth->save();

            // Log the OT finalization activity
            ActivityLogService::log(
                'ot_finalize',
                'Finalized OT for month',
                "Finalized OT for " . Carbon::createFromDate($year, $month, 1)->format('M Y') . " - {$totalOtHours} total hours, ₹{$totalOtAmount} total amount",
                [
                    'month' => $month,
                    'year' => $year,
                    'total_ot_hours' => $totalOtHours,
                    'total_ot_amount' => $totalOtAmount,
                    'employees_count' => $employeeCount,
                    'ot_finalized' => true
                ]
            );

            DB::commit();
            return back()->with('success', 'OT finalized successfully! Editing is now disabled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to finalize OT: ' . $e->getMessage());
        }
    }

    public function finalizeIncentive($month, $year)
    {
        DB::beginTransaction();

        try {
            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year
            ])->firstOrFail();

            // Get incentive details for logging
            $incentiveDetails = EmployeeIncentiveDetail::with('employee')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('incentive_days', '>', 0)
                ->get();

            $totalIncentiveDays = $incentiveDetails->sum('incentive_days');
            $totalIncentiveAmount = $incentiveDetails->sum('total_amount');
            $employeeCount = $incentiveDetails->count();

            $payoutMonth->incentive_finalized = true;
            $payoutMonth->save();

            // Log the incentive finalization activity
            ActivityLogService::log(
                'incentive_finalize',
                'Finalized incentive for month',
                "Finalized incentives for " . Carbon::createFromDate($year, $month, 1)->format('M Y') . " - {$totalIncentiveDays} total days, ₹{$totalIncentiveAmount} total amount",
                [
                    'month' => $month,
                    'year' => $year,
                    'total_incentive_days' => $totalIncentiveDays,
                    'total_incentive_amount' => $totalIncentiveAmount,
                    'employees_count' => $employeeCount,
                    'incentive_finalized' => true
                ]
            );

            DB::commit();
            return back()->with('success', 'Incentive finalized successfully! Editing is now disabled.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to finalize incentive: ' . $e->getMessage());
        }
    }

    public function downloadOtAndHolidayCSV($month, $year)
    {
        try {
            // 1. Fetch payout month and verify finalization
            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'ot_finalized' => true,
                'holiday_work_payout_finalized' => true
            ])->firstOrFail();

            // 2. Fetch OT and Holiday details with employee relationships
            $otDetails = EmployeeOtDetail::with('employee.bankDetail', 'employee.personalDetail')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('total_amount', '>', 0)
                ->get();

            $holidayDetails = EmployeeHolidayPayoutDetail::with('employee.bankDetail', 'employee.personalDetail')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('total_amount', '>', 0)
                ->get();

            // 3. Combine OT and Holiday amounts by employee
            $employeePayouts = [];
            foreach ($otDetails as $ot) {
                $empId = $ot->emp_id;
                $employeePayouts[$empId] = [
                    'employee' => $ot->employee,
                    'total_amount' => ($employeePayouts[$empId]['total_amount'] ?? 0) + $ot->total_amount
                ];
            }

            foreach ($holidayDetails as $holiday) {
                $empId = $holiday->emp_id;
                $employeePayouts[$empId] = [
                    'employee' => $holiday->employee,
                    'total_amount' => ($employeePayouts[$empId]['total_amount'] ?? 0) + $holiday->total_amount
                ];
            }

            // Filter out employees with zero total payout
            $employeePayouts = array_filter($employeePayouts, function ($payout) {
                return $payout['total_amount'] > 0;
            });

            if (empty($employeePayouts)) {
                return response()->json([
                    'error' => 'No OT or Holiday payout data available for this month'
                ], 404);
            }

            // 4. Static company info
            $company = [
                'account_no' => 'YOUR_COMPANY_ACCOUNT_NUMBER',
                'name' => 'DIVYA ROOPA INFRACON PVT LTD',
                'address' => [
                    'line1' => 'PERMUDE',
                    'line2' => 'MANGALORE',
                    'line3' => '5745'
                ]
            ];

            // 5. Header fields
            $currentDate = now()->format('d/m/Y');
            $totalAmount = number_format((float) array_sum(array_column($employeePayouts, 'total_amount')), 2, '.', '');
            $recordCount = count($employeePayouts);

            // 6. Prepare file
            $fileName = 'ot_holiday_bulk_' . now()->format('d_M_Y_Hi') . '.csv';
            $filePath = storage_path("app/output/{$fileName}");

            $handle = fopen($filePath, 'w');

            // Insert first row with D2, E2, F2 values
            $headerRow = ['', '', '', $currentDate, $totalAmount, $recordCount];
            fputcsv($handle, $headerRow);

            $types = function_exists('getTransactionTypes') ? getTransactionTypes() : [
                'neft' => 'NEFT TRANSFER',
                'rtgs' => 'RTGS TRANSFER',
                'imps' => 'IMPS TRANSFER',
            ];

            // 7. Insert data rows
            $index = 0;
            foreach ($employeePayouts as $payout) {
                $emp = $payout['employee'];
                $bank = $emp->bankDetail;
                $personal = $emp->personalDetail;

                $type = $bank && $bank->transaction_type
                    ? ($types[$bank->transaction_type] ?? 'NEFT TRANSFER')
                    : 'NEFT TRANSFER';

                $netPay = number_format((float) $payout['total_amount'], 2, '.', '');

                $rowData = [
                    $index + 1,                          // A
                    $type,                               // B
                    $bank->ifsc_code ?? '',              // C
                    // $bank->account_number ?? '',         // D
                    "'" . ($bank->account_number ?? ''),  // ✅ Fix here
                    strtoupper($emp->name ?? ''),        // E
                    '', '', '', '',                      // F, G, H, I
                    $index + 1,                          // J
                    $netPay,                             // K
                    $company['name'],                    // L
                ];

                fputcsv($handle, $rowData);
                $index++;
            }

            fclose($handle);

            // 8. Return file as download
            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('OT and Holiday CSV download error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate OT and Holiday CSV',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function downloadIncentiveCSV($month, $year)
    {
        try {
            // 1. Fetch payout month and verify finalization
            $payoutMonth = EmployeePayrollAttendancePayoutMonthStatus::where([
                'payout_month' => $month,
                'payout_year' => $year,
                'incentive_finalized' => true
            ])->firstOrFail();

            // 2. Fetch incentive details with employee relationships
            $incentiveDetails = EmployeeIncentiveDetail::with('employee.bankDetail', 'employee.personalDetail')
                ->where('payout_month', $month)
                ->where('payout_year', $year)
                ->where('total_amount', '>', 0)
                ->get();

            // 3. Prepare employee payouts
            $employeePayouts = [];
            foreach ($incentiveDetails as $incentive) {
                $empId = $incentive->emp_id;
                $employeePayouts[$empId] = [
                    'employee' => $incentive->employee,
                    'total_amount' => $incentive->total_amount
                ];
            }

            if (empty($employeePayouts)) {
                return response()->json([
                    'error' => 'No incentive payout data available for this month'
                ], 404);
            }

            // 4. Static company info
            $company = [
                'account_no' => 'YOUR_COMPANY_ACCOUNT_NUMBER',
                'name' => 'DIVYA ROOPA INFRACON PVT LTD',
                'address' => [
                    'line1' => 'PERMUDE',
                    'line2' => 'MANGALORE',
                    'line3' => '5745'
                ]
            ];

            // 5. Header fields
            $currentDate = now()->format('d/m/Y');
            $totalAmount = number_format((float) array_sum(array_column($employeePayouts, 'total_amount')), 2, '.', '');
            $recordCount = count($employeePayouts);

            // 6. Prepare file
            $fileName = 'incentive_bulk_' . now()->format('d_M_Y_Hi') . '.csv';
            $filePath = storage_path("app/output/{$fileName}");

            $handle = fopen($filePath, 'w');

            // Insert first row with D2, E2, F2 values
            $headerRow = ['', '', '', $currentDate, $totalAmount, $recordCount];
            fputcsv($handle, $headerRow);

            $types = function_exists('getTransactionTypes') ? getTransactionTypes() : [
                'neft' => 'NEFT TRANSFER',
                'rtgs' => 'RTGS TRANSFER',
                'imps' => 'IMPS TRANSFER',
            ];

            // 7. Insert data rows
            $index = 0;
            foreach ($employeePayouts as $payout) {
                $emp = $payout['employee'];
                $bank = $emp->bankDetail;
                $personal = $emp->personalDetail;

                $type = $bank && $bank->transaction_type
                    ? ($types[$bank->transaction_type] ?? 'NEFT TRANSFER')
                    : 'NEFT TRANSFER';

                $netPay = number_format((float) $payout['total_amount'], 2, '.', '');

                $rowData = [
                    $index + 1,                          // A
                    $type,                               // B
                    $bank->ifsc_code ?? '',              // C
                    // $bank->account_number ?? '',         // D
                    "'" . ($bank->account_number ?? ''),  // ✅ Fix here
                    strtoupper($emp->name ?? ''),        // E
                    '', '', '', '',                      // F, G, H, I
                    $index + 1,                          // J
                    $netPay,                             // K
                    $company['name'],                    // L
                ];

                fputcsv($handle, $rowData);
                $index++;
            }

            fclose($handle);

            // 8. Return file as download
            return response()->download($filePath)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            \Log::error('Incentive CSV download error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate Incentive CSV',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}