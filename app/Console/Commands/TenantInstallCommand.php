<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;

class TenantInstallCommand extends Command
{
    protected $signature = 'tenant:install
        {--force : Run migrations even if database already exists}
        {--on-workspace : Store tenants table in the current workspace DB if hrms_central cannot be created}';

    protected $description = 'Create hrms_central database and run tenant registry migrations (Phase 1 — no app behavior change)';

    public function handle(): int
    {
        $database = config('database.connections.central.database');

        if (! $database) {
            $this->error('CENTRAL_DB_DATABASE is not configured.');

            return self::FAILURE;
        }

        $this->info("Central registry database: {$database}");

        if ($this->option('on-workspace')) {
            $workspaceDb = config('database.connections.mysql.database');
            config(['database.connections.central.database' => $workspaceDb]);
            $database = $workspaceDb;
            $this->warn("Using workspace database for registry: {$workspaceDb}");
        } elseif (! $this->ensureDatabaseExists($database)) {
            $this->newLine();
            $this->warn('Tip: ask your DBA to create hrms_central, or run:');
            $this->line('  php artisan tenant:install --on-workspace');

            return self::FAILURE;
        }

        $this->info('Running central migrations...');

        $exit = Artisan::call('migrate', [
            '--database' => 'central',
            '--path' => 'database/migrations/central',
            '--force' => true,
        ]);

        $this->line(Artisan::output());

        if ($exit !== 0) {
            $this->error('Central migrations failed.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Tenant registry installed successfully.');
        $this->line('Next: php artisan tenant:register-current');
        $this->line('Phase 1 does NOT switch application databases — existing apps keep using .env DB_*.');

        return self::SUCCESS;
    }

    private function ensureDatabaseExists(string $database): bool
    {
        $config = config('database.connections.central');

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=%s',
                $config['host'],
                $config['port'] ?? 3306,
                $config['charset'] ?? 'utf8mb4'
            );

            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $safeName = str_replace('`', '``', $database);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database `{$database}` is ready.");

            return true;
        } catch (\Throwable $e) {
            $this->error('Could not create central database: '.$e->getMessage());
            $this->line('Create it manually in MySQL, then run: php artisan migrate --database=central --path=database/migrations/central');

            return false;
        }
    }
}
