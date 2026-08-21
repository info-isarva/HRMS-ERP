<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAdvanceDeduction extends Model
{
    use HasFactory;

    protected $fillable = [
        'advance_id',
        'month',
        'year',
        'amount',
        'is_overridden',
        'original_amount',
        'created_by'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_amount' => 'decimal:2',
        'is_overridden' => 'boolean'
    ];

    /**
     * Relationships
     */
    public function advance()
    {
        return $this->belongsTo(EmployeeAdvance::class, 'advance_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    public function scopeForEmployee($query, $employeeId)
    {
        return $query->whereHas('advance', function ($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        });
    }

    /**
     * Get the period string (e.g., "Jan 2025")
     */
    public function getPeriodAttribute()
    {
        return \Carbon\Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }
}
