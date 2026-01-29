<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class AttendanceBatch extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'month',
        'year',
        'status', // 'processing', 'completed', 'failed'
        'total_records',
        'processed_records',
        'failed_records',
        'initiated_by',
        'completed_at',
        'is_locked',
    ];

    protected $casts = [
        'is_locked' => 'boolean',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the user that initiated this batch.
     */
    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    /**
     * Get the attendance records associated with this batch.
     */
    public function attendanceRecords()
    {
        return $this->hasMany(AttendanceRecord::class, 'batch_id');
    }

    /**
     * Scope a query to only include batches for a specific month and year.
     */
    public function scopeForMonthYear($query, $month, $year)
    {
        return $query->where('month', $month)->where('year', $year);
    }

    /**
     * Scope a query to only include locked batches.
     */
    public function scopeLocked($query)
    {
        return $query->where('is_locked', true);
    }

    /**
     * Scope a query to only include completed batches.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'month', 'year', 'status', 'total_records', 
                'processed_records', 'failed_records', 'is_locked'
            ])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Attendance batch {$eventName}")
            ->useLogName('attendance_batch');
    }
}
