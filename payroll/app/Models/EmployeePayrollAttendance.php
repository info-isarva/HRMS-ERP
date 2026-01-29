<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePayrollAttendance extends Model
{
    protected $table = 'employee_payroll_attendances';

    protected $fillable = [
        'emp_id',
        'payout_month_id',
        'total_working_days',
        'employee_worked_days',
        'gross_pay',
        'earnings',
        'deductions',
        'total_deduction',
        'total_payable',
        'manual_override',
        'early_salary_processed',
        'created_by',
        'updated_by',
    ];

    public function employee()
    {
        return $this->belongsTo(EmployeeBasicDetail::class, 'emp_id', 'id');
    }

    public function payoutMonth()
    {
        return $this->belongsTo(EmployeePayrollAttendancePayoutMonthStatus::class, 'payout_month_id', 'id');
    }

    public function salaryOverrides()
    {
        return $this->hasMany(EmployeePayrollAttendanceSalaryComponentOverride::class, 'payroll_attendance_id', 'id');
    }

    public function statutoryOverrides()
    {
        return $this->hasMany(EmployeePayrollAttendanceStatutoryComponentOverride::class, 'payroll_attendance_id', 'id');
    }
}
