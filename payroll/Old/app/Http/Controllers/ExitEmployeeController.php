<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmployeeBasicDetail;
use App\Models\EmployeeExitDetail;
use App\Models\EmploymentHistory;
use App\Models\EmployeeStatus;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\EmployeeAdvance;
use App\Models\EmployeePayrollAttendancePayoutMonthStatus;
use App\Models\EmployeeSalaryComponent;
use App\Models\SalaryComponent;
use App\Models\StatutoryComponent;

class ExitEmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = EmployeeExitDetail::with(['employee', 'employee.departmentObj', 'employee.designationObj', 'approver']);

        // Search by Employee Name or ID
        if ($request->filled('employee_name')) {
            $searchTerm = $request->employee_name;
            $query->whereHas('employee', function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('employee_id', 'like', "%{$searchTerm}%");
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by Date From
        if ($request->filled('date_from')) {
            $query->whereDate('last_working_day', '>=', $request->date_from);
        }

        // Filter by Date To
        if ($request->filled('date_to')) {
            $query->whereDate('last_working_day', '<=', $request->date_to);
        }

        $exitRequests = $query->orderBy('created_at', 'desc')->get();

        return view('employees.exit.index', compact('exitRequests'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get active employees
        $employees = EmployeeBasicDetail::where('status', '!=', 3) // Assuming 3 is "Left" status code from EmployeeStatus, but better to filter by string if possible
            ->orderBy('name')
            ->get();
            
        // Get Notice Period Settings
        $noticePeriodDuration = Setting::getValue('notice_period_duration', 30);

        return view('employees.exit.form', compact('employees', 'noticePeriodDuration'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employee_basic_details,id',
            'exit_type' => 'required|in:Resignation,Termination,Absconding,Retirement,Other',
            'resignation_date' => 'required|date',
            'last_working_day' => 'required|date|after_or_equal:resignation_date',
            'reason' => 'required|string',
            'settlement_mode' => 'nullable|in:immediate,payroll',
            'settlement_date' => 'nullable|required_if:settlement_mode,immediate|date',
            'settlement_amount' => 'nullable|numeric',
            'pending_advance' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            // Check if already exists
            $existing = EmployeeExitDetail::where('emp_id', $request->emp_id)
                ->whereIn('status', ['Pending', 'Approved'])
                ->first();

            if ($existing) {
                return back()->with('error', 'An active exit request already exists for this employee.');
            }

            $exitDetail = EmployeeExitDetail::create([
                'emp_id' => $request->emp_id,
                'exit_type' => $request->exit_type,
                'resignation_date' => $request->resignation_date,
                'last_working_day' => $request->last_working_day,
                'reason' => $request->reason,
                'status' => 'Pending',
                'notice_period_days' => Carbon::parse($request->resignation_date)->diffInDays(Carbon::parse($request->last_working_day)),
                'remarks' => $request->remarks,
                'settlement_mode' => $request->settlement_mode,
                'settlement_date' => $request->settlement_date,
                'settlement_amount' => $request->settlement_amount,
                'pending_advance' => $request->pending_advance,
                'settlement_notes' => $request->settlement_notes,
            // FFS Data
            'leave_encashment_days_calculated' => $request->leave_encashment_days_calculated,
            'leave_encashment_days_override' => $request->leave_encashment_days_override,
            'leave_encashment_amount_calculated' => $request->leave_encashment_amount_calculated,
            'leave_encashment_amount_override' => $request->leave_encashment_amount_override,
            'notice_period_shortfall_days' => $request->notice_period_shortfall_days,
            'notice_pay_amount_calculated' => $request->notice_pay_amount_calculated,
            'notice_pay_amount_override' => $request->notice_pay_amount_override,
            'gratuity_tenure_years_calculated' => $request->gratuity_tenure_years_calculated,
            'gratuity_tenure_years_override' => $request->gratuity_tenure_years_override,
            'gratuity_amount_calculated' => $request->gratuity_amount_calculated,
            'gratuity_amount_override' => $request->gratuity_amount_override,
            'bonus_amount_calculated' => $request->bonus_amount_calculated,
            'bonus_amount_override' => $request->bonus_amount_override,
            'other_earnings' => $request->other_earnings,
            'other_deductions' => $request->other_deductions,
            'prorated_salary_amount' => $request->prorated_salary_amount,
            'prorated_statutory_credit' => $request->prorated_statutory_credit,
            'prorated_statutory_debit' => $request->prorated_statutory_debit,
            ]);

            // Update Employee Basic Detail
            $employee = EmployeeBasicDetail::find($request->emp_id);
            $employee->date_of_resignation = $request->resignation_date;
            $employee->save();

            DB::commit();
            
            return redirect()->route('exit-employees.index')->with('success', 'Exit request initiated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to initiate exit: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $exitRequest = EmployeeExitDetail::findOrFail($id);
        
        $employees = EmployeeBasicDetail::where('id', $exitRequest->emp_id)->get(); // Only show current employee in edit
        
        return view('employees.exit.form', compact('exitRequest', 'employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $exitRequest = EmployeeExitDetail::findOrFail($id);

        $request->validate([
            'resignation_date' => 'required|date',
            'last_working_day' => 'required|date|after_or_equal:resignation_date',
            'status' => 'required|in:Pending,Approved,Rejected,Completed',
            'settlement_mode' => 'nullable|in:immediate,payroll',
            'settlement_date' => 'nullable|required_if:settlement_mode,immediate|date',
            'settlement_amount' => 'nullable|numeric',
            'pending_advance' => 'nullable|numeric',
        ]);

        DB::beginTransaction();
        try {
            $exitRequest->update([
                'exit_type' => $request->exit_type,
                'resignation_date' => $request->resignation_date,
                'last_working_day' => $request->last_working_day,
                'reason' => $request->reason,
                'status' => $request->status,
                'notice_period_days' => Carbon::parse($request->resignation_date)->diffInDays(Carbon::parse($request->last_working_day)),
                'exit_interview_conducted' => $request->has('exit_interview_conducted'),
                'exit_interview_notes' => $request->exit_interview_notes,
                'remarks' => $request->remarks,
                'approved_by' => ($request->status == 'Approved' && !$exitRequest->approved_by) ? auth()->id() : $exitRequest->approved_by,
                'settlement_mode' => $request->settlement_mode,
                'settlement_date' => $request->settlement_date,
                'settlement_amount' => $request->settlement_amount,
                'pending_advance' => $request->pending_advance,
                'settlement_notes' => $request->settlement_notes,
                // FFS Data
                'leave_encashment_days_calculated' => $request->leave_encashment_days_calculated,
                'leave_encashment_days_override' => $request->leave_encashment_days_override,
                'leave_encashment_amount_calculated' => $request->leave_encashment_amount_calculated,
                'leave_encashment_amount_override' => $request->leave_encashment_amount_override,
                'notice_period_shortfall_days' => $request->notice_period_shortfall_days,
                'notice_pay_amount_calculated' => $request->notice_pay_amount_calculated,
                'notice_pay_amount_override' => $request->notice_pay_amount_override,
                'gratuity_tenure_years_calculated' => $request->gratuity_tenure_years_calculated,
                'gratuity_tenure_years_override' => $request->gratuity_tenure_years_override,
                'gratuity_amount_calculated' => $request->gratuity_amount_calculated,
                'gratuity_amount_override' => $request->gratuity_amount_override,
                'bonus_amount_calculated' => $request->bonus_amount_calculated,
                'bonus_amount_override' => $request->bonus_amount_override,
                'other_earnings' => $request->other_earnings,
                'other_deductions' => $request->other_deductions,
                'prorated_salary_amount' => $request->prorated_salary_amount,
                'prorated_statutory_credit' => $request->prorated_statutory_credit,
                'prorated_statutory_debit' => $request->prorated_statutory_debit,
            ]);

            // Update Employee Basic Detail
            $employee = EmployeeBasicDetail::find($exitRequest->emp_id);
            $employee->date_of_resignation = $request->resignation_date;
            
            // If completed, mark employee as Left (Status ID 3 - verify this ID)
            if ($request->status == 'Completed') {
                // Find 'Left' or 'Resigned' status ID dynamically
                $leftStatus = EmployeeStatus::where('status_name', 'like', '%Resign%')
                    ->orWhere('status_name', 'like', '%Left%')
                    ->orWhere('status_name', 'like', '%Exit%')
                    ->first();
                    
                if ($leftStatus) {
                    $employee->status = $leftStatus->id;
                }
                
                // Disable User Login
                $user = User::where('employee_id', $employee->id)->first();
                if ($user) {
                    $user->status = 'Inactive';
                    $user->save();
                    
                    // Sync with Attendance System
                    $this->syncUserToAttendance($user);
                }
            }

            $employee->save();

            DB::commit();
            return redirect()->route('exit-employees.index')->with('success', 'Exit details updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update exit details: ' . $e->getMessage());
        }
    }

    /**
     * Process Rehire
     */
    public function processRehire(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employee_basic_details,id',
            'new_joining_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $employee = EmployeeBasicDetail::find($request->emp_id);
            
            // Archive current history
            EmploymentHistory::create([
                'emp_id' => $employee->id,
                'previous_joining_date' => $employee->date_of_joining,
                'previous_exit_date' => $employee->date_of_resignation ?? now(), // Fallback if missing
                'exit_type' => 'Resignation', // Ideally fetch from latest exit detail
            ]);

            // Update Employee for Rehire
            $employee->date_of_joining = $request->new_joining_date;
            $employee->date_of_resignation = null;
            
            // Reset Status to Active (ID 1 - Find dynamically)
            $activeStatus = EmployeeStatus::where('status_name', 'Active')->first();
            $employee->status = $activeStatus ? $activeStatus->id : 1;
            
            $employee->save();

            // Reactivate User Login if exists
            $user = User::where('employee_id', $employee->id)->first();
            if ($user) {
                // Check if user status column is integer or string based on User model
                // Assuming string 'Active' based on LoginController
                $user->status = 'Active'; 
                $user->save();
                
                // Sync with Attendance System
                $this->syncUserToAttendance($user);
            }

            DB::commit();
            return redirect()->route('employees.index')->with('success', 'Employee rehired successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to rehire employee: ' . $e->getMessage());
        }
    }

    /**
     * Sync user to Attendance System
     * Copied/Adapted from UserSyncController
     */
    private function syncUserToAttendance($user)
    {
        try {
            $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
            $apiToken = env('ATTENDANCE_API_TOKEN');

            if (empty($apiUrl) || empty($apiToken)) {
                \Log::warning('Attendance Sync skipped: API configuration missing.');
                return;
            }

            // Get related employee data
            $employee = EmployeeBasicDetail::find($user->employee_id);

            // Get department and designation names
            $departmentName = $user->department ? \DB::table('departments')->where('id', $user->department)->value('department') : '';
            $designationName = $user->position ? \DB::table('position_types')->where('id', $user->position)->value('position') : '';

            $userData = [
                'user_id' => $user->user_id,
                'payroll_id' => (string) $user->employee_id,
                'payroll_user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_name' => $user->role_name,
                'status' => $user->status,
                'department' => $departmentName,
                'department_id' => $user->department,
                'designation' => $designationName,
                'phone' => $user->phone_number,
                'password' => $user->password,
                'join_date' => $user->join_date,
                'date_of_joining' => $employee ? $employee->date_of_joining : $user->join_date,
                'reporting_manager_id' => $employee ? $employee->reporting_manager_id : null,
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            // Use Http facade
            $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                ->put("$apiUrl/users/{$user->user_id}/sync-simple", $userData);
            
            if (!$response->successful() && $response->status() === 404) {
                $response = \Illuminate\Support\Facades\Http::withHeaders($headers)
                    ->post("$apiUrl/users/sync-simple", $userData);
            }

            if (!$response->successful()) {
                \Log::error("Failed to sync user {$user->user_id} to attendance in Exit/Rehire", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            }
        } catch (\Exception $e) {
            \Log::error("Exception in ExitEmployeeController@syncUserToAttendance: " . $e->getMessage());
        }
    }

    /**
     * Calculate FFS Details
     */
    /**
     * Calculate FFS Details
     */
    public function calculateFFS(Request $request)
    {
        $request->validate([
            'emp_id' => 'required|exists:employee_basic_details,id',
            'last_working_day' => 'required|date',
        ]);

        try {
            $employeeId = $request->emp_id;
            $lwd = Carbon::parse($request->last_working_day);
            $month = $lwd->month;
            $year = $lwd->year;

            // 1. Pending Advance
            $pendingAdvance = 0;
            $advances = EmployeeAdvance::where('employee_id', $employeeId)
                ->where('status', 'active')
                ->get();
            
            foreach ($advances as $advance) {
                // Determine remaining amount.
                $pendingAdvance += $advance->remaining_amount;
            }
            $pendingAdvance = round($pendingAdvance, 2);

            // 2. Payroll Status for LWD Month
            $payrollStatus = EmployeePayrollAttendancePayoutMonthStatus::where('payout_month', $month)
                ->where('payout_year', $year)
                ->first();
            
            $isPayrollClosed = $payrollStatus && $payrollStatus->status === 'completed';
            
            // 3. Prorated Salary Calculation
            $employee = EmployeeBasicDetail::find($employeeId);
            
            // Eager load components
            $salaryComponents = $employee->salaryComponents()->with('salaryComponent')->get();
            $statutoryComponents = $employee->statutoryComponents()->with('statutoryComponent')->get();

            $daysInMonth = $lwd->daysInMonth;
            $workedDays = $lwd->day; 
            
            $monthlyGross = 0;
            $monthlyDeductions = 0;
            $basicSalary = 0;

            // Process Salary Components
            $salaryBreakdown = [];
            foreach ($salaryComponents as $comp) {
                // Assuming only enabled/active components are present in this relationship or we check status if needed.
                // If there is an 'is_enabled' or similar on the pivot, check it. 
                // Based on standard HRMS, usually only assigned are active.
                
                $type = $comp->salaryComponent->type ?? 'earning';
                $value = $comp->value;
                $name = $comp->salaryComponent->name ?? '';
                $code = $comp->salaryComponent->code ?? '';
                $slug = strtolower($name);

                if ($type === 'earning') {
                    $monthlyGross += $value;
                    if ($slug === 'basic' || $slug === 'basic salary') {
                        $basicSalary += $value;
                    }
                } else {
                     $monthlyDeductions += $value;
                }
                
                // Calculate prorated for this component
                $prorated = 0;
                if ($daysInMonth > 0) {
                    $prorated = round(($value / $daysInMonth) * $workedDays, 2);
                }

                $salaryBreakdown[] = [
                    'name' => $name,
                    'code' => $code,
                    'type' => $type, // earning or deduction
                    'monthly_amount' => $value, // Keep as float for now, format in JS or later
                    'prorated_amount' => $prorated,
                ];
            }

            // Process Statutory Components
            $statutoryEarnings = 0;
            $statutoryDeductions = 0;
            $statutoryBreakdown = [];

            foreach ($statutoryComponents as $comp) {
                $type = $comp->statutoryComponent->type ?? 'earning';
                $value = $comp->value;
                $name = $comp->statutoryComponent->name ?? '';
                $code = $comp->statutoryComponent->code ?? '';
                
                if ($type === 'earning') {
                    $monthlyGross += $value;
                    $statutoryEarnings += $value;
                } else {
                $monthlyDeductions += $value;
                    $statutoryDeductions += $value;
                }
                
                // Calculate prorated for this component
                $prorated = 0;
                if ($daysInMonth > 0) {
                    $prorated = round(($value / $daysInMonth) * $workedDays, 2);
                }
                
                // Format Status/Options
                $options = [];
                if (!empty($comp->epf_option)) {
                     // Map readable names if needed or just format
                     $options[] = ucwords(str_replace('_', ' ', $comp->epf_option));
                }
                if ($comp->full_amount_deduct_from_ctc) {
                    $options[] = "Full Deduct";
                }
                
                $statutoryBreakdown[] = [
                    'name' => $name,
                    'code' => $code,
                    'type' => $type,
                    'monthly_amount' => $value,
                    'prorated_amount' => $prorated,
                    'options' => $options,
                ];
            }

            $proratedSalary = 0;
            $proratedDeductions = 0; // Total deductions (salary + statutory)
            $proratedStatutoryCredit = 0;
            $proratedStatutoryDebit = 0;

            if ($monthlyGross > 0) {
                $proratedSalary = round(($monthlyGross / $daysInMonth) * $workedDays, 2);
            }
            
            if ($monthlyDeductions > 0) {
                $proratedDeductions = round(($monthlyDeductions / $daysInMonth) * $workedDays, 2);
            }
            
            if ($statutoryEarnings > 0) {
                 $proratedStatutoryCredit = round(($statutoryEarnings / $daysInMonth) * $workedDays, 2);
            }
            
            if ($statutoryDeductions > 0) {
                 $proratedStatutoryDebit = round(($statutoryDeductions / $daysInMonth) * $workedDays, 2);
            }

            // 4. Leave Encashment
            // Assuming we have leave balance module, for now defaulting to 0 or mock logic.
            // TODO: Integrate with actual leave balance logic.
            // Formula: (Basic Salary / 26) * Earned Leave Balance
            $leaveBalance = 0; // Placeholder
            $leaveEncashmentAmount = 0;
            if ($basicSalary > 0) {
                 $perDayEncashment = $basicSalary / 26; // Standard practice
                 $leaveEncashmentAmount = round($perDayEncashment * $leaveBalance, 2);
            }

            // 5. Notice Pay
            // Logic: Compare Resignation Date + Notice Period vs LWD.
            // If Shortfall, recovery (deduction). If Excess (management decision), usually ignore or pay.
            // This requires resignation date which comes from DB or Request if passed.
            // For initial calc, we assume 0 or handle in JS if resignation date passed.
            $noticeShortfallDays = 0;
            $noticePayAmount = 0;

            // 6. Gratuity
            // Formula: (15 * Last Drawn Basic * Tenure Years) / 26
            // Tenure: > 5 Years.
            $gratuityTenure = 0;
            $gratuityAmount = 0;
            
            if ($employee->date_of_joining) {
                $doj = Carbon::parse($employee->date_of_joining);
                $diffYears = $doj->diffInYears($lwd);
                $diffMonths = $doj->diffInMonths($lwd);
                $gratuityTenure = round($diffMonths / 12, 2); // e.g. 5.5 years

                if ($gratuityTenure >= 5) {
                    $gratuityAmount = round((15 * $basicSalary * $gratuityTenure) / 26, 2);
                }
            }

            // Net Pay Estimate (Initial)
            // Use calculated values. Can be overridden in UI.
            // Formula: (Prorated Salary + Gratuity + Leave + Bonus(0) + Other(0)) - (Prorated Deductions + Advance + Notice(0))
            
            $netPayEstimate = ($proratedSalary + $gratuityAmount + $leaveEncashmentAmount) - ($proratedDeductions + $pendingAdvance + $noticePayAmount);
            $netPayEstimate = round($netPayEstimate, 2);
            
            $monthlyNetPay = $monthlyGross - $monthlyDeductions;

            return response()->json([
                'success' => true,
                'lwd_month_status' => $isPayrollClosed ? 'Closed' : 'Open',
                'message' => $isPayrollClosed ? 'Payroll for LWD month is already closed.' : '',
                
                // Advance
                'pending_advance' => number_format($pendingAdvance, 2, '.', ''),
                
                // Detailed Breakdown
                'salary_breakdown' => $salaryBreakdown,
                'statutory_breakdown' => $statutoryBreakdown,

                // Salary
                'monthly_gross' => number_format($monthlyGross, 2, '.', ''),
                'monthly_net_pay' => number_format($monthlyNetPay, 2, '.', ''),
                'prorated_salary' => number_format($proratedSalary, 2, '.', ''),
                'prorated_deductions' => number_format($proratedDeductions, 2, '.', ''),
                'prorated_statutory_credit' => number_format($proratedStatutoryCredit, 2, '.', ''),
                'prorated_statutory_debit' => number_format($proratedStatutoryDebit, 2, '.', ''),
                'days_considered' => $workedDays,
                
                // Leave
                'leave_balance' => $leaveBalance,
                'leave_encashment_amount' => number_format($leaveEncashmentAmount, 2, '.', ''),
                
                // Notice
                'notice_shortfall_days' => 0, // Frontend handles calc primarily or needs res_date
                'notice_pay_amount' => 0,
                
                // Gratuity
                'gratuity_tenure' => $gratuityTenure,
                'gratuity_amount' => number_format($gratuityAmount, 2, '.', ''),
                
                // Bonus
                'bonus_amount' => '0.00',
                
                // Final
                'net_pay_estimate' => number_format($netPayEstimate, 2, '.', ''),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $exitRequest = EmployeeExitDetail::findOrFail($id);
            $exitRequest->delete();

            return redirect()->route('exit-employees.index')->with('success', 'Exit request deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete exit request: ' . $e->getMessage());
        }
    }
}
