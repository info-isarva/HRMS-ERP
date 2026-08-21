<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveApplicationDay extends Model
{
    protected $fillable = [
        'leave_application_id',
        'leave_date',
        'day_type',
        'days_count',
        'is_public_holiday',
        'is_week_off',
        'exclude_from_calculation',
        'notes'
    ];

    protected $casts = [
        'leave_date' => 'date',
        'days_count' => 'decimal:1',
        'is_public_holiday' => 'boolean',
        'is_week_off' => 'boolean',
        'exclude_from_calculation' => 'boolean'
    ];

    /**
     * Get the leave application that owns this day
     */
    public function leaveApplication(): BelongsTo
    {
        return $this->belongsTo(LeaveApplication::class);
    }

    /**
     * Check if this is a full day leave
     */
    public function isFullDay(): bool
    {
        return $this->day_type === 'full_day';
    }

    /**
     * Check if this is a half day leave
     */
    public function isHalfDay(): bool
    {
        return in_array($this->day_type, ['first_half', 'second_half']);
    }

    /**
     * Get formatted day type for display
     */
    public function getFormattedDayTypeAttribute(): string
    {
        return match($this->day_type) {
            'full_day' => 'Full Day',
            'first_half' => 'First Half (Morning)',
            'second_half' => 'Second Half (Afternoon)',
            default => 'Full Day'
        };
    }
}