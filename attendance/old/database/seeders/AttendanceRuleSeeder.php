<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AttendanceRule;
use Illuminate\Support\Facades\DB;

class AttendanceRuleSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing rules to start fresh for testing
        DB::table('attendance_rules')->truncate();

        // 1. Extreme Shift Rule (Highest Priority)
        AttendanceRule::create([
            'name' => 'Extreme Shift (20h+)',
            'shift_threshold_hours' => 20,
            'recovery_days_offset' => 1, // Rest on next day
            'recovery_status' => 'compoff',
            'is_active' => true
        ]);

        // 2. Long Shift Rule
        AttendanceRule::create([
            'name' => 'Long Shift (15h - 20h)',
            'shift_threshold_hours' => 15,
            'recovery_days_offset' => 2, // Rest after 2 days
            'recovery_status' => 'compoff',
            'is_active' => true
        ]);

        // 3. Double Recovery Rule (Special Test)
        AttendanceRule::create([
            'name' => 'Night Owl (12h - 15h)',
            'shift_threshold_hours' => 12,
            'recovery_days_offset' => 1,
            'recovery_status' => 'present', // Just mark present
            'is_active' => true
        ]);
        
        // 4. Standard Work Rule
        AttendanceRule::create([
            'name' => 'Standard Work (>4h)',
            'shift_threshold_hours' => 4,
            'recovery_days_offset' => 0,
            'recovery_status' => 'present',
            'is_active' => true
        ]);
    }
}
