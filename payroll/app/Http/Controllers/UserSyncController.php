<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserSyncController extends Controller
{
    /**
     * Show sync dashboard and status
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Count users with employee_id (linked to employees)
        $userCount = User::whereNotNull('employee_id')->count();
        
        // Get API configuration
        $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
        $apiToken = env('ATTENDANCE_API_TOKEN');
        $syncEnabled = env('ATTENDANCE_SYNC_ENABLED', false);
        
        return view('sync.users', [
            'userCount' => $userCount,
            'apiConfigured' => (!empty($apiUrl) && !empty($apiToken)),
            'apiUrl' => $apiUrl,
            'syncEnabled' => $syncEnabled,
            'lastSyncDate' => session('last_sync_date')
        ]);
    }
    
    /**
     * Manually trigger sync of all users to attendance
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncAll(Request $request)
    {
        // Get API configuration
        $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
        $apiToken = env('ATTENDANCE_API_TOKEN');
        
        if (empty($apiUrl) || empty($apiToken)) {
            return redirect()->route('users.sync')->with('error', 'API configuration missing. Please check .env file.');
        }
        
        // Get all users with employee associations
        $users = User::whereNotNull('employee_id')->get();
        
        $success = 0;
        $errors = 0;
        
        foreach ($users as $user) {
            try {
                // Get related employee data to ensure all fields are included
                $employee = \App\Models\EmployeeBasicDetail::find($user->employee_id);
                
                // Get department name if user has department ID
                $departmentName = null;
                if ($user->department) {
                    $departmentName = \DB::table('departments')->where('id', $user->department)->value('department');
                }
                
                // Get designation name if user has position ID
                $designationName = null;
                if ($user->position) {
                    $designationName = \DB::table('position_types')->where('id', $user->position)->value('position');
                }
              
              // Map status to Active/Inactive for valid API input
                $apiStatus = 'Inactive';
                $statusInput = $user->status;
                if ($statusInput === 'Active' || $statusInput === '1' || $statusInput === 1) {
                    $apiStatus = 'Active';
                }
                
                // Prepare user data with ALL required fields
                $userData = [
                    'user_id' => $user->user_id,
                    'payroll_id' => (string) $user->employee_id,      // Send employee_id as string
                    'payroll_user_id' => $user->id,          // Send user id as payroll_user_id
                    'name' => $user->name,
                    'email' => $user->email,
                    'role_name' => $user->role_name,
                    //'status' => $user->status,
                    'status' => $apiStatus, // Use mapped status
                    'department' => $departmentName ?? $user->department ?? '',
                    'department_id' => $user->department, // Include department_id
                    'designation' => $designationName ?? $user->position ?? '', // Note: attendance uses 'designation', payroll uses 'position'
                    'phone' => $user->phone_number,
                    'password' => $user->password, // Send the hashed password
                    'join_date' => $user->join_date,
                    'date_of_joining' => $employee ? $employee->date_of_joining : $user->join_date, // Include date_of_joining
                    'reporting_manager_id' => $employee ? $employee->reporting_manager_id : null, // Include reporting_manager_id
                ];                $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $apiToken,
                ];
                
                // Try updating first, if that fails, try creating
                $response = Http::withHeaders($headers)
                    ->put("$apiUrl/users/{$user->user_id}/sync-simple", $userData);
                
                if (!$response->successful() && $response->status() === 404) {
                    // User doesn't exist in attendance, create it
                    $response = Http::withHeaders($headers)
                        ->post("$apiUrl/users/sync-simple", $userData);
                }
                
                if ($response->successful()) {
                    $success++;
                } else {
                    $errors++;
                    Log::error("Failed to sync user to attendance", [
                        'user_id' => $user->user_id,
                        'response' => $response->body(),
                        'status' => $response->status()
                    ]);
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("Exception during manual user sync", [
                    'user_id' => $user->user_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        session(['last_sync_date' => now()]);
        
        if ($errors === 0) {
            return redirect()->route('users.sync')->with('success', "$success users synchronized successfully to attendance.");
        } else {
            return redirect()->route('users.sync')->with('warning', "$success users synchronized successfully, but $errors users failed. Check logs for details.");
        }
    }

    /**
     * Alias for syncAll - for backward compatibility
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function syncAllUsers(Request $request)
    {
        return $this->syncAll($request);
    }

    /**
     * Execute sync users - for AJAX/API calls
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function executeSyncUsers(Request $request)
    {
        try {
            // Get API configuration
            $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
            $apiToken = env('ATTENDANCE_API_TOKEN');
            
            if (empty($apiUrl) || empty($apiToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API configuration missing. Please check .env file.'
                ], 422);
            }

            // Get users with employee_id (those linked to employees)
            $users = User::whereNotNull('employee_id')->get();
            
            if ($users->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No users found to sync'
                ], 404);
            }

            $success = 0;
            $errors = 0;
            $errorDetails = [];

            foreach ($users as $user) {
                try {
                    // Get related employee data to ensure all fields are included
                    $employee = \App\Models\EmployeeBasicDetail::find($user->employee_id);
                    
                    // Get department name if user has department ID
                    $departmentName = null;
                    if ($user->department) {
                        $departmentName = \DB::table('departments')->where('id', $user->department)->value('department');
                    }
                    
                    // Get designation name if user has position ID
                    $designationName = null;
                    if ($user->position) {
                        $designationName = \DB::table('position_types')->where('id', $user->position)->value('position');
                    }
                  
                   // Map status to Active/Inactive for valid API input
                    $apiStatus = 'Inactive';
                    $statusInput = $user->status;
                    if ($statusInput === 'Active' || $statusInput === '1' || $statusInput === 1) {
                        $apiStatus = 'Active';
                    }
                    
                    $userData = [
                        'user_id' => $user->user_id,
                        'payroll_id' => (string) $user->employee_id,      // Send employee_id as string
                        'payroll_user_id' => $user->id,          // Send user id as payroll_user_id
                        'name' => $user->name,
                        'email' => $user->email,
                        'role_name' => $user->role_name,
                        //'status' => $user->status,
                        'status' => $apiStatus, // Use mapped status
                        'department' => $departmentName ?? $user->department ?? '',
                        'department_id' => $user->department, // Include department_id
                        'designation' => $designationName ?? $user->position ?? '',
                        'phone' => $user->phone_number,
                        'password' => $user->password,
                        'join_date' => $user->join_date,
                        'date_of_joining' => $employee ? $employee->date_of_joining : $user->join_date, // Include date_of_joining
                        'reporting_manager_id' => $employee ? $employee->reporting_manager_id : null, // Include reporting_manager_id
                    ];

                    $headers = [
                        'Authorization' => 'Bearer ' . $apiToken,
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                    ];

                    $response = Http::withHeaders($headers)
                        ->put("$apiUrl/users/{$user->user_id}/sync-simple", $userData);
                    
                    if (!$response->successful() && $response->status() === 404) {
                        $response = Http::withHeaders($headers)
                            ->post("$apiUrl/users/sync-simple", $userData);
                    }
                    
                    if ($response->successful()) {
                        $success++;
                    } else {
                        $errors++;
                        $errorDetails[] = [
                            'user_id' => $user->user_id,
                            'name' => $user->name,
                            'error' => $response->body()
                        ];
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $errorDetails[] = [
                        'user_id' => $user->user_id,
                        'name' => $user->name,
                        'error' => $e->getMessage()
                    ];
                }
            }

            session(['last_sync_date' => now()]);
            
            return response()->json([
                'success' => true,
                'message' => "Sync completed: $success successful, $errors failed",
                'data' => [
                    'success_count' => $success,
                    'error_count' => $errors,
                    'total_count' => $users->count(),
                    'errors' => $errorDetails
                ]
            ]);

        } catch (\Exception $e) {
            Log::error("Exception during execute sync users", [
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during sync: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sync status - for AJAX calls
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSyncStatus()
    {
        $userCount = User::whereNotNull('employee_id')->count();
        $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
        $apiToken = env('ATTENDANCE_API_TOKEN');
        $syncEnabled = env('ATTENDANCE_SYNC_ENABLED', false);
        
        return response()->json([
            'success' => true,
            'data' => [
                'user_count' => $userCount,
                'api_configured' => (!empty($apiUrl) && !empty($apiToken)),
                'api_url' => $apiUrl,
                'sync_enabled' => $syncEnabled,
                'last_sync_date' => session('last_sync_date')
            ]
        ]);
    }

    /**
     * Sync individual user - for single user sync
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function syncIndividualUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $user = User::where('id', $request->user_id)
                        ->whereNotNull('employee_id')
                        ->first();
                        
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found or not linked to employee'
                ], 404);
            }

            $apiUrl = env('ATTENDANCE_API_URL', env('ATTENDANCE_API_BASE_URL'));
            $apiToken = env('ATTENDANCE_API_TOKEN');
            
            if (empty($apiUrl) || empty($apiToken)) {
                return response()->json([
                    'success' => false,
                    'message' => 'API configuration missing'
                ], 422);
            }

            // Get related employee data to ensure all fields are included
            $employee = \App\Models\EmployeeBasicDetail::find($user->employee_id);
            
            // Get department name if user has department ID
            $departmentName = null;
            if ($user->department) {
                $departmentName = \DB::table('departments')->where('id', $user->department)->value('department');
            }
            
            // Get designation name if user has position ID
            $designationName = null;
            if ($user->position) {
                $designationName = \DB::table('position_types')->where('id', $user->position)->value('position');
            }

            $userData = [
                'user_id' => $user->user_id,
                'payroll_id' => (string) $user->employee_id,      // Send employee_id as string
                'payroll_user_id' => $user->id,          // Send user id as payroll_user_id
                'name' => $user->name,
                'email' => $user->email,
                'role_name' => $user->role_name,
                'status' => $user->status,
                'department' => $departmentName ?? $user->department ?? '',
                'department_id' => $user->department, // Include department_id
                'designation' => $designationName ?? $user->position ?? '',
                'phone' => $user->phone_number,
                'password' => $user->password,
                'join_date' => $user->join_date,
                'date_of_joining' => $employee ? $employee->date_of_joining : $user->join_date, // Include date_of_joining
                'reporting_manager_id' => $employee ? $employee->reporting_manager_id : null, // Include reporting_manager_id
            ];

            $headers = [
                'Authorization' => 'Bearer ' . $apiToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            $response = Http::withHeaders($headers)
                ->put("$apiUrl/users/{$user->user_id}/sync-simple", $userData);
            
            if (!$response->successful() && $response->status() === 404) {
                $response = Http::withHeaders($headers)
                    ->post("$apiUrl/users/sync-simple", $userData);
            }
            
            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'message' => "User {$user->name} synchronized successfully"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Failed to sync user {$user->name}",
                    'error' => $response->body()
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error("Exception during individual user sync", [
                'user_id' => $request->user_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during sync: ' . $e->getMessage()
            ], 500);
        }
    }
}
