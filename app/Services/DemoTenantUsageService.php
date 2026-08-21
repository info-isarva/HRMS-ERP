<?php

namespace App\Services;

use App\Models\Central\Tenant;
use Carbon\Carbon;

class DemoTenantUsageService
{
    /**
     * Usage score based on onboarding milestones (0–100).
     *
     * @return array{
     *   score: int,
     *   milestones: array<int, array{key: string, label: string, done: bool, value: int|string|null}>,
     *   stats: array<string, int|string|null>,
     * }
     */
    public function analyze(Tenant $tenant): array
    {
        $stats = $this->collectStats($tenant);
        $milestones = $this->buildMilestones($stats);
        $done = count(array_filter($milestones, fn (array $m) => $m['done']));
        $total = max(count($milestones), 1);
        $score = (int) round(($done / $total) * 100);

        return [
            'score' => $score,
            'milestones' => $milestones,
            'stats' => $stats,
        ];
    }

    public function snapshot(Tenant $tenant): array
    {
        $analysis = $this->analyze($tenant);

        return [
            'captured_at' => now()->toIso8601String(),
            'score' => $analysis['score'],
            'stats' => $analysis['stats'],
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function collectStats(Tenant $tenant): array
    {
        $stats = [
            'workspace_users' => 0,
            'payroll_users' => 0,
            'payroll_employees' => 0,
            'payroll_departments' => 0,
            'company_configured' => false,
            'attendance_employees' => 0,
            'attendance_records' => 0,
            'last_login_at' => null,
            'recent_sessions' => 0,
        ];

        try {
            if ($tenant->workspace_database) {
                $pdo = $this->pdoFor($tenant->workspace_database);
                $stats['workspace_users'] = $this->count($pdo, 'users');
            }
        } catch (\Throwable) {
        }

        try {
            if ($tenant->payroll_database) {
                $pdo = $this->pdoFor($tenant->payroll_database);
                $stats['payroll_users'] = $this->count($pdo, 'users');
                $stats['payroll_employees'] = $this->tableExists($pdo, 'employee_basic_details')
                    ? $this->count($pdo, 'employee_basic_details')
                    : ($this->tableExists($pdo, 'employees') ? $this->count($pdo, 'employees') : 0);
                $stats['payroll_departments'] = $this->tableExists($pdo, 'departments')
                    ? $this->count($pdo, 'departments')
                    : 0;

                if ($this->tableExists($pdo, 'company_settings')) {
                    $row = $pdo->query('SELECT company_name FROM company_settings ORDER BY id ASC LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
                    $stats['company_configured'] = $row && trim((string) ($row['company_name'] ?? '')) !== '';
                } elseif ($this->tableExists($pdo, 'companies')) {
                    $row = $pdo->query('SELECT name FROM companies ORDER BY id ASC LIMIT 1')->fetch(\PDO::FETCH_ASSOC);
                    $stats['company_configured'] = $row && trim((string) ($row['name'] ?? '')) !== '';
                }

                $login = $pdo->query('SELECT MAX(last_login) FROM users')->fetchColumn();
                if ($login) {
                    $stats['last_login_at'] = (string) $login;
                }
            }
        } catch (\Throwable) {
        }

        try {
            if ($tenant->attendance_database) {
                $pdo = $this->pdoFor($tenant->attendance_database);
                $stats['attendance_employees'] = $this->tableExists($pdo, 'employees')
                    ? $this->count($pdo, 'employees')
                    : 0;

                if ($this->tableExists($pdo, 'attendance_records')) {
                    $stats['attendance_records'] = $this->count($pdo, 'attendance_records');
                } elseif ($this->tableExists($pdo, 'attendances')) {
                    $stats['attendance_records'] = $this->count($pdo, 'attendances');
                }

                if ($this->tableExists($pdo, 'sessions')) {
                    $cutoff = now()->subDays(7)->timestamp;
                    $stmt = $pdo->prepare('SELECT COUNT(*) FROM sessions WHERE last_activity >= ?');
                    $stmt->execute([$cutoff]);
                    $stats['recent_sessions'] = (int) $stmt->fetchColumn();
                }
            }
        } catch (\Throwable) {
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $stats
     * @return array<int, array{key: string, label: string, done: bool, value: int|string|null}>
     */
    private function buildMilestones(array $stats): array
    {
        return [
            [
                'key' => 'logged_in',
                'label' => 'Admin logged in',
                'done' => ! empty($stats['last_login_at']),
                'value' => $stats['last_login_at'],
            ],
            [
                'key' => 'company_setup',
                'label' => 'Company profile configured',
                'done' => (bool) $stats['company_configured'],
                'value' => $stats['company_configured'] ? 'Yes' : 'No',
            ],
            [
                'key' => 'departments',
                'label' => 'Departments created',
                'done' => (int) $stats['payroll_departments'] > 0,
                'value' => (int) $stats['payroll_departments'],
            ],
            [
                'key' => 'employees',
                'label' => 'Employees added',
                'done' => (int) $stats['payroll_employees'] > 0,
                'value' => (int) $stats['payroll_employees'],
            ],
            [
                'key' => 'extra_users',
                'label' => 'Additional users created',
                'done' => (int) $stats['payroll_users'] > 1,
                'value' => max(0, (int) $stats['payroll_users'] - 1),
            ],
            [
                'key' => 'attendance_usage',
                'label' => 'Attendance records entered',
                'done' => (int) $stats['attendance_records'] > 0,
                'value' => (int) $stats['attendance_records'],
            ],
            [
                'key' => 'recent_activity',
                'label' => 'Active in last 7 days',
                'done' => (int) $stats['recent_sessions'] > 0 || ! empty($stats['last_login_at']),
                'value' => (int) $stats['recent_sessions'],
            ],
        ];
    }

    public function scoreColor(int $score): string
    {
        if ($score >= 70) {
            return 'success';
        }

        if ($score >= 35) {
            return 'warning';
        }

        return 'danger';
    }

    public function formatLastLogin(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        try {
            return Carbon::parse($value)->timezone('Asia/Kolkata')->format('d M Y, g:i a');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function pdoFor(string $database): \PDO
    {
        $config = config('database.connections.mysql');
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
            $config['host'],
            $config['port'] ?? 3306,
            $database
        );

        return new \PDO($dsn, $config['username'], $config['password'], [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
    }

    private function count(\PDO $pdo, string $table): int
    {
        if (! $this->tableExists($pdo, $table)) {
            return 0;
        }

        return (int) $pdo->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
    }

    private function tableExists(\PDO $pdo, string $table): bool
    {
        $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
        $stmt->execute([$table]);

        return (bool) $stmt->fetchColumn();
    }
}
