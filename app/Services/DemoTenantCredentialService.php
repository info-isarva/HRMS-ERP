<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Crypt;

class DemoTenantCredentialService
{
    public function store(Tenant $tenant, string $password, string $adminName): void
    {
        $credentials = [
            'login_url' => config('platform.login_url'),
            'payroll_url' => config('platform.payroll_url'),
            'attendance_url' => config('platform.attendance_url'),
            'company_code' => $tenant->company_code,
            'admin_email' => $tenant->demo_admin_email,
            'admin_name' => $adminName,
            'password_enc' => Crypt::encryptString($password),
            'expires_at' => $tenant->demo_expires_at?->toIso8601String(),
            'stored_at' => now()->toIso8601String(),
        ];

        $tenant->update([
            'meta' => array_merge($tenant->meta ?? [], [
                'demo_credentials' => $credentials,
            ]),
        ]);
    }

    /**
     * @return array<string, string|null>|null
     */
    public function resolve(Tenant $tenant): ?array
    {
        $stored = $tenant->meta['demo_credentials'] ?? null;

        if (! is_array($stored)) {
            return null;
        }

        $password = null;
        if (! empty($stored['password_enc'])) {
            try {
                $password = Crypt::decryptString($stored['password_enc']);
            } catch (\Throwable) {
                $password = null;
            }
        }

        return [
            'login_url' => $stored['login_url'] ?? config('platform.login_url'),
            'payroll_url' => $stored['payroll_url'] ?? config('platform.payroll_url'),
            'attendance_url' => $stored['attendance_url'] ?? config('platform.attendance_url'),
            'company_code' => $tenant->company_code,
            'admin_email' => $tenant->demo_admin_email,
            'admin_name' => $stored['admin_name'] ?? null,
            'password' => $password,
            'expires_at' => $tenant->demo_expires_at?->timezone('Asia/Kolkata')->format('d M Y'),
        ];
    }

    public function shareMessage(Tenant $tenant, ?array $credentials = null): string
    {
        $credentials ??= $this->resolve($tenant);

        if (! $credentials) {
            return '';
        }

        $lines = [
            'ISARVA HRMS — Demo Access',
            '',
            'Workspace login: '.$credentials['login_url'],
            'Payroll: '.$credentials['payroll_url'],
            'Attendance: '.$credentials['attendance_url'],
            '',
            'Company code: '.$credentials['company_code'],
            'Email: '.$credentials['admin_email'],
        ];

        if ($credentials['password']) {
            $lines[] = 'Password: '.$credentials['password'];
        }

        if ($credentials['expires_at']) {
            $lines[] = '';
            $lines[] = 'Demo valid until: '.$credentials['expires_at'];
        }

        $lines[] = '';
        $lines[] = 'Steps: Open Workspace URL → enter company code, email and password → access Payroll & Attendance from dashboard.';

        return implode("\n", $lines);
    }
}
