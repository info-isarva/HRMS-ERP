<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveForwardedToManager extends Notification
{
    use Queueable;

    protected $leaveApplication;
    protected $forwardingNote;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveApplication $leaveApplication, $forwardingNote = null)
    {
        $this->leaveApplication = $leaveApplication;
        $this->forwardingNote = $forwardingNote;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $leave = $this->leaveApplication;
        
        // Get employee record from employees table
        $employee = \App\Models\Employee::where('email', $leave->user->email)->first();
        $employeeName = $employee ? $employee->name : $leave->user->name;
        
        return (new MailMessage)
                    ->subject($employeeName . "'s leave application forwarded for your approval")
                    ->view('emails.leave-forwarded-to-manager', [
                        'leave' => $leave,
                        'employee' => $employee ?: $leave->user, // Use employee record if found, fallback to user
                        'manager' => $notifiable,
                        'forwardingNote' => $this->forwardingNote,
                        'leaveType' => $leave->leaveType
                    ]);
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return [
            'leave_application_id' => $this->leaveApplication->id,
            'employee_name' => $this->leaveApplication->user->name,
            'leave_type' => $this->leaveApplication->leaveType->name,
            'start_date' => $this->leaveApplication->start_date,
            'end_date' => $this->leaveApplication->end_date,
            'forwarding_note' => $this->forwardingNote,
        ];
    }
}