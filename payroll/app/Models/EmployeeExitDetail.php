<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeExitDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'emp_id',
        'exit_type',
        'resignation_date',
        'last_working_day',
        'reason',
        'status',
        'notice_period_days',
        'exit_interview_conducted',
        'exit_interview_notes',
        'approved_by',
        'remarks',
        'settlement_mode',
        'settlement_date',
        'settlement_amount',
        'pending_advance',
        'settlement_notes',
        // FFS Detailed Columns
        'leave_encashment_days_calculated',
        'leave_encashment_days_override',
        'leave_encashment_amount_calculated',
        'leave_encashment_amount_override',
        'notice_period_shortfall_days',
        'notice_pay_amount_calculated',
        'notice_pay_amount_override',
        'gratuity_tenure_years_calculated',
        'gratuity_tenure_years_override',
        'gratuity_amount_calculated',
        'gratuity_amount_override',
        'bonus_amount_calculated',
        'bonus_amount_override',
        'other_earnings',
        'other_deductions',
        'prorated_salary_amount',
        'prorated_statutory_credit',
        'prorated_statutory_debit',
    ];

    protected $casts = [
        'resignation_date' => 'date',
        'last_working_day' => 'date',
        'exit_interview_conducted' => 'boolean',
        'settlement_date' => 'date',
        'settlement_amount' => 'decimal:2',
        'pending_advance' => 'decimal:2',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
