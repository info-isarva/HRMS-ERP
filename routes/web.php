<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PayrollSSOController;
use App\Http\Controllers\AttendanceSSOController;
use App\Http\Controllers\CrmSSOController;
use App\Http\Controllers\PoshModuleController;
use App\Http\Controllers\PoshSSOController;

Route::get('/', function (Request $request) {
    $redirectUrl = $request->query('redirect') ?? session()->pull('sso_redirect_after_login');
    if ($redirectUrl) {
        if (str_contains($redirectUrl, 'attendance')) {
            return redirect()->route('attendance.redirect');
        } elseif (str_contains($redirectUrl, 'payroll')) {
            return redirect()->route('payroll.sso');
        } elseif (str_contains($redirectUrl, 'crm')) {
            return redirect()->route('crm.sso');
        } elseif (str_contains($redirectUrl, 'posh')) {
            return redirect()->route('posh.sso');
        }
    }
    $permissions = $request->attributes->get('employee_permissions', null);
    return view('dashboard', compact('permissions'));
})->middleware([\App\Http\Middleware\ResolveTenant::class, 'workspace.auth', 'check.user.access'])->name('dashboard');

Route::get('/dashboard', function (Request $request) {
    $redirectUrl = $request->query('redirect') ?? session()->pull('sso_redirect_after_login');
    if ($redirectUrl) {
        if (str_contains($redirectUrl, 'attendance')) {
            return redirect()->route('attendance.redirect');
        } elseif (str_contains($redirectUrl, 'payroll')) {
            return redirect()->route('payroll.sso');
        } elseif (str_contains($redirectUrl, 'crm')) {
            return redirect()->route('crm.sso');
        } elseif (str_contains($redirectUrl, 'posh')) {
            return redirect()->route('posh.sso');
        }
    }
    $permissions = $request->attributes->get('employee_permissions', null);
    return view('dashboard', compact('permissions'));
})->middleware([\App\Http\Middleware\ResolveTenant::class, 'workspace.auth', 'check.user.access'])->name('dashboard');

Route::get('/logout-hub', function (Request $request) {
    $urls = $request->query('urls', []);
    return view('logout-hub', compact('urls'));
})->name('logout.hub');

Route::middleware([\App\Http\Middleware\ResolveTenant::class, 'workspace.auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/payroll-access', [PayrollSSOController::class, 'redirectToPayroll'])
         ->name('payroll.sso');
    Route::get('/attendance-redirect', [AttendanceSSOController::class, 'redirectToAttendance'])
     ->name('attendance.redirect');
    Route::get('/crm-access', [CrmSSOController::class, 'redirectToCrm'])
     ->name('crm.sso');

    Route::get('/posh-access', [PoshSSOController::class, 'redirectToPosh'])->name('posh.sso');
    Route::get('/posh', [PoshModuleController::class, 'comingSoon'])->name('posh.coming-soon');

    // Admin Consent Routes
    Route::get('/admin/consent', [App\Http\Controllers\Auth\ConsentController::class, 'show'])->name('admin.consent.show');
    Route::post('/admin/consent', [App\Http\Controllers\Auth\ConsentController::class, 'store'])->name('admin.consent.store');

    Route::middleware('platform.admin')->prefix('platform/demo-tenants')->name('platform.demo-tenants.')->group(function () {
        Route::get('/', [App\Http\Controllers\Central\DemoTenantController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\Central\DemoTenantController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\Central\DemoTenantController::class, 'store'])->name('store');
        Route::get('/{tenant}', [App\Http\Controllers\Central\DemoTenantController::class, 'show'])->name('show');
        Route::post('/{tenant}/extend', [App\Http\Controllers\Central\DemoTenantController::class, 'extend'])->name('extend');
        Route::post('/{tenant}/deactivate', [App\Http\Controllers\Central\DemoTenantController::class, 'deactivate'])->name('deactivate');
        Route::post('/{tenant}/refresh-usage', [App\Http\Controllers\Central\DemoTenantController::class, 'refreshUsage'])->name('refresh-usage');
    });
});

// Compliance Consent Routes
Route::controller(App\Http\Controllers\ComplianceController::class)->group(function () {
    Route::get('compliance/dpdp-policy', 'showDpdpPolicy')->name('compliance.dpdp.policy');
    Route::post('compliance/dpdp-accept', 'acceptDpdpPolicy')->name('compliance.dpdp.accept');
    Route::get('compliance/posh-policy', 'showPoshPolicy')->name('compliance.posh.policy');
    Route::post('compliance/posh-accept', 'acceptPoshPolicy')->name('compliance.posh.accept');
});

require __DIR__.'/auth.php';
