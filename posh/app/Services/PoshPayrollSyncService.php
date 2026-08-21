<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\PoshEmployeeDirectory;
use Illuminate\Support\Str;

/**
 * Mock payroll sync — POSH-only demo data. Does not call HRMS or Payroll apps.
 */
class PoshPayrollSyncService
{
    /** @return list<array<string, mixed>> */
    protected function mockPayrollEmployees(): array
    {
        return [
            ['code' => 'EMP-101', 'name' => 'Priya Nair', 'email' => 'priya.nair@demo-erp.local', 'department' => 'Human Resources', 'designation' => 'HR Executive'],
            ['code' => 'EMP-102', 'name' => 'Rahul Mehta', 'email' => 'rahul.mehta@demo-erp.local', 'department' => 'Finance', 'designation' => 'Accounts Manager'],
            ['code' => 'EMP-103', 'name' => 'Anita Sharma', 'email' => 'presiding@example.com', 'department' => 'Human Resources', 'designation' => 'VP - HR'],
            ['code' => 'EMP-104', 'name' => 'Sneha Kapoor', 'email' => 'sneha.kapoor@demo-erp.local', 'department' => 'Operations', 'designation' => 'Team Lead'],
            ['code' => 'EMP-105', 'name' => 'Vikram Singh', 'email' => 'vikram.singh@demo-erp.local', 'department' => 'IT', 'designation' => 'Software Engineer'],
            ['code' => 'EMP-106', 'name' => 'Meera Joshi', 'email' => 'meera.joshi@demo-erp.local', 'department' => 'Marketing', 'designation' => 'Marketing Manager'],
            ['code' => 'EMP-107', 'name' => 'Arjun Patel', 'email' => 'arjun.patel@demo-erp.local', 'department' => 'Sales', 'designation' => 'Sales Executive'],
            ['code' => 'EMP-108', 'name' => 'Kavita Reddy', 'email' => 'kavita.reddy@demo-erp.local', 'department' => 'Administration', 'designation' => 'Office Admin'],
        ];
    }

    public function sync(Organization $org): int
    {
        $count = 0;
        $ref = 1000;

        foreach ($this->mockPayrollEmployees() as $row) {
            PoshEmployeeDirectory::updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'email' => $row['email'],
                ],
                [
                    'name' => $row['name'],
                    'employee_code' => $row['code'],
                    'department' => $row['department'],
                    'designation' => $row['designation'],
                    'source' => 'payroll',
                    'payroll_ref' => $ref++,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        $org->update(['payroll_synced_at' => now()]);

        return $count;
    }

    public function seedStandaloneEmployees(Organization $org): int
    {
        $rows = [
            ['code' => 'POSH-001', 'name' => 'Neha Verma', 'email' => 'neha.verma@demo-posh.local', 'department' => 'HR', 'designation' => 'HR Manager'],
            ['code' => 'POSH-002', 'name' => 'Rohan Das', 'email' => 'rohan.das@demo-posh.local', 'department' => 'Finance', 'designation' => 'Accountant'],
            ['code' => 'POSH-003', 'name' => 'Anita Sharma', 'email' => 'presiding@example.com', 'department' => 'HR', 'designation' => 'VP - HR'],
            ['code' => 'POSH-004', 'name' => 'Divya Iyer', 'email' => 'divya.iyer@demo-posh.local', 'department' => 'Operations', 'designation' => 'Coordinator'],
            ['code' => 'POSH-005', 'name' => 'Karan Malhotra', 'email' => 'karan.malhotra@demo-posh.local', 'department' => 'IT', 'designation' => 'Developer'],
        ];

        $count = 0;
        foreach ($rows as $i => $row) {
            PoshEmployeeDirectory::updateOrCreate(
                ['organization_id' => $org->id, 'email' => $row['email']],
                [
                    'name' => $row['name'],
                    'employee_code' => $row['code'],
                    'department' => $row['department'],
                    'designation' => $row['designation'],
                    'source' => 'posh',
                    'payroll_ref' => null,
                    'is_active' => true,
                ]
            );
            $count++;
        }

        return $count;
    }
}
