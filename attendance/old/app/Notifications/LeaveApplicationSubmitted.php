<?php

namespace App\Notifications;

use App\Models\LeaveApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveApplicationSubmitted extends Notification
{
    use Queueable;

    protected $leaveApplication;

    /**
     * Create a new notification instance.
     */
    public function __construct(LeaveApplication $leaveApplication)
    {
        $this->leaveApplication = $leaveApplication;
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
                    ->subject($employeeName . ' has applied for leave')
                    ->view('emails.leave-application-submitted', [
                        'leave' => $leave,
                        'employee' => $employee ?: $leave->user, // Use employee record if found, fallback to user
                        'notifiable' => $notifiable,
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
        ];
    }
}