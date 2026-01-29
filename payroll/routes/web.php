<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSOAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\FinancialYearSwitchController;
use App\Http\Controllers\IncrementController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ----------- Public Routes -------------- //
Route::get('/', function () {
    $baseUrl    = env('SSO_WORKSPACE_URL');
        return redirect()->away($baseUrl);
});
// 
// Route to trigger scheduler via URL (Alternative to System Cron)


// Route to trigger scheduler via URL (Alternative to System Cron)
Route::get('/run-scheduler-9j2x8', function () {
    try {
        // Run specific command to ensure notifications are processed
        \Illuminate\Support\Facades\Artisan::call('notifications:process-scheduled');
        $output = \Illuminate\Support\Facades\Artisan::output();
        
        \Illuminate\Support\Facades\Log::info('Web-Cron triggered: ' . $output);
        
        return "Scheduler executed at " . now() . "<br>Output:<br>" . nl2br($output);
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Web-Cron failed: ' . $e->getMessage());
        return 'Error: ' . $e->getMessage();
    }
});

// Route::get('/', function () {
//     return view('auth.login');
// });

// Public API Routes for Attendance System Integration (No Authentication Required)
Route::prefix('api/notifications')->group(function () {
    Route::get('/user', [App\Http\Controllers\Api\NotificationApiController::class, 'getUserNotifications'])->name('api.notifications.user.web');
    Route::post('/mark-read', [App\Http\Controllers\Api\NotificationApiController::class, 'markAsRead'])->name('api.notifications.mark-read.web');
    Route::post('/mark-all-read', [App\Http\Controllers\Api\NotificationApiController::class, 'markAllAsRead'])->name('api.notifications.mark-all-read.web');
    Route::get('/statistics', [App\Http\Controllers\Api\NotificationApiController::class, 'getStatistics'])->name('api.notifications.statistics.web');
});

