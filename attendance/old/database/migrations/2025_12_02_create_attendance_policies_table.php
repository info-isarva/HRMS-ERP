<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attendance_policies', function (Blueprint $table) {
            $table->id();
            
            // Basic Info
            $table->string('name')->default('Default Attendance Policy');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Grace Periods (in minutes)
            $table->integer('late_arrival_grace_minutes')->default(10)->comment('Grace period for late arrival (no penalty)');
            $table->integer('early_departure_grace_minutes')->default(10)->comment('Grace period for early departure (no penalty)');
            $table->integer('early_arrival_grace_minutes')->default(30)->comment('How early can employee arrive (before counting as early)');
            $table->integer('late_departure_grace_minutes')->default(30)->comment('How late can employee stay (before counting OT)');
            
            // Half Day Thresholds (in minutes)
            $table->integer('half_day_late_threshold_minutes')->default(120)->comment('Late arrival threshold for half day (e.g., 2 hours)');
            $table->integer('half_day_early_departure_threshold_minutes')->default(120)->comment('Early departure threshold for half day (e.g., leaving 2 hours early)');
            $table->integer('half_day_minimum_hours')->default(4)->comment('Minimum hours worked for half day');
            
            // Absent Thresholds (in minutes)
            $table->integer('absent_threshold_minutes')->default(240)->comment('Late arrival threshold for absent (e.g., 4 hours)');
            $table->integer('minimum_work_hours_for_present')->default(6)->comment('Minimum hours to mark as present');
            
            // Overtime Rules
            $table->boolean('enable_overtime')->default(true)->comment('Enable OT calculation');
            $table->integer('overtime_start_after_minutes')->default(30)->comment('OT starts after staying X minutes beyond shift end');
            $table->decimal('overtime_multiplier', 4, 2)->default(1.5)->comment('OT pay multiplier (e.g., 1.5x)');
            $table->integer('maximum_overtime_hours_per_day')->default(4)->comment('Maximum OT hours allowed per day');
            $table->boolean('require_approval_for_overtime')->default(false)->comment('Require manager approval for OT');
            
            // Undertime Rules
            $table->boolean('deduct_undertime_from_salary')->default(true)->comment('Deduct undertime from salary');
            $table->boolean('allow_undertime_adjustment')->default(true)->comment('Allow manual undertime adjustment');
            
            // Weekend/Holiday Rules
            $table->boolean('weekend_overtime_applies')->default(true)->comment('Apply OT rules on weekends');
            $table->decimal('weekend_overtime_multiplier', 4, 2)->default(2.0)->comment('Weekend OT multiplier (e.g., 2x)');
            $table->decimal('holiday_overtime_multiplier', 4, 2)->default(2.5)->comment('Holiday OT multiplier (e.g., 2.5x)');
            
            // Rounding Rules
            $table->boolean('round_check_in_time')->default(false)->comment('Round check-in time to nearest interval');
            $table->integer('check_in_rounding_minutes')->default(15)->comment('Round check-in to nearest X minutes');
            $table->boolean('round_check_out_time')->default(false)->comment('Round check-out time to nearest interval');
            $table->integer('check_out_rounding_minutes')->default(15)->comment('Round check-out to nearest X minutes');
            
            // Consecutive Late Policy
            $table->boolean('track_consecutive_late')->default(true)->comment('Track consecutive late arrivals');
            $table->integer('consecutive_late_limit')->default(3)->comment('Max consecutive late days before action');
            $table->string('consecutive_late_action')->default('warning')->comment('Action: warning, half_day, absent');
            
            // Monthly Cumulative Rules
            $table->boolean('track_monthly_late_minutes')->default(true)->comment('Track cumulative late minutes per month');
            $table->integer('monthly_late_minutes_warning_threshold')->default(60)->comment('Warning at X total late minutes/month');
            $table->integer('monthly_late_minutes_penalty_threshold')->default(120)->comment('Penalty at X total late minutes/month');
            $table->string('monthly_late_penalty_type')->default('half_day')->comment('Penalty type: warning, half_day, full_day');
            
            // Break/Lunch Deduction
            $table->boolean('deduct_break_time')->default(false)->comment('Deduct break/lunch time from total hours');
            $table->integer('break_duration_minutes')->default(60)->comment('Break duration to deduct (e.g., 1 hour lunch)');
            
            // Shift Flexibility
            $table->boolean('allow_flexible_timing')->default(false)->comment('Allow flexible work hours');
            $table->integer('flexible_buffer_minutes')->default(60)->comment('Flexibility buffer (e.g., can arrive 1 hour before/after)');
            
            // Notification Settings
            $table->boolean('notify_on_late_arrival')->default(true)->comment('Send notification on late arrival');
            $table->boolean('notify_on_early_departure')->default(true)->comment('Send notification on early departure');
            $table->boolean('notify_manager_on_violation')->default(true)->comment('Notify manager on policy violation');
            
            $table->timestamps();
            
            // Indexes
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_policies');
    }
};
