<?php
use App\Http\Controllers\BulkAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSyncController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\LeaveTypeController as ApiLeaveTypeController;
use App\Http\Controllers\Api\OvertimeRecordController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PermissionsApiController;

Route::get('/employees', [EmployeeController::class, 'api']);

// JWT Authentication endpoints
Route::post('/login', [ApiAuthController::class, 'login']);
Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [ApiAuthController::class, 'me']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
    Route::post('/refresh', [ApiAuthController::class, 'refresh']);
});

// Leave Types API endpoint (JWT Protected)
Route::middleware('jwt.auth')->get('/leave-types', [ApiLeaveTypeController::class, 'index']);

// Leave Types API endpoint for Payroll Integration (Token Protected)
Route::middleware('api')->get('/payroll/leave-types', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token');
    $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    // Call the same controller method
    $controller = new ApiLeaveTypeController();
    return $controller->index($request);
});

// Attendance Data API endpoint (JWT Protected)
Route::middleware('jwt.auth')->get('/attendance-data', [BulkAttendanceController::class, 'apiAttendanceData']);


// Bulk attendance API endpoint for Payroll Integration (Token Protected)
Route::middleware('api')->get('/payroll/attendance-data', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token');
    $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    // Add the api_key parameter to the request since the controller expects it
    $request->merge(['api_key' => $expectedToken]);
    
    // Use Laravel's service container to resolve the controller with its dependencies
    $controller = app(BulkAttendanceController::class);
    return $controller->apiAttendanceData($request);
});

// Overtime data API endpoint for Payroll Integration (Token Protected)
Route::middleware('api')->get('/payroll/overtime-data', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token');
    $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');

    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }

    // Use Laravel's service container to resolve the controller
    $controller = app(\App\Http\Controllers\OvertimeController::class);
    return $controller->apiData($request);
});

// Payroll API endpoint for attendance data (Token Protected - Legacy)
//Route::middleware('api')->get('/payroll/attendance-data', [BulkAttendanceController::class, 'apiAttendanceData']);

// Password sync endpoint for receiving password changes from payroll system
Route::middleware('api')->post('/sync/password/from-payroll', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token') ?: $request->sync_token;
    $expectedToken = env('PAYROLL_SYNC_TOKEN', 'default-token');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    // Call UserSyncController method with new name
    $controller = new \App\Http\Controllers\UserSyncController();
    return $controller->syncPasswordFromPayrollByEmail($request);
});

// Endpoint to get user permissions for payroll system
Route::middleware('api')->get('/user/permissions/{email}', function(Request $request, $email) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token');
    $expectedToken = env('PAYROLL_SYNC_TOKEN', 'default-token');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    try {
        $user = \App\Models\User::where('email', $email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in attendance system',
                'permissions' => []
            ], 404);
        }
        
        $permissions = [];
        if ($user->permissions_json) {
            $permissions = json_decode($user->permissions_json, true) ?: [];
        }
        
        return response()->json([
            'success' => true,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'permissions' => $permissions
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to get user permissions: ' . $e->getMessage(),
            'permissions' => []
        ], 500);
    }
});

// Endpoint to update user permissions from payroll system
Route::middleware('api')->post('/sync/permissions/from-payroll', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token') ?: $request->sync_token;
    $expectedToken = env('PAYROLL_SYNC_TOKEN', 'default-token');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    try {
        $user = \App\Models\User::where('email', $request->user_email)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in attendance system'
            ], 404);
        }
        
        // Update permissions_json column
        $user->permissions_json = json_encode($request->attendance_permissions ?? []);
        $user->save();
        
        \Illuminate\Support\Facades\Log::info('User permissions updated from payroll', [
            'user_email' => $request->user_email,
            'permissions' => $request->attendance_permissions,
            'updated_by' => 'payroll_system'
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Permissions updated successfully',
            'user_id' => $user->id
        ]);
        
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Failed to update user permissions from payroll', [
            'user_email' => $request->user_email,
            'error' => $e->getMessage()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to update permissions: ' . $e->getMessage()
        ], 500);
    }
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Employee synchronization endpoints for payroll integration
Route::middleware('api')->group(function () {
    Route::post('/employees/sync', [EmployeeSyncController::class, 'apiSync']);
    Route::put('/employees/{employee_id}/sync', [EmployeeSyncController::class, 'apiSync']);
    Route::delete('/employees/{employee_id}/sync', [EmployeeSyncController::class, 'apiSync']);
});

// Employee Sync Webhook - No authentication needed for external webhooks
Route::post('/employee-sync/webhook', [EmployeeSyncController::class, 'webhook'])
      ->name('employee-sync.webhook');

// Department synchronization endpoints for payroll integration
Route::middleware('api')->group(function () {
    Route::post('/departments/sync', [\App\Http\Controllers\DepartmentSyncController::class, 'apiSync']);
    Route::put('/departments/{department_id}/sync', [\App\Http\Controllers\DepartmentSyncController::class, 'apiSync']);
    Route::delete('/departments/{department_id}/sync', [\App\Http\Controllers\DepartmentSyncController::class, 'apiSync']);
});

