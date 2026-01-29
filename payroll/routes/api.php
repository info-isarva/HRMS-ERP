<?php

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EmployeeController;

// Test route to verify API is working
Route::get('/test', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API is working!',
        'timestamp' => now()
    ]);
});

// Public endpoints (no authentication required for testing)
Route::get('/departments', [EmployeeController::class, 'departments']);
Route::get('/reporting-managers', [EmployeeController::class, 'reportingManagers']);
Route::get('/employees/{id}', [EmployeeController::class, 'show']);
Route::get('/roles', [EmployeeController::class, 'roles']);

Route::post('/login', function (Request $request) {
    
    // Check JWT token in Authorization header
    $authHeader = $request->header('Authorization');
    $expectedJwtToken = env('JWT_SECRET');
    
    if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
        return response()->json(['message' => 'JWT token required'], 401);
    }
    
    $providedToken = substr($authHeader, 7); // Remove 'Bearer ' prefix
    
    if ($providedToken !== $expectedJwtToken) {
        return response()->json(['message' => 'Invalid JWT token'], 401);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Invalid credentials'], 401);
    }

    // Optionally, you can also check the password if needed
    // if (!Hash::check($request->password, $user->password)) {
    //     return response()->json(['message' => 'Invalid credentials'], 401);
    // }

    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json(['token' => $token]);
});

Route::middleware('auth:sanctum')->get('/employees', [EmployeeController::class, 'index']);
Route::middleware('auth:sanctum')->get('/employees/{id}', [EmployeeController::class, 'show']);
Route::middleware('auth:sanctum')->get('/reporting-managers', [EmployeeController::class, 'reportingManagers']);
Route::middleware('auth:sanctum')->get('/departments', [EmployeeController::class, 'departments']);
Route::middleware('auth:sanctum')->get('/roles', [EmployeeController::class, 'roles']);
Route::middleware('auth:sanctum')->get('/settings', [EmployeeController::class, 'settings']);
Route::middleware('auth:sanctum')->get('/settings', [EmployeeController::class, 'settings']);


// Financial Year API endpoints
Route::middleware('api')->group(function () {
    Route::get('/financial-year/current', [\App\Http\Controllers\FinancialYearController::class, 'apiCurrent']);
    Route::get('/financial-year/by-date', [\App\Http\Controllers\FinancialYearController::class, 'apiByDate']);
    Route::post('/financial-year/maintenance', [\App\Http\Controllers\FinancialYearController::class, 'runMaintenance']);
});

// Public financial year endpoints (for attendance system)
Route::get('/financial-year/current', [App\Http\Controllers\FinancialYearController::class, 'apiCurrent']);
Route::get('/financial-year/by-date', [App\Http\Controllers\FinancialYearController::class, 'apiByDate']);

// Password sync endpoint for receiving password changes from attendance system
Route::post('/sync/password/from-attendance', [\App\Http\Controllers\UserManagementController::class, 'syncPasswordFromAttendance']);

// Company Settings API - Single comprehensive endpoint
Route::middleware('auth:sanctum')->get('/company-settings', [\App\Http\Controllers\Api\SettingController::class, 'getCompanySettings']);

// Notification API Routes for Attendance System Integration (Public - No Auth Required)
// Use 'api' prefix but exclude from stateful Sanctum middleware
Route::prefix('notifications')->withoutMiddleware([\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class])->group(function () {
    Route::get('/user', [App\Http\Controllers\Api\NotificationApiController::class, 'getUserNotifications'])->name('api.notifications.user');
    Route::post('/mark-read', [App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead'])->name('api.notifications.mark-read');
    Route::post('/mark-all-read', [App\Http\Controllers\Api\NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read');
    Route::get('/statistics', [App\Http\Controllers\Api\NotificationApiController::class, 'getStatistics'])->name('api.notifications.statistics');
});