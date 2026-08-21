<?php

namespace Database\Seeders;

use App\Models\AttendancePolicy;
use Illuminate\Database\Seeder;

class AttendancePolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AttendancePolicy::create([
            'name' => 'Default Attendance Policy',
            'description' => 'Standard attendance policy with moderate rules suitable for most organizations',
            'is_active' => true,
            
            // Grace Periods (in minutes)
            'late_arrival_grace_minutes' => 10,
            'early_departure_grace_minutes' => 10,
            'early_arrival_grace_minutes' => 30,
            'late_departure_grace_minutes' => 30,
            
            // Half Day Thresholds
            'half_day_late_threshold_minutes' => 120, // 2 hours
            'half_day_early_departure_threshold_minutes' => 120, // leaving 2 hours early
            'half_day_minimum_hours' => 4,
            
            // Absent Thresholds
            'absent_threshold_minutes' => 240, // 4 hours late = absent
            'minimum_work_hours_for_present' => 6,
            
            // Overtime Rules
            'enable_overtime' => true,
            'overtime_start_after_minutes' => 30, // OT starts after 30 min beyond shift
            'overtime_multiplier' => 1.5,
            'maximum_overtime_hours_per_day' => 4,
            'require_approval_for_overtime' => false,
            
            // Undertime Rules
            'deduct_undertime_from_salary' => true,
            'allow_undertime_adjustment' => true,
            
            // Weekend/Holiday Rules
            'weekend_overtime_applies' => true,
            'weekend_overtime_multiplier' => 2.0,
            'holiday_overtime_multiplier' => 2.5,
            
            // Rounding Rules
            'round_check_in_time' => false,
            'check_in_rounding_minutes' => 15,
            'round_check_out_time' => false,
            'check_out_rounding_minutes' => 15,
            
            // Consecutive Late Policy
            'track_consecutive_late' => true,
            'consecutive_late_limit' => 3,
            'consecutive_late_action' => 'warning',
            
            // Monthly Cumulative Rules
            'track_monthly_late_minutes' => true,
            'monthly_late_minutes_warning_threshold' => 60,
            'monthly_late_minutes_penalty_threshold' => 120,
            'monthly_late_penalty_type' => 'half_day',
            
            // Break/Lunch Deduction
            'deduct_break_time' => false,
            'break_duration_minutes' => 60,
            
            // Shift Flexibility
            'allow_flexible_timing' => false,
            'flexible_buffer_minutes' => 60,
            
            // Notification Settings
            'notify_on_late_arrival' => true,
            'notify_on_early_departure' => true,
            'notify_manager_on_violation' => true,
        ]);

        // Additional preset policies for different sectors
        AttendancePolicy::create([
            'name' => 'Strict Policy (Manufacturing/Production)',
            'description' => 'Strict attendance policy with minimal grace periods - suitable for manufacturing, production, shift-based work',
            'is_active' => false,
            'late_arrival_grace_minutes' => 5,
            'early_departure_grace_minutes' => 5,
            'half_day_late_threshold_minutes' => 60, // 1 hour
            'absent_threshold_minutes' => 180, // 3 hours
            'minimum_work_hours_for_present' => 7,
            'overtime_multiplier' => 2.0,
            'track_consecutive_late' => true,
            'consecutive_late_limit' => 2,
            'consecutive_late_action' => 'half_day',
        ]);

        AttendancePolicy::create([
            'name' => 'Flexible Policy (IT/Creative)',
            'description' => 'Flexible attendance policy with generous grace periods - suitable for IT, creative, knowledge work',
            'is_active' => false,
            'late_arrival_grace_minutes' => 30,
            'early_departure_grace_minutes' => 30,
            'half_day_late_threshold_minutes' => 180, // 3 hours
            'absent_threshold_minutes' => 300, // 5 hours
            'minimum_work_hours_for_present' => 5,
            'allow_flexible_timing' => true,
            'flexible_buffer_minutes' => 120, // 2 hours flexibility
            'require_approval_for_overtime' => true,
            'track_consecutive_late' => false,
        ]);
    }
}
