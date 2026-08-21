<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeGpsPing extends Model
{
    protected $fillable = [
        'employee_id',
        'user_id',
        'latitude',
        'longitude',
        'altitude',
        'accuracy',
        'speed',
        'bearing',
        'track_date',
        'recorded_at',
        'source',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'altitude' => 'float',
        'accuracy' => 'float',
        'speed' => 'float',
        'bearing' => 'float',
        'track_date' => 'date',
        'recorded_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