// --------- Authenticated Routes ---------- //
Route::middleware('auth')->group(function () {
    Route::get('home', function () {
        return view('home');
    });
    
    // Notification Routes
    Route::get('/notifications/get', [App\Http\Controllers\NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/all', [App\Http\Controllers\NotificationController::class, 'viewAll'])->name('notifications.all')->middleware('permission:notifications.view');
    Route::get('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'show'])->name('notifications.show');
    
    // Manual Notification Management Routes
    Route::middleware('auth')->group(function () {
        //this will handle all CRUD operations(create, read, update, delete)
        Route::resource('manual-notifications', App\Http\Controllers\ManualNotificationController::class, [
            'parameters' => ['manualNotification' => 'manualNotification']
        ])->middleware([
            'index' => 'permission:manual_notifications.view',
            'create' => 'permission:manual_notifications.create',
            'store' => 'permission:manual_notifications.create',
            'edit' => 'permission:manual_notifications.edit',
            'update' => 'permission:manual_notifications.edit',
            'destroy' => 'permission:manual_notifications.delete'
        ]);
        
        Route::post('/manual-notifications/{manualNotification}/activate', [App\Http\Controllers\ManualNotificationController::class, 'activate'])->name('manual-notifications.activate')->middleware('permission:manual_notifications.edit');
        Route::post('/manual-notifications/{manualNotification}/deactivate', [App\Http\Controllers\ManualNotificationController::class, 'deactivate'])->name('manual-notifications.deactivate')->middleware('permission:manual_notifications.edit');
        Route::get('/manual-notifications/{manualNotification}/analytics', [App\Http\Controllers\ManualNotificationController::class, 'analytics'])->name('manual-notifications.analytics')->middleware('permission:manual_notifications.view');
    });
});

Auth::routes();

Route::group(['namespace' => 'App\Http\Controllers\Auth'],function()
{
     Route::get('/sso-authenticate', [SSOAuthController::class, 'authenticate'])
     ->name('sso.authenticate');
    // -----------------------------login--------------------------------------//
    Route::controller(LoginController::class)->group(function () {
        Route::get('/login', 'login')->name('login');
        Route::post('/login', 'authenticate');
        Route::get('/logout', 'logout')->name('logout');
    });
  
	Route::get('/sso-passive-logout', [LoginController::class, 'ssoPassiveLogout']);
  
    // ------------------------------ Register ---------------------------------//
    Route::controller(RegisterController::class)->group(function () {
        Route::get('/register', 'register')->name('register');
        Route::post('/register','storeUser')->name('register');
    });

    // ----------------------------- Forget Password --------------------------//
    Route::controller(ForgotPasswordController::class)->group(function () {
        Route::get('forget-password', 'getEmail')->name('forget-password');
        Route::post('forget-password', 'postEmail')->name('forget-password');
    });

    // ---------------------------- Reset Password ----------------------------//
    Route::controller(ResetPasswordController::class)->group(function () {
        Route::get('reset-password/{token}', 'getPassword');
        Route::post('reset-password', 'updatePassword');
    });
});

// Route::group(['namespace' => 'App\Http\Controllers', 'middleware' => 'auth'], function() {
//     // Advance Management Routes
//     Route::post('/advance', 'AdvanceController@addAdvance')->name('advance.add')->middleware('permission:employees.edit');
//     Route::get('/advance/{advance}/edit', 'AdvanceController@getAdvance')->middleware('permission:employees.view');
//     Route::put('/advance/{advance}', 'AdvanceController@updateAdvance')->middleware('permission:employees.edit');
//     Route::post('/advance/{advance}/update', 'AdvanceController@updateAdvance')->middleware('permission:employees.edit'); // For the form POST
//     Route::delete('/advance/{advance}', 'AdvanceController@deleteAdvance')->middleware('permission:employees.edit');
//     Route::delete('/advance/{advance}/delete', 'AdvanceController@deleteAdvance')->middleware('permission:employees.edit'); // Alternative route
//     Route::post('/advance/{advance}/close', 'AdvanceController@closeAdvance')->middleware('permission:employees.edit');
//     Route::post('/advance/{advance}/preclose', 'AdvanceController@preCloseAdvance')->middleware('permission:employees.edit');
//     Route::get('/employees/{employee}/advances-partial', 'AdvanceController@getAdvancesPartialView')->middleware('permission:employees.view');
// });

Route::group(['namespace' => 'App\Http\Controllers'],function()
{
    // ------------------------- Main Dashboard ----------------------------//
    Route::controller(HomeController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('/home', 'index')->name('home')->middleware('permission:dashboard.view');
            Route::get('em/dashboard', 'emDashboard')->name('em/dashboard')->middleware('permission:dashboard.view');
        });
    });

    // --------------------------- Lock Screen ----------------------------//
    Route::controller(LockScreen::class)->group(function () {
        Route::get('lock_screen','lockScreen')->middleware('auth')->name('lock_screen');
        Route::post('unlock', 'unlock')->name('unlock');
    });

    // --------------------------- Settings -------------------------------//
    Route::controller(SettingController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            // Route::get('company/settings/page', 'companySettings')->name('company/settings/page'); /** index page */
            // Route::post('company/settings/save', 'saveRecord')->name('company/settings/save'); /** save record or update */
            Route::get('company/settings/page', 'companySettings')->name('company/settings/page'); /** index page */
            Route::post('company/settings/save', 'saveRecord')->name('company/settings/save'); /** save record or update */
            Route::get('roles/permissions/page', 'rolesPermissions')->name('roles/permissions/page');
            Route::post('roles/permissions/save', 'addRecord')->name('roles/permissions/save');
            Route::post('roles/permissions/update', 'editRolesPermissions')->name('roles/permissions/update');
            Route::post('roles/permissions/delete', 'deleteRolesPermissions')->name('roles/permissions/delete');
            
            // Permission Management Routes
            Route::get('permissions/manage', 'permissionManagement')->name('permissions.manage');
            Route::post('permissions/save', 'savePermission')->name('permissions.save');
            Route::post('permissions/update', 'updatePermission')->name('permissions.update');
            Route::post('permissions/delete', 'deletePermission')->name('permissions.delete');
            Route::get('permissions/get/{id}', 'getPermission')->name('permissions.get');
            
            Route::get('localization/page', 'localizationIndex')->name('localization/page'); /** index page localization */
            Route::get('salary/settings/page', 'salarySettingsIndex')->name('salary/settings/page'); /** index page salary settings */
            Route::post('salary/settings/save', 'saveSalarySettings')->name('salary/settings/save'); /** save salary settings */
            Route::get('email/settings/page', 'emailSettingsIndex')->name('email/settings/page'); /** index page email settings */
            
            // // Master Settings route referenced in sidebar
            // Route::get('settings', 'masterSettingsIndex')->name('settings.index'); /** master settings index page */
            // Route::post('settings/save', 'saveMasterSettings')->name('settings.save'); /** save master settings */
        });
    });
