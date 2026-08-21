<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Hash;

class UserDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeder.
     */
    public function run(): void
    {
        // Get all departments
        $departments = Department::all();
        
        if ($departments->isEmpty()) {
            $this->command->error('No departments found. Please run DepartmentSeeder first.');
            return;
        }
        
        // Sample employee data for each department
        $employeesByDepartment = [
            'Development Department' => [
                ['name' => 'John Smith', 'email' => 'john.smith@company.com'],
                ['name' => 'Sarah Johnson', 'email' => 'sarah.johnson@company.com'],
                ['name' => 'Mike Wilson', 'email' => 'mike.wilson@company.com'],
                ['name' => 'Emily Davis', 'email' => 'emily.davis@company.com'],
                ['name' => 'David Brown', 'email' => 'david.brown@company.com'],
                ['name' => 'Lisa Anderson', 'email' => 'lisa.anderson@company.com'],
                ['name' => 'Chris Martinez', 'email' => 'chris.martinez@company.com'],
                ['name' => 'Jessica Garcia', 'email' => 'jessica.garcia@company.com'],
            ],
            'Human Resources' => [
                ['name' => 'Jennifer Taylor', 'email' => 'jennifer.taylor@company.com'],
                ['name' => 'Robert Miller', 'email' => 'robert.miller@company.com'],
                ['name' => 'Amanda White', 'email' => 'amanda.white@company.com'],
                ['name' => 'Kevin Lee', 'email' => 'kevin.lee@company.com'],
                ['name' => 'Maria Rodriguez', 'email' => 'maria.rodriguez@company.com'],
            ],
            'Finance Department' => [
                ['name' => 'Thomas Wilson', 'email' => 'thomas.wilson@company.com'],
                ['name' => 'Michelle Thompson', 'email' => 'michelle.thompson@company.com'],
                ['name' => 'Daniel Martinez', 'email' => 'daniel.martinez@company.com'],
                ['name' => 'Ashley Johnson', 'email' => 'ashley.johnson@company.com'],
                ['name' => 'James Anderson', 'email' => 'james.anderson@company.com'],
                ['name' => 'Laura Garcia', 'email' => 'laura.garcia@company.com'],
            ],
            'Marketing Department' => [
                ['name' => 'Ryan Davis', 'email' => 'ryan.davis@company.com'],
                ['name' => 'Nicole Brown', 'email' => 'nicole.brown@company.com'],
                ['name' => 'Mark Taylor', 'email' => 'mark.taylor@company.com'],
                ['name' => 'Stephanie Miller', 'email' => 'stephanie.miller@company.com'],
                ['name' => 'Andrew Wilson', 'email' => 'andrew.wilson@company.com'],
            ],
            'Operations Department' => [
                ['name' => 'Brian Jones', 'email' => 'brian.jones@company.com'],
                ['name' => 'Samantha Lee', 'email' => 'samantha.lee@company.com'],
                ['name' => 'Steven Rodriguez', 'email' => 'steven.rodriguez@company.com'],
                ['name' => 'Rachel Thompson', 'email' => 'rachel.thompson@company.com'],
                ['name' => 'Matthew Garcia', 'email' => 'matthew.garcia@company.com'],
                ['name' => 'Heather Martinez', 'email' => 'heather.martinez@company.com'],
                ['name' => 'Jason Anderson', 'email' => 'jason.anderson@company.com'],
            ],
            'Quality Assurance' => [
                ['name' => 'Brandon White', 'email' => 'brandon.white@company.com'],
                ['name' => 'Christina Davis', 'email' => 'christina.davis@company.com'],
                ['name' => 'Justin Brown', 'email' => 'justin.brown@company.com'],
                ['name' => 'Melissa Johnson', 'email' => 'melissa.johnson@company.com'],
                ['name' => 'Tyler Wilson', 'email' => 'tyler.wilson@company.com'],
                ['name' => 'Kimberly Taylor', 'email' => 'kimberly.taylor@company.com'],
            ],
            'IT Support' => [
                ['name' => 'Jonathan Miller', 'email' => 'jonathan.miller@company.com'],
                ['name' => 'Amy Garcia', 'email' => 'amy.garcia@company.com'],
                ['name' => 'Nathan Rodriguez', 'email' => 'nathan.rodriguez@company.com'],
                ['name' => 'Brittany Martinez', 'email' => 'brittany.martinez@company.com'],
            ],
            'Customer Support' => [
                ['name' => 'Gregory Anderson', 'email' => 'gregory.anderson@company.com'],
                ['name' => 'Vanessa Thompson', 'email' => 'vanessa.thompson@company.com'],
                ['name' => 'Aaron Lee', 'email' => 'aaron.lee@company.com'],
                ['name' => 'Danielle White', 'email' => 'danielle.white@company.com'],
                ['name' => 'Joshua Davis', 'email' => 'joshua.davis@company.com'],
            ],
        ];
        
        // Create employees for each department
        foreach ($departments as $department) {
            $employees = $employeesByDepartment[$department->name] ?? [];
            
            foreach ($employees as $employee) {
                User::create([
                    'name' => $employee['name'],
                    'email' => $employee['email'],
                    'password' => Hash::make('password'),
                    'role' => 'staff',
                    'department_id' => $department->id,
                    'financial_year' => '2025-2026',
                ]);
            }
        }
        
        // Assign existing admin user to a department if not already assigned
        $existingUsers = User::whereNull('department_id')->get();
        foreach ($existingUsers as $user) {
            if ($user->role === 'admin' || $user->role === 'super_admin') {
                $user->update(['department_id' => $departments->first()->id]);
            }
        }
        
        $this->command->info('Sample employees created and assigned to departments!');
    }
}
