<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePayrollAttendanceStatutoryComponentOverride extends Model
{
    protected $table = 'employee_payroll_attendance_statutory_component_overrides';

    protected $fillable = [
        'emp_id',
        'payroll_attendance_id',
        'statutory_component_id',
        'default_value',
        'override_value',
        'created_by',
        'updated_by',
    ];
}
