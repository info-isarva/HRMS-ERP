<?php

namespace App\Observers;

use App\Models\EmployeeBasicDetail;
use App\Models\EmployeeStatus;
use App\Services\AttendanceWebhookService;
use Illuminate\Support\Facades\Log;

class EmployeeBasicDetailObserver
{
    protected $webhookService;

    public function __construct(AttendanceWebhookService $webhookService)
    {
        $this->webhookService = $webhookService;
    }

    /**
     * Handle the EmployeeBasicDetail "created" event.
     */
    public function created(EmployeeBasicDetail $employee)
    {
        $this->sendWebhook('create', $employee);
    }

    /**
     * Handle the EmployeeBasicDetail "updated" event.
     */
    public function updated(EmployeeBasicDetail $employee)
    {
        // Check if relevant fields were changed
        $relevantFields = [
            'employee_id',
            'name', 
            'email',
            'contact_number',
            'designation',
            'department',
            'date_of_joining',
            'date_of_resignation',
            'status',
            'role',
            'reporting_manager_id'
        ];

        $hasRelevantChanges = false;
        foreach ($relevantFields as $field) {
            if ($employee->isDirty($field)) {
                $hasRelevantChanges = true;
                break;
            }
        }

        if ($hasRelevantChanges) {
            $this->sendWebhook('update', $employee);
            
            // Check if employee has enable_self_portal and update corresponding user
            if ($employee->enable_self_portal && !empty($employee->email)) {
                $this->syncUserFromEmployee($employee);
            }
        }
    }

    /**
     * Handle the EmployeeBasicDetail "deleted" event.
     */
    public function deleted(EmployeeBasicDetail $employee)
    {
        $this->sendWebhook('delete', $employee);
    }

