<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManualPunch extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_payroll_id',
        'employee_id',
        'employee_email',
        'date',
        'punch_in_time',
        'punch_out_time',
        'reason',
        'shift_id',
        'added_by',
        'approved_by',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'date' => 'date',
        'punch_in_time' => 'datetime:H:i',
        'punch_out_time' => 'datetime:H:i',
    ];

    /**
     * Get the employee associated with this manual punch
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }

    /**
     * Get the shift assigned
     */
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user who added this punch
     */
    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by');
    }

    /**
     * Get the user who approved this punch
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope for specific employee
     */
    public function scopeForEmployee($query, $payrollId)
    {
        return $query->where('employee_payroll_id', $payrollId);
    }

    /**
     * Scope for specific date
     */
    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    /**
     * Scope for date range
     */
    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope for approved punches only
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Get formatted punch in time
     */
    public function getPunchInFormattedAttribute()
    {
        return $this->punch_in_time ? \Carbon\Carbon::parse($this->punch_in_time)->format('H:i') : null;
    }

    /**
     * Get formatted punch out time
     */
    public function getPunchOutFormattedAttribute()
    {
        return $this->punch_out_time ? \Carbon\Carbon::parse($this->punch_out_time)->format('H:i') : null;
    }
}
