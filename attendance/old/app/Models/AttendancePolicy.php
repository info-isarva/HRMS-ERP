<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendancePolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_active',
        'late_arrival_grace_minutes',
        'early_departure_grace_minutes',
        'early_arrival_grace_minutes',
        'late_departure_grace_minutes',
        'half_day_late_threshold_minutes',
        'half_day_early_departure_threshold_minutes',
        'half_day_minimum_hours',
        'absent_threshold_minutes',
        'minimum_work_hours_for_present',
        'enable_overtime',
        'overtime_start_after_minutes',
        'overtime_multiplier',
        'maximum_overtime_hours_per_day',
        'require_approval_for_overtime',
        'deduct_undertime_from_salary',
        'allow_undertime_adjustment',
        'weekend_overtime_applies',
        'weekend_overtime_multiplier',
        'holiday_overtime_multiplier',
        'round_check_in_time',
        'check_in_rounding_minutes',
        'round_check_out_time',
        'check_out_rounding_minutes',
        'track_consecutive_late',
        'consecutive_late_limit',
        'consecutive_late_action',
        'track_monthly_late_minutes',
        'monthly_late_minutes_warning_threshold',
        'monthly_late_minutes_penalty_threshold',
        'monthly_late_penalty_type',
        'deduct_break_time',
        'break_duration_minutes',
        'allow_flexible_timing',
        'flexible_buffer_minutes',
        'notify_on_late_arrival',
        'notify_on_early_departure',
        'notify_manager_on_violation',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'enable_overtime' => 'boolean',
        'require_approval_for_overtime' => 'boolean',
        'deduct_undertime_from_salary' => 'boolean',
        'allow_undertime_adjustment' => 'boolean',
        'weekend_overtime_applies' => 'boolean',
        'round_check_in_time' => 'boolean',
        'round_check_out_time' => 'boolean',
        'track_consecutive_late' => 'boolean',
        'track_monthly_late_minutes' => 'boolean',
        'deduct_break_time' => 'boolean',
        'allow_flexible_timing' => 'boolean',
        'notify_on_late_arrival' => 'boolean',
        'notify_on_early_departure' => 'boolean',
        'notify_manager_on_violation' => 'boolean',
        'overtime_multiplier' => 'decimal:2',
        'weekend_overtime_multiplier' => 'decimal:2',
        'holiday_overtime_multiplier' => 'decimal:2',
    ];

    /**
     * Get the active policy
     */
    public static function getActivePolicy(): ?self
    {
        return self::where('is_active', true)->first() ?? self::first();
    }

    /**
     * Apply grace period to late arrival minutes
     */
    public function applyLateArrivalGrace(int $lateMinutes): int
    {
        if ($lateMinutes <= $this->late_arrival_grace_minutes) {
            return 0; // Within grace period
        }
        return $lateMinutes;
    }

    /**
     * Apply grace period to early departure minutes
     */
    public function applyEarlyDepartureGrace(int $earlyMinutes): int
    {
        if ($earlyMinutes <= $this->early_departure_grace_minutes) {
            return 0; // Within grace period
        }
        return $earlyMinutes;
    }

    /**
     * Determine attendance status based on policy rules
     */
    public function determineStatus(array $attendance): string
    {
        $lateMinutes = $attendance['late_arrival_minutes'] ?? 0;
        $earlyDepartureMinutes = $attendance['early_departure_minutes'] ?? 0;
        $totalHours = $attendance['total_hours'] ?? 0;
        $checkIn = $attendance['check_in_time'] ?? null;
        $checkOut = $attendance['check_out_time'] ?? null;

        // No punch data
        if (!$checkIn && !$checkOut) {
            return 'absent';
        }

        if (!$checkIn || !$checkOut) {
            return 'pm';
        }

        // Apply grace periods
        $effectiveLate = $this->applyLateArrivalGrace($lateMinutes);
        $effectiveEarlyDeparture = $this->applyEarlyDepartureGrace($earlyDepartureMinutes);

        // Check for absent (very late or no show)
        if ($lateMinutes >= $this->absent_threshold_minutes) {
            return 'absent';
        }

        // Check for insufficient work hours (Custom threshold: less than 3.0 hours is absent)
        if ($totalHours > 0 && $totalHours < 3.0) {
            return 'absent';
        }

        // Check for half day (total hours Custom threshold: between 3.0 and 6.5 hours)
        if ($totalHours > 0 && $totalHours >= 3.0 && $totalHours < 6.5) {
            return 'half_day';
        }

        // Check for half day (late arrival)
        if ($lateMinutes >= $this->half_day_late_threshold_minutes) {
            return 'half_day';
        }

        // Check for half day (early departure)
        if ($earlyDepartureMinutes >= $this->half_day_early_departure_threshold_minutes) {
            return 'half_day';
        }

        // Late but present (beyond grace period)
        if ($effectiveLate > 0) {
            return 'late';
        }

        // Early departure but not half day
        if ($effectiveEarlyDeparture > 0) {
            return 'early_departure';
        }

        // All good
        return 'present';
    }

    /**
     * Calculate overtime with policy rules
     */
    public function calculateOvertime(float $overtimeHours, bool $isWeekend = false, bool $isHoliday = false): array
    {
        if (!$this->enable_overtime) {
            return [
                'overtime_hours' => 0,
                'overtime_pay_multiplier' => 0,
                'overtime_amount' => 0,
            ];
        }

        // Apply maximum limit
        $overtimeHours = min($overtimeHours, $this->maximum_overtime_hours_per_day);

        // Determine multiplier
        $multiplier = $this->overtime_multiplier;
        if ($isHoliday) {
            $multiplier = $this->holiday_overtime_multiplier;
        } elseif ($isWeekend && $this->weekend_overtime_applies) {
            $multiplier = $this->weekend_overtime_multiplier;
        }

        return [
            'overtime_hours' => round($overtimeHours, 2),
            'overtime_pay_multiplier' => $multiplier,
            'requires_approval' => $this->require_approval_for_overtime,
        ];
    }

    /**
     * Round time based on policy
     */
    public function roundTime(string $time, string $type = 'check_in'): string
    {
        if ($type === 'check_in' && !$this->round_check_in_time) {
            return $time;
        }

        if ($type === 'check_out' && !$this->round_check_out_time) {
            return $time;
        }

        $roundingMinutes = $type === 'check_in' ? $this->check_in_rounding_minutes : $this->check_out_rounding_minutes;

        $timestamp = strtotime($time);
        $minutes = date('i', $timestamp);
        $roundedMinutes = round($minutes / $roundingMinutes) * $roundingMinutes;

        return date('H:i:s', strtotime(date('Y-m-d H:00:00', $timestamp) . " +{$roundedMinutes} minutes"));
    }
}
