<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TestController extends Controller
{
    public function createTestUsers()
    {
        // Clean up previous test users
        DB::statement('DELETE FROM users WHERE employee_id LIKE "TEST-%"');

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

        // Test the relationships
        $results = [
            'userA' => [
                'id' => $userA->id,
                'name' => $userA->name,
                'payroll_id' => $userA->payroll_id,
                'reporting_manager_id' => $userA->reporting_manager_id,
                'reportingManager' => $userA->reportingManager ? $userA->reportingManager->name : 'No manager found',
                'reportees' => $userA->reportees->pluck('name')->toArray()
            ],
            'userB' => [
                'id' => $userB->id,
                'name' => $userB->name,
                'payroll_id' => $userB->payroll_id,
                'reporting_manager_id' => $userB->reporting_manager_id,
                'reportingManager' => $userB->reportingManager ? $userB->reportingManager->name : 'No manager found',
                'reportees' => $userB->reportees->pluck('name')->toArray()
            ],
            'userC' => [
                'id' => $userC->id,
                'name' => $userC->name,
                'payroll_id' => $userC->payroll_id,
                'reporting_manager_id' => $userC->reporting_manager_id,
                'reportingManager' => $userC->reportingManager ? $userC->reportingManager->name : 'No manager found',
                'reportees' => $userC->reportees->pluck('name')->toArray()
            ]
        ];

        return response()->json($results);
    }
}
