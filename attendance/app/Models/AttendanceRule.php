<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    protected $fillable = [
        'name',
        'shift_threshold_hours',
        'recovery_days_offset',
        'recovery_status',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shift_threshold_hours' => 'decimal:2',
    ];
}
