<?php

namespace App\Services;

use App\Models\LeaveApplication;
use App\Models\User;
use App\Models\Employee;
use App\Notifications\LeaveApplicationSubmitted;
use App\Notifications\LeaveForwardedToManager;
use App\Notifications\LeaveStatusUpdated;
use App\Http\Controllers\EmailSettingsController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class LeaveNotificationService
{
    /**
     * Send notification when leave is submitted
     */
    public function sendLeaveSubmittedNotifications(LeaveApplication $leave)
    {
        // Check if emails are enabled
        if (!EmailSettingsController::areEmailsEnabled()) {
            Log::info('Email notifications are disabled, skipping leave submitted notifications', ['leave_id' => $leave->id]);
            return;
        }
        
        try {
            Log::info('Starting leave submitted notifications', ['leave_id' => $leave->id]);
            
            // Get HR users (admin role only, not super_admin)
            $hrUsers = $this->getHRUsers();
            Log::info('Found HR users', ['count' => $hrUsers->count(), 'emails' => $hrUsers->pluck('email')->toArray()]);
            
            // Get reporting manager
            $reportingManager = $this->getReportingManager($leave);
            Log::info('Found reporting manager', ['manager' => $reportingManager ? $reportingManager->name : 'None']);
            
            // Send to HR
            foreach ($hrUsers as $hrUser) {
                Log::info('Attempting to send notification to HR', [
                    'leave_id' => $leave->id,
                    'hr_email' => $hrUser->email
                ]);
                
                $hrUser->notify(new LeaveApplicationSubmitted($leave));
                
                Log::info('Leave submitted notification sent to HR', [
                    'leave_id' => $leave->id,
                    'hr_email' => $hrUser->email
                ]);
            }
            
            // Send to reporting manager
            if ($reportingManager) {
                // Find user account for the reporting manager
                $managerUser = User::where('email', $reportingManager->email)->first();
                if ($managerUser) {
                    Log::info('Attempting to send notification to manager', [
                        'leave_id' => $leave->id,
                        'manager_email' => $managerUser->email
                    ]);
                    
                    $managerUser->notify(new LeaveApplicationSubmitted($leave));
                    
                    Log::info('Leave submitted notification sent to manager', [
                        'leave_id' => $leave->id,
                        'manager_email' => $managerUser->email
                    ]);
                } else {
                    Log::warning('Manager user account not found', [
                        'leave_id' => $leave->id,
                        'manager_email' => $reportingManager->email
                    ]);
                }
            } else {
                Log::warning('No reporting manager found for leave', [
                    'leave_id' => $leave->id,
                    'user_email' => $leave->user->email
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send leave submitted notifications', [
                'leave_id' => $leave->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send notification when leave is forwarded to manager
     */
    public function sendLeaveForwardedNotification(LeaveApplication $leave, $forwardingNote = null)
    {
        // Check if emails are enabled
        if (!EmailSettingsController::areEmailsEnabled()) {
            Log::info('Email notifications are disabled, skipping leave forwarded notification', ['leave_id' => $leave->id]);
            return;
        }
        
        try {
            // Get reporting manager
            $reportingManager = $this->getReportingManager($leave);
            
            if ($reportingManager) {
                // Find user account for the reporting manager
                $managerUser = User::where('email', $reportingManager->email)->first();
                if ($managerUser) {
                    $managerUser->notify(new LeaveForwardedToManager($leave, $forwardingNote));
                    Log::info('Leave forwarded notification sent to manager', [
                        'leave_id' => $leave->id,
                        'manager_email' => $managerUser->email
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send leave forwarded notification', [
                'leave_id' => $leave->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send notification when leave status is updated (approved/rejected by manager or HR)
     */
    public function sendLeaveStatusUpdatedNotifications(LeaveApplication $leave, $status, $rejectionReason = null, $approvedBy = null)
    {
        // Check if emails are enabled
        if (!EmailSettingsController::areEmailsEnabled()) {
            Log::info('Email notifications are disabled, skipping leave status updated notifications', ['leave_id' => $leave->id]);
            return;
        }
        
        try {
            // Always notify the employee
            $leave->user->notify(new LeaveStatusUpdated($leave, $status, $rejectionReason, $approvedBy));
            Log::info('Leave status notification sent to employee', [
                'leave_id' => $leave->id,
                'employee_email' => $leave->user->email,
                'status' => $status
            ]);
            
            // If manager approved, also notify HR for tracking purposes
            // NOTE: Rejection emails are NO LONGER sent to all admins to prevent notification spam
            // Only the employee is notified of rejection - the rejector already knows as they made the decision
            if ($status === 'approved_by_manager') {
                $hrUsers = $this->getHRUsers();
                foreach ($hrUsers as $hrUser) {
                    $hrUser->notify(new LeaveStatusUpdated($leave, $status, $rejectionReason, $approvedBy));
                    Log::info('Leave status notification sent to HR', [
                        'leave_id' => $leave->id,
                        'hr_email' => $hrUser->email,
                        'status' => $status
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send leave status notifications', [
                'leave_id' => $leave->id,
                'status' => $status,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Get HR users (admin role only)
     */
    private function getHRUsers(): Collection
    {
        return User::where('role', 'admin')->get();
    }
    
    /**
     * Get reporting manager for a leave application using employees table
     */
    private function getReportingManager(LeaveApplication $leave): ?Employee
    {
        Log::info('Getting reporting manager', [
            'leave_id' => $leave->id,
            'user_email' => $leave->user->email
        ]);
        
        // Find employee record using email
        $employee = Employee::where('email', $leave->user->email)->first();
        
        if (!$employee) {
            Log::warning('Employee not found in employees table', [
                'leave_id' => $leave->id,
                'user_email' => $leave->user->email
            ]);
            return null;
        }
        
        Log::info('Found employee record', [
            'employee_id' => $employee->id,
            'employee_name' => $employee->name,
            'reporting_manager_payroll_id' => $employee->reporting_manager_payroll_id
        ]);
        
        if (!$employee->reporting_manager_payroll_id) {
            Log::warning('Employee has no reporting manager assigned', [
                'employee_id' => $employee->id,
                'employee_name' => $employee->name
            ]);
            return null;
        }
        
        // Find reporting manager using payroll_id
        $manager = Employee::where('payroll_id', $employee->reporting_manager_payroll_id)->first();
        
        if ($manager) {
            Log::info('Found reporting manager', [
                'manager_id' => $manager->id,
                'manager_name' => $manager->name,
                'manager_email' => $manager->email
            ]);
        } else {
            Log::warning('Reporting manager not found', [
                'searching_payroll_id' => $employee->reporting_manager_payroll_id
            ]);
        }
        
        return $manager;
    }
}