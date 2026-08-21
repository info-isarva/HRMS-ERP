<?php

namespace App\Services;

use App\Models\EmployeeBasicDetail;
use App\Models\EmployeeExitDetail;
use App\Models\EmployeeStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExitProcessorService
{
    /**
     * Process all approved exit records whose last working day has passed.
     */
    public function processApprovedExits()
    {
        $today = now()->toDateString();
        
        $pendingExits = EmployeeExitDetail::where('status', 'Approved')
            ->whereDate('last_working_day', '<=', $today)
            ->get();
            
        if ($pendingExits->isEmpty()) {
            return;
        }

        foreach ($pendingExits as $exitRequest) {
            DB::beginTransaction();
            try {
                // Update Exit Status to Completed
                $exitRequest->status = 'Completed';
                $exitRequest->save();

                // Get related employee
                $employee = EmployeeBasicDetail::find($exitRequest->emp_id);
                if ($employee) {
                    // Update resignation date if not set
                    $employee->date_of_resignation = $exitRequest->resignation_date;
                    
                    // Find 'Left' or 'Resigned' status ID dynamically
                    $leftStatus = EmployeeStatus::where('status_name', 'like', '%Resign%')
                        ->orWhere('status_name', 'like', '%Left%')
                        ->orWhere('status_name', 'like', '%Exit%')
                        ->first();
                        
                    if ($leftStatus) {
                        $employee->status = $leftStatus->id;
                    }
                    $employee->save();

                    // Disable User Login
                    $user = User::where('employee_id', $employee->id)->first();
                    if ($user) {
                        $user->status = 'Inactive';
                        $user->save();
                        
                        // Sync with Attendance
                        $this->syncUserToAttendance($user);
                    }
                }

                DB::commit();
                Log::info("Exit request Completed for employee ID: {$exitRequest->emp_id}");
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to auto-complete exit for employee {$exitRequest->emp_id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Sync user to Attendance System
     */
    private function syncUserToAttendance($user)
    {
        try {
            $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
            $apiToken = env('ATTENDANCE_API_TOKEN');

            if (empty($apiUrl) || empty($apiToken)) {
                Log::warning('Attendance Sync skipped: API configuration missing.');
                return;
            }

            $employee = EmployeeBasicDetail::find($user->employee_id);

            $departmentName = $user->department ? DB::table('departments')->where('id', $user->department)->value('department') : '';
            $designationName = $user->position ? DB::table('position_types')->where('id', $user->position)->value('position') : '';

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
                'Authorization' => "Bearer {$apiToken}",
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            $url = rtrim($apiUrl, '/') . '/api/payroll/sync-user';
            
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_map(
                fn($k, $v) => "$k: $v",
                array_keys($headers),
                array_values($headers)
            ));
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error("Failed to sync user status to attendance. HTTP code: {$httpCode}, Response: {$response}");
            }
        } catch (\Exception $e) {
            Log::error('Exception during user sync to attendance: ' . $e->getMessage());
        }
    }
}
