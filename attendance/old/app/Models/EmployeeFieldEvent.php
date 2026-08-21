<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeFieldEvent extends Model
{
    public const TYPE_OFFICE = 'office';

    public const TYPE_VISIT = 'visit';

    public const TYPE_TRAVEL = 'travel';

    protected $fillable = [
        'employee_id',
        'user_id',
        'event_type',
        'place_name',
        'address',
        'latitude',
        'longitude',
        'track_date',
        'check_in_at',
        'check_out_at',
        'travel_distance_km',
        'travel_duration_minutes',
        'metadata',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'track_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
        'travel_distance_km' => 'float',
        'travel_duration_minutes' => 'integer',
        'metadata' => 'array',
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
