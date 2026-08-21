<?php
namespace App\Policies;

use App\Models\LeaveApplication;
use App\Models\User;

class LeaveApplicationPolicy
{
    /**
     * Determine whether the user can view any leave applications.
     */
    public function viewAny(User $user): bool
    {
        return true; // All users can see the list of their own leave applications
    }

    /**
     * Determine whether the user can view the leave application.
     */
    public function view(User $user, LeaveApplication $leaveApplication): bool
    {
        // User can view their own leave applications
        if ($user->id === $leaveApplication->user_id) {
            return true;
        }
        
        // Admins and super admins can view all leave applications
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }
        
        // Reporting managers can view their reportees' leave applications using email-based lookup
        $employee = \App\Models\Employee::where('email', $leaveApplication->user->email)->first();
        $manager = \App\Models\Employee::where('email', $user->email)->first();
        
        if ($employee && $manager && $employee->reporting_manager_payroll_id === $manager->payroll_id) {
            return true;
        }
        
        return false;
    }

    /**
     * Determine whether the user can create leave applications.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create leave applications
    }

    /**
     * Determine whether the user can update the leave application.
     */
    public function update(User $user, LeaveApplication $leaveApplication): bool
    {
        // Users can only update their own pending leave applications
        return $user->id === $leaveApplication->user_id && $leaveApplication->status === 'pending';
    }

    /**
     * Determine whether the user can delete/cancel the leave application.
     */
    public function delete(User $user, LeaveApplication $leaveApplication): bool
    {
        // Users can only cancel their own pending leave applications
        return $user->id === $leaveApplication->user_id && $leaveApplication->status === 'pending';
    }

    /**
     * Determine whether the user can approve leave applications.
     */
    public function approve(User $user, LeaveApplication $leaveApplication): bool
    {
        // Only block self-approval for non-admins
        if ($user->id === $leaveApplication->user_id && !($user->isAdmin() || $user->isSuperAdmin())) {
            return false;
        }
        
        // Admin/HR can approve any pending or manager-approved leave
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return in_array($leaveApplication->status, ['pending', 'approved_by_manager', 'forwarded_to_manager']);
        }
        
        // Reporting managers can approve forwarded leaves using email-based lookup
        $employee = \App\Models\Employee::where('email', $leaveApplication->user->email)->first();
        $manager = \App\Models\Employee::where('email', $user->email)->first();
        
        if ($employee && $manager && $employee->reporting_manager_payroll_id === $manager->payroll_id) {
            return $leaveApplication->status === 'forwarded_to_manager';
        }
        
        return false;
    }
    
    /**
     * Determine whether the user can reject leave applications.
     */
    public function reject(User $user, LeaveApplication $leaveApplication): bool
    {
        // Can't reject own leave
        if ($user->id === $leaveApplication->user_id) {
            return false;
        }
        
        // Admin/HR can reject any pending, forwarded, or manager-approved leave
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return in_array($leaveApplication->status, ['pending', 'forwarded_to_manager', 'approved_by_manager']);
        }
        
        // Reporting managers can reject forwarded leaves using email-based lookup
        $employee = \App\Models\Employee::where('email', $leaveApplication->user->email)->first();
        $manager = \App\Models\Employee::where('email', $user->email)->first();
        
        if ($employee && $manager && $employee->reporting_manager_payroll_id === $manager->payroll_id) {
            return $leaveApplication->status === 'forwarded_to_manager';
        }
        
        return false;
    }
}
