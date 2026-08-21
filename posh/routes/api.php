<?php
use App\Http\Controllers\Api\Mobile\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LeadApiController;

use App\Http\Controllers\Api\Mobile\LoginController;

use App\Http\Controllers\Api\Mobile\GoogleLoginController;

use App\Http\Controllers\Api\Mobile\OrganizationController;
use App\Http\Controllers\Api\Mobile\ContactPersonController;
use App\Http\Controllers\Api\Mobile\CompanyOwnerController;

// Route::get('/leads', [LeadApiController::class, 'create_lead']);
Route::apiResource('leads', LeadApiController::class);

Route::post('/leads/check-duplicate', [LeadApiController::class, 'checkDuplicate']);

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from API']);
});

// Mobile App Login API
Route::post('/mobile/login', [LoginController::class, 'login']);


// Mobile App Google Login API
Route::post('/mobile/google-login', [GoogleLoginController::class, 'login']);


Route::middleware('auth:sanctum')->group(function () {
    
    // Mobile App Contact Person List API (requires token)
    Route::get('/mobile/organizations/{organization}/contacts', [ContactPersonController::class, 'index']);
    // Mobile App Company Owner List API (requires token)
    Route::get('/mobile/organizations/{organization}/owners', [CompanyOwnerController::class, 'index']);

    Route::get('/mobile/other/lead-sources', [App\Http\Controllers\Api\Mobile\OtherController::class, 'leadSourceList']);
    Route::get('/mobile/other/lead-statuses', [App\Http\Controllers\Api\Mobile\OtherController::class, 'leadStatusList']);
    Route::get('/mobile/other/priorities', [App\Http\Controllers\Api\Mobile\OtherController::class, 'priorityList']);
    Route::get('/mobile/other/categories', [App\Http\Controllers\Api\Mobile\OtherController::class, 'categoryList']);
    Route::get('/mobile/other/users', [App\Http\Controllers\Api\Mobile\OtherController::class, 'userList']);
    Route::get('/mobile/other/stages', [App\Http\Controllers\Api\Mobile\OtherController::class, 'dealStageList']);
    Route::get('/mobile/other/user-permissions', [App\Http\Controllers\Api\Mobile\OtherController::class, 'userPermissions']);

    // Mobile App Leads APIs (requires token)
    Route::get('/mobile/leads', [App\Http\Controllers\Api\Mobile\LeadsController::class, 'index']);
    Route::get('/mobile/leads/{lead}', [App\Http\Controllers\Api\Mobile\LeadsController::class, 'show']);
    Route::post('/mobile/leads', [App\Http\Controllers\Api\Mobile\LeadsController::class, 'store']);
    Route::post('/mobile/leads/{lead}', [App\Http\Controllers\Api\Mobile\LeadsController::class, 'update']);
    Route::delete('/mobile/leads/{lead}', [App\Http\Controllers\Api\Mobile\LeadsController::class, 'destroy']);
    
    // Mobile App Deals APIs (requires token)
    Route::get('/mobile/deals', [App\Http\Controllers\Api\Mobile\DealsController::class, 'index']);
    Route::get('/mobile/deals/{deal}', [App\Http\Controllers\Api\Mobile\DealsController::class, 'show']);
    Route::post('/mobile/deals', [App\Http\Controllers\Api\Mobile\DealsController::class, 'store']);
    Route::post('/mobile/deals/{deal}', [App\Http\Controllers\Api\Mobile\DealsController::class, 'update']);
    Route::delete('/mobile/deals/{deal}', [App\Http\Controllers\Api\Mobile\DealsController::class, 'destroy']);
    Route::post('/mobile/deals/store-from-lead/{lead}', [App\Http\Controllers\Api\Mobile\DealsController::class, 'storeFromLead']);

    // Mobile App Organizations APIs (requires token)
    Route::get('/mobile/organizations', [OrganizationController::class, 'index']);
    Route::post('/mobile/organizations', [OrganizationController::class, 'store']);
    Route::get('/mobile/organizations/{organization}', [OrganizationController::class, 'show']);
    Route::post('/mobile/organizations/{organization}', [OrganizationController::class, 'update']);
    Route::delete('/mobile/organizations/{organization}', [OrganizationController::class, 'destroy']);

    // Mobile App Contact Persons APIs (requires token)
    Route::get('/mobile/contacts', [ContactPersonController::class, 'listAllContacts']);
    Route::get('/mobile/contacts/{contact}', [ContactPersonController::class, 'show']);
    Route::post('/mobile/contacts', [ContactPersonController::class, 'store']);
    Route::post('/mobile/contacts/{contact}', [ContactPersonController::class, 'update']);
    Route::delete('/mobile/contacts/{contact}', [ContactPersonController::class, 'destroy']);

    // Mobile App Tasks APIs (requires token)
    Route::get('/mobile/tasks', [App\Http\Controllers\Api\Mobile\TaskController::class, 'index']);
    Route::post('/mobile/tasks/create', [App\Http\Controllers\Api\Mobile\TaskController::class, 'store']);
    Route::post('/mobile/tasks/{id}/mark-as-completed', [App\Http\Controllers\Api\Mobile\TaskController::class, 'markAsCompleted']);
    Route::delete('/mobile/tasks/delete/{id}', [App\Http\Controllers\Api\Mobile\TaskController::class, 'destroy']);
    Route::get('/mobile/tasks/{id}', [App\Http\Controllers\Api\Mobile\TaskController::class, 'show']);
    Route::post('/mobile/tasks/update/{id}', [App\Http\Controllers\Api\Mobile\TaskController::class, 'update']);


    // Mobile App Report APIs (requires token)
    Route::get('/mobile/reports/leads', [ReportController::class, 'leadReport']);
    Route::get('/mobile/reports/deals', [ReportController::class, 'dealReport']);
    Route::get('/mobile/reports/deals/monthly-revenue', [ReportController::class, 'revenueReport']);
    Route::get('/mobile/reports/converted-leads', [ReportController::class, 'convertedLeadsReport']);
    Route::get('/mobile/reports/user-performance', [ReportController::class, 'userPerformanceReport']);
    
});

// Payroll User Sync Endpoints (token-validated in controller, no auth middleware)
Route::post('/users/sync-simple', [\App\Http\Controllers\Api\UserSyncController::class, 'syncUserFromPayroll']);
Route::put('/users/{user_id}/sync-simple', [\App\Http\Controllers\Api\UserSyncController::class, 'updateUserFromPayroll']);
Route::delete('/users/{user_id}/sync-simple', [\App\Http\Controllers\Api\UserSyncController::class, 'deleteUserFromPayroll']);
Route::post('/users/sync-password', [\App\Http\Controllers\Api\UserSyncController::class, 'syncPasswordFromPayroll']);