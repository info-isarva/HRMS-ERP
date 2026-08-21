<?php
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SSOAuthController;
use App\Http\Controllers\LeaveTypeController;
use App\Http\Controllers\PublicHolidayController;
use App\Http\Controllers\DepartmentHolidayConfigController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\PublicHolidayApplicationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PayslipController;
use App\Http\Controllers\EmailSettingsController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\DutyRosterController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FinancialYearController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\ReportsController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Root route - check authentication first, then redirect appropriately
Route::get('/', function () {
    // If user is already authenticated, redirect to dashboard
    if (Auth::check()) {
        return redirect('/dashboard');
    }
    
    // If not authenticated, check if SSO is enabled
    $ssoWorkspaceUrl = env('SSO_WORKSPACE_URL');
    if ($ssoWorkspaceUrl) {
        // Add redirect parameter to tell SSO where to send user back
        $attendanceUrl = env('APP_URL', 'https://attendancedev.isarva.in');
        $redirectUrl = $ssoWorkspaceUrl . '?redirect=' . urlencode($attendanceUrl);
        return redirect()->away($redirectUrl);
    }
    
    // Fallback to traditional login
    return redirect('/login');
});

// SSO Authentication Routes
Route::get('/sso-authenticate', [SSOAuthController::class, 'authenticate'])->name('sso.authenticate');
Route::post('/sso-passive-logout', [SSOAuthController::class, 'passiveLogout'])->name('sso.passive-logout');

// CSRF Token refresh for AJAX requests
Route::get('/refresh-csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');

// Traditional Authentication Routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/analytics/employee-monthly-leaves', [DashboardController::class, 'getEmployeeMonthlyLeaves'])->name('api.analytics.employee-monthly-leaves');
    Route::get('/home', fn() => redirect('/dashboard'))->name('home'); // Compatibility with old system
    
    // Notification Routes
    Route::get('/notifications/get', [NotificationController::class, 'getNotifications'])->name('notifications.get');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::get('/notifications/all', [NotificationController::class, 'viewAll'])->name('notifications.all');
    Route::get('/notifications/{id}', [NotificationController::class, 'show'])->name('notifications.show');
    
    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/data-change-request', [ProfileController::class, 'submitDataChangeRequest'])->name('profile.data-change-request');

    // Employee payslips (from payroll)
    Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
    Route::get('/payslips/{month}/{year}/view', [PayslipController::class, 'view'])->name('payslips.view')->where(['month' => '[0-9]+', 'year' => '[0-9]+']);
    Route::get('/payslips/{month}/{year}/download', [PayslipController::class, 'download'])->name('payslips.download')->where(['month' => '[0-9]+', 'year' => '[0-9]+']);
    
    // Form 16 (from payroll)
    Route::get('/form16', [\App\Http\Controllers\Form16Controller::class, 'index'])->name('form16.index');
    Route::get('/form16/{year}/download', [\App\Http\Controllers\Form16Controller::class, 'download'])->name('form16.download');
    
    // Advances (from payroll)
    Route::get('/advances', [\App\Http\Controllers\AdvanceController::class, 'index'])->name('advances.index');
    
    // POSH Act Compliance Routes (deprecated — Phase 0 redirects when POSH_LEGACY_ENABLED=false)
    Route::middleware('posh.legacy.block')->controller(\App\Http\Controllers\PoshComplianceController::class)->group(function () {
        Route::get('/compliance/posh', 'index')->name('compliance.posh.index');
        Route::post('/compliance/posh/complaint', 'storeComplaint')->name('compliance.posh.complaint.store');
        Route::get('/compliance/posh/complaint/{id}', 'showComplaint')->name('compliance.posh.complaint.show');
    });
    
    // Test route - TEMPORARY
    Route::get('/test-reporting-relationships', [\App\Http\Controllers\TestController::class, 'createTestUsers']);
    
    // Bulk Attendance Management (Admin and Super Admin Only)
    Route::middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/admin/gps-tracking', [\App\Http\Controllers\Admin\GpsTrackingController::class, 'index'])->name('admin.gps-tracking.index');
        Route::get('/admin/gps-tracking/data', [\App\Http\Controllers\Admin\GpsTrackingController::class, 'trackingData'])->name('admin.gps-tracking.data');

        Route::get('/attendance/bulk', [\App\Http\Controllers\BulkAttendanceController::class, 'index'])->name('admin.attendance.index');
        Route::get('/attendance/bulk/preview', [\App\Http\Controllers\BulkAttendanceController::class, 'preview'])->name('admin.attendance.preview');
        Route::post('/attendance/bulk/update-record', [\App\Http\Controllers\BulkAttendanceController::class, 'updateRecord'])->name('admin.attendance.update-record');
        Route::post('/attendance/bulk/revert-record', [\App\Http\Controllers\BulkAttendanceController::class, 'revertRecord'])->name('admin.attendance.revert-record');
        Route::post('/attendance/bulk/regenerate', [\App\Http\Controllers\BulkAttendanceController::class, 'regenerate'])->name('admin.attendance.regenerate');
        Route::post('/attendance/bulk/save', [\App\Http\Controllers\BulkAttendanceController::class, 'save'])->name('admin.attendance.save');
        Route::post('/attendance/bulk/lock', [\App\Http\Controllers\BulkAttendanceController::class, 'lock'])->name('admin.attendance.lock');
        Route::post('/attendance/bulk/save-with-progress', [\App\Http\Controllers\BulkAttendanceController::class, 'saveWithProgress'])->name('admin.attendance.save-with-progress');
        Route::post('/attendance/bulk/lock-with-progress', [\App\Http\Controllers\BulkAttendanceController::class, 'lockWithProgress'])->name('admin.attendance.lock-with-progress');
        Route::post('/attendance/bulk/convert-pm-to-absent', [\App\Http\Controllers\BulkAttendanceController::class, 'convertPMToAbsent'])->name('admin.attendance.convert-pm-to-absent');
        Route::get('/attendance/bulk/progress', [\App\Http\Controllers\BulkAttendanceController::class, 'checkProgress'])->name('admin.attendance.check-progress');
        Route::get('/attendance/bulk/diagnose-leave', [\App\Http\Controllers\BulkAttendanceController::class, 'diagnoseLeave'])->name('admin.attendance.diagnose-leave');
        Route::get('/attendance/bulk/diagnose-month', [\App\Http\Controllers\BulkAttendanceController::class, 'diagnoseMonthLeaves'])->name('admin.attendance.diagnose-month');
        Route::get('/attendance/bulk/test-payroll-api', [\App\Http\Controllers\BulkAttendanceController::class, 'testPayrollApi'])->name('admin.attendance.test-payroll-api');
        
        // Shift Master and Duty Roster
        Route::resource('shifts', ShiftController::class);
        Route::resource('duty-rosters', DutyRosterController::class);
        Route::get('duty-rosters/bulk/create', [DutyRosterController::class, 'bulkCreate'])->name('duty-rosters.bulk-create');
        Route::post('duty-rosters/bulk/store', [DutyRosterController::class, 'bulkStore'])->name('duty-rosters.bulk-store');
        Route::post('/duty-rosters/copy-week', [DutyRosterController::class, 'copyWeek'])->name('duty-rosters.copy-week');
