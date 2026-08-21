<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PublicHoliday;
use App\Models\Department;
use Carbon\Carbon;

class FlexiblePublicHolidaySeeder extends Seeder
{
    public function run()
    {
        $currentYear = Carbon::now()->year;
        $financialYear = $currentYear . '-' . ($currentYear + 1);
        
        // Create flexible public holidays
        $flexibleHolidays = [
            [
                'name' => 'Diwali',
                'description' => 'Festival of Lights',
                'date' => Carbon::create($currentYear, 11, 12),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#f59e0b',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Eid al-Fitr',
                'description' => 'Festival of Breaking the Fast',
                'date' => Carbon::create($currentYear, 6, 17),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#10b981',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Holi',
                'description' => 'Festival of Colors',
                'date' => Carbon::create($currentYear, 3, 25),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#e11d48',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Dussehra',
                'description' => 'Victory of Good over Evil',
                'date' => Carbon::create($currentYear, 10, 22),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#8b5cf6',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Guru Nanak Jayanti',
                'description' => 'Guru Nanak\'s Birthday',
                'date' => Carbon::create($currentYear, 11, 15),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#0ea5e9',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Karva Chauth',
                'description' => 'Festival for married women',
                'date' => Carbon::create($currentYear, 10, 28),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#ec4899',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Raksha Bandhan',
                'description' => 'Festival celebrating sibling bond',
                'date' => Carbon::create($currentYear, 8, 30),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#f97316',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Janmashtami',
                'description' => 'Krishna\'s Birthday',
                'date' => Carbon::create($currentYear, 8, 26),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#3b82f6',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Mahashivratri',
                'description' => 'Night of Shiva',
                'date' => Carbon::create($currentYear, 2, 26),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#6366f1',
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'name' => 'Regional Day',
                'description' => 'Local/Regional Holiday',
                'date' => Carbon::create($currentYear, 7, 15),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#84cc16',
                'created_by' => 1,
                'updated_by' => 1,
            ]
        ];

        // Create holidays
        $createdHolidays = [];
        foreach ($flexibleHolidays as $holidayData) {
            $holiday = PublicHoliday::create($holidayData);
            $createdHolidays[] = $holiday;
        }

        // Assign holidays to departments
        $departments = Department::all();
        
        foreach ($departments as $department) {
            // Assign different holidays to different departments
            $holidaysToAssign = [];
            
            switch ($department->id) {
                case 1: // Development Department - gets all holidays
                    $holidaysToAssign = collect($createdHolidays)->pluck('id')->toArray();
                    break;
                case 2: // Human Resources - gets 8 holidays
                    $holidaysToAssign = collect($createdHolidays)->take(8)->pluck('id')->toArray();
                    break;
                case 3: // Finance Department - gets 5 holidays (limited)
                    $holidaysToAssign = collect($createdHolidays)->take(5)->pluck('id')->toArray();
                    break;
                case 4: // Marketing Department - gets 4 holidays (very limited)
                    $holidaysToAssign = collect($createdHolidays)->take(4)->pluck('id')->toArray();
                    break;
                default: // Other departments get 6 holidays
                    $holidaysToAssign = collect($createdHolidays)->take(6)->pluck('id')->toArray();
                    break;
            }
            
            // Attach holidays to department
            foreach ($holidaysToAssign as $holidayId) {
                \DB::table('department_public_holidays')->updateOrInsert([
                    'department_id' => $department->id,
                    'public_holiday_id' => $holidayId
                ], [
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        $this->command->info('Created ' . count($createdHolidays) . ' flexible public holidays and assigned them to departments.');
    }
}
