<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class EmployeeAdvance extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'advance_amount',
        'tenure_months',
        'monthly_deduction',
        'start_date',
        'end_date',
        'total_deducted',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'advance_amount' => 'decimal:2',
        'monthly_deduction' => 'decimal:2',
        'total_deducted' => 'decimal:2',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    /**
     * Relationships
     */
    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'employee_id');
    }

    public function deductions()
    {
        return $this->hasMany(EmployeeAdvanceDeduction::class, 'advance_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /**
     * Business Logic Methods
     */

    /**
     * Calculate monthly deduction amount
     */
    public function calculateMonthlyDeduction()
    {
        if ($this->tenure_months > 0) {
            return round($this->advance_amount / $this->tenure_months, 2);
        }
        return 0;
    }

    /**
     * Calculate end date based on start date and tenure
     */
    public function calculateEndDate()
    {
        if ($this->start_date && $this->tenure_months > 0) {
            return Carbon::parse($this->start_date)
                ->addMonths($this->tenure_months - 1)
                ->endOfMonth()
                ->toDateString();
        }
        return null;
    }

    /**
     * Get remaining amount to be deducted
     */
    public function getRemainingAmountAttribute()
    {
        $totalDeducted = $this->deductions()->sum('amount');
        return $this->advance_amount - $totalDeducted;
    }

    /**
     * Get total deducted amount
     */
    public function getTotalDeductedAttribute()
    {
        return $this->deductions()->sum('amount');
    }

    /**
     * Get current balance
     */
    public function getBalanceAttribute()
    {
        return $this->remaining_amount;
    }

    /**
     * Check if advance is active for a specific month/year
     */
    public function isActiveForMonth($month, $year)
    {
        if ($this->status !== 'active') {
            return false;
        }

        $currentMonth = Carbon::createFromDate($year, $month, 1);
        $startMonth = Carbon::parse($this->start_date)->startOfMonth();
        $endMonth = Carbon::parse($this->end_date)->endOfMonth();

        return $currentMonth->between($startMonth, $endMonth);
    }

    /**
     * Get deduction amount for a specific month/year
     */
    public function getDeductionForMonth($month, $year)
    {
        if (!$this->isActiveForMonth($month, $year)) {
            return 0;
        }

        // Check if already deducted
        $existingDeduction = $this->deductions()
            ->where('month', $month)
            ->where('year', $year)
            ->first();

        if ($existingDeduction) {
            return $existingDeduction->amount;
        }

        // Calculate new deduction
        $remainingAmount = $this->remaining_amount;
        return min($this->monthly_deduction, $remainingAmount);
    }

    /**
     * Auto-populate calculated fields before saving
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($advance) {
            // Auto-calculate monthly deduction if not set
            if (!$advance->monthly_deduction) {
                $advance->monthly_deduction = $advance->calculateMonthlyDeduction();
            }

            // Auto-calculate end date if not set
            if (!$advance->end_date) {
                $advance->end_date = $advance->calculateEndDate();
            }
        });

        static::updating(function ($advance) {
            // Recalculate end date if tenure changed
            if ($advance->isDirty(['start_date', 'tenure_months'])) {
                $advance->end_date = $advance->calculateEndDate();
            }

            // Recalculate monthly deduction if advance amount or tenure changed
            if ($advance->isDirty(['advance_amount', 'tenure_months'])) {
                $advance->monthly_deduction = $advance->calculateMonthlyDeduction();
            }
        });
    }
}
