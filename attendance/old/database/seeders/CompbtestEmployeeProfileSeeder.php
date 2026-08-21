<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Links COMPBTEST users to employees rows so mobile GPS APIs can resolve profiles.
 * Run against hrms_compbtest_attendance after tenant:provision.
 */
class CompbtestEmployeeProfileSeeder extends Seeder
{
    public function run(): void
    {
        $fieldUserEmail = 'compb.emp1@isarva.in';

        Employee::query()
            ->where('email', $fieldUserEmail)
            ->where('employee_id', '!=', 'MNG-001')
            ->delete();

        // Field employee with GPS demo route data (MNG-001)
        Employee::query()->updateOrCreate(
            ['employee_id' => 'MNG-001'],
            [
                'payroll_id' => 3,
                'name' => 'Rahul Shetty',
                'email' => $fieldUserEmail,
                'designation' => 'Field Executive — Mangaluru',
                'status' => 'active',
                'financial_year' => '2025-2026',
            ]
        );

        User::query()
            ->where('email', $fieldUserEmail)
            ->update(['employee_id' => 'MNG-001']);

        User::query()->orderBy('id')->each(function (User $user) use ($fieldUserEmail) {
            if (! $user->employee_id || $user->email === $fieldUserEmail) {
                return;
            }

            Employee::query()->updateOrCreate(
                ['employee_id' => $user->employee_id],
                [
                    'payroll_id' => $user->payroll_id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'designation' => ucfirst(str_replace('_', ' ', (string) $user->role)),
                    'status' => 'active',
                    'financial_year' => '2025-2026',
                ]
            );
        });
    }
}
