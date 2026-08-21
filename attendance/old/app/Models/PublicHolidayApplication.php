<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class PublicHolidayApplication extends Model
{
    use LogsActivity;

    protected $fillable = [
        'payroll_id',
        'user_id',
        'email',
        'public_holiday_id',
        'department_id',
        'financial_year',
        'status',
        'reason',
        'applied_at',
        'approved_at',
        'approved_by',
        'rejected_at',
        'rejected_by',
        'rejection_reason'
    ];

    protected $casts = [
        'applied_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Get the user who applied for the holiday
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee who applied for the holiday (by payroll_id)
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'payroll_id', 'payroll_id');
    }

    /**
     * Get the employee who applied for the holiday (by email for backward compatibility)
     */
    public function employeeByEmail()
    {
        return $this->belongsTo(Employee::class, 'email', 'email');
    }

    /**
     * Get the public holiday
     */
    public function publicHoliday(): BelongsTo
    {
        return $this->belongsTo(PublicHoliday::class);
    }

    /**
     * Get the department
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the user who approved the application
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who rejected the application
     */
    public function rejector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Scope for pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved applications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected applications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for a specific user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for a specific employee email
     */
    public function scopeForEmail($query, $email)
    {
        return $query->where('email', $email);
    }

    /**
     * Scope for a specific payroll ID
     */
    public function scopeForPayrollId($query, $payrollId)
    {
        return $query->where('payroll_id', $payrollId);
    }

    /**
     * Scope for a specific financial year
     */
    public function scopeForFinancialYear($query, $year)
    {
        return $query->where('financial_year', $year);
    }

    /**
     * Check if the application is approved
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if the application is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if the application is rejected
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reason', 'rejection_reason'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Public holiday application {$eventName}")
            ->useLogName('public_holiday_application');
    }
}
