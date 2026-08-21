<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TenantRegisterCommand extends Command
{
    protected $signature = 'tenant:register
        {--code= : Company code e.g. COMPA}
        {--name= : Display name}
        {--workspace-domain= : Workspace hostname}
        {--payroll-domain= : Payroll hostname}
        {--attendance-domain= : Attendance hostname}
        {--workspace-db= : Workspace database name}
        {--payroll-db= : Payroll database name}
        {--attendance-db= : Attendance database name}
        {--inactive : Register as inactive (for future provisioning)}';

    protected $description = 'Register a company in the central tenant registry';

    public function handle(): int
    {
        $code = strtoupper($this->option('code') ?: $this->ask('Company code', 'COMPA'));
        $name = $this->option('name') ?: $this->ask('Company name', 'Company A');

        $workspaceDomain = $this->normalizeHost(
            $this->option('workspace-domain') ?: $this->ask('Workspace domain', 'hrmsdev.isarva.in')
        );
        $payrollDomain = $this->normalizeHost(
            $this->option('payroll-domain') ?: $this->ask('Payroll domain', 'payrolldev.isarva.in')
        );
        $attendanceDomain = $this->normalizeHost(
            $this->option('attendance-domain') ?: $this->ask('Attendance domain', 'attendancedev.isarva.in')
        );

        $workspaceDb = $this->option('workspace-db') ?: $this->ask('Workspace database');
        $payrollDb = $this->option('payroll-db') ?: $this->ask('Payroll database');
        $attendanceDb = $this->option('attendance-db') ?: $this->ask('Attendance database');

        $tenant = Tenant::query()->updateOrCreate(
            ['company_code' => $code],
            [
                'name' => $name,
                'workspace_domain' => $workspaceDomain,
                'payroll_domain' => $payrollDomain,
                'attendance_domain' => $attendanceDomain,
                'workspace_database' => $workspaceDb,
                'payroll_database' => $payrollDb,
                'attendance_database' => $attendanceDb,
                'status' => $this->option('inactive') ? 'inactive' : 'active',
                'meta' => [
                    'registered_via' => 'tenant:register',
                    'registered_at' => now()->toIso8601String(),
                ],
            ]
        );

        $this->info("Tenant registered: [{$tenant->company_code}] {$tenant->name} (id={$tenant->id})");
        $this->line('Run tenant:provision {code} after creating shard databases in CyberPanel.');

        return self::SUCCESS;
    }

    private function normalizeHost(string $host): string
    {
        $host = Str::lower(trim($host));
        $host = preg_replace('#^https?://#', '', $host);
        $host = rtrim($host, '/');

        return $host;
    }
}
