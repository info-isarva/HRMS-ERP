<?php

namespace App\Observers;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(User $user)
    {
        // Sync employee data to attendance (existing functionality)
        $this->syncToAttendance($user, 'create');
        
        // Also sync user data to attendance users table if user has employee_id
        if ($user->employee_id) {
            $this->syncUserToAttendance($user, 'create');
        }
    }

    /**
     * Handle the User "updated" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updated(User $user)
    {
        // Check if relevant fields were updated
        $relevantFields = ['name', 'email', 'employee_id', 'department', 'position', 'line_manager', 'role_name', 'status', 'join_date'];
        $changed = false;
        
        foreach ($relevantFields as $field) {
            if ($user->isDirty($field)) {
                $changed = true;
                break;
            }
        }
        
        if ($changed) {
            // Sync employee data to attendance (existing functionality)
            $this->syncToAttendance($user, 'update');
            
            // Also sync user data to attendance users table if user has employee_id
            if ($user->employee_id) {
                $this->syncUserToAttendance($user, 'update');
            }
        }
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function deleted(User $user)
    {
        // Sync employee deletion to attendance (existing functionality)
        $this->syncToAttendance($user, 'delete');
        
        // Also delete user from attendance users table if user has employee_id
        if ($user->employee_id) {
            $this->syncUserToAttendance($user, 'delete');
        }
    }

    /**
     * Sync user data to attendance module via webhook
     *
     * @param  \App\Models\User  $user
     * @param  string  $action
     * @return void
     */
    private function syncToAttendance(User $user, $action)
    {
        // Only sync employees (users with employee_id)
        if (!$user->employee_id) {
            return;
        }

        try {
            // Use the new webhook service
            $webhookService = app(\App\Services\AttendanceWebhookService::class);
            
            if ($action === 'delete') {
                $result = $webhookService->sendEmployeeDelete($user->employee_id, $user->name);
            } else {
                $result = $webhookService->sendEmployeeUpdate($user, $action);
            }

            if ($result) {
                Log::info("Employee {$action} webhook sent successfully", [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'action' => $action
                ]);
            } else {
                Log::warning("Employee {$action} webhook failed", [
                    'employee_id' => $user->employee_id,
                    'name' => $user->name,
                    'action' => $action
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Exception during employee webhook sync", [
                'employee_id' => $user->employee_id,
                'name' => $user->name,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Sync user data to attendance users table (similar to manual sync)
     *
     * @param  \App\Models\User  $user
     * @param  string  $action
     * @return void
     */
    private function syncUserToAttendance(User $user, $action)
    {
        // Check if user sync is enabled
        $syncEnabled = env('ATTENDANCE_SYNC_ENABLED', true);
        if (!$syncEnabled) {
            Log::info("User sync to attendance is disabled", [
                'action' => $action,
                'user_id' => $user->user_id
            ]);
            return;
        }

        try {
            $attendanceApiUrl = env('ATTENDANCE_API_BASE_URL', 'https://attendancedemo.isarva.in/api');
            $apiToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');

            // Validate configuration
            if (empty($attendanceApiUrl) || empty($apiToken)) {
                Log::warning("Attendance API configuration missing for user sync", [
                    'url' => $attendanceApiUrl,
                    'token_present' => !empty($apiToken)
                ]);
                return;
            }

            // Prepare user data for sync
            $userData = $this->prepareUserDataForSync($user);

            $endpoint = match($action) {
                'create' => '/users/sync-simple',
                'update' => '/users/' . $user->user_id . '/sync-simple',
                'delete' => '/users/' . $user->user_id . '/sync-simple'
            };

            $method = match($action) {
                'create' => 'POST',
                'update' => 'PUT',
                'delete' => 'DELETE'
            };

            $fullUrl = rtrim($attendanceApiUrl, '/') . $endpoint;

            Log::info("Attempting to sync user to attendance", [
                'action' => $action,
                'url' => $fullUrl,
                'method' => $method,
                'user_id' => $user->user_id,
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'HRMS-Payroll-UserSync/1.0'
            ])
            ->timeout(10)
            ->connectTimeout(5)
            ->$method($fullUrl, $userData);

            if ($response->successful()) {
                Log::info("User {$action} synced to attendance successfully", [
                    'user_id' => $user->user_id,
                    'response' => $response->json()
                ]);
            } else {
                Log::warning("Failed to sync user {$action} to attendance", [
                    'user_id' => $user->user_id,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
            }

        } catch (\Exception $e) {
            Log::error("Exception during user sync to attendance", [
                'user_id' => $user->user_id,
                'action' => $action,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Prepare user data for attendance sync
     */
    private function prepareUserDataForSync(User $user)
    {
        // Get department ID from employee record
        $employee = $user->employee;
        $departmentId = null;
        $departmentName = $user->department;
        $designationName = $user->position;
        
        if ($employee && $employee->departmentObj) {
            $departmentId = $employee->departmentObj->id; // Use actual department ID from payroll
            $departmentName = $employee->departmentObj->department;
        }
        
        if ($employee && $employee->designationObj) {
            $designationName = $employee->designationObj->position;
        }
        
        return [
            'user_id' => $user->user_id,
            'payroll_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role_name' => $user->role_name,
            'status' => ucfirst($user->status), // Convert 'active' to 'Active', 'inactive' to 'Inactive'
            'department' => $departmentName,
            'department_id' => $departmentId, // Add department_id for api_department_id mapping
            'designation' => $designationName,
            'phone' => $user->phone_number ?? null,
            'password' => null, // Don't sync password for security
            'join_date' => $user->join_date,
            'line_manager' => $user->line_manager,
        ];
    }
}
