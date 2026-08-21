<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Overtime extends Model
{
    // Use plural table name to follow Laravel conventions and match migrations
    protected $table = 'overtimes';

    protected $fillable = [
        'employee_payroll_id',
        'month',
        'year',
        'overtime_hours',
        'calculated_ot_hours',
        'approved_ot_hours',
        'is_manually_overridden',
        'original_calculated_hours',
        'overridden_by',
        'overridden_at',
        'approval_status',
        'approved_by',
        'approved_at',
        'remarks',
        'is_locked',
        'locked_at',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'overtime_hours' => 'decimal:2',
        'calculated_ot_hours' => 'decimal:2',
        'approved_ot_hours' => 'decimal:2',
        'original_calculated_hours' => 'decimal:2',
        'is_manually_overridden' => 'boolean',
        'is_locked' => 'boolean',
        'locked_at' => 'datetime',
        'overridden_at' => 'datetime',
        'approved_at' => 'datetime'
    ];

    /**
     * Get the employee that owns the overtime record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }

    /**
     * Get the user who created this record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who overrode the calculated OT.
     */
    public function overrider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'overridden_by');
    }

    /**
     * Get the user who approved the OT.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope to get only locked records (for API).
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope to get records for a specific month/year.
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope to get pending approval records.
     */
    public function scopePendingApproval($query)
    {
        return $query->where('approval_status', 'pending');
    }

    /**
     * Get the final OT hours to use (approved if overridden, else calculated).
     */
    public function getFinalOtHoursAttribute()
    {
        return $this->approved_ot_hours ?? $this->calculated_ot_hours;
    }
}
