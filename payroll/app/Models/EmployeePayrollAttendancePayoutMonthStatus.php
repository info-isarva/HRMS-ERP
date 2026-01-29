<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeePayrollAttendancePayoutMonthStatus extends Model
{
   // use SoftDeletes;

    protected $table = 'employee_payroll_attendance_payout_month_statuses';

    protected $fillable = [
        'payout_month',
        'payout_year',
        'location_id',
        'status',
        'ot_finalized',
        'incentive_finalized',
        'holiday_work_payout_finalized',
        'created_by',
        'updated_by',
        'finalized_at',
        'finalized_by'
    ];
}
