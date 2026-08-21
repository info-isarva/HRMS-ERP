<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DutyRoster extends Model
{
    protected $fillable = ['employee_payroll_id', 'shift_id', 'date'];

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }
}