//Master Settings Only for Super Admin
     Route::controller(SettingsController::class)->group(function () {
        Route::middleware('auth')->group(function () {
                Route::get('master-settings', 'index')->name('settings.index'); /** master settings index page */
                Route::post('master-settings/save', 'update')->name('settings.update'); /** save master settings */
            });
    });

    // --------------------------- Activity Logs -------------------------------//
    Route::middleware('auth')->controller(ActivityLogController::class)->group(function () {
        Route::get('activity-logs', 'index')->name('activity-logs')->middleware('permission:activityLogs.view');
        Route::match(['GET', 'POST'], 'activity-logs/data', 'getActivityLogsData')->name('activity-logs.data')->middleware('permission:activityLogs.view');
        Route::post('activity-logs/clear', 'clearLogs')->name('activity-logs.clear')->middleware('permission:activityLogs.clear');
        Route::get('activity-logs/export', 'export')->name('activity-logs.export')->middleware('permission:activityLogs.export');
        Route::post('activity-logs/details', 'getActivityLogDetails')->name('activity-logs.details')->middleware('permission:activityLogs.details');
        Route::post('activity-logs/cleanup', 'cleanup')->name('activity-logs.cleanup')->middleware('permission:activityLogs.clear');
    });

    // --------------------------- Form Management Routes ---------------------------//
    Route::middleware('auth')->group(function () {
        // Department Management
        Route::controller(DepartmentController::class)->group(function () {
            Route::get('form/department/manage', 'index')->name('form/department/manage')->middleware('permission:department.index');
            Route::post('form/department/save', 'store')->name('form/department/save')->middleware('permission:department.save');
            Route::post('form/department/update', 'update')->name('form/department/update')->middleware('permission:department.edit');
            Route::post('form/department/delete', 'destroy')->name('form/department/delete')->middleware('permission:department.delete');
            Route::get('form/department/get/{id}', 'getById')->name('form/department/get')->middleware('permission:department.edit');
        });

        // Designation Management
        Route::controller(DesignationController::class)->group(function () {
            Route::get('form/designation/manage', 'index')->name('form/designation/manage')->middleware('permission:designation.index');
            Route::post('form/designation/save', 'store')->name('form/designation/save')->middleware('permission:designation.save');
            Route::post('form/designation/update', 'update')->name('form/designation/update')->middleware('permission:designation.edit');
            Route::post('form/designation/delete', 'destroy')->name('form/designation/delete')->middleware('permission:designation.delete');
            Route::get('form/designation/get/{id}', 'getById')->name('form/designation/get')->middleware('permission:designation_edit.view');
        });

        // Role Management
        Route::controller(RoleController::class)->group(function () {
            Route::get('form/role/manage', 'index')->name('form/role/manage')->middleware('permission:employee_role.view');
            Route::post('form/role/save', 'store')->name('form/role/save')->middleware('permission:employee_role.save');
            Route::post('form/role/update', 'update')->name('form/role/update')->middleware('permission:employee_role.edit');
            Route::post('form/role/delete', 'destroy')->name('form/role/delete')->middleware('permission:employee_role.delete');
            Route::get('form/role/get/{id}', 'getById')->name('form/role/get')->middleware('permission:employee_role.edit');
        });

        // Employee Status Management
        Route::controller(EmployeeStatusController::class)->group(function () {
            Route::get('form/employee-status/manage', 'index')->name('form/employee-status/manage')->middleware('permission:employee_status.view');
            Route::post('form/employee-status/save', 'store')->name('form/employee-status/save')->middleware('permission:employee_status.save');
            Route::post('form/employee-status/update', 'update')->name('form/employee-status/update')->middleware('permission:employee_status.edit');
            Route::post('form/employee-status/delete', 'destroy')->name('form/employee-status/delete')->middleware('permission:employee_status.delete');
            Route::get('form/employee-status/get/{id}', 'getById')->name('form/employee-status/get')->middleware('permission:employee_status.edit');
        });

        // Document Type Management
        Route::controller(DocumentTypeController::class)->group(function () {
            Route::get('form/document-type/manage', 'index')->name('form/document-type/manage')->middleware('permission:employee_doctype.view');
            Route::post('form/document-type/save', 'store')->name('form/document-type/save')->middleware('permission:employee_doctype.save');
            Route::post('form/document-type/update', 'update')->name('form/document-type/update')->middleware('permission:employee_doctype.edit');
            Route::post('form/document-type/delete', 'destroy')->name('form/document-type/delete')->middleware('permission:employee_doctype.delete');
            Route::get('form/document-type/get/{id}', 'getById')->name('form/document-type/get')->middleware('permission:employee_doctype.edit');
        });

        // Location Management
        Route::controller(App\Http\Controllers\LocationController::class)->group(function () {
            Route::get('form/location/manage', 'index')->name('form/location/manage')->middleware('permission:designation.index');
            Route::post('form/location/save', 'store')->name('form/location/save')->middleware('permission:designation.save');
            Route::post('form/location/update', 'update')->name('form/location/update')->middleware('permission:designation.edit');
            Route::post('form/location/delete', 'destroy')->name('form/location/delete')->middleware('permission:designation.delete');
            Route::get('form/location/get/{id}', 'getById')->name('form/location/get')->middleware('permission:designation_edit.view');
        });
    });

    // --------------------------- Manage Users ---------------------------//
    Route::controller(UserManagementController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('profile_user', 'profile')->name('profile_user');
            Route::post('profile/information/save', 'profileSave')->name('profile/information/save');
            //Route::post('profile/information/save', 'profileInformation')->name('profile/information/save');
            Route::get('userManagement', 'index')->name('userManagement')->middleware('permission:userManagement');
            Route::post('user/add/save', 'addNewUserSaveWithSync')->name('user/add/save')->middleware('permission:user_management.add');
            Route::post('update', 'update')->name('update')->middleware('permission:user_management.edit');
            Route::post('user/delete', 'delete')->name('user/delete')->middleware('permission:user_management.delete');
            Route::get('change/password', 'changePasswordView')->name('change/password');
            Route::post('change/password/update', 'updatePasswordWithSync')->name('change/password/update');
            Route::post('change/password/db', 'changePasswordDBWithSync')->name('change/password/db');
            Route::post('change/password/db', 'changePasswordDB')->name('change/password/db')->middleware('permission:user_management.profile_password_change');
            Route::post('user/profile/emergency/contact/save', 'emergencyContactSaveOrUpdate')->name('user/profile/emergency/contact/save')->middleware('permission:user_management.edit'); /** save or update emergency contact */
            Route::get('get-users-data', 'getUsersData')->name('get-users-data'); /** get all data users */
            Route::get('get-user-details', 'getUserDetails')->name('get-user-details'); /** get user details for edit form */
        });
    });

    // Manual Notifications Data Route
    Route::middleware(['auth'])->group(function () {
        Route::match(['GET', 'POST'], 'get-manual-notifications-data', [App\Http\Controllers\ManualNotificationController::class, 'getData'])->name('get-manual-notifications-data');
    });

    // User Management with Attendance Sync Routes
    Route::controller(UserManagementController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            // New sync routes
            Route::post('/user/add/save/sync', 'addNewUserSaveWithSync')->name('user/add/save/sync')->middleware('permission:user_management.add');
            Route::put('/user/update/sync', 'updateUserWithSync')->name('user/update/sync')->middleware('permission:user_management.edit');
            Route::delete('/user/delete/sync', 'deleteUserWithSync')->name('user/delete/sync')->middleware('permission:user_management.delete');
            
            // Keep existing routes for backward compatibility
            Route::post('user/add/save', 'storeEmployeeWithSync')->name('user/add/save');
            Route::post('update', 'updateEmployeeWithSync')->name('update');
            Route::post('user/delete', 'deleteEmployeeWithSync')->name('user/delete');
            
            // Existing routes  
            Route::get('userManagement', 'index')->name('userManagement')->middleware('permission:userManagement');
            Route::match(['GET', 'POST'], 'get-users-data', 'getUsersData')->name('get-users-data');
            
            // Keep the old employee sync routes
            Route::post('/employees/store-with-sync', 'storeEmployeeWithSync')->name('employees.store-with-sync');
            Route::put('/employees/{id}/update-with-sync', 'updateEmployeeWithSync')->name('employees.update-with-sync');
            Route::delete('/employees/{id}/delete-with-sync', 'deleteEmployeeWithSync')->name('employees.delete-with-sync');
            
            // User sync routes moved to UserSyncController - see below
        });    });

    // --------------------------- User Sync Controller  Manual Sync ---------------------------//
    Route::controller(UserSyncController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('users/sync', 'index')->name('users.sync');
            Route::get('users/sync/all', 'syncAllUsers')->name('users.sync.all');
            Route::post('users/sync/execute', 'executeSyncUsers')->name('users.sync.execute');
            Route::get('users/sync/status', 'getSyncStatus')->name('users.sync.status');
            Route::post('users/sync/individual', 'syncIndividualUser')->name('users.sync.individual');
        });
    });

    // --------------------------- Bidirectional Password Sync ---------------------------//
    // Password sync endpoint moved to routes/api.php to avoid CSRF protection

       // ---------------- Employee masters with payrollcomponents SKP 2025 ----------------//
    Route::controller(EmployeeController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            // Manage/List employees - requires view permission
            Route::get('employees', 'listEmployees')->name('employees.index')->middleware('permission:employees.index');

            // Create new employee - requires add permission
            Route::get('employees/new', 'createEmployee')->name('employees.new')->middleware('permission:employees.add_create');
            Route::post('employees/save', 'saveEmployee')->name('employees.save')->middleware('permission:employees.add_create');

            // Edit existing employee - requires edit permission
            Route::get('employees/{employee}/edit', 'editEmployee')->name('employees.edit')->middleware('permission:employees.edit_update');
            Route::put('employees/{employee}', 'updateEmployee')->name('employees.update')->middleware('permission:employees.edit_update');

            // Delete employee
           // Route::post('employees/delete', 'deleteEmployee')->name('employees.delete');

            // View single employee
         //   Route::get('employees/view', 'viewEmployee')->name('employees.view');

            Route::get('employee/document/{id}', 'EmployeeController@viewDocument')->name('employees.document.view')->middleware('permission:employees.edit_update');
            Route::delete('employee/document/{id}/delete', 'deleteDocument')->name('employees.document.delete')->middleware('permission:employees.edit_update');

            //joining letter pdf - requires view permission
            Route::get('employee/{employee}/joining-letter', 'joiningLetterPDF')->name('employee.joining-letter')->middleware('permission:employees.joining_letter');
            Route::get('employee/{employee}/offer-letter', 'offerLetterPDF')->name('employee.offer-letter')->middleware('permission:employees.offer_letter');
            Route::get('employee/{employee}/experience-letter', 'experienceLetterPDF')->name('employee.experience-letter')->middleware('permission:employees.experience_letter');

            // Leave allocation management (AJAX endpoints)
            Route::post('employees/leave-types/department', 'getDepartmentLeaveTypes')->name('employees.leave-types.department');
            Route::post('employees/leave-types/sync', 'syncLeaveTypes')->name('employees.leave-types.sync');
            Route::get('employees/leave-types/test-api', 'testLeaveTypeAPI')->name('employees.leave-types.test-api');

            // Exit Employee Module
            Route::resource('exit-employees', \App\Http\Controllers\ExitEmployeeController::class);
            Route::post('exit-employees/rehire', [\App\Http\Controllers\ExitEmployeeController::class, 'processRehire'])->name('exit-employees.rehire');
            Route::post('exit-employees/calculate-ffs', [\App\Http\Controllers\ExitEmployeeController::class, 'calculateFFS'])->name('exit-employees.calculate-ffs');
        });
    });


    // ------------------------ Payroll New SKP 22-05-2025 -----------------------//

    Route::middleware('auth')->group(function () {
        Route::controller(PayrollController::class)->group(function () {
            Route::get('payroll', 'index')->name('payroll.index')->middleware('permission:payroll.index');
            Route::get('/check-payroll-status', 'checkPayrollStatus')->name('checkPayroll.Status');
            Route::get('/get-payroll-month-summary', 'getMonthStatusSummary')->name('payroll.month-summary');
            Route::post('payroll', 'store')->name('payroll.store')->middleware('permission:payroll.index');
            Route::get('payroll/attendance/{month}/{year}', 'attendance')->name('payroll.attendance')->middleware('permission:payroll.attendance');
            Route::post('payroll/attendance/{month}/{year}/save', 'saveAttendance')->name('payroll.save-attendance')->middleware('permission:payroll.attendance');
            Route::get('payroll/salary-breakdown/{month}/{year}', 'salaryBreakdown')->name('payroll.salary-breakdown')->middleware('permission:payroll.salary-breakdown');
            //For payroll comparison view
            Route::get('payroll/comparison/{month}/{year}', 'comparison')->name('payroll.comparison')->middleware('permission:payroll.salary-breakdown');
            //For saving manual override components
            Route::post('/payroll/save-component-override', 'saveComponentOverride')->name('payroll.save-component-override')->middleware('permission:payroll.save-component-override');
            //For finalize payroll month to generate
            Route::post('/payroll/finalize/{month}/{year}', 'finalize')->name('payroll.finalize')->middleware('permission:payroll.finalize');
			
			//For Payslip 
            Route::get('payslip/employee-list/{month?}/{year?}', 'paySlipsList')->name('payroll/employee-list')->middleware('permission:payslip.view');
            Route::get('payslip/employee-list', 'payrollList')->name('payroll.attendance-list.get')->middleware('permission:payslip.view');
            Route::post('payslip/employee-list', 'payrollList')->name('payroll.attendance-list')->middleware('permission:payslip.view');
            
            Route::get('payroll/payslip', 'payslip')->name('payroll/payslip')->middleware('permission:payroll.view');
            // Route::get('payroll/payslip-pdf', 'payslip_pdf')->name('payroll/payslip-pdf');
            // Route::get('payroll/payslip', 'payslip')->name('payroll/payslip');
            Route::get('payroll/payslip-pdf/{employee}/{month}/{year}', 'payslip_pdf')->name('payroll.payslip-pdf')->middleware('permission:payroll.payslip-pdf');
            //For sending salary slip via email
            Route::post('payroll/send-salary-slip', 'sendSalarySlipEmail')->name('payroll.send-salary-slip');
            //For sending salary slips to all employees (bulk)
            Route::post('payroll/send-all-salary-slips', 'sendAllSalarySlips')->name('payroll.send-all-salary-slips');
            //canara bank xlsx
            Route::get('payroll/bank-excel/{month}/{year}', 'downloadBankTransferExcel')->name('payroll.bank-excel')->middleware('permission:payroll.bank-download');
            //canara bank csv
            Route::get('payroll/bank-csv/{month}/{year}', 'downloadBankTransferCsv')->name('payroll.bank-csv')->middleware('permission:payroll.bank-download');
            //icici bank xlsx
            Route::get('payroll/bank-icici-xlsx/{month}/{year}', 'downloadBankTransferICICI')->name('payroll.bank-icici-xlsx')->middleware('permission:payroll.bank-download');
            //For updating early salary processed status
            Route::post('/payroll/update-early-salary-processed', 'updateEarlySalaryProcessed')->name('payroll.update-early-salary-processed')->middleware('permission:payroll.update-early-salary-processed');
            Route::get('payroll/epf-excel-csv/{month}/{year}', 'epfExcelORCSV')->name('payroll.epf-excel-csv')->middleware('permission:payroll.epf-esi-download');
            Route::get('payroll/generateESIExcel/{month}/{year}', 'generateESIExcel')->name('payroll.epf-excel-csv')->middleware('permission:payroll.epf-esi-download');
            
            // Bulk Actions for Process View
            Route::post('payroll/export-bank-bulk', 'exportBankBulk')->name('payroll.export-bank-bulk')->middleware('permission:payroll.bank-download');
            Route::post('payroll/export-statutory-bulk', 'exportStatutoryBulk')->name('payroll.export-statutory-bulk')->middleware('permission:payroll.epf-esi-download');
            Route::post('payroll/send-payslips-bulk', 'sendPayslipsBulk')->name('payroll.send-payslips-bulk')->middleware('permission:payslip.view');
           

        });

        // ------------------------ Analytical Reports -----------------------//
        Route::controller(ReportsController::class)->group(function () {
            Route::get('reports/payroll-analytics', 'payrollAnalytics')->name('reports.payroll.analytics')->middleware('permission:payroll.analytics.reports');
        });
        
        // Analytical Payroll Comparison Report - handled by ReportsController
        Route::controller(ReportsController::class)->group(function () {
            Route::get('reports/payroll-comparison', 'analyticalPayrollComparison')->name('reports.payroll.comparison')->middleware('permission:payroll.analytics.reports');
            Route::get('reports/payroll-comparison/pdf', 'analyticalPayrollComparisonPDF')->name('reports.payroll.comparison.pdf')->middleware('permission:payroll.analytics.reports');
        });

    });


    // Hold Salary Routes
    Route::controller(App\Http\Controllers\HeldSalaryController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('hold-salary', 'index')->name('hold-salary.index')->middleware('permission:payroll.index');
            Route::get('hold-salary/create', 'create')->name('hold-salary.create')->middleware('permission:payroll.edit');
            Route::post('hold-salary/store', 'store')->name('hold-salary.store')->middleware('permission:payroll.edit');
            Route::get('hold-salary/{id}/edit', 'edit')->name('hold-salary.edit')->middleware('permission:payroll.edit');
            Route::put('hold-salary/{id}', 'update')->name('hold-salary.update')->middleware('permission:payroll.edit');
            Route::get('hold-salary/{id}/release', 'showReleaseForm')->name('hold-salary.release-form')->middleware('permission:payroll.edit');
            Route::post('hold-salary/{id}/release', 'release')->name('hold-salary.release')->middleware('permission:payroll.edit');
            Route::get('hold-salary/process', 'processView')->name('hold-salary.process')->middleware('permission:payroll.index');
        });
    });

    // -------------Payroll components from SKP -------------------//
    //Statutory Settings Routes
    Route::controller(StatutoryComponentController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::prefix('form/statutory-component')->group(function () {
                Route::get('/manage', 'index')->name('form/statutory-component/manage')->middleware('permission:master.statutory_components.view');
                Route::post('/save','store')->name('form/statutory-component/save')->middleware('permission:master.statutory_components.add');
                Route::post('/update', 'update')->name('form/statutory-component/update')->middleware('permission:master.statutory_components.edit');
                Route::post('/delete', 'destroy')->name('form/statutory-component/delete');
                Route::get('/get/{id}', 'getById')->name('form/statutory-component/get');
            });

        });
    });
    // Salary Settings Routes
    Route::controller(SalaryComponentController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::prefix('salary/structure')->group(function () {
                Route::get('/', 'SalaryStructureConfigController@index')->name('salary.structure.index');
                Route::post('/save', 'SalaryStructureConfigController@store')->name('salary.structure.save');
                Route::get('/get-configs', 'SalaryStructureConfigController@getConfigs')->name('salary.structure.get');
            });

            Route::prefix('form/salary-component')->group(function () {
                Route::get('/manage', 'index')->name('form/salary-component/manage')->middleware('permission:master.salary_components.view');
                Route::post('/save','store')->name('form/salary-component/save')->middleware('permission:master.salary_components.add');
                Route::post('/update', 'update')->name('form/salary-component/update')->middleware('permission:master.salary_components.edit');
                Route::post('/delete', 'destroy')->name('form/salary-component/delete');
                Route::get('/get/{id}', 'getById')->name('form/salary-component/get');
            });
            
             // Increment & Promotion Routes
            Route::resource('increments', IncrementController::class);
            Route::get('increments/get-employee/{id}', [IncrementController::class, 'getEmployeeDetails']);
            Route::delete('increments/{id}/revert', [IncrementController::class, 'revert'])->name('increments.revert');
            Route::get('increments/history/{employeeId}', [IncrementController::class, 'getHistoryPartial'])->name('increments.history.partial');

        });
    });

	//OT and Incentive routes
    Route::controller(OtIncentiveController::class)->group(function () {
        Route::middleware('auth')->group(function () {
                Route::prefix('ot-incentive')->group(function () {
                Route::get('/',  'index')->name('ot-incentive.index')->middleware('permission:ot_incentive.view');
              //  Route::get('/ot/{month}/{year}', 'showOtForm')->name('ot-incentive.ot');
                Route::get('/incentive/{month}/{year}', 'showIncentiveForm')->name('ot-incentive.incentive')->middleware('permission:incentive_generate.view');
              //  Route::post('/ot/{month}/{year}/save',  'saveOt')->name('ot-incentive.save-ot');
                Route::post('/incentive/{month}/{year}/save', 'saveIncentive')->name('ot-incentive.save-incentive')->middleware('permission:payroll.edit');
                Route::post('/ot/{month}/{year}/finalize', 'finalizeOt')->name('ot-incentive.finalize-ot')->middleware('permission:payroll.edit');
                Route::post('/incentive/{month}/{year}/finalize', 'finalizeIncentive')->name('ot-incentive.finalize-incentive')->middleware('permission:payroll.edit');
                Route::post('/ot-incentive/get-month-status', 'getMonthStatus')->name('ot-incentive.get-month-status');
                // web.php

                // Show OT & Holiday form
                Route::get('/ot-incentive/ot/{month}/{year}',  'showOtForm')
                ->name('ot-incentive.ot')->middleware('permission:ot_incentive.view');

                // Save OT & Holiday data
                Route::post('/ot-incentive/save-ot-holiday/{month}/{year}',  'saveOtAndHoliday')
                ->name('ot-incentive.save-ot-holiday')->middleware('permission:payroll.edit');

                Route::get('ot-incentive/downloadOtAndHolidayCSV/{month}/{year}', 'downloadOtAndHolidayCSV')->name('ot-incentive.ot_and_sunday_csv_download')->middleware('permission:payroll.view');

                Route::get('ot-incentive/downloadIncentiveCSV/{month}/{year}', 'downloadIncentiveCSV')->name('ot-incentive.incentive_csv_download')->middleware('permission:payroll.view');
            });
        });
    }); 

	// ---------------------------- Reports  SKP 13.06.2025  ----------------------------//    
     Route::controller(ReportsController::class)->group(function () {
        Route::middleware('auth')->group(function () {        
            Route::prefix('payroll-reports')->group(function () {
                Route::get('/', 'payrollReport')->name('payroll.reports.index')->middleware('permission:payroll_reports.view');
                Route::match(['GET', 'POST'], '/generate', 'generatePayrollReport')->name('payroll.reports.generate')->middleware('permission:payroll_reports.view');
                Route::get('/export','exportPayrollReport')->name('payroll.reports.export')->middleware('permission:payroll_reports.export');
                Route::get('/export-excel','exportPayrollReportExcel')->name('payroll.reports.export-excel')->middleware('permission:payroll_reports.export');
            });
 
            /** Overtime Reports SK -17.06.2025 */
             Route::prefix('overtime-reports')->group(function () {
                Route::get('/', 'overTimeReport')->name('overtime.reports.index')->middleware('permission:overtime_reports.view');
                Route::post('/generate', 'generateOvertimeReport')->name('overtime.reports.generate')->middleware('permission:reports.view');
                Route::get('/export','exportOvertimeReport')->name('overtime.reports.export')->middleware('permission:overtime_reports.export');
            });

            /** Incentive Reports SK -20.06.2025 */
             Route::prefix('incentive-reports')->group(function () {
                Route::get('/', 'incentiveReport')->name('incentive.reports.index')->middleware('permission:incentive_reports.view');
                Route::post('/generate', 'generateIncentiveReport')->name('incentive.reports.generate')->middleware('permission:reports.view');
                Route::get('/export','exportIncentiveReport')->name('incentive.reports.export')->middleware('permission:incentive_reports.export');
            });

            Route::prefix('combined-reports')->group(function () {
                Route::get('/', 'combinedReport')->name('combined.reports.index')->middleware('permission:combined_reports.view');
                Route::get('/export', 'exportCombinedReport')->name('combined.reports.export')->middleware('permission:combined_reports.export');
            });

            /** Payroll Comparison Reports */
            Route::prefix('comparison-reports')->group(function () {
                Route::get('/', 'comparisonReport')->name('payroll.comparison.index')->middleware('permission:comparision_reports.view');
                Route::post('/generate', 'generateComparisonReport')->name('payroll.comparison.generate')->middleware('permission:comparision_reports.view');
                Route::get('/export', 'exportComparisonReport')->name('payroll.comparison.export')->middleware('permission:comparision_reports.export');
            });
        });
    }); 

    // ---------------------------- Reports  ----------------------------//
    Route::controller(ExpenseReportsController::class)->group(function () {
        Route::get('form/expense/reports/page', 'index')->middleware('auth')->middleware('permission:reports.view')->name('form/expense/reports/page');
        Route::get('form/invoice/reports/page', 'invoiceReports')->middleware('auth')->middleware('permission:reports.view')->name('form/invoice/reports/page');
        Route::get('form/daily/reports/page', 'dailyReport')->middleware('auth')->middleware('permission:reports.view')->name('form/daily/reports/page');
        Route::get('form/leave/reports/page','leaveReport')->middleware('auth')->middleware('permission:reports.view')->name('form/leave/reports/page');
        Route::get('form/payments/reports/page','paymentsReportIndex')->middleware('auth')->middleware('permission:reports.view')->name('form/payments/reports/page');
        Route::get('form/employee/reports/page','employeeReportsIndex')->middleware('auth')->middleware('permission:reports.view')->name('form/employee/reports/page');
        Route::get('form/payslip/reports/page','payslipReports')->middleware('auth')->middleware('permission:reports.view')->name('form/payslip/reports/page');
        Route::get('form/attendance/reports/page','attendanceReports')->middleware('auth')->middleware('permission:reports.view')->name('form/attendance/reports/page');
    });



    // ---------------------- Personal Information ----------------------//
    Route::controller(PersonalInformationController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::post('user/information/save', 'saveRecord')->name('user/information/save')->middleware('permission:user_management.edit');
        });
    });


    Route::controller(DashboardController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::get('/home', 'index')->name('home');
            Route::get('/home/analytics-data', 'getAnalyticsData')->name('home.analytics-data');
            Route::get('/home/calendar-events', 'getCalendarEvents')->name('home.calendar-events');
            Route::post('/home/calendar-events/store', 'storeCalendarEvent')->name('home.calendar-events.store');
            Route::post('/home/calendar-events/update', 'updateCalendarEvent')->name('home.calendar-events.update');
            Route::post('/home/calendar-events/delete', 'deleteCalendarEvent')->name('home.calendar-events.delete');
        });
    });
  
  
  
    // Advance Management Routes
    Route::middleware(['auth'])->group(function () {
        Route::prefix('advance')->name('advance.')->controller(App\Http\Controllers\AdvanceController::class)->group(function () {
            Route::get('employee/{employeeId}', 'getEmployeeAdvances')->name('employee');
            Route::post('add', 'addAdvance')->name('add');
            Route::get('{advanceId}/details', 'getAdvanceDetails')->name('details');
            Route::post('{advanceId}/update', 'updateAdvance')->name('update');
            Route::delete('{advanceId}/delete', 'deleteAdvance')->name('delete');
            Route::put('{advanceId}/status', 'updateAdvanceStatus')->name('updateStatus');
            Route::post('override', 'overrideDeduction')->name('override');
            Route::get('{advanceId}/history', 'getAdvanceHistory')->name('history');
            Route::get('report', 'getAdvanceReport')->name('report');
        });
        
        // Routes for employee advances
        Route::get('employees/{employeeId}/advances', [App\Http\Controllers\AdvanceController::class, 'getEmployeeAdvances'])->name('employees.advances');
        Route::get('employees/{employeeId}/advances-partial', [App\Http\Controllers\AdvanceController::class, 'getAdvancesPartialView'])->name('employees.advances.partial');
    });

    
    

});

