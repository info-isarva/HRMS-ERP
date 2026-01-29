<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PublicHoliday;
use App\Models\Department;
use Illuminate\Support\Facades\DB;

class AssignFixedHolidaysSeeder extends Seeder
{
    public function run()
    {
        $departments = Department::all();
        $fixedHolidays = PublicHoliday::where('type', 'fixed')->get();
        
        foreach($departments as $dept) {
            foreach($fixedHolidays as $holiday) {
                DB::table('department_public_holidays')->updateOrInsert([
                    'department_id' => $dept->id,
                    'public_holiday_id' => $holiday->id
                ], [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
        
        $this->command->info('Assigned fixed holidays to all departments.');
    }
}
