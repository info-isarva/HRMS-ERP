<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class TenantProvisionCommand extends Command
{
    protected $signature = 'tenant:provision
        {code : Company code e.g. COMPBTEST}
        {--name= : Display name}
        {--workspace-domain= : Workspace hostname}
        {--payroll-domain= : Payroll hostname}
        {--attendance-domain= : Attendance hostname}
        {--workspace-db= : Workspace shard database (must already exist in MySQL)}
        {--payroll-db= : Payroll shard database}
        {--attendance-db= : Attendance shard database}
        {--register-only : Register in hrms_central only — no migrations}
        {--migrate-only : Run migrations only — tenant must already be registered}
        {--skip-workspace : Skip workspace migrations}
        {--skip-payroll : Skip payroll migrations}
        {--skip-attendance : Skip attendance migrations}
        {--inactive : Leave tenant inactive/provisioning (do not activate)}';

    protected $description = 'Register a test/production company and migrate its shard databases (Phase 6)';

    public function handle(): int
    {
        $code = strtoupper(trim($this->argument('code')));

        if ($code === 'ISARVADEV') {
            $this->error('ISARVADEV is the live dev tenant. Use a different code for Company B testing (e.g. COMPBTEST).');

            return self::FAILURE;
        }

        $tenant = Tenant::query()->where('company_code', $code)->first();
        $registerOnly = (bool) $this->option('register-only');
        $migrateOnly = (bool) $this->option('migrate-only');

        if ($migrateOnly && ! $tenant) {
            $this->error("Tenant [{$code}] is not registered. Run without --migrate-only first.");

            return self::FAILURE;
        }

        if (! $migrateOnly) {
            $tenant = $this->registerTenant($code, $tenant);
            if (! $tenant) {
                return self::FAILURE;
            }

            if ($registerOnly) {
                $this->printManualSteps($tenant);

                return self::SUCCESS;
            }
        }

        $tenant = $tenant ?? Tenant::query()->where('company_code', $code)->firstOrFail();

        if (! $this->confirmDatabasesExist($tenant)) {
            return self::FAILURE;
        }

        if (! $this->runShardMigrations($tenant)) {
            $tenant->update(['status' => 'provisioning']);

            return self::FAILURE;
        }

        if (! $this->option('inactive')) {
            $tenant->update([
                'status' => 'active',
                'meta' => array_merge($tenant->meta ?? [], [
                    'provisioned_at' => now()->toIso8601String(),
                    'provisioned_via' => 'tenant:provision',
                ]),
            ]);
            $this->info("Tenant [{$code}] is active and ready for domain testing.");
        } else {
            $this->info("Tenant [{$code}] migrations complete. Status left unchanged.");
        }

        $this->printManualSteps($tenant);
        $this->printRealityTestChecklist($tenant);

        return self::SUCCESS;
    }

    private function registerTenant(string $code, ?Tenant $existing): ?Tenant
    {
        $name = $this->option('name') ?: $this->ask('Company name', "Company {$code} Test");

        $defaults = $this->defaultDomainsAndDatabases($code);

        $workspaceDomain = $this->normalizeHost(
            $this->option('workspace-domain') ?: $this->ask('Workspace domain', $defaults['workspace_domain'])
        );
        $payrollDomain = $this->normalizeHost(
            $this->option('payroll-domain') ?: $this->ask('Payroll domain', $defaults['payroll_domain'])
        );
        $attendanceDomain = $this->normalizeHost(
            $this->option('attendance-domain') ?: $this->ask('Attendance domain', $defaults['attendance_domain'])
        );

        $workspaceDb = $this->option('workspace-db') ?: $this->ask('Workspace database', $defaults['workspace_db']);
        $payrollDb = $this->option('payroll-db') ?: $this->ask('Payroll database', $defaults['payroll_db']);
        $attendanceDb = $this->option('attendance-db') ?: $this->ask('Attendance database', $defaults['attendance_db']);

        if ($existing && $existing->status === 'active' && ! $this->confirm("Tenant [{$code}] already exists. Update registry?", true)) {
            return $existing;
        }

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
                'status' => $this->option('inactive') ? 'provisioning' : 'provisioning',
                'meta' => array_merge(($existing?->meta) ?? [], [
                    'registered_via' => 'tenant:provision',
                    'registered_at' => now()->toIso8601String(),
                ]),
            ]
        );

        $this->info("Registered tenant #{$tenant->id} [{$tenant->company_code}] (status: provisioning)");
        $this->table(
            ['Module', 'Domain', 'Database'],
            [
                ['Workspace', $tenant->workspace_domain, $tenant->workspace_database],
                ['Payroll', $tenant->payroll_domain, $tenant->payroll_database],
                ['Attendance', $tenant->attendance_domain, $tenant->attendance_database],
            ]
        );

        return $tenant;
    }

    /**
     * @return array{workspace_domain: string, payroll_domain: string, attendance_domain: string, workspace_db: string, payroll_db: string, attendance_db: string}
     */
    private function defaultDomainsAndDatabases(string $code): array
    {
        $slug = Str::lower($code);

        return [
            'workspace_domain' => "{$slug}-hrmsdev.isarva.in",
            'payroll_domain' => "{$slug}-payrolldev.isarva.in",
            'attendance_domain' => "{$slug}-attendancedev.isarva.in",
            'workspace_db' => "hrms_{$slug}_workspace",
            'payroll_db' => "hrms_{$slug}_payroll",
            'attendance_db' => "hrms_{$slug}_attendance",
        ];
    }

    private function confirmDatabasesExist(Tenant $tenant): bool
    {
        $this->newLine();
        $this->line('<fg=yellow>Checking shard databases (you must create these empty DBs in CyberPanel first)...</>');

        $shards = [
            'workspace' => $tenant->workspace_database,
            'payroll' => $tenant->payroll_database,
            'attendance' => $tenant->attendance_database,
        ];

        $allOk = true;

        foreach ($shards as $module => $database) {
            if (! $database) {
                $this->error("Missing {$module} database name in registry.");

                return false;
            }

            if ($this->databaseConnects($database)) {
                $this->line("  ✓ {$module}: {$database}");
            } else {
                $this->error("  ✗ {$module}: cannot connect to [{$database}]");
                $allOk = false;
            }
        }

        if (! $allOk) {
            $this->newLine();
            $this->warn('Create the empty databases in CyberPanel, then re-run:');
            $this->line("  php artisan tenant:provision {$tenant->company_code} --migrate-only");

            return false;
        }

        return true;
    }

    private function databaseConnects(string $database): bool
    {
        $config = config('database.connections.mysql');

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $config['host'],
                $config['port'] ?? 3306,
                $database
            );
            new \PDO($dsn, $config['username'], $config['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function runShardMigrations(Tenant $tenant): bool
    {
        $this->newLine();
        $this->info('Running shard migrations...');

        $jobs = [];

        if (! $this->option('skip-workspace')) {
            $jobs[] = ['app' => '', 'database' => $tenant->workspace_database, 'label' => 'workspace'];
        }
        if (! $this->option('skip-payroll')) {
            $jobs[] = ['app' => 'payroll', 'database' => $tenant->payroll_database, 'label' => 'payroll'];
        }
        if (! $this->option('skip-attendance')) {
            $jobs[] = ['app' => 'attendance', 'database' => $tenant->attendance_database, 'label' => 'attendance'];
        }

        foreach ($jobs as $job) {
            if (! $this->runMigrateShard($job['app'], $job['database'], $job['label'])) {
                return false;
            }
        }

        return true;
    }

    private function runMigrateShard(string $appSubdir, string $database, string $label): bool
    {
        $cwd = $appSubdir === '' ? base_path() : base_path($appSubdir);

        if (! is_dir($cwd)) {
            $this->error("App path not found for {$label}: {$cwd}");

            return false;
        }

        $this->line("→ {$label}: {$database}");

        $phpBinary = escapeshellarg(PHP_BINARY);
        $process = Process::fromShellCommandline(
            $phpBinary.' artisan tenant:migrate-shard '.escapeshellarg($database),
            $cwd,
            null,
            null,
            600
        );
        $process->run(function ($type, $buffer) {
            $this->output->write($buffer);
        });

        if (! $process->isSuccessful()) {
            $this->error("Migration failed for {$label} [{$database}]");

            return false;
        }

        return true;
    }

    private function printManualSteps(Tenant $tenant): void
    {
        $this->newLine();
        $this->line('<fg=cyan>── Manual steps (CyberPanel) ──</>');
        $this->line('1. Create empty MySQL databases (if not done yet):');
        $this->line("   • {$tenant->workspace_database}");
        $this->line("   • {$tenant->payroll_database}");
        $this->line("   • {$tenant->attendance_database}");
        $this->newLine();
        $this->line('2. Grant the existing MySQL user access to the new databases.');
        $this->newLine();
        $this->line('3. Company-code model: no per-company domains needed.');
        $this->line("   Use same app URLs and login with company code [{$tenant->company_code}].");
        $this->newLine();
        $this->line('4. Verify:');
        $this->line("   php artisan tenant:verify {$tenant->company_code}");
    }

    private function printRealityTestChecklist(Tenant $tenant): void
    {
        $this->newLine();
        $this->line('<fg=cyan>── Reality test checklist ──</>');
        $this->line("• Login on same workspace URL with company code: {$tenant->company_code}");
        $this->line('• Dashboard → Payroll SSO uses same payroll URL, but tenant context = '.$tenant->company_code);
        $this->line('• Dashboard → Attendance SSO uses same attendance URL, but tenant context = '.$tenant->company_code);
        $this->line('• ISARVADEV login token must NOT work for '.$tenant->company_code.' context (tenant mismatch)');
        $this->line('• Employees/data in COMPB shards must not appear in ISARVADEV shards');
        $this->newLine();
        $this->line('Full guide: docs/multitenancy/COMPANY-CODE-LOGIN.md');
    }

    private function normalizeHost(string $host): string
    {
        $host = Str::lower(trim($host));
        $host = preg_replace('#^https?://#', '', $host);
        $host = rtrim($host, '/');

        return $host;
    }
}