// ----------------------- Financial Year Management ----------------------//
    Route::controller(FinancialYearController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            // Financial Year CRUD
            Route::get('financial-years', 'index')->name('financial-years.index')->middleware('permission:financial_years.view');
            Route::get('financial-years/create', 'create')->name('financial-years.create')->middleware('permission:financial_years.add');
            Route::post('financial-years', 'store')->name('financial-years.store')->middleware('permission:financial_years.add');
            Route::get('financial-years/{financialYear}', 'show')->name('financial-years.show')->middleware('permission:settings.view');
            Route::post('financial-years/{financialYear}/close', 'close')->name('financial-years.close')->middleware('permission:settings.edit');
            Route::post('financial-years/{financialYear}/set-current', 'setCurrent')->name('financial-years.set-current')->middleware('permission:settings.edit');
            
            // Financial Year Settings
            Route::get('financial-years-settings', 'settings')->name('financial-years.settings')->middleware('permission:financial_years_setting.view');
            Route::post('financial-years-settings', 'updateSettings')->name('financial-years.settings.update')->middleware('permission:financial_years_setting.edit');
            
            // Financial Year Reports
            Route::post('financial-years/{financialYear}/generate-report', 'generateReport')->name('financial-years.generate-report')->middleware('permission:reports.view');
            Route::get('financial-years/reports/{report}/download', 'downloadReport')->name('financial-year.reports.download')->middleware('permission:reports.view');
            
            // Maintenance
            Route::post('financial-years/run-maintenance', 'runMaintenance')->name('financial-years.run-maintenance')->middleware('permission:settings.edit');
        });
    });

    // --------------------------- Financial Year Switching -----------------//
    Route::controller(FinancialYearSwitchController::class)->group(function () {
        Route::middleware('auth')->group(function () {
            Route::post('financial-year/switch', 'switch')->name('financial-year.switch')->middleware('permission:settings.edit');
            Route::get('financial-year/current', 'current')->name('financial-year.current')->middleware('permission:settings.view');
            Route::post('financial-year/reset-to-current', 'resetToCurrent')->name('financial-year.reset-current')->middleware('permission:settings.edit');
        });
    });
