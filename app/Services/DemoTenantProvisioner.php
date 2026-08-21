<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class DemoTenantProvisioner
{
    public function __construct(
        private DemoTenantUsageService $usageService,
    ) {
    }

    /**
     * @param  array{
     *   company_code: string,
     *   name: string,
     *   demo_days: int,
     *   expires_at?: string|null,
     *   admin_email: string,
     *   admin_name?: string,
     *   seed_profile: string,
     *   contact_name?: string|null,
     *   internal_notes?: string|null,
     * }  $input
     * @return array{tenant: Tenant, password: string, steps: array<int, string>, warnings: array<int, string>}
     */
    public function provision(array $input): array
    {
        $code = strtoupper(trim($input['company_code']));
        $this->guardCode($code);

        $slug = Str::lower($code);
        $password = $this->generatePassword();
        $expiresAt = isset($input['expires_at']) && $input['expires_at']
            ? \Carbon\Carbon::parse($input['expires_at'])->endOfDay()
            : now()->addDays((int) $input['demo_days'])->endOfDay();

        $databases = [
            'workspace' => "hrms_{$slug}_workspace",
            'payroll' => "hrms_{$slug}_payroll",
            'attendance' => "hrms_{$slug}_attendance",
        ];

        $steps = [];
        $warnings = [];

        $provisioned = $this->provisionShardDatabases($databases);
        foreach ($databases as $module => $database) {
            $steps[] = ($provisioned[$database] ?? false)
                ? "Created database {$database} and granted app access"
                : "Database {$database} already exists — refreshed grants";
        }

        $tenant = Tenant::query()->updateOrCreate(
            ['company_code' => $code],
            [
                'name' => $input['name'],
                'workspace_domain' => "{$slug}-hrmsdev.isarva.in",
                'payroll_domain' => "{$slug}-payrolldev.isarva.in",
                'attendance_domain' => "{$slug}-attendancedev.isarva.in",
                'workspace_database' => $databases['workspace'],
                'payroll_database' => $databases['payroll'],
                'attendance_database' => $databases['attendance'],
                'status' => 'provisioning',
                'is_demo' => true,
                'demo_expires_at' => $expiresAt,
                'demo_admin_email' => strtolower(trim($input['admin_email'])),
                'seed_profile' => $input['seed_profile'] === 'standard' ? 'standard' : 'none',
                'contact_name' => $input['contact_name'] ?? null,
                'internal_notes' => $input['internal_notes'] ?? null,
                'meta' => [
                    'provisioned_via' => 'demo-tenant-manager',
                    'provisioned_at' => now()->toIso8601String(),
                    'demo_days' => (int) $input['demo_days'],
                ],
            ]
        );

        $steps[] = "Registered tenant {$code} in central registry";

        foreach ([
            ['app' => '', 'database' => $databases['workspace'], 'label' => 'workspace'],
            ['app' => 'payroll', 'database' => $databases['payroll'], 'label' => 'payroll'],
            ['app' => 'attendance', 'database' => $databases['attendance'], 'label' => 'attendance'],
        ] as $job) {
            if (! $this->databaseConnects($job['database'], $job['label'])) {
                $user = $job['label'] === 'attendance'
                    ? config('platform.shard_users.attendance.username')
                    : config('platform.shard_users.payroll.username');

                throw new \RuntimeException("Cannot connect to {$job['label']} database [{$job['database']}] as [{$user}]. Check PLATFORM_MYSQL_ADMIN credentials and shard grants.");
            }

            if (! $this->runMigrateShard($job['app'], $job['database'])) {
                throw new \RuntimeException("Migration failed for {$job['label']} [{$job['database']}]. Check storage/logs/laravel.log for details.");
            }

            $steps[] = "Migrated {$job['label']} shard ({$job['database']})";
        }

        $adminName = $input['admin_name'] ?? ($input['name'].' Admin');

        $payrollUserId = $this->createPayrollSuperAdmin(
            $databases['payroll'],
            strtolower(trim($input['admin_email'])),
            $adminName,
            $password
        );
        $steps[] = 'Created payroll super admin';

        $this->createWorkspaceSuperAdmin(
            $databases['workspace'],
            strtolower(trim($input['admin_email'])),
            $adminName,
            $password
        );
        $steps[] = 'Created workspace login user';

        $this->createAttendanceSuperAdmin(
            $databases['attendance'],
            strtolower(trim($input['admin_email'])),
            $adminName,
            $password,
            $payrollUserId
        );
        $steps[] = 'Created attendance super admin';

        if ($input['seed_profile'] === 'standard') {
            try {
                $this->seedStandardData($tenant, $databases, $input['name']);
                $steps[] = 'Seeded sample departments, employees, and attendance data';
            } catch (\Throwable $e) {
                $warnings[] = 'Sample data seed partially failed: '.$e->getMessage();
            }
        }

        $tenant->update([
            'status' => 'active',
            'meta' => array_merge($tenant->meta ?? [], [
                'provisioned_at' => now()->toIso8601String(),
                'last_usage_snapshot' => $this->usageService->snapshot($tenant),
            ]),
        ]);

        $steps[] = "Demo tenant {$code} is active until ".$expiresAt->format('d M Y');

        return [
            'tenant' => $tenant->fresh(),
            'password' => $password,
            'steps' => $steps,
            'warnings' => $warnings,
        ];
    }

    public function extend(Tenant $tenant, int $extraDays): Tenant
    {
        $base = $tenant->demo_expires_at && $tenant->demo_expires_at->isFuture()
            ? $tenant->demo_expires_at
            : now();

        $tenant->update([
            'demo_expires_at' => $base->copy()->addDays($extraDays)->endOfDay(),
            'status' => 'active',
        ]);

        return $tenant->fresh();
    }

    public function deactivate(Tenant $tenant): Tenant
    {
        $tenant->update(['status' => 'inactive']);

        return $tenant->fresh();
    }

    public function reactivate(Tenant $tenant): Tenant
    {
        if ($tenant->is_demo && $tenant->isDemoExpired()) {
            throw new \RuntimeException('Cannot reactivate an expired demo without extending the expiry date first.');
        }

        $tenant->update(['status' => 'active']);

        return $tenant->fresh();
    }

    private function guardCode(string $code): void
    {
        if ($code === '' || strlen($code) > 32) {
            throw new \InvalidArgumentException('Company code must be 1–32 characters.');
        }

        if (! preg_match('/^[A-Z0-9]+$/', $code)) {
            throw new \InvalidArgumentException('Company code may only contain letters and numbers.');
        }

        if ($code === 'ISARVADEV') {
            throw new \InvalidArgumentException('ISARVADEV is reserved for internal development.');
        }
    }

    private function generatePassword(): string
    {
        return Str::password(12, letters: true, numbers: true, symbols: false);
    }

    /**
     * @param  array<string, string>  $databases  module => database name
     * @return array<string, bool>  database => true when newly created
     */
    private function provisionShardDatabases(array $databases): array
    {
        $admin = config('platform.mysql_provision');

        if (empty($admin['username']) || $admin['password'] === null || $admin['password'] === '') {
            throw new \RuntimeException(
                'Demo provisioning needs a MySQL admin account. Add PLATFORM_MYSQL_ADMIN_USERNAME and PLATFORM_MYSQL_ADMIN_PASSWORD to workspace .env (CyberPanel → Databases → Reset MySQL Password).'
            );
        }

        $payrollUser = (string) config('platform.shard_users.payroll.username');
        $attendanceUser = (string) config('platform.shard_users.attendance.username');

        if ($payrollUser === '') {
            throw new \RuntimeException('PLATFORM_PAYROLL_DB_USERNAME (or DB_USERNAME) must be set for demo provisioning.');
        }

        if ($attendanceUser === '') {
            throw new \RuntimeException('PLATFORM_ATTENDANCE_DB_USERNAME must be set for demo provisioning.');
        }

        try {
            $pdo = $this->adminPdo($admin);
        } catch (\Throwable $e) {
            throw new \RuntimeException('MySQL admin login failed: '.$e->getMessage());
        }

        $created = [];

        foreach ($databases as $module => $database) {
            $safe = $this->escapeIdentifier($database);
            $existed = (bool) $pdo->query("SHOW DATABASES LIKE '{$safe}'")->fetch();
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safe}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $created[$database] = ! $existed;

            $this->grantDatabaseAccess($pdo, $database, $payrollUser);

            if ($module === 'attendance') {
                $this->grantDatabaseAccess($pdo, $database, $attendanceUser);
            }
        }

        return $created;
    }

    private function adminPdo(array $admin): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;charset=utf8mb4',
            $admin['host'],
            $admin['port'] ?? 3306
        );

        return new \PDO($dsn, $admin['username'], $admin['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    private function grantDatabaseAccess(\PDO $pdo, string $database, string $username): void
    {
        if ($username === '') {
            return;
        }

        $safeDb = $this->escapeIdentifier($database);
        $safeUser = $this->escapeIdentifier($username);
        $pdo->exec("GRANT ALL PRIVILEGES ON `{$safeDb}`.* TO `{$safeUser}`@`localhost`");
        $pdo->exec('FLUSH PRIVILEGES');
    }

    private function escapeIdentifier(string $value): string
    {
        return str_replace('`', '``', $value);
    }

    private function databaseConnects(string $database, string $module): bool
    {
        $creds = $this->credentialsForModule($module);

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $creds['host'],
                $creds['port'],
                $database
            );
            new \PDO($dsn, $creds['username'], $creds['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return array{host: string, port: int|string, username: string, password: string}
     */
    private function credentialsForModule(string $module): array
    {
        $base = config('database.connections.mysql');

        if ($module === 'attendance') {
            return [
                'host' => $base['host'],
                'port' => $base['port'] ?? 3306,
                'username' => (string) config('platform.shard_users.attendance.username'),
                'password' => (string) config('platform.shard_users.attendance.password'),
            ];
        }

        return [
            'host' => $base['host'],
            'port' => $base['port'] ?? 3306,
            'username' => (string) (config('platform.shard_users.payroll.username') ?: $base['username']),
            'password' => (string) (config('platform.shard_users.payroll.password') ?: $base['password']),
        ];
    }

    private function runMigrateShard(string $appSubdir, string $database): bool
    {
        if ($appSubdir === 'payroll' || $appSubdir === 'attendance') {
            return $this->runAppMigrateSubprocess($appSubdir, $database);
        }

        $migrationPath = 'database/migrations';

        if (! is_dir(base_path($migrationPath))) {
            Log::error('Demo provision: migration path missing', [
                'app' => $appSubdir,
                'database' => $database,
                'path' => $migrationPath,
            ]);

            return false;
        }

        $originalDatabase = config('database.connections.mysql.database');

        config(['database.connections.mysql.database' => $database]);
        DB::purge('mysql');

        try {
            DB::connection('mysql')->getPdo();

            $exit = Artisan::call('migrate', [
                '--force' => true,
                '--path' => $migrationPath,
            ]);

            if ($exit !== 0) {
                Log::error('Demo provision: migrate command failed', [
                    'database' => $database,
                    'path' => $migrationPath,
                    'exit_code' => $exit,
                    'output' => Artisan::output(),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Demo provision: migrate exception', [
                'database' => $database,
                'path' => $migrationPath,
                'error' => $e->getMessage(),
            ]);

            return false;
        } finally {
            config(['database.connections.mysql.database' => $originalDatabase]);
            DB::purge('mysql');
        }
    }

    private function runAppMigrateSubprocess(string $appSubdir, string $database): bool
    {
        $php = (string) config('platform.php_cli', '/usr/local/lsws/lsphp82/bin/php');
        $cwd = base_path($appSubdir);

        if (! is_executable($php) || ! is_dir($cwd)) {
            Log::error('Demo provision: subprocess migrate prerequisites missing', [
                'app' => $appSubdir,
                'php' => $php,
                'cwd' => $cwd,
            ]);

            return false;
        }

        $process = Process::fromShellCommandline(
            escapeshellarg($php).' artisan tenant:migrate-shard '.escapeshellarg($database),
            $cwd,
            null,
            null,
            600
        );
        $process->run();

        if (! $process->isSuccessful()) {
            Log::error('Demo provision: subprocess migrate failed', [
                'app' => $appSubdir,
                'database' => $database,
                'command' => $process->getCommandLine(),
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'error_output' => $process->getErrorOutput(),
            ]);
        }

        return $process->isSuccessful();
    }

    private function createPayrollSuperAdmin(string $database, string $email, string $name, string $password): int
    {
        $pdo = $this->pdoFor($database);
        $hash = Hash::make($password);

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $pdo->prepare('UPDATE users SET name = ?, password = ?, role_name = ?, status = ? WHERE id = ?')
                ->execute([$name, $hash, 'Super Admin', 'Active', $existing]);

            return (int) $existing;
        }

        $pdo->prepare(
            'INSERT INTO users (name, email, password, role_name, status, enable_crm, enable_self_portal, enable_payroll, location_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, 0, 0, 0, 0, NOW(), NOW())'
        )->execute([$name, $email, $hash, 'Super Admin', 'Active']);

        return (int) $pdo->lastInsertId();
    }

    private function createWorkspaceSuperAdmin(string $database, string $email, string $name, string $password): void
    {
        $pdo = $this->pdoFor($database);
        $hash = Hash::make($password);

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $pdo->prepare('UPDATE users SET name = ?, password = ? WHERE id = ?')
                ->execute([$name, $hash, $existing]);

            return;
        }

        $pdo->prepare(
            'INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())'
        )->execute([$name, $email, $hash]);
    }

    private function createAttendanceSuperAdmin(string $database, string $email, string $name, string $password, int $payrollUserId): void
    {
        $pdo = $this->pdoFor($database);
        $hash = Hash::make($password);

        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $existing = $stmt->fetchColumn();

        if ($existing) {
            $pdo->prepare('UPDATE users SET name = ?, password = ?, role = ?, payroll_user_id = ? WHERE id = ?')
                ->execute([$name, $hash, 'super_admin', $payrollUserId, $existing]);

            return;
        }

        $pdo->prepare(
            'INSERT INTO users (name, email, password, role, payroll_user_id, financial_year, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())'
        )->execute([$name, $email, $hash, 'super_admin', $payrollUserId, $this->currentFinancialYear()]);
    }

    private function seedStandardData(Tenant $tenant, array $databases, string $companyName): void
    {
        $payrollPdo = $this->pdoFor($databases['payroll']);
        $attendancePdo = $this->pdoFor($databases['attendance']);

        if ($this->tableExists($payrollPdo, 'company_settings')) {
            $count = (int) $payrollPdo->query('SELECT COUNT(*) FROM company_settings')->fetchColumn();
            if ($count === 0) {
                $payrollPdo->prepare(
                    'INSERT INTO company_settings (company_name, address, city, postal_code, country, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, NOW(), NOW())'
                )->execute([
                    $companyName,
                    'Demo address — update in Company Settings',
                    'Mangaluru',
                    '575001',
                    'India',
                ]);
            } else {
                $id = $payrollPdo->query('SELECT id FROM company_settings ORDER BY id ASC LIMIT 1')->fetchColumn();
                if ($id) {
                    $payrollPdo->prepare('UPDATE company_settings SET company_name = ? WHERE id = ?')
                        ->execute([$companyName, $id]);
                }
            }
        }

        $departments = ['Human Resources', 'Operations', 'Finance'];

        if ($this->tableExists($payrollPdo, 'departments')) {
            foreach ($departments as $dept) {
                $stmt = $payrollPdo->prepare('SELECT id FROM departments WHERE department = ? LIMIT 1');
                $stmt->execute([$dept]);

                if (! $stmt->fetchColumn()) {
                    $payrollPdo->prepare(
                        'INSERT INTO departments (department, short_name, status, created_at, updated_at) VALUES (?, ?, 1, NOW(), NOW())'
                    )->execute([$dept, strtoupper(substr($dept, 0, 3))]);
                }
            }
        }

        $sampleEmployees = [
            ['Rahul Shetty', 'MNG-001', 'Field Executive', 'Operations'],
            ['Priya Rao', 'MNG-002', 'HR Executive', 'Human Resources'],
            ['Arjun Menon', 'MNG-003', 'Accountant', 'Finance'],
        ];

        if ($this->tableExists($payrollPdo, 'employee_basic_details')) {
            foreach ($sampleEmployees as [$empName, $empCode, $designation, $department]) {
                $stmt = $payrollPdo->prepare('SELECT id FROM employee_basic_details WHERE employee_id = ? LIMIT 1');
                $stmt->execute([$empCode]);
                if ($stmt->fetchColumn()) {
                    continue;
                }

                $payrollPdo->prepare(
                    'INSERT INTO employee_basic_details
                     (employee_id, name, email, contact_number, gender, marital_status, designation, department, date_of_joining, created_at, updated_at)
                     VALUES (?, ?, ?, ?, 1, 1, ?, ?, ?, NOW(), NOW())'
                )->execute([
                    $empCode,
                    $empName,
                    Str::lower(str_replace(' ', '.', $empName)).'@demo.local',
                    '9999900001',
                    $designation,
                    $department,
                    now()->toDateString(),
                ]);
            }
        }

        if ($this->tableExists($attendancePdo, 'employees')) {
            foreach ($sampleEmployees as [$empName, $empCode, $designation]) {
                $stmt = $attendancePdo->prepare('SELECT id FROM employees WHERE employee_id = ? LIMIT 1');
                $stmt->execute([$empCode]);
                if ($stmt->fetchColumn()) {
                    continue;
                }

                $attendancePdo->prepare(
                    'INSERT INTO employees (employee_id, name, designation, created_at, updated_at)
                     VALUES (?, ?, ?, NOW(), NOW())'
                )->execute([$empCode, $empName, $designation]);
            }
        }

        if ($this->tableExists($attendancePdo, 'attendance_records')) {
            $empStmt = $attendancePdo->query("SELECT id, employee_id FROM employees WHERE employee_id = 'MNG-001' LIMIT 1");
            $emp = $empStmt->fetch(\PDO::FETCH_ASSOC);

            if ($emp) {
                $today = now()->toDateString();
                $exists = $attendancePdo->prepare('SELECT id FROM attendance_records WHERE employee_id = ? AND date = ? LIMIT 1');
                $exists->execute([$emp['id'], $today]);

                if (! $exists->fetchColumn()) {
                    $attendancePdo->prepare(
                        'INSERT INTO attendance_records (employee_id, date, status, check_in_time, check_out_time, total_hours, data_source, created_at, updated_at)
                         VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
                    )->execute([
                        $emp['id'],
                        $today,
                        'present',
                        '09:00:00',
                        '18:00:00',
                        8.0,
                        'manual',
                    ]);
                }
            }
        }
    }

    private function pdoFor(string $database, string $module = 'payroll'): \PDO
    {
        $creds = $this->credentialsForModule($module);
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $creds['host'],
            $creds['port'],
            $database
        );

        return new \PDO($dsn, $creds['username'], $creds['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    private function tableExists(\PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }

    private function currentFinancialYear(): string
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');

        if ($month >= 4) {
            return "{$year}-".($year + 1);
        }

        return ($year - 1)."-{$year}";
    }
}
