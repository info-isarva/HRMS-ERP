<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\DepartmentHolidayConfig;
use App\Models\Employee;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublicHolidayApplicationController extends Controller
{
    /**
     * Display the public holiday application page
     */
    public function index()
    {
        $user = auth()->user();
        $currentFinancialYear = active_fy_label();
        
        // Debug logging
        \Log::info('PublicHolidayApplicationController index accessed', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'payroll_id' => $user->payroll_id ?? 'null'
        ]);
        
        // Resolve employee record using only the employees table.
        // We prefer lookup by payroll_id, but fall back to email matching if payroll_id is not set on user.
        $employee = $this->getEmployeeForUser($user);

        // If no employee found (likely admin or not yet mapped), show appropriate message
        if (!$employee) {
            \Log::info('No employee record found for current user (may be admin). User cannot apply for public holidays.', [
                'user_email' => $user->email,
                'user_payroll_id' => $user->payroll_id ?? null
            ]);

            return view('public-holiday-applications.index', [
                'error' => 'This feature is only available for employees with payroll records. Admins cannot apply for public holidays.',
                'department' => null,
                'config' => null,
                'fixedHolidays' => collect(),
                'flexibleHolidays' => collect(),
                'userApplications' => collect(),
                'appliedHolidayIds' => [],
                'remainingApplications' => 0,
                'currentFinancialYear' => $currentFinancialYear
            ]);
        }

        // Get department info - use payroll_department_id for holiday matching
        $department = null;
        $config = null;
        
        // Find department by payroll_department_id for correct display
        if ($employee->payroll_department_id) {
            $department = Department::where('api_department_id', $employee->payroll_department_id)->first();
            
            // Get holiday config for this payroll department
            $config = DepartmentHolidayConfig::where('payroll_department_id', $employee->payroll_department_id)
                ->where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->first();
                
            // Fallback: If no config found by payroll_department_id, try by department_id
            if (!$config && $department) {
                $config = DepartmentHolidayConfig::where('department_id', $department->id)
                    ->where('financial_year', $currentFinancialYear)
                    ->where('is_active', true)
                    ->first();
            }
        }
        
        // Fallback: If no department found by payroll_department_id, use local department_id
        if (!$department && $employee->department_id) {
            $department = Department::find($employee->department_id);
            
            if (!$config) {
                $config = DepartmentHolidayConfig::where('department_id', $department->id)
                    ->where('financial_year', $currentFinancialYear)
                    ->where('is_active', true)
                    ->first();
            }
        }

        // Get all public holidays for the employee's payroll department
        $fixedHolidays = collect();
        $flexibleHolidays = collect();
        
        if ($employee->payroll_department_id) {
            $fixedHolidays = PublicHoliday::join('department_public_holidays', 'public_holidays.id', '=', 'department_public_holidays.public_holiday_id')
                ->where('department_public_holidays.payroll_department_id', $employee->payroll_department_id)
                ->where('public_holidays.financial_year', $currentFinancialYear)
                ->where('public_holidays.type', 'fixed')
                ->where('public_holidays.status', 'active')
                ->select('public_holidays.*')
                ->orderBy('date')
                ->get();

            $flexibleHolidays = PublicHoliday::join('department_public_holidays', 'public_holidays.id', '=', 'department_public_holidays.public_holiday_id')
                ->where('department_public_holidays.payroll_department_id', $employee->payroll_department_id)
                ->where('public_holidays.financial_year', $currentFinancialYear)
                ->where('public_holidays.type', 'flexible')
                ->where('public_holidays.status', 'active')
                ->select('public_holidays.*')
                ->orderBy('date')
                ->get();
        }

        // Get employee's applications for this financial year
        $userApplications = collect();
        $appliedHolidayIds = [];
        $remainingApplications = 0;
        
        if ($employee->payroll_id) {
            $userApplications = PublicHolidayApplication::where('payroll_id', $employee->payroll_id)
                ->where('financial_year', $currentFinancialYear)
                ->with('publicHoliday')
                ->get()
                ->keyBy('public_holiday_id');

            // Get applied holiday IDs
            $appliedHolidayIds = $userApplications->pluck('public_holiday_id')->toArray();

            // Calculate remaining flexible holidays the user can apply for
            if ($config) {
                $approvedApplicationsCount = $userApplications->where('status', '!=', 'rejected')->count();
                $remainingApplications = max(0, $config->flexible_public_holidays - $approvedApplicationsCount);
            }
        }

        return view('public-holiday-applications.index', compact(
            'department',
            'config',
            'fixedHolidays',
            'flexibleHolidays',
            'userApplications',
            'appliedHolidayIds',
            'remainingApplications',
            'currentFinancialYear'
        ));
    }

    /**
     * Store a new public holiday application
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $currentFinancialYear = active_fy_label();

        $request->validate([
            'holiday_ids' => 'required|array|min:1',
            'holiday_ids.*' => 'exists:public_holidays,id'
        ]);

        // Resolve employee using employees table only
        $employee = $this->getEmployeeForUser($user);
        if (!$employee) {
            return back()->with('error', 'Employee record not found in payroll system or not assigned to a payroll department.');
        }

        // Get employee's department configuration using payroll_department_id first
        $config = null;
        if ($employee->payroll_department_id) {
            $config = DepartmentHolidayConfig::where('payroll_department_id', $employee->payroll_department_id)
                ->where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->first();
        }
        
        // Fallback to local department_id if no config found
        if (!$config && $employee->department_id) {
            $config = DepartmentHolidayConfig::where('department_id', $employee->department_id)
                ->where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->first();
        }

        if (!$config) {
            return back()->with('error', 'Holiday configuration not found for your department.');
        }

        // Check how many applications employee already has using payroll_id
        $existingApplicationsCount = PublicHolidayApplication::where('payroll_id', $employee->payroll_id)
            ->where('financial_year', $currentFinancialYear)
            ->where('status', '!=', 'rejected')
            ->count();

        // Check if employee can apply for more holidays
        $totalRequestedCount = $existingApplicationsCount + count($request->holiday_ids);
        if ($totalRequestedCount > $config->flexible_public_holidays) {
            return back()->with('error', "You can only apply for {$config->flexible_public_holidays} flexible public holidays. You already have {$existingApplicationsCount} applications.");
        }

        // Get the holidays and verify they're flexible
        $holidays = PublicHoliday::whereIn('id', $request->holiday_ids)
            ->where('type', 'flexible')
            ->where('status', 'active')
            ->where('financial_year', $currentFinancialYear)
            ->get();

        if ($holidays->count() !== count($request->holiday_ids)) {
            return back()->with('error', 'One or more selected holidays are not valid flexible holidays.');
        }

        // Check for duplicate applications using payroll_id
        $existingApplications = PublicHolidayApplication::where('payroll_id', $employee->payroll_id)
            ->whereIn('public_holiday_id', $request->holiday_ids)
            ->pluck('public_holiday_id')
            ->toArray();

        if (!empty($existingApplications)) {
            return back()->with('error', 'You have already applied for one or more of these holidays.');
        }

        // Create applications
        foreach ($request->holiday_ids as $holidayId) {
            PublicHolidayApplication::create([
                'payroll_id' => $employee->payroll_id,
                'user_id' => $user->id,
                'email' => $user->email, // Keep for backward compatibility
                'public_holiday_id' => $holidayId,
                'department_id' => $employee->department_id,
                'financial_year' => $currentFinancialYear,
                'status' => 'approved', // Auto-approved for flexible holidays
                'applied_at' => now(),
                'approved_at' => now(),
                'approved_by' => $user->id,
            ]);
        }

        return back()->with('success', 'Holiday applications submitted successfully!');
    }

    /**
     * Cancel/Change a flexible public holiday application
     * Note: Users can change their selection only before the holiday date
     */
    public function cancel($id)
    {
        $user = auth()->user();
        
        // Resolve employee using employees table only
        $employee = $this->getEmployeeForUser($user);
        if (!$employee) {
            return back()->with('error', 'Employee record not found in payroll system.');
        }

        // Find the application using payroll_id from employee record
        $application = PublicHolidayApplication::where('id', $id)
            ->where('payroll_id', $employee->payroll_id)
            ->with('publicHoliday')
            ->firstOrFail();

        // Check if the holiday date has passed
        if ($application->publicHoliday->date->isPast()) {
            return back()->with('error', 'Cannot change selection for a holiday that has already passed.');
        }

        // For flexible public holidays, allow cancellation since they can reselect
        $application->delete();

        return back()->with('success', 'Holiday selection removed successfully. You can now select a different holiday.');
    }

    /**
     * Locate the Employee model for the current authenticated user using only the employees table.
     * Preference order:
     *  - If user has payroll_id: find Employee where payroll_id matches
     *  - Else: attempt to find Employee by email match
     * Returns Employee|NULL
     */
    private function getEmployeeForUser($user)
    {
        // Prefer payroll_id lookup if present on user (login table may contain payroll_id but we only use it to find the employees table)
        if (!empty($user->payroll_id)) {
            $employee = Employee::where('payroll_id', $user->payroll_id)->first();
            if ($employee) return $employee;
        }

        // Fallback: find by email in employees table
        if (!empty($user->email)) {
            $employee = Employee::where('email', $user->email)->first();
            if ($employee) return $employee;
        }

        return null;
    }


}
