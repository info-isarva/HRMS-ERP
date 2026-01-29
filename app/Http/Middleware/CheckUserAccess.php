<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckUserAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Check if user has employee_id (is connected to an employee)
        if (is_null($user->employee_id)) {
            // User is not connected to employee - show workspace dashboard
            Log::info('User without employee connection accessing dashboard', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            return $next($request);
        }

        // User has employee_id - check employee permissions
        try {
            $employee = DB::table('employee_basic_details')
                ->where('id', $user->employee_id)
                ->first();

            if (!$employee) {
                Log::warning('User has employee_id but employee not found', [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id
                ]);
                // Employee not found - treat as no permissions
                return $this->showNoPermissionError();
            }

            $enablePayroll = (bool) $employee->enable_payroll;
            $enableSelfPortal = (bool) $employee->enable_self_portal;

            Log::info('Employee permissions check', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'enable_payroll' => $enablePayroll,
                'enable_self_portal' => $enableSelfPortal
            ]);

            // Check permissions and redirect accordingly
            if (!$enablePayroll && !$enableSelfPortal) {
                // Both disabled - no permission to login
                return $this->showNoPermissionError();
            }

            if (!$enablePayroll && $enableSelfPortal) {
                // Only attendance enabled - redirect directly to attendance
                Log::info('Redirecting user to attendance (payroll disabled)', [
                    'user_id' => $user->id,
                    'employee_id' => $user->employee_id
                ]);
                return redirect()->route('attendance.redirect');
            }

            // If we reach here, either:
            // 1. Both payroll and self portal are enabled - show workspace dashboard with options
            // 2. Only payroll is enabled - show workspace dashboard with options
            // Let the dashboard view handle showing appropriate options based on permissions
            $request->attributes->set('employee_permissions', [
                'enable_payroll' => $enablePayroll,
                'enable_self_portal' => $enableSelfPortal,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Error checking employee permissions', [
                'user_id' => $user->id,
                'employee_id' => $user->employee_id,
                'error' => $e->getMessage()
            ]);
            
            // On error, show no permission message
            return $this->showNoPermissionError();
        }
    }

    /**
     * Show no permission error page
     */
    private function showNoPermissionError()
    {
        return response()->view('errors.no-permission', [
            'message' => 'You don\'t have permission to access this system. Please contact HR or Admin for assistance.'
        ], 403);
    }
}