<?php

namespace Database\Seeders;

use App\Models\PublicHoliday;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('department_public_holidays')->delete();
        DB::table('public_holidays')->delete();
        
        echo "Tables flushed successfully\n";
        
        $currentYear = Carbon::now()->year;
        $financialYear = Carbon::now()->month >= 4 ? "$currentYear-" . ($currentYear + 1) : ($currentYear - 1) . "-$currentYear";
        
        // Get the first admin user or create a default one
        $admin = User::where('role', 'admin')->first() ?? User::where('role', 'super_admin')->first();
        
        if (!$admin) {
            $admin = User::create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'financial_year' => $financialYear,
            ]);
        }

        // Get all departments
        $departments = Department::active()->get();
        
        if ($departments->isEmpty()) {
            echo "No departments found. Please create departments first.\n";
            return;
        }

        $fixedHolidays = [
            [
                'name' => 'Independence Day',
                'description' => 'National Independence Day celebration',
                'date' => Carbon::create($currentYear, 8, 15),
                'financial_year' => $financialYear,
                'type' => 'fixed',
                'status' => 'active',
                'is_national' => true,
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Republic Day',
                'description' => 'National Republic Day celebration',
                'date' => Carbon::create($currentYear, 1, 26),
                'financial_year' => $financialYear,
                'type' => 'fixed',
                'status' => 'active',
                'is_national' => true,
                'color' => '#ef4444',
            ],
            [
                'name' => 'Gandhi Jayanti',
                'description' => 'Birthday of Mahatma Gandhi',
                'date' => Carbon::create($currentYear, 10, 2),
                'financial_year' => $financialYear,
                'type' => 'fixed',
                'status' => 'active',
                'is_national' => true,
                'color' => '#10b981',
            ],
            [
                'name' => 'Christmas',
                'description' => 'Christmas Day celebration',
                'date' => Carbon::create($currentYear, 12, 25),
                'financial_year' => $financialYear,
                'type' => 'fixed',
                'status' => 'active',
                'is_national' => true,
                'color' => '#dc2626',
            ],
            [
                'name' => 'New Year\'s Day',
                'description' => 'New Year celebration',
                'date' => Carbon::create($currentYear, 1, 1),
                'financial_year' => $financialYear,
                'type' => 'fixed',
                'status' => 'active',
                'is_national' => true,
                'color' => '#6366f1',
            ],
        ];

        $flexibleHolidays = [
            [
                'name' => 'Diwali',
                'description' => 'Festival of Lights',
                'date' => Carbon::create($currentYear, 11, 12),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => true,
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Holi',
                'description' => 'Festival of Colors',
                'date' => Carbon::create($currentYear, 3, 14),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => true,
                'color' => '#ec4899',
            ],
            [
                'name' => 'Eid ul-Fitr',
                'description' => 'End of Ramadan',
                'date' => Carbon::create($currentYear, 3, 30),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#10b981',
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
            ],
            [
                'name' => 'Karva Chauth',
                'description' => 'Hindu festival',
                'date' => Carbon::create($currentYear, 11, 20),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#f97316',
            ],
            [
                'name' => 'Bhai Dooj',
                'description' => 'Brother-Sister festival',
                'date' => Carbon::create($currentYear, 11, 3),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#06b6d4',
            ],
            [
                'name' => 'Raksha Bandhan',
                'description' => 'Brother-Sister bond celebration',
                'date' => Carbon::create($currentYear, 8, 9),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#84cc16',
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
            ],
            [
                'name' => 'Navratri (Day 1)',
                'description' => 'Nine nights festival',
                'date' => Carbon::create($currentYear, 9, 15),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#f59e0b',
            ],
            [
                'name' => 'Makar Sankranti',
                'description' => 'Harvest festival',
                'date' => Carbon::create($currentYear + 1, 1, 14),
                'financial_year' => $financialYear,
                'type' => 'flexible',
                'status' => 'active',
                'is_national' => false,
                'color' => '#ef4444',
            ],
        ];

        // Create Fixed Holidays
        foreach ($fixedHolidays as $holiday) {
            $publicHoliday = PublicHoliday::create(array_merge($holiday, [
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            
            // Assign to all departments
            $publicHoliday->departments()->sync($departments->pluck('id')->toArray());
        }

        // Create Flexible Holidays
        foreach ($flexibleHolidays as $holiday) {
            $publicHoliday = PublicHoliday::create(array_merge($holiday, [
                'created_by' => $admin->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
            
            // Assign to all departments
            $publicHoliday->departments()->sync($departments->pluck('id')->toArray());
        }

        echo "Successfully seeded " . count($fixedHolidays) . " fixed holidays and " . count($flexibleHolidays) . " flexible holidays.\n";
        echo "All holidays assigned to " . $departments->count() . " departments.\n";
    }
}
