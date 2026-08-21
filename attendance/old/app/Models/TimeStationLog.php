<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeStationLog extends Model
{
    protected $fillable = [
        'ts_activity_id',
        'ts_user_id',
        'employee_payroll_id',
        'timestamp',
        'activity_type',
        'device_id',
        'gps_location',
        'raw_response',
        'sync_status',
        'sync_error'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
        'raw_response' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }
}
