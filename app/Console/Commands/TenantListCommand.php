<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;

class TenantListCommand extends Command
{
    protected $signature = 'tenant:list {--all : Include inactive tenants}';

    protected $description = 'List registered companies in the central tenant registry';

    public function handle(): int
    {
        try {
            $query = Tenant::query()->orderBy('id');

            if (! $this->option('all')) {
                $query->where('status', 'active');
            }

            $tenants = $query->get();
        } catch (\Throwable $e) {
            $this->error('Cannot read tenant registry. Run: php artisan tenant:install');
            $this->line($e->getMessage());

            return self::FAILURE;
        }

        if ($tenants->isEmpty()) {
            $this->warn('No tenants registered.');
            $this->line('Run: php artisan tenant:register-current');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Code', 'Name', 'Workspace', 'Payroll DB', 'Attendance DB', 'Status'],
            $tenants->map(fn (Tenant $t) => [
                $t->id,
                $t->company_code,
                $t->name,
                $t->workspace_domain,
                $t->payroll_database ?? '—',
                $t->attendance_database ?? '—',
                $t->status,
            ])
        );

        return self::SUCCESS;
    }
}
