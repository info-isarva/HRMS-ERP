<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_payroll_id',
        'date',
        'check_in_time',
        'check_out_time',
        'total_hours',
        'status',
        'shift_id',
        'raw_data',
        'processed_at',
        'source',
        'notes',
        'is_late_arrival',
        'is_early_arrival',
        'is_late_departure',
        'is_early_departure',
        'is_overtime',
        'late_arrival_minutes',
        'early_departure_minutes',
        'overtime_hours',
        'scheduled_start_time',
        'scheduled_end_time',
        'attendance_category',
        'undertime_hours',
    ];

    protected $casts = [
        'date' => 'date',
        'check_in_time' => 'string',
        'check_out_time' => 'string',
        'total_hours' => 'decimal:2',
        'raw_data' => 'array',
        'processed_at' => 'datetime',
        'undertime_hours' => 'decimal:2',
        'overtime_hours' => 'decimal:2',
        'late_arrival_minutes' => 'integer',
        'early_departure_minutes' => 'integer',
        'is_late_arrival' => 'boolean',
        'is_early_arrival' => 'boolean',
        'is_late_departure' => 'boolean',
        'is_early_departure' => 'boolean',
        'is_overtime' => 'boolean',
        'scheduled_start_time' => 'string',
        'scheduled_end_time' => 'string'
    ];

    /**
     * Get the employee that owns the attendance record.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_payroll_id', 'payroll_id');
    }

    /**
     * Get the shift associated with the attendance record.
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Calculate total hours based on check-in and check-out times.
     */
    public function calculateTotalHours(): float
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return 0;
        }

        $checkIn = strtotime($this->check_in_time);
        $checkOut = strtotime($this->check_out_time);

        if ($checkOut <= $checkIn) {
            return 0; // Invalid times
        }

        $totalSeconds = $checkOut - $checkIn;
        return round($totalSeconds / 3600, 2); // Convert to hours
    }

    /**
     * Determine attendance status based on shift timings and total hours.
     */
    public function determineStatus(): string
    {
        if (!$this->shift) {
            return $this->total_hours > 0 ? 'present' : 'absent';
        }

        $shiftStart = strtotime($this->shift->start_time);
        $shiftEnd = strtotime($this->shift->end_time);
        $checkIn = $this->check_in_time ? strtotime($this->check_in_time) : null;
        $checkOut = $this->check_out_time ? strtotime($this->check_out_time) : null;

        if (!$checkIn || !$checkOut) {
            return 'absent';
        }

        $totalHours = $this->calculateTotalHours();
        $shiftHours = ($shiftEnd - $shiftStart) / 3600;

        // Late arrival (more than 15 minutes after shift start)
        if ($checkIn > $shiftStart + 900) {
            return 'late';
        }

        // Early departure (more than 15 minutes before shift end)
        if ($checkOut < $shiftEnd - 900) {
            return 'early_departure';
        }

        // Half day (less than 50% of shift hours)
        if ($totalHours < ($shiftHours * 0.5)) {
            return 'half_day';
        }

        // Overtime (more than 110% of shift hours)
        if ($totalHours > ($shiftHours * 1.1)) {
            return 'overtime';
        }

        return 'present';
    }
}
