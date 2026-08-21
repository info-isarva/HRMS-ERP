<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeStationMapping extends Model
{
    protected $fillable = [
        'ts_user_id',
        'ts_name',
        'ts_department',
        'employee_payroll_id',
        'is_ignored',
    ];

    protected $casts = [
        'is_ignored' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }
}
