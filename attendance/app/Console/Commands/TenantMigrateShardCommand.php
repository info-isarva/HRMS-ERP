<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use PDO;

class TenantMigrateShardCommand extends Command
{
    protected $signature = 'tenant:migrate-shard
        {database : Attendance shard database name e.g. hrms_company_b_attendance}
        {--create : Create database if missing (requires MySQL CREATE privilege)}';

    protected $description = 'Run attendance migrations on a tenant shard database (Company B provisioning)';

    public function handle(): int
    {
        $database = $this->argument('database');

        if ($this->option('create') && ! $this->createDatabase($database)) {
            return self::FAILURE;
        }

        $original = config('database.connections.mysql.database');
        config(['database.connections.mysql.database' => $database]);

        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();
        } catch (\Throwable $e) {
            $this->error("Cannot connect to database [{$database}]: ".$e->getMessage());

            return self::FAILURE;
        }

        $this->info("Running attendance migrations on [{$database}]...");

        $exit = Artisan::call('migrate', ['--force' => true]);
        $this->line(Artisan::output());

        config(['database.connections.mysql.database' => $original]);
        DB::purge('mysql');

        if ($exit !== 0) {
            $this->error('Migrations failed.');

            return self::FAILURE;
        }

        $this->info("Shard [{$database}] is ready. Register tenant in hrms_central and point domain in CyberPanel.");

        return self::SUCCESS;
    }

    private function createDatabase(string $database): bool
    {
        $config = config('database.connections.mysql');

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;charset=utf8mb4', $config['host'], $config['port'] ?? 3306);
            $pdo = new PDO($dsn, $config['username'], $config['password'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $safe = str_replace('`', '``', $database);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $this->info("Database [{$database}] created or already exists.");

            return true;
        } catch (\Throwable $e) {
            $this->error('Could not create database: '.$e->getMessage());

            return false;
        }
    }
}
