<?php
use App\Http\Controllers\BulkAttendanceController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EmployeeSyncController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\Mobile\V1\AdminLeaveController as MobileV1AdminLeaveController;
use App\Http\Controllers\Api\Mobile\V1\AuthController as MobileV1AuthController;
use App\Http\Controllers\Api\Mobile\V1\LeaveBalanceController as MobileV1LeaveBalanceController;
use App\Http\Controllers\Api\Mobile\V1\LeaveRequestController as MobileV1LeaveRequestController;
use App\Http\Controllers\Api\Mobile\V1\LeaveTypeController as MobileV1LeaveTypeController;
use App\Http\Controllers\Api\Mobile\V1\ReferenceController as MobileV1ReferenceController;
use App\Http\Controllers\Api\Mobile\V1\DeviceController as MobileV1DeviceController;
use App\Http\Controllers\Api\LeaveTypeController as ApiLeaveTypeController;
use App\Http\Controllers\Api\OvertimeRecordController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\PermissionsApiController;
use App\Http\Controllers\Api\CronPushController;

Route::get('/employees', [EmployeeController::class, 'api']);

// Scheduled push triggers (server cron / wget — token required, not JWT)
Route::prefix('cron/push')->group(function () {
    Route::get('/birthdays', [CronPushController::class, 'birthdays']);
    Route::get('/admin-pending-leaves', [CronPushController::class, 'adminPendingLeaves']);
});

// JWT Authentication endpoints
Route::post('/login', [ApiAuthController::class, 'login']);

// Mobile app API (versioned) — same JWT guard as /api/login
Route::prefix('mobile/v1')->group(function () {
    Route::post('/auth/login', [MobileV1AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/auth/me', [MobileV1AuthController::class, 'me']);
        Route::post('/auth/logout', [MobileV1AuthController::class, 'logout']);
        Route::post('/auth/refresh', [MobileV1AuthController::class, 'refresh']);

        Route::post('/devices/register', [MobileV1DeviceController::class, 'register']);
        Route::delete('/devices', [MobileV1DeviceController::class, 'unregister']);
        Route::get('/devices', [MobileV1DeviceController::class, 'index']);

        Route::get('/financial-years', [MobileV1ReferenceController::class, 'financialYears']);
        Route::get('/holidays', [MobileV1ReferenceController::class, 'holidays']);
        Route::get('/permissions', [MobileV1ReferenceController::class, 'permissions']);

        Route::get('/leave-types', [MobileV1LeaveTypeController::class, 'index']);
        Route::get('/me/leave-balances', [MobileV1LeaveBalanceController::class, 'index']);

        Route::post('/leave-requests/calculate', [MobileV1LeaveRequestController::class, 'calculate']);
        Route::get('/leave-requests', [MobileV1LeaveRequestController::class, 'index']);
        Route::post('/leave-requests', [MobileV1LeaveRequestController::class, 'store']);
        Route::get('/leave-requests/{leave}', [MobileV1LeaveRequestController::class, 'show']);
        Route::put('/leave-requests/{leave}', [MobileV1LeaveRequestController::class, 'update']);
        Route::post('/leave-requests/{leave}/withdraw', [MobileV1LeaveRequestController::class, 'withdraw']);

        Route::get('/admin/leave-requests', [MobileV1AdminLeaveController::class, 'index']);
        Route::get('/admin/leave-requests/{leave}', [MobileV1AdminLeaveController::class, 'show']);
        Route::post('/admin/leave-requests/{leave}/approve', [MobileV1AdminLeaveController::class, 'approve']);
        Route::post('/admin/leave-requests/{leave}/reject', [MobileV1AdminLeaveController::class, 'reject']);
        Route::post('/admin/leave-requests/{leave}/forward', [MobileV1AdminLeaveController::class, 'forward']);
        Route::get('/admin/employees/{employee}/leave-balances', [MobileV1AdminLeaveController::class, 'employeeLeaveBalances']);

        // Mobile Attendance & Correction APIs
        Route::prefix('attendance')->group(function () {
            Route::get('/today-status', [AttendanceController::class, 'todayStatus']);
            Route::post('/check-in', [AttendanceController::class, 'checkIn']);
            Route::post('/check-out', [AttendanceController::class, 'checkOut']);
            Route::get('/monthly-summary', [AttendanceController::class, 'monthlySummary']);
            Route::get('/corrections', [AttendanceController::class, 'correctionsList']);
            Route::post('/correction-request', [AttendanceController::class, 'storeCorrection']);
            Route::post('/monthly-review', [AttendanceController::class, 'storeReview']);
        });
    });
});
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
            \Illuminate\Support\Facades\Log::info('Financial year sync received from payroll', $data);
            
            $fyData = $data['financial_year'];
            $fySettings = $data['settings'] ?? [];
            
            // 1. Sync local 'financial_years' table
            // We use the 'name' as a unique identifier since it's common across systems
            $financialYear = \App\Models\FinancialYear::updateOrCreate(
                ['name' => $fyData['name']],
                [
                    'start_date' => $fyData['start_date'],
                    'end_date' => $fyData['end_date'],
                    'is_active' => $fyData['is_current'] ?? false,
                    'status' => ($fyData['is_closed'] ?? false) ? 'closed' : 'open',
                ]
            );

            // 2. If this is marked current, ensure others are marked inactive
            if ($fyData['is_current'] ?? false) {
                \App\Models\FinancialYear::where('id', '!=', $financialYear->id)
                    ->update(['is_active' => false]);
                
                // 3. Update system setting for FY start month if provided
                if (isset($fySettings['start_month'])) {
                    \App\Models\SystemSetting::set('fy_start_month', $fySettings['start_month']);
                }
                
                // 4. Cache for quick access (existing logic)
                \Illuminate\Support\Facades\Cache::put('current_financial_year', $fyData, 60 * 24 * 365);
                \Illuminate\Support\Facades\Cache::put('financial_year_settings', $fySettings, 60 * 24 * 365);
            }
            
            // Clear local FY cache to reflect changes immediately
            app(\App\Services\FinancialYearService::class)->clearCache();

            \Illuminate\Support\Facades\Log::info('Financial year data synchronized successfully', [
                'name' => $fyData['name'],
                'local_id' => $financialYear->id
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Financial year synced successfully',
                'data' => [
                    'name' => $fyData['name'],
                    'id' => $financialYear->id
                ]
            ]);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Financial year sync error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
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

// GPS field tracking (mobile / Obie app)
Route::middleware('jwt.auth')->prefix('gps')->group(function () {
    Route::post('/ping', [\App\Http\Controllers\Api\GpsTrackingController::class, 'ping']);
    Route::post('/check-in', [\App\Http\Controllers\Api\GpsTrackingController::class, 'checkIn']);
    Route::post('/check-out', [\App\Http\Controllers\Api\GpsTrackingController::class, 'checkOut']);
    Route::get('/timeline', [\App\Http\Controllers\Api\GpsTrackingController::class, 'timeline']);
});

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