Route::post('/duty-rosters/clear-week', [DutyRosterController::class, 'clearWeek'])->name('duty-rosters.clear-week');
        Route::post('duty-rosters/clear-week', [DutyRosterController::class, 'clearWeek'])->name('duty-rosters.clear-week');

        // Biometric Attendance Management
        Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/upload', [App\Http\Controllers\AttendanceController::class, 'upload'])->name('attendance.upload');
        Route::post('/attendance/upload-biometric', [App\Http\Controllers\AttendanceController::class, 'uploadBiometric'])->name('attendance.upload-biometric');
        Route::post('/attendance/detect-format', [App\Http\Controllers\AttendanceController::class, 'detectFormat'])->name('attendance.detect-format');
        Route::get('/attendance/template', [App\Http\Controllers\AttendanceController::class, 'downloadTemplate'])->name('attendance.template');
        Route::get('/attendance/records', [App\Http\Controllers\AttendanceController::class, 'records'])->name('attendance.records');
        Route::get('/attendance/export', [App\Http\Controllers\AttendanceController::class, 'export'])->name('attendance.export');
        Route::delete('/attendance/bulk-delete', [App\Http\Controllers\AttendanceController::class, 'bulkDelete'])->name('attendance.bulk-delete');

        // Attendance Policy Management
        Route::get('/attendance-policies', [App\Http\Controllers\AttendancePolicyController::class, 'index'])->name('attendance-policies.index');
        Route::get('/attendance-policies/create', [App\Http\Controllers\AttendancePolicyController::class, 'create'])->name('attendance-policies.create');
        Route::post('/attendance-policies', [App\Http\Controllers\AttendancePolicyController::class, 'store'])->name('attendance-policies.store');
        Route::get('/attendance-policies/{id}/edit', [App\Http\Controllers\AttendancePolicyController::class, 'edit'])->name('attendance-policies.edit');
        Route::put('/attendance-policies/{id}', [App\Http\Controllers\AttendancePolicyController::class, 'update'])->name('attendance-policies.update');
        Route::post('/attendance-policies/{id}/activate', [App\Http\Controllers\AttendancePolicyController::class, 'activate'])->name('attendance-policies.activate');
        Route::delete('/attendance-policies/{id}', [App\Http\Controllers\AttendancePolicyController::class, 'destroy'])->name('attendance-policies.destroy');

        // Overtime Management
        Route::get('/overtime', [App\Http\Controllers\OvertimeController::class, 'index'])->name('overtime.index');
        Route::get('/overtime/data', [App\Http\Controllers\OvertimeController::class, 'getData'])->name('overtime.data');
        Route::post('/overtime/save', [App\Http\Controllers\OvertimeController::class, 'save'])->name('overtime.save');
        Route::post('/overtime/approve', [App\Http\Controllers\OvertimeController::class, 'approve'])->name('overtime.approve');
        Route::post('/overtime/lock', [App\Http\Controllers\OvertimeController::class, 'lock'])->name('overtime.lock');
        
        // Manual Punch Entry Management
        Route::resource('manual-punches', App\Http\Controllers\ManualPunchController::class);
        Route::get('/manual-punches/get-employee-shift', [App\Http\Controllers\ManualPunchController::class, 'getEmployeeShift'])->name('manual-punches.get-shift');

        // TimeStation Mapping
        Route::get('/timestation/mapping', [App\Http\Controllers\TimeStationMappingController::class, 'index'])->name('timestation.mapping');
        Route::get('/timestation/unmapped', [App\Http\Controllers\TimeStationMappingController::class, 'getUnmapped'])->name('timestation.unmapped');
        Route::get('/timestation/search-employees', [App\Http\Controllers\TimeStationMappingController::class, 'searchEmployees'])->name('timestation.search-employees');
        Route::post('/timestation/map', [App\Http\Controllers\TimeStationMappingController::class, 'mapUser'])->name('timestation.map');
        Route::post('/timestation/ignore', [App\Http\Controllers\TimeStationMappingController::class, 'ignoreUser'])->name('timestation.ignore');
        Route::post('/timestation/sync-now', [App\Http\Controllers\TimeStationMappingController::class, 'syncNow'])->name('timestation.sync-now');

        // Attendance Rules Management
        Route::resource('attendance-rules', App\Http\Controllers\AttendanceRuleController::class);

        // TimeStation Fetch Module
        Route::get('/timestation/fetch', [App\Http\Controllers\TimeStationFetchController::class, 'index'])->name('timestation.fetch.index');
        Route::post('/timestation/fetch', [App\Http\Controllers\TimeStationFetchController::class, 'fetch'])->name('timestation.fetch.process');
        Route::post('/timestation/fetch/override', [App\Http\Controllers\TimeStationFetchController::class, 'override'])->name('timestation.fetch.override');
        Route::post('/timestation/fetch/finalize', [App\Http\Controllers\TimeStationFetchController::class, 'finalize'])->name('timestation.fetch.finalize');

        // Financial Year Management
        Route::resource('financial-years', FinancialYearController::class);
        Route::post('financial-years/{id}/activate', [FinancialYearController::class, 'activate'])->name('financial-years.activate');
        Route::post('financial-years/settings/update-start-month', [FinancialYearController::class, 'updateStartMonth'])->name('financial-years.update-start-month');
        Route::post('financial-years/switch', [FinancialYearController::class, 'switch'])->name('financial-years.switch');
    });

    // Reports Management (Admin and Super Admin Only)
    Route::middleware(['role:admin,super_admin'])->group(function () {
        Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('/reports/leave-approved', [ReportsController::class, 'leaveApproved'])->name('reports.leave-approved');
        Route::get('/reports/leave-rejected', [ReportsController::class, 'leaveRejected'])->name('reports.leave-rejected');
        Route::get('/reports/employee-leave-status', [ReportsController::class, 'employeeLeaveStatus'])->name('reports.employee-leave-status');
        Route::get('/reports/employee-monthly', [ReportsController::class, 'employeeMonthlyReport'])->name('reports.employee-monthly');
        Route::get('/reports/leave-lop', [ReportsController::class, 'leaveLop'])->name('reports.leave-lop');
        
         Route::get('/reports/daily-leave', [ReportsController::class, 'dailyLeave'])->name('reports.daily-leave');
        Route::get('/reports/daily-leave/pdf', [ReportsController::class, 'dailyLeavePdf'])->name('reports.daily-leave.pdf');
    });

    // Permission Management (Admin and Super Admin Only)
    Route::middleware([\App\Http\Middleware\CheckRoutePermission::class])->group(function () {
        Route::get('permissions/manage', [\App\Http\Controllers\PermissionsController::class, 'index'])->name('permissions.manage');
        Route::post('permissions/save', [\App\Http\Controllers\PermissionsController::class, 'save'])->name('permissions.save');
        Route::post('permissions/update', [\App\Http\Controllers\PermissionsController::class, 'update'])->name('permissions.update');
        Route::post('permissions/delete', [\App\Http\Controllers\PermissionsController::class, 'delete'])->name('permissions.delete');
        Route::get('permissions/get/{id}', [\App\Http\Controllers\PermissionsController::class, 'get'])->name('permissions.get');
    });
    
    // New Leave Application Routes
    Route::get('/leaves/pending', [LeaveApplicationController::class, 'pending'])->name('leaves.pending');
    Route::get('/leaves', [LeaveApplicationController::class, 'index'])->name('leaves.index');
    Route::get('/leaves/create', [LeaveApplicationController::class, 'create'])->name('leaves.create');
    Route::post('/leaves', [LeaveApplicationController::class, 'store'])->name('leaves.store');
    Route::get('/leaves/{leave}', [LeaveApplicationController::class, 'show'])->name('leaves.show');
    Route::get('/leaves/{leave}/edit', [LeaveApplicationController::class, 'edit'])->name('leaves.edit');
    Route::put('/leaves/{leave}', [LeaveApplicationController::class, 'update'])->name('leaves.update');
    Route::delete('/leaves/{leave}', [LeaveApplicationController::class, 'cancel'])->name('leaves.cancel');
    
    // Leave calculation API
    Route::post('/leaves/calculate', [LeaveApplicationController::class, 'calculateLeaveDaysApi'])->name('leaves.calculate');
    
    // Get leave days for a specific leave application
    Route::get('/leaves/{leave}/days', [LeaveApplicationController::class, 'getLeaveDays'])->name('leaves.days');
    
    // Dummy Payroll API - Leave Balances (will be replaced with real payroll API)
    Route::get('/api/dummy/employee-leave-balance', [App\Http\Controllers\Api\DummyPayrollApiController::class, 'getEmployeeLeaveBalance'])->name('api.dummy.leave-balance');
    
    // Leave Actions for managers and admins
    Route::middleware(['auth'])->group(function () {
        // Manager approval - Available to reporting managers and admins
        Route::post('/leaves/{leave}/manager-approve', [LeaveApplicationController::class, 'approveAsManager'])
            ->name('leaves.manager-approve');
        
        // HR approval - Available only to admins
        Route::middleware(['role:admin,super_admin'])->group(function () {
            Route::post('/leaves/{leave}/approve', [LeaveApplicationController::class, 'approveAsHR'])
                ->name('leaves.approve');
        });
        
        // Rejection - Available to reporting managers and admins
        Route::post('/leaves/{leave}/reject', [LeaveApplicationController::class, 'reject'])
            ->name('leaves.reject');
        
        // Forward to manager - Available only to admins
        Route::middleware(['role:admin,super_admin'])->group(function () {
            Route::post('/leaves/{leave}/forward', [LeaveApplicationController::class, 'forwardToManager'])
                ->name('leaves.forward');
        });
    });
    
    // Public Holiday Applications (for employees)
    Route::get('/public-holiday-applications', [PublicHolidayApplicationController::class, 'index'])->name('public-holiday-applications.index');
    Route::post('/public-holiday-applications', [PublicHolidayApplicationController::class, 'store'])->name('public-holiday-applications.store');
    Route::delete('/public-holiday-applications/{id}', [PublicHolidayApplicationController::class, 'cancel'])->name('public-holiday-applications.cancel'); // Cancel/Change selection
    
    Route::resource('public-holidays', PublicHolidayController::class)->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::patch('/public-holidays/{publicHoliday}/toggle-status', [PublicHolidayController::class, 'toggleStatus'])
        ->name('public-holidays.toggle-status')
        ->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::resource('holiday-department-configs', DepartmentHolidayConfigController::class)->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::post('/holiday-department-configs/sync-departments', [DepartmentHolidayConfigController::class, 'syncDepartments'])
        ->name('holiday-department-configs.sync-departments')
        ->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::post('/holiday-department-configs/sync-used-holidays', [DepartmentHolidayConfigController::class, 'syncUsedHolidaysPublic'])
        ->name('holiday-department-configs.sync-used-holidays')
        ->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::post('/holiday-department-configs/cleanup-orphaned', [DepartmentHolidayConfigController::class, 'cleanupOrphanedConfigs'])
        ->name('holiday-department-configs.cleanup-orphaned')
        ->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    
    // Leave Types Management
    Route::resource('leave-types', LeaveTypeController::class)->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    Route::post('/leave-types/sync-departments', [LeaveTypeController::class, 'syncDepartments'])
        ->name('leave-types.sync-departments')
        ->middleware(\App\Http\Middleware\CheckRoutePermission::class);
    
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    
    // API Synchronization (Admin and Super Admin Only)
    Route::middleware(['role:admin,super_admin'])->group(function () {
        // Main API Sync Dashboard
        Route::get('/api-sync', [App\Http\Controllers\ApiSyncController::class, 'index'])->name('admin.api-sync');
        Route::get('/api-sync/test', [App\Http\Controllers\ApiSyncController::class, 'testConnection'])->name('admin.api-sync.test');
        
        // Employee Sync - Comprehensive sync with payroll system
        Route::get('/employee-sync', [App\Http\Controllers\EmployeeSyncController::class, 'index'])->name('admin.employee-sync');
        Route::post('/employee-sync', [App\Http\Controllers\EmployeeSyncController::class, 'sync'])->name('admin.employee-sync.sync');
        Route::get('/employee-sync/status', [App\Http\Controllers\EmployeeSyncController::class, 'status'])->name('admin.employee-sync.status');
        Route::get('/employee-sync/preview', [App\Http\Controllers\EmployeeSyncController::class, 'preview'])->name('admin.employee-sync.preview');
        Route::get('/employee-sync/test', [App\Http\Controllers\EmployeeSyncController::class, 'testConnection'])->name('admin.employee-sync.test');
        Route::post('/employee-sync/api', [App\Http\Controllers\EmployeeSyncController::class, 'apiSync'])->name('admin.employee-sync.api');
        
        // Department Sync - Comprehensive sync with payroll system
        Route::get('/department-sync', [App\Http\Controllers\DepartmentSyncController::class, 'index'])->name('admin.department-sync');
        Route::post('/department-sync', [App\Http\Controllers\DepartmentSyncController::class, 'sync'])->name('admin.department-sync.sync');
        Route::get('/department-sync/status', [App\Http\Controllers\DepartmentSyncController::class, 'status'])->name('admin.department-sync.status');
        Route::get('/department-sync/preview', [App\Http\Controllers\DepartmentSyncController::class, 'preview'])->name('admin.department-sync.preview');
        Route::get('/department-sync/test', [App\Http\Controllers\DepartmentSyncController::class, 'testConnection'])->name('admin.department-sync.test');
        Route::post('/department-sync/api', [App\Http\Controllers\DepartmentSyncController::class, 'apiSync'])->name('admin.department-sync.api');
        
        // Removed database management as requested
    });

    // Activity Logs (Super Admin Only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
        Route::match(['GET', 'POST'], '/activity-logs/data', [ActivityLogController::class, 'getActivityLogsData'])->name('activity-logs.data');
        Route::post('/activity-logs/details', [ActivityLogController::class, 'getActivityLogDetails'])->name('activity-logs.details');
        Route::get('/activity-logs/{id}', [ActivityLogController::class, 'show'])->name('activity-logs.show');
        Route::get('/activity-logs-export', [ActivityLogController::class, 'export'])->name('activity-logs.export');
        Route::post('/activity-logs/cleanup', [ActivityLogController::class, 'cleanup'])->name('activity-logs.cleanup');
        Route::get('/activity-logs/stream', [ActivityLogController::class, 'stream'])->name('activity-logs.stream');
    });
    
    // Email Testing Route (Temporary - for development)
    Route::get('/test-email-notifications', function () {
        try {
            // Get sample leave application
            $sampleLeave = \App\Models\LeaveApplication::with(['user', 'leaveType'])->first();
            
            if (!$sampleLeave) {
                return response('No leave applications found for testing', 404);
            }
            
            // Create static notification users
            $hrUser = \App\Models\StaticNotificationUser::hr();
            $managerUser = \App\Models\StaticNotificationUser::reportingManager();
            
            // Send test notifications
            $hrUser->notify(new \App\Notifications\LeaveApplicationSubmitted($sampleLeave));
            $managerUser->notify(new \App\Notifications\LeaveApplicationSubmitted($sampleLeave));
            
            return response()->json([
                'message' => 'Test email notifications sent successfully!',
                'hr_email' => $hrUser->email,
                'manager_email' => $managerUser->email,
                'test_leave_id' => $sampleLeave->id
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send test emails',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('test.email-notifications')->middleware('role:admin,super_admin');
    
    // Clear Demo Data Route (Temporary)
    Route::get('/clear-demo-data', function () {
        try {
            DB::beginTransaction();
            
            // Get counts before deletion
            $leaveAppsCount = DB::table('leave_applications')->count();
            $leaveDaysCount = DB::table('leave_application_days')->count();
            
            // Clear leave application days first (foreign key constraint)
            DB::table('leave_application_days')->truncate();
            
            // Clear leave applications
            DB::table('leave_applications')->truncate();
            
            // Clear leave-related activity logs
            $activityCount = DB::table('activity_log')
                ->where('subject_type', 'App\\Models\\LeaveApplication')
                ->count();
            DB::table('activity_log')
                ->where('subject_type', 'App\\Models\\LeaveApplication')
                ->delete();
            
            // Try to clear bulk attendance tables (ignore if they don't exist)
            $bulkTables = ['bulk_attendance_records', 'bulk_attendance_sessions', 'attendance_records'];
            $bulkCleared = [];
            
            foreach ($bulkTables as $table) {
                try {
                    $count = DB::table($table)->count();
                    if ($count > 0) {
                        DB::table($table)->truncate();
                        $bulkCleared[] = "{$table}: {$count} records";
                    }
                } catch (\Exception $e) {
                    // Table might not exist, ignore
                }
            }
            
            // Reset auto increment
            DB::statement('ALTER TABLE leave_applications AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE leave_application_days AUTO_INCREMENT = 1');
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Demo data cleared successfully!',
                'cleared' => [
                    'leave_applications' => $leaveAppsCount,
                    'leave_application_days' => $leaveDaysCount,
                    'activity_logs' => $activityCount,
                    'bulk_attendance' => $bulkCleared
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to clear demo data',
                'message' => $e->getMessage()
            ], 500);
        }
    })->name('clear.demo-data')->middleware('role:admin,super_admin');
    
    // Debug logs route (Temporary)
    Route::get('/debug-logs', function () {
        $logFile = storage_path('logs/laravel.log');
        if (file_exists($logFile)) {
            $logs = file_get_contents($logFile);
            $recentLogs = array_slice(explode("\n", $logs), -50); // Last 50 lines
            return response('<pre>' . implode("\n", $recentLogs) . '</pre>');
        }
        return 'No log file found';
    })->name('debug.logs')->middleware('role:admin,super_admin');

    // Email Settings (Super Admin Only)
    Route::middleware('role:super_admin')->group(function () {
        Route::get('/email-settings', [EmailSettingsController::class, 'index'])->name('email-settings.index');
        Route::put('/email-settings', [EmailSettingsController::class, 'update'])->name('email-settings.update');
    });

    // Self Attendance Routes
    Route::controller(\App\Http\Controllers\SelfAttendanceController::class)->group(function () {
        Route::get('/self-attendance', 'index')->name('self-attendance.index');
        Route::post('/self-attendance/check-in', 'checkIn')->name('self-attendance.check-in');
        Route::post('/self-attendance/check-out', 'checkOut')->name('self-attendance.check-out');
        Route::post('/self-attendance/correction', 'storeCorrection')->name('self-attendance.correction');
        Route::post('/self-attendance/review', 'storeReview')->name('self-attendance.review');
        Route::get('/admin/portal-punches', 'adminIndex')->name('self-attendance.admin-logs')->middleware('role:admin,super_admin,hr');
    });

    // Employee correction approvals
    Route::post('/manual-punches/{id}/approve', [\App\Http\Controllers\ManualPunchController::class, 'approveCorrection'])
        ->name('manual-punches.approve');
    Route::post('/manual-punches/{id}/reject', [\App\Http\Controllers\ManualPunchController::class, 'rejectCorrection'])
        ->name('manual-punches.reject');
});