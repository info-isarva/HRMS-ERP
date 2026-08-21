<?php

namespace App\Console\Commands;

use App\Models\Central\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TenantRegisterCurrentCommand extends Command
{
    protected $signature = 'tenant:register-current
        {--code=ISARVADEV : Company code for this dev environment}
        {--name=ISARVA Dev : Display name}';

    protected $description = 'Register the current hrmsdev environment as Tenant A (reads workspace .env + optional attendance/payroll .env paths)';

    public function handle(): int
    {
        $workspaceUrl = env('APP_URL', 'https://hrmsdev.isarva.in');
        $payrollUrl = env('PAYROLL_URL', 'https://payrolldev.isarva.in');
        $attendanceUrl = env('ATTENDANCE_URL', 'https://attendancedev.isarva.in');

        $workspaceDb = env('DB_DATABASE');
        $payrollDb = $this->readEnvValue(base_path('payroll/.env'), 'DB_DATABASE') ?: $workspaceDb;
        $attendanceDb = $this->readEnvValue(base_path('attendance/.env'), 'DB_DATABASE');

        if (! $workspaceDb || ! $attendanceDb) {
            $this->error('Could not read DB_DATABASE from workspace or attendance .env files.');

            return self::FAILURE;
        }

        $tenant = Tenant::query()->updateOrCreate(
            ['company_code' => strtoupper($this->option('code'))],
            [
                'name' => $this->option('name'),
                'workspace_domain' => $this->hostFromUrl($workspaceUrl),
                'payroll_domain' => $this->hostFromUrl($payrollUrl),
                'attendance_domain' => $this->hostFromUrl($attendanceUrl),
                'workspace_database' => $workspaceDb,
                'payroll_database' => $payrollDb,
                'attendance_database' => $attendanceDb,
                'status' => 'active',
                'meta' => [
                    'environment' => 'dev',
                    'registered_via' => 'tenant:register-current',
                    'registered_at' => now()->toIso8601String(),
                    'note' => 'Phase 1 registry only — apps still use .env DB until Phase 3+',
                ],
            ]
        );

        $this->info("Current environment registered as tenant #{$tenant->id} [{$tenant->company_code}]");
        $this->table(
            ['Module', 'Domain', 'Database'],
            [
                ['Workspace', $tenant->workspace_domain, $tenant->workspace_database],
                ['Payroll', $tenant->payroll_domain, $tenant->payroll_database],
                ['Attendance', $tenant->attendance_domain, $tenant->attendance_database],
            ]
        );

        $this->newLine();
        $this->line('Existing login, SSO, payroll, and attendance behavior is unchanged.');

        return self::SUCCESS;
    }

    private function hostFromUrl(string $url): string
    {
        $host = parse_url($url, PHP_URL_HOST);

        return Str::lower($host ?: trim($url, '/'));
    }

    private function readEnvValue(string $path, string $key): ?string
    {
        if (! is_readable($path)) {
            return null;
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (! str_contains($line, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $line, 2);
            if (trim($k) === $key) {
                return trim($v, " \t\"'");
            }
        }

        return null;
    }
}
