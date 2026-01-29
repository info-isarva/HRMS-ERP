<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ActivityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'user_name',
        'email',
        'phone_number',
        'role_name',
        'activity_type',
        'module',
        'description',
        'old_data',
        'new_data',
        'ip_address',
        'user_agent',
        'session_id',
        'activity_timestamp',
    ];

    protected $casts = [
        'old_data' => 'array',
        'new_data' => 'array',
        'activity_timestamp' => 'datetime',
    ];

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_timestamp', [$startDate, $endDate]);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by activity type
     */
    public function scopeByActivityType($query, $activityType)
    {
        return $query->where('activity_type', $activityType);
    }

    /**
     * Scope to filter by module
     */
    public function scopeByModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Format activity timestamp for display
     */
    public function getFormattedTimestampAttribute()
    {
        return $this->activity_timestamp->format('d-m-Y H:i:s');
    }

    /**
     * Get user relation (if user still exists)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
