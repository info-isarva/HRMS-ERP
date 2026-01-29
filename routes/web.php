<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PayrollSSOController;
use App\Http\Controllers\AttendanceSSOController;

Route::get('/', function (Request $request) {
    $permissions = $request->attributes->get('employee_permissions', null);
    return view('dashboard', compact('permissions'));
})->middleware(['auth', 'verified', 'check.user.access'])->name('dashboard');

Route::get('/dashboard', function (Request $request) {
    $permissions = $request->attributes->get('employee_permissions', null);
    return view('dashboard', compact('permissions'));
})->middleware(['auth', 'verified', 'check.user.access'])->name('dashboard');

Route::get('/logout-hub', function (Request $request) {
    $urls = $request->query('urls', []);
    return view('logout-hub', compact('urls'));
})->name('logout.hub');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/payroll-access', [PayrollSSOController::class, 'redirectToPayroll'])
         ->name('payroll.sso');
    Route::get('/attendance-redirect', [AttendanceSSOController::class, 'redirectToAttendance'])
     ->name('attendance.redirect');

    // Admin Consent Routes
    Route::get('/admin/consent', [App\Http\Controllers\Auth\ConsentController::class, 'show'])->name('admin.consent.show');
    Route::post('/admin/consent', [App\Http\Controllers\Auth\ConsentController::class, 'store'])->name('admin.consent.store');
});

require __DIR__.'/auth.php';
