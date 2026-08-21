<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class TenantVerifyCommand extends Command
{
    protected $signature = 'tenant:verify
        {code : Company code e.g. COMPBTEST}
        {--skip-http : Skip HTTP header checks (domains not live yet)}';

    protected $description = 'Verify a tenant registry entry, database connectivity, and optional domain resolution';

    public function handle(): int
    {
        $code = strtoupper(trim($this->argument('code')));

        $tenant = Tenant::query()->where('company_code', $code)->first();

        if (! $tenant) {
            $this->error("Tenant [{$code}] not found in hrms_central.");

            return self::FAILURE;
        }

        $this->info("Verifying tenant #{$tenant->id} [{$tenant->company_code}] — {$tenant->name}");
        $this->line("Status: {$tenant->status}");
        $this->newLine();

        $dbOk = $this->checkDatabases($tenant);
        $httpOk = $this->option('skip-http') ? true : $this->checkHttpHeaders($tenant);

        $this->newLine();

        if ($dbOk && $httpOk) {
            $this->info('All checks passed. Ready for browser reality testing.');

            return self::SUCCESS;
        }

        $this->warn('Some checks failed. See messages above.');

        return self::FAILURE;
    }

    private function checkDatabases(Tenant $tenant): bool
    {
        $this->line('<fg=cyan>Database connectivity</>');

        $config = config('database.connections.mysql');
        $allOk = true;

        foreach ([
            'workspace' => $tenant->workspace_database,
            'payroll' => $tenant->payroll_database,
            'attendance' => $tenant->attendance_database,
        ] as $module => $database) {
            if (! $database) {
                $this->error("  ✗ {$module}: not configured");
                $allOk = false;

                continue;
            }

            try {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $config['host'],
                    $config['port'] ?? 3306,
                    $database
                );
                $pdo = new \PDO($dsn, $config['username'], $config['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
                $tables = $pdo->query('SHOW TABLES')->fetchAll(\PDO::FETCH_COLUMN);
                $count = count($tables);
                $this->line("  ✓ {$module}: {$database} ({$count} tables)");
            } catch (\Throwable $e) {
                $this->error("  ✗ {$module}: {$database} — ".$e->getMessage());
                $allOk = false;
            }
        }

        return $allOk;
    }

    private function checkHttpHeaders(Tenant $tenant): bool
    {
        $this->newLine();
        $this->line('<fg=cyan>HTTP tenant resolution (requires live domains + APP_DEBUG)</>');

        $checks = [
            'workspace' => $tenant->workspace_domain,
            'payroll' => $tenant->payroll_domain,
            'attendance' => $tenant->attendance_domain,
        ];

        $allOk = true;

        foreach ($checks as $module => $host) {
            if (! $host) {
                continue;
            }

            $url = "https://{$host}/login";

            try {
                $response = Http::timeout(10)->withOptions(['verify' => false])->head($url);
                $code = $response->header('X-Tenant-Code');
                $db = $response->header('X-Tenant-Database');

                if (strtoupper((string) $code) === $tenant->company_code) {
                    $this->line("  ✓ {$module}: {$host} → X-Tenant-Code={$code}".($db ? ", DB={$db}" : ''));
                } else {
                    $this->error("  ✗ {$module}: {$host} — expected X-Tenant-Code={$tenant->company_code}, got ".($code ?: 'none'));
                    $allOk = false;
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ {$module}: {$host} — ".$e->getMessage());
                $allOk = false;
            }
        }

        return $allOk;
    }
}
