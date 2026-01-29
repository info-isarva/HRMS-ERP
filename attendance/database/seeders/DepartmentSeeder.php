<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\DepartmentHolidayConfig;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Development Department', 'code' => 'DEV', 'description' => 'Software Development Team', 'api_id' => 1],
            ['name' => 'Human Resources', 'code' => 'HR', 'description' => 'Human Resources Department', 'api_id' => 2],
            ['name' => 'Finance Department', 'code' => 'FIN', 'description' => 'Finance and Accounting', 'api_id' => 3],
            ['name' => 'Marketing Department', 'code' => 'MKT', 'description' => 'Marketing and Sales', 'api_id' => 4],
            ['name' => 'Operations Department', 'code' => 'OPS', 'description' => 'Operations and Logistics', 'api_id' => 5],
            ['name' => 'Quality Assurance', 'code' => 'QA', 'description' => 'Quality Assurance Team', 'api_id' => 6],
            ['name' => 'IT Support', 'code' => 'IT', 'description' => 'IT Support and Infrastructure', 'api_id' => 7],
            ['name' => 'Customer Support', 'code' => 'CS', 'description' => 'Customer Support and Service', 'api_id' => 8],
        ];

        foreach ($departments as $deptData) {
            Department::create($deptData);
        }

        // Create holiday configurations for current financial year
        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $financialYear = $currentYear . '-' . ($currentYear + 1);
        
        $holidayConfigs = [
            ['department_name' => 'Development Department', 'allowed_holidays' => 20],
            ['department_name' => 'Human Resources', 'allowed_holidays' => 18],
            ['department_name' => 'Finance Department', 'allowed_holidays' => 15],
            ['department_name' => 'Marketing Department', 'allowed_holidays' => 17],
            ['department_name' => 'Operations Department', 'allowed_holidays' => 16],
            ['department_name' => 'Quality Assurance', 'allowed_holidays' => 19],
            ['department_name' => 'IT Support', 'allowed_holidays' => 18],
            ['department_name' => 'Customer Support', 'allowed_holidays' => 14],
        ];

        foreach ($holidayConfigs as $config) {
            $department = Department::where('name', $config['department_name'])->first();
            if ($department) {
                DepartmentHolidayConfig::create([
                    'department_id' => $department->id,
                    'financial_year' => $financialYear,
                    'allowed_holidays' => $config['allowed_holidays'],
                    'used_holidays' => 0,
                    'is_active' => true,
                    'created_by' => 1, // Assuming first user is admin
                ]);
            }
        }
    }
}
