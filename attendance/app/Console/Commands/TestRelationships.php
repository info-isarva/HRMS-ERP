<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TestRelationships extends Command
{
    protected $signature = 'test:relationships';
    protected $description = 'Test the reporting manager relationships';

    public function handle()
    {
        $this->info("Starting relationship test...");

        try {
            // Clean up previous test users
            DB::statement('DELETE FROM users WHERE employee_id LIKE "TEST-%"');
            $this->info("Cleaned up previous test users");

            // Create user C (top manager)
            $userC = User::create([
                'name' => 'Test User C',
                'email' => 'test-c@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'TEST-C',
                'payroll_id' => 3,
                'reporting_manager_id' => null,
                'department_id' => 3,
                'designation' => 'Manager C',
                'role' => 'admin',
                'date_of_joining' => '2025-01-01',
                'financial_year' => '2025-2026',
            ]);
            $this->info("Created user C with ID {$userC->id}");

            // Create user B (middle manager)
            $userB = User::create([
                'name' => 'Test User B',
                'email' => 'test-b@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'TEST-B',
                'payroll_id' => 2,
                'reporting_manager_id' => 3, // Reports to C
                'department_id' => 3,
                'designation' => 'Manager B',
                'role' => 'admin',
                'date_of_joining' => '2025-01-01',
                'financial_year' => '2025-2026',
            ]);
            $this->info("Created user B with ID {$userB->id}");

            // Create user A (staff)
            $userA = User::create([
                'name' => 'Test User A',
                'email' => 'test-a@example.com',
                'password' => Hash::make('password'),
                'employee_id' => 'TEST-A',
                'payroll_id' => 1,
                'reporting_manager_id' => 2, // Reports to B
                'department_id' => 3,
                'designation' => 'Staff A',
                'role' => 'staff',
                'date_of_joining' => '2025-01-01',
                'financial_year' => '2025-2026',
            ]);
            $this->info("Created user A with ID {$userA->id}");

            // Fresh query to ensure relationships are loaded correctly
            $userA = User::find($userA->id);
            $userB = User::find($userB->id);
            $userC = User::find($userC->id);

            // Test relationships
            $this->info("\nTesting User A:");
            $this->line("Name: {$userA->name}");
            $this->line("Payroll ID: {$userA->payroll_id}");
            $this->line("Reporting Manager ID: {$userA->reporting_manager_id}");
            $this->line("Reporting Manager: " . ($userA->reportingManager ? $userA->reportingManager->name : "No manager found"));
            $this->line("Reportees count: " . $userA->reportees->count());
            
            $this->info("\nTesting User B:");
            $this->line("Name: {$userB->name}");
            $this->line("Payroll ID: {$userB->payroll_id}");
            $this->line("Reporting Manager ID: {$userB->reporting_manager_id}");
            $this->line("Reporting Manager: " . ($userB->reportingManager ? $userB->reportingManager->name : "No manager found"));
            $this->line("Reportees count: " . $userB->reportees->count());
            $this->line("Reportees: " . implode(", ", $userB->reportees->pluck('name')->toArray()));
            
            $this->info("\nTesting User C:");
            $this->line("Name: {$userC->name}");
            $this->line("Payroll ID: {$userC->payroll_id}");
            $this->line("Reporting Manager ID: " . ($userC->reporting_manager_id ?? "NULL"));
            $this->line("Reporting Manager: " . ($userC->reportingManager ? $userC->reportingManager->name : "No manager found"));
            $this->line("Reportees count: " . $userC->reportees->count());
            $this->line("Reportees: " . implode(", ", $userC->reportees->pluck('name')->toArray()));
            
            $this->info("\nRelationship test completed successfully!");

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }
    }
}
