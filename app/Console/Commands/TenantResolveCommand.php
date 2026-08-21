<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class TenantResolveCommand extends Command
{
    protected $signature = 'tenant:resolve {host : Domain to look up e.g. hrmsdev.isarva.in}';

    protected $description = 'Test domain → tenant lookup against hrms_central (Phase 2)';

    public function handle(): int
    {
        $host = strtolower(trim($this->argument('host')));
        $host = preg_replace('#^https?://#', '', $host);
        $host = rtrim($host, '/');

        try {
            $tenant = Tenant::findByDomain($host);
        } catch (\Throwable $e) {
            $this->error('Registry error: '.$e->getMessage());

            return self::FAILURE;
        }

        if (! $tenant) {
            $this->warn("No active tenant for domain: {$host}");

            return self::FAILURE;
        }

        $this->info("Tenant found for {$host}");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $tenant->id],
                ['Code', $tenant->company_code],
                ['Name', $tenant->name],
                ['Workspace domain', $tenant->workspace_domain],
                ['Payroll domain', $tenant->payroll_domain],
                ['Attendance domain', $tenant->attendance_domain],
                ['Workspace DB', $tenant->workspace_database],
                ['Payroll DB', $tenant->payroll_database],
                ['Attendance DB', $tenant->attendance_database],
                ['Status', $tenant->status],
            ]
        );

        return self::SUCCESS;
    }
}
