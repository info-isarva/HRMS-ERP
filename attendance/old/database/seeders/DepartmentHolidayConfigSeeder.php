<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\DepartmentHolidayConfig;
use App\Models\User;
use Illuminate\Database\Seeder;

class DepartmentHolidayConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = '2025-2026';
        $superAdmin = User::where('role', 'super_admin')->first();
        
        if (!$superAdmin) {
            $this->command->error('Super admin user not found!');
            return;
        }
        
        $configs = [
            [
                'department_code' => 'DEV',
                'allowed_holidays' => 20,
                'used_holidays' => 1,
            ],
            [
                'department_code' => 'HR',
                'allowed_holidays' => 18,
                'used_holidays' => 1,
            ],
            [
                'department_code' => 'FIN',
                'allowed_holidays' => 15,
                'used_holidays' => 1,
            ],
            [
                'department_code' => 'MKT',
                'allowed_holidays' => 17,
                'used_holidays' => 1,
            ],
        ];
        
        foreach ($configs as $configData) {
            $department = Department::where('code', $configData['department_code'])->first();
            
            if ($department) {
                DepartmentHolidayConfig::create([
                    'department_id' => $department->id,
                    'financial_year' => $currentYear,
                    'allowed_holidays' => $configData['allowed_holidays'],
                    'used_holidays' => $configData['used_holidays'],
                    'is_active' => true,
                    'created_by' => $superAdmin->id,
                ]);
                
                $this->command->info("Created config for {$department->name}");
            } else {
                $this->command->error("Department with code {$configData['department_code']} not found!");
            }
        }
    }
}
