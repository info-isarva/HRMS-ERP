<?php

use App\Http\Controllers\AnnualReportController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CaseController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\ComplianceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeDirectoryController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\EmployeePortalController;
use App\Http\Controllers\IcMemberController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\PoshAuthController;
use App\Http\Controllers\PublicIntakeController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SSOAuthController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/intake/{orgKey}', [PublicIntakeController::class, 'show'])->name('intake.show');
Route::post('/intake/{orgKey}', [PublicIntakeController::class, 'store'])->name('intake.store');

Route::get('/sso-authenticate', [SSOAuthController::class, 'authenticate'])->name('sso.authenticate');
Route::post('/sso-passive-logout', [SSOAuthController::class, 'passiveLogout'])->name('sso.passive-logout');

Route::get('/login', [PoshAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [PoshAuthController::class, 'login']);
Route::post('/logout', [PoshAuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    Route::get('/locale/{locale}', [LocaleController::class, 'switch'])->name('locale.switch');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/guide', [GuideController::class, 'index'])->name('guide.index');

    Route::get('/employee', [EmployeePortalController::class, 'index'])->name('employee.portal');
    Route::get('/employee/policy', [EmployeePortalController::class, 'policy'])->name('employee.policy');
    Route::post('/employee/policy/acknowledge', [EmployeePortalController::class, 'acknowledge'])->name('employee.policy.acknowledge');

    Route::get('/complaints/file', [ComplaintController::class, 'create'])->name('complaints.create');
    Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');
    Route::get('/complaints/my', [ComplaintController::class, 'myCases'])->name('complaints.my');
    Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
    Route::get('/complaints/{complaint}/evidence/{evidence}/download', [ComplaintController::class, 'downloadEvidence'])->name('complaints.evidence.download');

    Route::get('/management', [ManagementController::class, 'index'])->name('management.index');

    Route::middleware('posh.ic')->group(function () {
        Route::get('/cases', [CaseController::class, 'index'])->name('cases.index');
        Route::get('/cases/{complaint}/operate', [CaseController::class, 'operate'])->name('cases.operate');
        Route::post('/cases/{complaint}/operate', [CaseController::class, 'saveStep'])->name('cases.operate.save');
        Route::get('/cases/{complaint}/notice', [NoticeController::class, 'respondentNotice'])->name('cases.notice');
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit.index');
        Route::get('/compliance', [ComplianceController::class, 'index'])->name('compliance.index');
        Route::post('/compliance/duties/{duty}', [ComplianceController::class, 'updateDuty'])->name('compliance.duty.update');
        Route::post('/compliance/events', [ComplianceController::class, 'storeEvent'])->name('compliance.events.store');
        Route::get('/reports/annual', [AnnualReportController::class, 'index'])->name('reports.annual.index');
        Route::post('/reports/annual/generate', [AnnualReportController::class, 'generate'])->name('reports.annual.generate');
        Route::get('/reports/annual/{report}', [AnnualReportController::class, 'show'])->name('reports.annual.show');
        Route::post('/reports/annual/{report}/submitted', [AnnualReportController::class, 'markSubmitted'])->name('reports.annual.submitted');
        Route::get('/reports/annual/{report}/export', [AnnualReportController::class, 'export'])->name('reports.annual.export');
    });

    Route::middleware('posh.admin')->group(function () {
        Route::get('/employees', [EmployeeDirectoryController::class, 'index'])->name('employees.index');
        Route::post('/employees', [EmployeeDirectoryController::class, 'store'])->name('employees.store');
        Route::put('/employees/{directory}', [EmployeeDirectoryController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{directory}', [EmployeeDirectoryController::class, 'destroy'])->name('employees.destroy');
        Route::post('/employees/sync', [EmployeeDirectoryController::class, 'sync'])->name('employees.sync');
        Route::post('/employees/{directory}/enable-login', [EmployeeDirectoryController::class, 'enableLogin'])->name('employees.enable-login');

        Route::get('/ic-setup', [IcMemberController::class, 'index'])->name('ic-members.index');
        Route::post('/ic-setup', [IcMemberController::class, 'store'])->name('ic-members.store');
        Route::put('/ic-setup/{icMember}', [IcMemberController::class, 'update'])->name('ic-members.update');
        Route::delete('/ic-setup/{icMember}', [IcMemberController::class, 'destroy'])->name('ic-members.destroy');

        Route::get('/policies', [PolicyController::class, 'index'])->name('policies.index');
        Route::get('/policies/create', [PolicyController::class, 'create'])->name('policies.create');
        Route::post('/policies', [PolicyController::class, 'store'])->name('policies.store');
        Route::get('/policies/{policy}/edit', [PolicyController::class, 'edit'])->name('policies.edit');
        Route::put('/policies/{policy}', [PolicyController::class, 'update'])->name('policies.update');
        Route::post('/policies/{policy}/activate', [PolicyController::class, 'activate'])->name('policies.activate');

        Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/intake-key', [SettingsController::class, 'regenerateIntakeKey'])->name('settings.intake.regenerate');
    });
});
