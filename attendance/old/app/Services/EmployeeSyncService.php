<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Department;
use App\Services\PayrollApiService;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EmployeeSyncService
{
    protected $payrollService;
    protected $activityLogger;

    public function __construct(PayrollApiService $payrollService, ActivityLogger $activityLogger)
    {
        $this->payrollService = $payrollService;
        $this->activityLogger = $activityLogger;
    }

    /**
     * Comprehensive employee synchronization from payroll to attendance
     * This method ensures complete data consistency
     */
    public function syncAllEmployees($options = [])
    {
        $forceUpdate = $options['force_update'] ?? false;
        $deleteExtra = $options['delete_extra'] ?? false; // Changed default to false for safety
        $verbose = $options['verbose'] ?? false;

        Log::info('Starting comprehensive employee synchronization from payroll to attendance');
        
        try {
            DB::beginTransaction();

            // Step 1: Get all employees from payroll API
            $payrollEmployees = $this->payrollService->getEmployees();
            
            if ($payrollEmployees === null) {
                throw new \Exception('Failed to fetch employees from payroll API');
            }

            Log::info("Fetched {count} employees from payroll API", ['count' => count($payrollEmployees)]);

            // Step 2: Get all current employees in attendance system
            $attendanceEmployees = Employee::all()->keyBy('employee_id');
            $attendanceEmployeeIds = $attendanceEmployees->keys()->toArray();

            // Step 3: Process payroll employees (create/update)
            $stats = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'deleted' => 0,
                'errors' => 0
            ];

            $payrollEmployeeIds = [];

            foreach ($payrollEmployees as $payrollEmployee) {
                try {
                    $result = $this->processEmployee($payrollEmployee, $forceUpdate, $verbose);
                    $payrollEmployeeIds[] = $payrollEmployee['employee_id'];
                    $stats[$result['action']]++;
                    
                    if ($verbose && $result['action'] !== 'skipped') {
                        Log::info("Employee {$payrollEmployee['employee_id']}: {$result['action']} - {$result['message']}");
                    }
                } catch (\Exception $e) {
                    $stats['errors']++;
                    Log::error("Error processing employee {$payrollEmployee['employee_id']}: " . $e->getMessage());
                }
            }

            // Step 4: Handle employees that exist in attendance but not in payroll
            if ($deleteExtra) {
                $extraEmployees = array_diff($attendanceEmployeeIds, $payrollEmployeeIds);
                
                Log::warning("Found " . count($extraEmployees) . " employees in attendance system not present in payroll", [
                    'employee_ids' => $extraEmployees,
                    'delete_extra_enabled' => $deleteExtra
                ]);
                
                foreach ($extraEmployees as $extraEmployeeId) {
                    try {
                        $employee = $attendanceEmployees[$extraEmployeeId];
                        
                        // Log before deletion
                        Log::warning("Deleting employee {$extraEmployeeId} ({$employee->name}) - not found in payroll system");
                        
                        // Delete related records first (attendance records, leave applications, etc.)
                        $this->cleanupEmployeeData($employee);
                        
                        // Delete the employee
                        $employee->delete();
                        $stats['deleted']++;
                        
                        $this->activityLogger->log(
                            "Deleted employee {$extraEmployeeId} ({$employee->name}) - removed from payroll system",
                            'employees',
                            null,
                            ['employee_id' => $extraEmployeeId, 'name' => $employee->name]
                        );
                        
                    } catch (\Exception $e) {
                        $stats['errors']++;
                        Log::error("Error deleting extra employee {$extraEmployeeId}: " . $e->getMessage());
                    }
                }
            } else {
                // If not deleting, just report what would be deleted
                $extraEmployees = array_diff($attendanceEmployeeIds, $payrollEmployeeIds);
                if (!empty($extraEmployees)) {
                    Log::info("Found " . count($extraEmployees) . " employees in attendance system not present in payroll (not deleting)", [
                        'employee_ids' => $extraEmployees,
                        'delete_extra_enabled' => $deleteExtra
                    ]);
                }
            }

            DB::commit();

            // Log the completion
            $this->activityLogger->log(
                'Completed comprehensive employee synchronization',
                'employees',
                null,
                $stats
            );

            Log::info('Employee synchronization completed successfully', $stats);

            return [
                'success' => true,
                'message' => 'Employee synchronization completed successfully',
                'stats' => $stats
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Employee synchronization failed: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Employee synchronization failed: ' . $e->getMessage(),
                'stats' => $stats ?? []
            ];
        }
    }

    /**
     * Process a single employee from payroll data
     */
    protected function processEmployee($payrollEmployee, $forceUpdate = false, $verbose = false)
    {
        // Validate required fields
        if (empty($payrollEmployee['employee_id']) || empty($payrollEmployee['name'])) {
            throw new \Exception('Missing required employee data: employee_id or name');
        }

        $employeeId = $payrollEmployee['employee_id'];
        $existingEmployee = Employee::where('employee_id', $employeeId)->first();

    // Prepare employee data
    $employeeData = $this->prepareEmployeeData($payrollEmployee);

        if (!$existingEmployee) {
            // Create new employee
            $employee = Employee::create($employeeData);
            
            if ($verbose) {
                Log::info("Created new employee: {$employee->employee_id} - {$employee->name}");
            }

            return [
                'action' => 'created',
                'message' => "Created employee {$employee->name}",
                'employee' => $employee
            ];
        }

        // Check if update is needed
        if ($this->needsUpdate($existingEmployee, $employeeData) || $forceUpdate) {
            // Update existing employee
            $changes = $this->getChanges($existingEmployee, $employeeData);
            $existingEmployee->update($employeeData);
            
            if ($verbose) {
                Log::info("Updated employee: {$existingEmployee->employee_id} - {$existingEmployee->name}", $changes);
            }

            return [
                'action' => 'updated',
                'message' => "Updated employee {$existingEmployee->name}",
                'employee' => $existingEmployee,
                'changes' => $changes
            ];
        }

        return [
            'action' => 'skipped',
            'message' => "No changes needed for {$existingEmployee->name}",
            'employee' => $existingEmployee
        ];
    }

    /**
     * Prepare employee data from payroll API response
     */
    protected function prepareEmployeeData($payrollEmployee)
    {
        // Handle department mapping
        $departmentId = null;
        $payrollDepartmentId = null;

        // The payroll API may return department id as 'department_id' or 'payroll_department_id'
        if (!empty($payrollEmployee['department_id'])) {
            $payrollDepartmentId = $payrollEmployee['department_id'];
        } elseif (!empty($payrollEmployee['payroll_department_id'])) {
            $payrollDepartmentId = $payrollEmployee['payroll_department_id'];
        }

        if ($payrollDepartmentId) {
            // Try to find department by payroll department ID
            $department = Department::where('api_department_id', $payrollDepartmentId)->first();
            if ($department) {
                $departmentId = $department->id;
            } else {
                // If we can't find by api_department_id, we'll leave local mapping null
                Log::warning("Department with payroll ID {$payrollDepartmentId} not found for employee {$payrollEmployee['employee_id']}");
                $departmentId = null; // Leave null for manual assignment
            }
        }

        // Fallback: check if we have department name
        if (empty($departmentId) && !empty($payrollEmployee['department'])) {
            $department = Department::where('name', $payrollEmployee['department'])->first();
            if ($department) {
                $departmentId = $department->id;
            } else {
                // Create department if it doesn't exist
                $department = Department::create([
                    'name' => $payrollEmployee['department'],
                    'is_active' => true
                ]);
                $departmentId = $department->id;
                Log::info("Created new department: {$payrollEmployee['department']}");
            }
        }

        // Parse dates safely
        $dateOfJoining = null;
        $dateOfResignation = null;

        if (!empty($payrollEmployee['date_of_joining'])) {
            try {
                $dateOfJoining = Carbon::parse($payrollEmployee['date_of_joining'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Invalid date_of_joining for employee {$payrollEmployee['employee_id']}: {$payrollEmployee['date_of_joining']}");
            }
        }

        if (!empty($payrollEmployee['date_of_resignation'])) {
            try {
                $dateOfResignation = Carbon::parse($payrollEmployee['date_of_resignation'])->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Invalid date_of_resignation for employee {$payrollEmployee['employee_id']}: {$payrollEmployee['date_of_resignation']}");
            }
        }

        // Handle email properly - check for valid email or set to null
        $email = null;
        if (!empty($payrollEmployee['email']) && 
            $payrollEmployee['email'] !== 'No email provided' && 
            filter_var($payrollEmployee['email'], FILTER_VALIDATE_EMAIL)) {
            $email = $payrollEmployee['email'];
        }

        // Parse additional data for personal information
        $additionalData = $payrollEmployee['additional_data'] ?? [];
        
        // Normalize payroll id: API may provide 'id' or 'payroll_id'
        $normalizedPayrollId = $payrollEmployee['payroll_id'] ?? ($payrollEmployee['id'] ?? null);

        return [
            'employee_id' => $payrollEmployee['employee_id'],
            'payroll_id' => $normalizedPayrollId,
            'payroll_department_id' => $payrollDepartmentId ?? null,
            'name' => $payrollEmployee['name'],
            'email' => $email,
            'designation' => $payrollEmployee['designation'] ?? null,
            'phone' => $payrollEmployee['phone'] ?? null,
            'status' => $payrollEmployee['status'] ?? 'Active',
            'department_id' => $departmentId,
            'financial_year' => $payrollEmployee['financial_year'] ?? null,
            'date_of_joining' => $dateOfJoining,
            'date_of_resignation' => $dateOfResignation,
            'reporting_manager_payroll_id' => $payrollEmployee['reporting_manager_payroll_id'] ?? null,
            'additional_data' => !empty($additionalData) ? json_encode($additionalData) : null,
            'exclude_from_payroll' => $payrollEmployee['exclude_from_payroll'] ?? 0,
        ];
    }

    /**
     * Check if employee needs update
     */
    protected function needsUpdate($existingEmployee, $newData)
    {
        // Compare key fields
        $compareFields = [
            'payroll_id', 'name', 'email', 'designation', 'phone', 'status',
            'department_id', 'financial_year', 'date_of_joining', 'date_of_resignation',
            'reporting_manager_payroll_id', 'exclude_from_payroll'
        ];

        foreach ($compareFields as $field) {
            $existingValue = $existingEmployee->{$field};
            $newValue = $newData[$field] ?? null;

            // Handle date comparison
            if (in_array($field, ['date_of_joining', 'date_of_resignation'])) {
                $existingValue = $existingValue ? $existingValue->format('Y-m-d') : null;
            }

            if ($existingValue != $newValue) {
                return true;
            }
        }

        // Compare additional_data if it exists
        if (isset($newData['additional_data'])) {
            $existingAdditionalData = $existingEmployee->additional_data;
            $newAdditionalData = $newData['additional_data'];
            
            if (json_encode($existingAdditionalData) !== json_encode($newAdditionalData)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get changes between existing and new data
     */
    protected function getChanges($existingEmployee, $newData)
    {
        $changes = [];
        $compareFields = [
            'payroll_id', 'name', 'email', 'designation', 'phone', 'status',
            'department_id', 'financial_year', 'date_of_joining', 'date_of_resignation',
            'reporting_manager_payroll_id', 'exclude_from_payroll'
        ];

        foreach ($compareFields as $field) {
            $existingValue = $existingEmployee->{$field};
            $newValue = $newData[$field] ?? null;

            // Handle date comparison
            if (in_array($field, ['date_of_joining', 'date_of_resignation'])) {
                $existingValue = $existingValue ? $existingValue->format('Y-m-d') : null;
            }

            if ($existingValue != $newValue) {
                $changes[$field] = [
                    'old' => $existingValue,
                    'new' => $newValue
                ];
            }
        }

        return $changes;
    }

    /**
     * Clean up employee-related data before deletion
     */
    protected function cleanupEmployeeData($employee)
    {
        try {
            // Clean up attendance records
            DB::table('attendance_records')->where('user_id', $employee->id)->delete();
            
            // Clean up leave applications
            DB::table('leave_applications')->where('user_id', $employee->id)->delete();
            
            // Clean up public holiday applications
            DB::table('public_holiday_applications')->where('user_id', $employee->id)->delete();
            
            // Clean up any other related records as needed
            // Add more cleanup as required by your system
            
            Log::info("Cleaned up related data for employee {$employee->employee_id}");
            
        } catch (\Exception $e) {
            Log::error("Error cleaning up data for employee {$employee->employee_id}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get sync status and statistics
     */
    public function getSyncStatus()
    {
        try {
            // Get payroll employees count
            $payrollEmployees = $this->payrollService->getEmployees();
            $payrollCount = $payrollEmployees ? count($payrollEmployees) : 0;
            
            // Get attendance employees count
            $attendanceCount = Employee::count();
            
            // Simple last sync check using employee updated_at timestamps
            $lastSyncEmployee = Employee::latest('updated_at')->first();
            $lastSync = $lastSyncEmployee ? $lastSyncEmployee->updated_at->diffForHumans() : 'Never';
            
            return [
                'payroll_employees' => $payrollCount,
                'attendance_employees' => $attendanceCount,
                'difference' => abs($payrollCount - $attendanceCount),
                'last_sync' => $lastSync,
                'sync_needed' => $payrollCount !== $attendanceCount
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting sync status: ' . $e->getMessage());
            return [
                'error' => 'Unable to get sync status: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process a single employee (used for real-time webhook updates)
     */
    public function processSingleEmployee($employeeData, $options = [])
    {
        $forceUpdate = $options['force_update'] ?? true; // Default to force for real-time updates
        $verbose = $options['verbose'] ?? false;

        try {
            DB::beginTransaction();

            $result = $this->processEmployee($employeeData, $forceUpdate, $verbose);
            
            DB::commit();

            Log::info("Single employee sync completed: {$employeeData['employee_id']}", $result);

            return [
                'success' => true,
                'message' => "Employee {$employeeData['employee_id']} synced successfully",
                'action' => $result['action'],
                'employee' => $result['employee'] ?? null
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Single employee sync failed: " . $e->getMessage(), [
                'employee_data' => $employeeData
            ]);
            
            return [
                'success' => false,
                'message' => 'Single employee sync failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Safe employee synchronization that only creates/updates, doesn't delete
     * This is the recommended method for regular syncing
     */
    public function safeSyncEmployees($options = [])
    {
        $options['delete_extra'] = false; // Force delete_extra to false
        $options['verbose'] = $options['verbose'] ?? true; // Default to verbose for better logging
        
        Log::info('Starting SAFE employee synchronization (no deletions)');
        
        return $this->syncAllEmployees($options);
    }

    /**
     * Get status of employees that would be affected by sync
     */
    public function getSyncPreview()
    {
        try {
            // Get all employees from payroll API
            $payrollEmployees = $this->payrollService->getEmployees();
            
            if ($payrollEmployees === null) {
                throw new \Exception('Failed to fetch employees from payroll API');
            }

            // Get all current employees in attendance system
            $attendanceEmployees = Employee::all()->keyBy('employee_id');
            $attendanceEmployeeIds = $attendanceEmployees->keys()->toArray();
            $payrollEmployeeIds = collect($payrollEmployees)->pluck('employee_id')->toArray();

            // Employees that will be created (in payroll but not in attendance)
            $toCreate = array_diff($payrollEmployeeIds, $attendanceEmployeeIds);
            
            // Employees that exist in both systems (candidates for update)
            $toUpdate = array_intersect($payrollEmployeeIds, $attendanceEmployeeIds);
            
            // Employees that exist in attendance but not in payroll (would be deleted if delete_extra=true)
            $extraInAttendance = array_diff($attendanceEmployeeIds, $payrollEmployeeIds);

            return [
                'payroll_employees_count' => count($payrollEmployees),
                'attendance_employees_count' => count($attendanceEmployees),
                'employees_to_create' => [
                    'count' => count($toCreate),
                    'employee_ids' => $toCreate
                ],
                'employees_to_potentially_update' => [
                    'count' => count($toUpdate),
                    'employee_ids' => $toUpdate
                ],
                'employees_only_in_attendance' => [
                    'count' => count($extraInAttendance),
                    'employee_ids' => $extraInAttendance,
                    'details' => collect($extraInAttendance)->map(function($id) use ($attendanceEmployees) {
                        $emp = $attendanceEmployees[$id];
                        return [
                            'employee_id' => $id,
                            'name' => $emp->name,
                            'email' => $emp->email,
                            'status' => $emp->status
                        ];
                    })->toArray()
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error getting sync preview: ' . $e->getMessage());
            return [
                'error' => 'Unable to get sync preview: ' . $e->getMessage()
            ];
        }
    }
}
