<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TenantDatabaseManager
{
    protected ?string $activeDatabase = null;
    protected ?string $activeSignature = null;

    /** @var array{database: ?string, username: ?string, password: ?string}|null */
    protected static ?array $baselineConnection = null;

    protected function baselineConnection(): array
    {
        if (self::$baselineConnection === null) {
            self::$baselineConnection = [
                'database' => env('DB_DATABASE'),
                'username' => env('DB_USERNAME'),
                'password' => env('DB_PASSWORD'),
            ];
        }

        return self::$baselineConnection;
    }

    public function switchForTenant(Tenant $tenant, string $module = 'payroll'): void
    {
        if (! config('tenant.switch_database_connection')) {
            return;
        }

        $database = $tenant->databaseForModule($module);

        if (! $database) {
            throw new \RuntimeException("Tenant {$tenant->company_code} has no database configured for module [{$module}].");
        }

        $meta = (array) ($tenant->meta ?? []);
        $baseline = $this->baselineConnection();
        $username = $meta["{$module}_db_username"]
            ?? $meta["{$module}_username"]
            ?? $baseline['username'];
        $password = $meta["{$module}_db_password"]
            ?? $meta["{$module}_password"]
            ?? $baseline['password'];
        $signature = $database.'|'.$username;
        $current = config('database.connections.mysql.database');
        $currentUser = config('database.connections.mysql.username');

        if ($current === $database && $currentUser === $username && $this->activeSignature === $signature) {
            return;
        }

        config([
            'database.connections.mysql.database' => $database,
            'database.connections.mysql.username' => $username,
            'database.connections.mysql.password' => $password,
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        $this->activeDatabase = $database;
        $this->activeSignature = $signature;

        if (config('tenant.log_resolutions')) {
            Log::debug('Tenant database connection switched', [
                'tenant_id' => $tenant->id,
                'company_code' => $tenant->company_code,
                'module' => $module,
                'database' => $database,
                'username' => $username,
            ]);
        }
    }

    public function activeDatabase(): ?string
    {
        return $this->activeDatabase ?? config('database.connections.mysql.database');
    }
}
