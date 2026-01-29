<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class EmployeeSeeder extends Seeder
{
    public function run()
    {
        $currentYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $financialYear = $currentYear . '-' . ($currentYear + 1);
        
        $employees = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'department_id' => 1,
                'financial_year' => $financialYear,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'department_id' => 2,
                'financial_year' => $financialYear,
            ],
            [
                'name' => 'Alice Johnson',
                'email' => 'alice@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'department_id' => 3,
                'financial_year' => $financialYear,
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'department_id' => 4,
                'financial_year' => $financialYear,
            ],
            [
                'name' => 'Carol Brown',
                'email' => 'carol@example.com',
                'password' => Hash::make('password123'),
                'role' => 'staff',
                'department_id' => 5,
                'financial_year' => $financialYear,
            ],
        ];

        foreach ($employees as $employeeData) {
            User::updateOrCreate(
                ['email' => $employeeData['email']],
                $employeeData
            );
        }
    }
}
