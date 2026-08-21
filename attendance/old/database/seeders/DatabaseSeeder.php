<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\DepartmentHolidayConfig;
use App\Models\PublicHoliday;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create departments first
        $departments = [
            [
                'name' => 'Development Department',
                'code' => 'DEV',
                'description' => 'Software Development Team',
                'is_active' => true,
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Human Resources Department',
                'is_active' => true,
            ],
            [
                'name' => 'Finance Department',
                'code' => 'FIN',
                'description' => 'Finance and Accounting',
                'is_active' => true,
            ],
            [
                'name' => 'Marketing Department',
                'code' => 'MKT',
                'description' => 'Marketing and Sales',
                'is_active' => true,
            ],
        ];

        foreach ($departments as $deptData) {
            Department::create($deptData);
        }

        // Create test users manually
        $users = [
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'role' => 'staff',
                'financial_year' => '2025-2026',
                'department_id' => Department::where('code', 'DEV')->first()->id,
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'financial_year' => '2025-2026',
                'department_id' => Department::where('code', 'HR')->first()->id,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@example.com',
                'password' => Hash::make('password'),
                'role' => 'super_admin',
                'financial_year' => '2025-2026',
                'department_id' => Department::where('code', 'HR')->first()->id,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create public holidays
        $holidays = [
            [
                'name' => 'New Year',
                'date' => '2025-01-01',
                'type' => 'fixed',
                'status' => 'active',
                'financial_year' => '2025-2026',
                'is_national' => true,
            ],
            [
                'name' => 'Republic Day',
                'date' => '2025-01-26',
                'type' => 'fixed',
                'status' => 'active',
                'financial_year' => '2025-2026',
                'is_national' => true,
            ],
            [
                'name' => 'Independence Day',
                'date' => '2025-08-15',
                'type' => 'fixed',
                'status' => 'active',
                'financial_year' => '2025-2026',
                'is_national' => true,
            ],
            [
                'name' => 'Gandhi Jayanti',
                'date' => '2025-10-02',
                'type' => 'fixed',
                'status' => 'active',
                'financial_year' => '2025-2026',
                'is_national' => true,
            ],
            [
                'name' => 'Christmas',
                'date' => '2025-12-25',
                'type' => 'fixed',
                'status' => 'active',
                'financial_year' => '2025-2026',
                'is_national' => true,
            ],
        ];

        foreach ($holidays as $holidayData) {
            PublicHoliday::create($holidayData);
        }

        // Create department holiday configs
        $currentYear = '2025-2026';
        $configs = [
            [
                'department_id' => Department::where('code', 'DEV')->first()->id,
                'financial_year' => $currentYear,
                'allowed_holidays' => 20,
                'used_holidays' => 1,
                'is_active' => true,
                'created_by' => User::where('role', 'super_admin')->first()->id,
            ],
            [
                'department_id' => Department::where('code', 'HR')->first()->id,
                'financial_year' => $currentYear,
                'allowed_holidays' => 18,
                'used_holidays' => 1,
                'is_active' => true,
                'created_by' => User::where('role', 'super_admin')->first()->id,
            ],
            [
                'department_id' => Department::where('code', 'FIN')->first()->id,
                'financial_year' => $currentYear,
                'allowed_holidays' => 15,
                'used_holidays' => 1,
                'is_active' => true,
                'created_by' => User::where('role', 'super_admin')->first()->id,
            ],
            [
                'department_id' => Department::where('code', 'MKT')->first()->id,
                'financial_year' => $currentYear,
                'allowed_holidays' => 17,
                'used_holidays' => 1,
                'is_active' => true,
                'created_by' => User::where('role', 'super_admin')->first()->id,
            ],
        ];

        foreach ($configs as $configData) {
            DepartmentHolidayConfig::create($configData);
        }
    }
}
