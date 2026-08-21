<?php

namespace App\Services;

use App\Services\PayrollApiService;
use App\Models\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PayrollLeaveService
{
    protected $payrollApiService;

    public function __construct()
    {
        $this->payrollApiService = new PayrollApiService();
    }

    /**
     * Get employee leave balance from payroll system
     * Fetches leave allocations from payroll API based on employee email
     */
    public function getEmployeeLeaveBalance($user = null)
    {
        try {
            $user = $user ?? Auth::user();
            $currentFinancialYear = active_fy_label();
            
            // Find employee record by payroll_id in attendance system
            $employee = null;
            if ($user && $user->payroll_id) {
                $employee = Employee::where('payroll_id', $user->payroll_id)->first();
            }
            
            // If not found by payroll_id, try to find by email
            if (!$employee && $user && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
                Log::info('Employee found by email lookup', [
                    'user_email' => $user->email,
                    'employee_payroll_id' => $employee ? $employee->payroll_id : null
                ]);
            }
            
            if (!$employee || !$employee->payroll_id) {
                Log::warning('Employee not found or no payroll_id for leave balance lookup', [
                    'user_id' => $user ? $user->id : null,
                    'user_email' => $user ? $user->email : null,
                    'user_payroll_id' => $user ? $user->payroll_id : null
                ]);
                return $this->getFallbackLeaveTypes($user);
            }

            // Try to get leave allocations from payroll API first
            $payrollEmployeeData = $this->getEmployeeDataFromPayrollAPI($user->email);
            
            if ($payrollEmployeeData && isset($payrollEmployeeData['leave_allocations'])) {
                // Use actual API data
                $leaveAllocations = $payrollEmployeeData['leave_allocations'];
                
                Log::info('Using leave allocations from payroll API', [
                    'employee_email' => $user->email,
                    'allocations_count' => count($leaveAllocations)
                ]);
                
                return [
                    'success' => true,
                    'leave_types' => $this->formatLeaveTypesFromPayroll($leaveAllocations, $user),
                    'financial_year' => $currentFinancialYear,
                    'source' => 'payroll_api'
                ];
            }

            // Fallback to local calculation if API data not available
            Log::info('Payroll API data not available, using local calculation', [
                'employee_email' => $user->email
            ]);
            
            // Get active leave types available to this employee's payroll department
            $leaveTypes = $employee->availableLeaveTypes()
                ->where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->get();
                
            $leaveAllocations = [];
            
            foreach ($leaveTypes as $leaveType) {
                // Get allocated days for this leave type (hardcoded for now)
                $allocatedDays = $this->getAllocatedDaysForLeaveType($leaveType->id);
                
                $leaveAllocations[] = [
                    'leave_type_id' => $leaveType->id,
                    'leave_type_name' => $leaveType->name,
                    'leave_type_code' => $leaveType->code,
                    'allocated_days' => $allocatedDays,
                    'override_days' => null,
                    'effective_days' => $allocatedDays, // Use allocated as effective
                    'is_manual_override' => false,
                    'is_pro_rated' => false,
                    'financial_year' => $currentFinancialYear
                ];
            }
            
            if (empty($leaveAllocations)) {
                Log::info('No leave allocations found for employee', [
                    'employee_email' => $employee->email,
                    'payroll_department_id' => $employee->payroll_department_id
                ]);
                
                // If employee has a valid payroll_department_id but no leave types assigned,
                // return empty result instead of falling back to all leave types
                if ($employee->payroll_department_id) {
                    Log::info('Employee has payroll_department_id but no leave types assigned, returning empty', [
                        'employee_email' => $employee->email,
                        'payroll_department_id' => $employee->payroll_department_id
                    ]);
                    
                    return [
                        'success' => true,
                        'leave_types' => collect([]),
                        'financial_year' => $currentFinancialYear,
                        'source' => 'payroll_api'
                    ];
                }
                
                // Only fallback to all leave types if employee has no payroll_department_id
                return $this->getFallbackLeaveTypes($user);
            }

            Log::info('Successfully calculated leave balance using local data', [
                'employee_email' => $employee->email,
                'leave_types_count' => count($leaveAllocations)
            ]);

            return [
                'success' => true,
                'leave_types' => $this->formatLeaveTypesFromPayroll($leaveAllocations, $user),
                'financial_year' => $currentFinancialYear,
                'source' => 'payroll_api'
            ];
            
        } catch (\Exception $e) {
            Log::error('Error fetching leave balance', [
                'user_id' => $user ? $user->id : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to local data
            return $this->getFallbackLeaveTypes($user);
        }
    }
    
    /**
     * Format leave types from payroll API employee data
     */
    private function formatLeaveTypesFromPayroll($leaveAllocations, $user = null)
    {
        $formattedTypes = [];
        $currentFinancialYear = active_fy_label();
        
        foreach ($leaveAllocations as $allocation) {
            // Normalize financial year format from API (2025-26) to system format (2025-2026)
            $apiFinancialYear = $allocation['financial_year'] ?? $currentFinancialYear;
            $normalizedFinancialYear = $this->normalizeFinancialYear($apiFinancialYear);
            
            // Calculate used days from attendance system leave applications
            // Use email_id to match employee and sum total_days from approved applications
            $usedDays = 0;
            if ($user && $user->email) {
                // Find employee by email to get the proper user association
                $employee = \App\Models\Employee::where('email', $user->email)->first();
                
                if ($employee) {
                    // Sum up total_days from all approved leave applications for this leave type
                    // Include both 'approved' and 'approved_by_manager' statuses
                    $usedDays = \App\Models\LeaveApplication::whereHas('user', function($query) use ($user) {
                            $query->where('email', $user->email);
                        })
                        ->where('leave_type_id', $allocation['leave_type_id'])
                        ->where('financial_year', $normalizedFinancialYear)
                        ->whereIn('status', ['approved', 'approved_by_manager'])
                        ->sum('total_days') ?? 0;
                }
            }
            
            $effectiveDays = $allocation['effective_days'];  // Allocated days from payroll API
            $balance = max(0, $effectiveDays - $usedDays);  // Balance = Allocated - Used
            
            $formattedTypes[] = (object) [
                'id' => $allocation['leave_type_id'],
                'name' => $allocation['leave_type_name'],
                'code' => $allocation['leave_type_code'],
                'days_count' => $effectiveDays, // Use effective_days as allocated days
                'is_active' => true,
                // Balance calculation based on payroll API effective_days and attendance system usage
                'allocated' => $effectiveDays,  // Use effective_days from payroll API
                'override_days' => $allocation['override_days'] ?? null,
                'effective_days' => $effectiveDays,
                'used' => $usedDays,  // Sum of total_days from approved leave applications
                'balance' => $balance, // Dynamic calculation: allocated - used
                'is_manual_override' => $allocation['is_manual_override'] ?? false,
                'is_pro_rated' => $allocation['is_pro_rated'] ?? false,
                'financial_year' => $normalizedFinancialYear
            ];
        }
        
        return collect($formattedTypes);
    }
    
    /**
     * Fallback to local leave types if payroll API is unavailable
     */
    private function getFallbackLeaveTypes($user)
    {
        Log::info('Using fallback local leave types', ['user_id' => $user->id]);
        
        $currentFinancialYear = active_fy_label();
        
        // Get leave types from local database as fallback
        $availableLeaveTypes = collect([]);
        
        // Find employee record by payroll_id to get payroll_department_id
        $employee = null;
        if ($user && $user->payroll_id) {
            $employee = Employee::where('payroll_id', $user->payroll_id)->first();
        }
        
        if ($employee && $employee->payroll_department_id) {
            // Use the Employee model's availableLeaveTypes method for payroll_department_id filtering
            $availableLeaveTypes = $employee->availableLeaveTypes()
                ->where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->get();
        } else {
            // If user has no employee record or payroll_department_id (e.g., admin/super admin), get all available leave types
            Log::info('User has no employee record or payroll_department_id, fetching all active leave types', [
                'user_id' => $user->id,
                'has_employee' => $employee ? true : false,
                'payroll_department_id' => $employee ? $employee->payroll_department_id : null
            ]);
            
            $availableLeaveTypes = \App\Models\LeaveType::where('financial_year', $currentFinancialYear)
                ->where('is_active', true)
                ->get();
        }
        
        // Calculate leave balances from local data using email-based matching
        foreach ($availableLeaveTypes as $leaveType) {
            $totalAllocated = $leaveType->days_count ?? 0; // Ensure we have a default value
            
            // Calculate used days using email-based matching (same logic as payroll API)
            $totalUsed = 0;
            if ($user && $user->email) {
                $totalUsed = \App\Models\LeaveApplication::whereHas('user', function($query) use ($user) {
                        $query->where('email', $user->email);
                    })
                    ->where('leave_type_id', $leaveType->id)
                    ->where('financial_year', $currentFinancialYear)
                    ->where('status', 'approved')
                    ->sum('total_days') ?? 0;
            }
            
            $balance = max(0, $totalAllocated - $totalUsed); // Balance = Allocated - Used
            
            // Add calculated properties to the leave type object
            $leaveType->allocated = $totalAllocated;
            $leaveType->effective_days = $totalAllocated; // Set effective_days same as allocated for fallback
            $leaveType->used = $totalUsed;
            $leaveType->balance = $balance;
            $leaveType->override_days = null;
            $leaveType->is_manual_override = false;
            $leaveType->is_pro_rated = false;
            $leaveType->financial_year = $currentFinancialYear;
        }
        
        return [
            'success' => true,
            'leave_types' => $availableLeaveTypes,
            'financial_year' => $currentFinancialYear,
            'fallback' => true // Indicate this is fallback data
        ];
    }
    
    /**
     * Get leave balance for specific leave type
     */
    public function getLeaveTypeBalance($leaveTypeId, $user = null)
    {
        $leaveData = $this->getEmployeeLeaveBalance($user);
        
        if ($leaveData['success']) {
            $leaveType = $leaveData['leave_types']->firstWhere('id', $leaveTypeId);
            
            if ($leaveType) {
                return [
                    'allocated' => $leaveType->allocated,
                    'effective_days' => $leaveType->effective_days,
                    'used' => $leaveType->used,
                    'balance' => $leaveType->balance,
                    'override_days' => $leaveType->override_days ?? null,
                    'is_manual_override' => $leaveType->is_manual_override ?? false
                ];
            }
        }
        
        return ['allocated' => 0, 'effective_days' => 0, 'used' => 0, 'balance' => 0, 'override_days' => null, 'is_manual_override' => false];
    }
    
    /**
     * Get leave balance for an employee by email
     * This is useful for direct email-based lookup without requiring a user object
     */
    public function getEmployeeLeaveBalanceByEmail($email)
    {
        try {
            // Find employee record by email in attendance system
            $employee = Employee::where('email', $email)->first();
            
            if (!$employee) {
                Log::warning('Employee not found by email for leave balance lookup', [
                    'email' => $email
                ]);
                return [
                    'success' => false,
                    'message' => 'Employee not found',
                    'leave_types' => collect([])
                ];
            }

            // Find associated user by email
            $user = \App\Models\User::where('email', $email)->first();

            if (!$user) {
                Log::warning('User not found by email for leave balance lookup', [
                    'email' => $email
                ]);
                return [
                    'success' => false,
                    'message' => 'User not found',
                    'leave_types' => collect([])
                ];
            }

            // Get leave balance using existing method
            return $this->getEmployeeLeaveBalance($user);
            
        } catch (\Exception $e) {
            Log::error('Error fetching leave balance by email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error fetching leave balance',
                'leave_types' => collect([])
            ];
        }
    }

    /**
     * Get allocated days for a specific leave type
     * This simulates payroll API data with realistic allocations
     */
    private function getAllocatedDaysForLeaveType($leaveTypeId)
    {
        // Hardcoded allocation based on leave type - updated to match API data
        // This should ideally come from payroll system configuration
        $allocations = [
            1 => 10, // Casual Leave - 10 days (updated from 15 to match API)
            2 => 5,  // Sick Leave - 5 days
            3 => 21, // Earned Leave - 21 days
            4 => 10, // Any other leave types
        ];
        
        return $allocations[$leaveTypeId] ?? 10; // Default 10 days if not found
    }

    /**
     * Get employee's week-off configuration from payroll API
     * Returns the week-off days configuration for leave calculation
     */
    public function getEmployeeWeekOffConfiguration($user = null)
    {
        try {
            $user = $user ?? Auth::user();
            
            // Find employee record by email in attendance system
            $employee = null;
            if ($user && $user->email) {
                $employee = Employee::where('email', $user->email)->first();
            }
            
            if (!$employee || !$employee->email) {
                Log::warning('Employee not found or no email for week-off configuration lookup', [
                    'user_id' => $user ? $user->id : null,
                    'user_email' => $user ? $user->email : null
                ]);
                return $this->getDefaultWeekOffConfiguration();
            }

            // Get employee data from payroll API which includes week_off_configuration
            $employees = $this->payrollApiService->getEmployees();
            
            if (!$employees) {
                Log::warning('Failed to fetch employees from payroll API for week-off configuration', [
                    'employee_email' => $employee->email
                ]);
                return $this->getDefaultWeekOffConfiguration();
            }

            // Find the employee by email in payroll API response
            $payrollEmployee = null;
            foreach ($employees as $emp) {
                if (isset($emp['email']) && $emp['email'] === $employee->email) {
                    $payrollEmployee = $emp;
                    break;
                }
            }

            if (!$payrollEmployee) {
                Log::warning('Employee not found in payroll API response for week-off configuration', [
                    'employee_email' => $employee->email
                ]);
                return $this->getDefaultWeekOffConfiguration();
            }

            // Extract week-off configuration
            if (isset($payrollEmployee['week_off_configuration']) && 
                isset($payrollEmployee['week_off_configuration']['week_off_days'])) {
                
                $weekOffConfig = $payrollEmployee['week_off_configuration'];
                
                Log::info('Successfully fetched week-off configuration from payroll API', [
                    'employee_email' => $employee->email,
                    'week_off_days' => $weekOffConfig['week_off_days'],
                    'week_off_pattern' => $weekOffConfig['week_off_pattern'] ?? 'Unknown'
                ]);
                
                return [
                    'success' => true,
                    'week_off_days' => $weekOffConfig['week_off_days'],
                    'week_off_pattern' => $weekOffConfig['week_off_pattern'] ?? '',
                    'working_days_per_week' => $weekOffConfig['working_days_per_week'] ?? 5,
                    'day_names' => $weekOffConfig['day_names'] ?? [],
                    'source' => 'payroll_api'
                ];
            } else {
                Log::warning('Week-off configuration not found in payroll API response', [
                    'employee_email' => $employee->email,
                    'has_week_off_config' => isset($payrollEmployee['week_off_configuration'])
                ]);
                return $this->getDefaultWeekOffConfiguration();
            }
            
        } catch (\Exception $e) {
            Log::error('Error fetching week-off configuration from payroll API', [
                'user_id' => $user ? $user->id : null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to default configuration
            return $this->getDefaultWeekOffConfiguration();
        }
    }
    
    /**
     * Get default week-off configuration (Saturday-Sunday) as fallback
     */
    private function getDefaultWeekOffConfiguration()
    {
        Log::info('Using default week-off configuration (Saturday-Sunday)');
        
        return [
            'success' => true,
            'week_off_days' => [0, 6], // Sunday=0, Saturday=6
            'week_off_pattern' => 'Sunday, Saturday',
            'working_days_per_week' => 5,
            'day_names' => [
                ['day_number' => 0, 'day_name' => 'Sunday'],
                ['day_number' => 6, 'day_name' => 'Saturday']
            ],
            'source' => 'default_fallback'
        ];
    }



    /**
     * Leave balances keyed by user id for admin leave roster rows.
     *
     * @param  \Illuminate\Support\Collection<int, \App\Models\LeaveApplication>  $leavesOnDate
     * @return array<int, float|null>
     */
    public function getRosterLeaveBalances($leavesOnDate): array
    {
        $balances = [];
        $seen = [];

        foreach ($leavesOnDate as $leave) {
            $user = $leave->user;

            if (! $user || isset($seen[$user->id])) {
                continue;
            }

            $seen[$user->id] = true;

            try {
                $data = $this->getEmployeeLeaveBalance($user);
                $balances[$user->id] = ($data['success'] && isset($data['leave_types']))
                    ? round((float) $data['leave_types']->sum('balance'), 1)
                    : null;
            } catch (\Exception $e) {
                Log::warning('Could not load roster leave balance', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
                $balances[$user->id] = null;
            }
        }

        return $balances;
    }

    /**
     * Normalize financial year format from API (2025-26) to system format (2025-2026)
     */
    private function normalizeFinancialYear($apiFinancialYear)
    {
        // If it's already in the full format (2025-2026), return as is
        if (preg_match('/^\d{4}-\d{4}$/', $apiFinancialYear)) {
            return $apiFinancialYear;
        }
        
        // If it's in short format (2025-26), convert to full format (2025-2026)
        if (preg_match('/^(\d{4})-(\d{2})$/', $apiFinancialYear, $matches)) {
            $startYear = $matches[1];
            $endShort = $matches[2];
            
            // If end is less than start, it's likely 20xx-2x format, convert to 20xx-20xx
            if ($endShort < substr($startYear, 2, 2)) {
                $endYear = substr($startYear, 0, 2) . $endShort;
                return $startYear . '-' . $endYear;
            }
        }
        
        // Fallback to current financial year if format is unrecognized
        return active_fy_label();
    }

    /**
     * Get employee data from payroll API by email
     */
    private function getEmployeeDataFromPayrollAPI($email)
    {
        try {
            $employees = $this->payrollApiService->getEmployees();
            
            if (!$employees) {
                Log::warning('Failed to fetch employees from payroll API');
                return null;
            }

            // Find employee by email
            foreach ($employees as $employee) {
                if (isset($employee['email']) && $employee['email'] === $email) {
                    return $employee;
                }
            }

            Log::warning('Employee not found in payroll API by email', [
                'email' => $email
            ]);
            return null;
            
        } catch (\Exception $e) {
            Log::error('Error fetching employee data from payroll API', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}