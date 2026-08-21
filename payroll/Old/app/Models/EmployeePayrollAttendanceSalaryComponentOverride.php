<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeePayrollAttendanceSalaryComponentOverride extends Model
{
    protected $table = 'employee_payroll_attendance_salary_component_overrides';

    protected $fillable = [
        'emp_id',
        'payroll_attendance_id',
        'salary_component_id',
        'default_value',
        'override_value',
        'created_by',
        'updated_by',
    ];
}
