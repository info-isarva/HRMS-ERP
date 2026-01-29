<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class LeaveApplication extends Model
{
    use LogsActivity;
    protected $fillable = [
        'user_id', 'email_id', 'start_date', 'end_date', 'start_half_day', 'end_half_day',
        'total_days', 'status', 'reason', 'financial_year', 'leave_type', 'leave_type_id',
        'manager_approved_by', 'manager_approved_at', 'hr_approved_by', 'hr_approved_at',
        'rejected_by', 'rejected_at', 'rejection_reason', 'emergency_contact_name', 'emergency_contact_phone',
        'forwarded_by', 'forwarded_at', 'forwarding_note',
        'lop_days', 'paid_days', 'has_lop', 'lop_acknowledged'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'start_half_day' => 'string',
        'end_half_day' => 'string',
        'total_days' => 'decimal:1',
        'lop_days' => 'decimal:1',
        'paid_days' => 'decimal:1',
        'has_lop' => 'boolean',
        'lop_acknowledged' => 'boolean',
        'manager_approved_at' => 'datetime',
        'hr_approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'forwarded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }
    
    /**
     * Get the employee record from employees table
     */
    public function employee()
    {
        return $this->hasOneThrough(
            Employee::class,
            User::class,
            'id', // Foreign key on users table
            'email', // Foreign key on employees table  
            'user_id', // Local key on leave_applications table
            'email' // Local key on users table
        );
    }
    
    public function managerApprovedBy()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }
    
    public function hrApprovedBy()
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }
    
    public function rejectedBy()
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }
    
    public function forwardedBy()
    {
        return $this->belongsTo(User::class, 'forwarded_by');
    }
    
    /**
     * Get the detailed leave days for this application
     */
    public function leaveDays()
    {
        return $this->hasMany(LeaveApplicationDay::class);
    }
    
    /**
     * Check if the application is pending any approval
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
    
    /**
     * Check if the application is forwarded to manager
     */
    public function isForwardedToManager()
    {
        return $this->status === 'forwarded_to_manager';
    }
    
    /**
     * Check if the application is approved by manager but pending HR approval
     */
    public function isManagerApproved()
    {
        return $this->status === 'approved_by_manager';
    }
    
    /**
     * Check if the application is fully approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }
    
    /**
     * Check if the application is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }
    
    /**
     * Check if the application is cancelled
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }
    
    /**
     * Check if the user can approve this leave application as a manager
     */
    public function canApproveAsManager(User $user)
    {
        // Manager can only approve if the leave is forwarded to them
        if ($this->isForwardedToManager()) {
            // Check if user is the reporting manager using email-based lookup
            $employee = Employee::where('email', $this->user->email)->first();
            $manager = Employee::where('email', $user->email)->first();
            
            if ($employee && $manager && $employee->reporting_manager_payroll_id === $manager->payroll_id) {
                return true;
            }
        }
        
        // Admin/HR can approve directly regardless of forwarding
        if ($user->role === 'admin' || $user->role === 'super_admin') {
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if the user can forward this leave application
     */
    public function canForward(User $user)
    {
        // Only admin/HR can forward, and only if the leave is pending
        return ($user->role === 'admin' || $user->role === 'super_admin') && $this->isPending();
    }
    
    /**
     * Check if the user can approve this leave application as HR
     */
    public function canApproveAsHR(User $user)
    {
        // Only admin and super_admin can approve as HR
        return $user->role === 'admin' || $user->role === 'super_admin';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'start_date', 'end_date', 'status', 'reason', 'leave_type', 'total_days',
                'manager_approved_by', 'manager_approved_at', 'hr_approved_by', 'hr_approved_at',
                'rejected_by', 'rejected_at', 'rejection_reason',
                'forwarded_by', 'forwarded_at', 'forwarding_note'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Leave application {$eventName}")
            ->useLogName('leave_application');
    }
}