    /**
     * Send webhook notification to attendance system
     */
    protected function sendWebhook($action, EmployeeBasicDetail $employee)
    {
        try {
            // Get status name from employee_statuses table
            $employeeStatus = EmployeeStatus::find($employee->status);
            $statusName = $employeeStatus ? $employeeStatus->status_name : 'Unknown';
            
            // Get designation name from position_types table
            $positionType = \App\Models\PositionType::find($employee->designation);
            $designationName = $positionType ? $positionType->position : 'Unknown';
            
            $employeeData = [
                'id' => $employee->id,
                'payroll_id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email ?: null, // Send null instead of empty string
                'designation' => $designationName, // Use proper designation name
                'department_id' => $employee->department, // This is the department ID
                'phone' => $employee->contact_number,
                'status' => $statusName, // Use proper status name
                'status_id' => $employee->status, // Include original status ID for reference
                'date_of_joining' => $employee->date_of_joining,
                'date_of_resignation' => $employee->date_of_resignation,
                'reporting_manager_payroll_id' => $employee->reporting_manager_id,
                'additional_data' => [
                    'date_of_birth' => $employee->date_of_birth,
                    'gender' => $employee->gender,
                    'original_status_id' => $employee->status,
                    'original_department_id' => $employee->department,
                    'original_designation_id' => $employee->designation
                ]
            ];

            $this->webhookService->sendEmployeeUpdate($action, $employeeData);
            
            Log::info("Webhook sent for employee {$action}: {$employee->employee_id} - {$employee->name}");
            
        } catch (\Exception $e) {
            Log::error("Failed to send webhook for employee {$action}: " . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? null,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync user from employee when employee is updated
     */
    protected function syncUserFromEmployee(EmployeeBasicDetail $employee)
    {
        try {
            // Find existing user by email, user_id, or employee_id
            $user = \App\Models\User::where('email', $employee->email)
                                  ->orWhere('user_id', $employee->employee_id)
                                  ->orWhere('employee_id', $employee->id)
                                  ->first();

            if ($user) {
                // Update existing user with latest employee data
                Log::info("Updating user from employee changes", [
                    'employee_id' => $employee->employee_id,
                    'user_id' => $user->user_id,
                    'changes' => $employee->getDirty()
                ]);
                
                $this->updateUserFromEmployee($user, $employee);
            } else {
                // Create new user if employee has enable_self_portal
                Log::info("Creating user from employee changes", [
                    'employee_id' => $employee->employee_id
                ]);
                
                \App\Models\User::createFromEmployee($employee);
            }
        } catch (\Exception $e) {
            Log::error("Failed to sync user from employee: " . $e->getMessage(), [
                'employee_id' => $employee->employee_id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user record from employee changes and sync to attendance
     */
    protected function updateUserFromEmployee(\App\Models\User $user, EmployeeBasicDetail $employee)
    {
        try {
            // Get related data
            $departmentName = null;
            $designationName = null;
            $statusName = null;
            $roleName = null;

            if ($employee->department) {
                $departmentName = \DB::table('departments')->where('id', $employee->department)->value('department');
            }

            if ($employee->designation) {
                $designationName = \DB::table('position_types')->where('id', $employee->designation)->value('position');
            }

            if ($employee->status) {
                $statusName = \DB::table('employee_statuses')->where('id', $employee->status)->value('status_name');
            }

            if ($employee->role) {
                $roleName = \DB::table('roles')->where('id', $employee->role)->value('role_name');
            }

            // Prepare user update data - only sync fields that should be controlled by employee data
            $userUpdateData = [
                'name' => $employee->name,
                'email' => $employee->email,
                'phone_number' => $employee->contact_number,
                'department' => $employee->department, // Store as ID
                'position' => $employee->designation, // Store as ID
                'avatar' => $employee->profile_image ? basename($employee->profile_image) : $user->avatar,
                'updated_at' => now(),
            ];

            // Map employee status to user status
            $userStatus = $this->mapEmployeeStatusToUserStatus($statusName ?? '');
            if ($userStatus) {
                $userUpdateData['status'] = $userStatus;
            }

            // Map employee role to user role (only if role exists)
            if ($roleName) {
                $userUpdateData['role_name'] = $roleName;
            }

            // Update user record
            $user->update($userUpdateData);

            Log::info("User updated from employee sync", [
                'user_id' => $user->user_id,
                'updated_fields' => array_keys($userUpdateData),
                'role_updated' => $roleName ? 'Yes' : 'No',
                'new_role' => $roleName
            ]);

            // Sync with attendance system
            $this->syncToAttendanceSystem($employee, $user, $departmentName, $designationName, $userStatus, $roleName);

        } catch (\Exception $e) {
            Log::error("Failed to update user from employee", [
                'employee_id' => $employee->id,
                'user_id' => $user->user_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Map employee status to user status
     */
    protected function mapEmployeeStatusToUserStatus($employeeStatus)
    {
        $statusMap = [
            'Active' => 'Active',
            'Inactive' => 'Inactive',
            'Terminated' => 'Inactive',
            'Resigned' => 'Inactive',
            'On Leave' => 'Active', // Still active but on leave
            'Probation' => 'Active', // Active but in probation
        ];

        return $statusMap[$employeeStatus] ?? null;
    }

    /**
     * Sync employee changes to attendance system
     */
    protected function syncToAttendanceSystem($employee, $user, $departmentName, $designationName, $userStatus, $roleName)
    {
        try {
            // Prepare sync data for attendance system with ALL required fields
            $syncData = [
                'user_id' => $user->user_id,
                'payroll_id' => (string) $employee->id, // Send employee primary key as payroll_id
                'payroll_user_id' => $user->id, // Send user primary key as payroll_user_id
                'name' => $employee->name,
                'email' => $employee->email,
                'phone' => $employee->contact_number ?? '',
                'department' => $departmentName ?? '',
                'department_id' => $employee->department, // Include department_id
                'designation' => $designationName ?? '',
                'status' => $userStatus ?? 'Active',
                'role_name' => $roleName ?? 'Employee',
                'join_date' => $employee->date_of_joining,
                'date_of_joining' => $employee->date_of_joining, // Include date_of_joining
                'reporting_manager_id' => $employee->reporting_manager_id, // Include reporting_manager_id
            ];

            // Use reflection to access the private sync method from UserManagementController
            $userController = new \App\Http\Controllers\UserManagementController();
            $syncMethod = new \ReflectionMethod($userController, 'syncUserWithAttendance');
            $syncMethod->setAccessible(true);

            $syncResult = $syncMethod->invoke($userController, $syncData, 'update', $user->user_id);

            if ($syncResult) {
                Log::info("Successfully synced employee changes to attendance system", [
                    'employee_id' => $employee->id,
                    'user_id' => $user->user_id
                ]);
            } else {
                Log::warning("Failed to sync employee changes to attendance system", [
                    'employee_id' => $employee->id,
                    'user_id' => $user->user_id
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Error syncing employee changes to attendance system", [
                'employee_id' => $employee->id,
                'user_id' => $user->user_id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }
    }
}