// Department Sync Webhook - No authentication needed for external webhooks
Route::post('/department-sync/webhook', [\App\Http\Controllers\DepartmentSyncController::class, 'webhook'])
      ->name('department-sync.webhook');

// User synchronization endpoints for payroll integration (keep separate from employees)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/users/verify-token', [\App\Http\Controllers\UserSyncController::class, 'verifyToken']);
    Route::post('/users/sync', [\App\Http\Controllers\UserSyncController::class, 'syncUserFromPayroll']);
    Route::put('/users/{user_id}/sync', [\App\Http\Controllers\UserSyncController::class, 'updateUserFromPayroll']);
    Route::delete('/users/{user_id}/sync', [\App\Http\Controllers\UserSyncController::class, 'deleteUserFromPayroll']);
    Route::post('/users/{user_id}/password', [\App\Http\Controllers\UserSyncController::class, 'syncPasswordFromPayrollByEmail']);
});

// Alternative route for simple token authentication if sanctum is not available
Route::middleware('api')->group(function () {
    Route::post('/users/sync-simple', [\App\Http\Controllers\UserSyncController::class, 'syncUserFromPayroll']);
    Route::put('/users/{user_id}/sync-simple', [\App\Http\Controllers\UserSyncController::class, 'updateUserFromPayroll']);
    Route::delete('/users/{user_id}/sync-simple', [\App\Http\Controllers\UserSyncController::class, 'deleteUserFromPayroll']);
    Route::post('/users/{user_id}/sync-password', [\App\Http\Controllers\UserSyncController::class, 'syncPasswordFromPayrollByEmail']);
});

// Financial Year sync endpoints from payroll
Route::middleware('api')->group(function () {
    Route::post('/financial-year/sync', function (Request $request) {
        // Handle financial year sync from payroll
        try {
            $data = $request->validate([
                'action' => 'required|string',
                'financial_year' => 'required|array',
                'settings' => 'sometimes|array'
            ]);
            
            // Log the sync request
            \Illuminate\Support\Facades\Log::info('Financial year sync received', $data);
            
            // Store financial year data in cache for attendance system to use
            $cacheKey = 'payroll_financial_year';
            $cacheData = [
                'financial_year' => $data['financial_year'],
                'settings' => $data['settings'] ?? [],
                'synced_at' => now()->toISOString(),
                'action' => $data['action'],
            ];
            
            // Cache for 1 year (since FY changes annually)
            \Illuminate\Support\Facades\Cache::put($cacheKey, $cacheData, 60 * 24 * 365);
            
            // Also store current FY separately for quick access
            if ($data['financial_year']['is_current']) {
                \Illuminate\Support\Facades\Cache::put('current_financial_year', $data['financial_year'], 60 * 24 * 365);
                \Illuminate\Support\Facades\Cache::put('financial_year_settings', $data['settings'] ?? [], 60 * 24 * 365);
            }
            
            \Illuminate\Support\Facades\Log::info('Financial year data cached successfully', [
                'financial_year_name' => $data['financial_year']['name'] ?? 'Unknown',
                'is_current' => $data['financial_year']['is_current'] ?? false,
                'action' => $data['action'],
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Financial year synced successfully',
                'data' => [
                    'financial_year_name' => $data['financial_year']['name'] ?? 'Unknown',
                    'cached_at' => now()->toISOString(),
                ]
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Financial year sync error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync financial year: ' . $e->getMessage()
            ], 500);
        }
    });
    
    // Get current financial year for attendance system
    Route::get('/financial-year/current', function (Request $request) {
        try {
            $currentFY = \Illuminate\Support\Facades\Cache::get('current_financial_year');
            $settings = \Illuminate\Support\Facades\Cache::get('financial_year_settings');
            
            if (!$currentFY) {
                return response()->json([
                    'success' => false,
                    'message' => 'No financial year data found. Please sync from payroll system.',
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'financial_year' => $currentFY,
                'settings' => $settings,
                'cached_at' => \Illuminate\Support\Facades\Cache::get('payroll_financial_year')['synced_at'] ?? null,
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Financial year fetch error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch financial year: ' . $e->getMessage()
            ], 500);
        }
    });
});

Route::middleware('jwt.auth')->get('/permissions', [PermissionsApiController::class, 'index'])->name('api.permissions.index');

// Bulk attendance API endpoint for Payroll Integration (Token Protected)
Route::middleware('api')->get('/payroll/attendance-permissions', function(Request $request) {
    // Simple token validation
    $token = $request->header('X-API-Token') ?: $request->get('api_token');
    $expectedToken = env('ATTENDANCE_API_TOKEN', 'hrms_sync_token_2025_secure_key');
    
    if ($token !== $expectedToken) {
        return response()->json([
            'success' => false,
            'message' => 'Invalid API token'
        ], 401);
    }
    
    // Call the same controller method
    $controller = new PermissionsApiController();
    return $controller->index($request);
});