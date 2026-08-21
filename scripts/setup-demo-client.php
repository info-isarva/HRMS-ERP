<?php

/**
 * One-time demo setup: keep Super Admin only, flush operational data.
 * Run only against demo databases (hrms_client_dev_*).
 */

declare(strict_types=1);

const PAYROLL_SUPER_ADMIN_ID = 21;
const PAYROLL_SUPER_ADMIN_EMAIL = 'sup_admin@gmail.com';

function connect(string $basePath): PDO
{
    $dotenv = Dotenv\Dotenv::createMutable($basePath);
    $dotenv->safeLoad();

    $database = $_ENV['DB_DATABASE'] ?? '';
    if (!str_contains($database, 'client_dev')) {
        throw new RuntimeException("Refusing to run on non-demo database: {$database}");
    }

    return new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s', $_ENV['DB_HOST'], $_ENV['DB_PORT'] ?? 3306, $database),
        $_ENV['DB_USERNAME'],
        $_ENV['DB_PASSWORD'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
}

function tableExists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$table]);

    return (bool) $stmt->fetchColumn();
}

function truncateIfExists(PDO $pdo, string $table): void
{
    if (tableExists($pdo, $table)) {
        $pdo->exec("TRUNCATE TABLE `{$table}`");
        echo "  truncated {$table}\n";
    }
}

function setupPayroll(PDO $pdo): void
{
    echo "Payroll demo cleanup ({$pdo->query('SELECT DATABASE()')->fetchColumn()})\n";

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    $truncateTables = [
        'activity_logs',
        'apply_for_jobs',
        'employee_advance_deductions',
        'employee_advances',
        'employee_bank_details',
        'employee_basic_details',
        'employee_documents',
        'employee_exit_details',
        'employee_holiday_payout_details',
        'employee_incentive_details',
        'employee_increments',
        'employee_leave_allocations',
        'employee_ot_details',
        'employee_payroll_attendance_payout_month_statuses',
        'employee_payroll_attendance_salary_component_overrides',
        'employee_payroll_attendance_statutory_component_overrides',
        'employee_payroll_attendances',
        'employee_personal_details',
        'employee_salary_components',
        'employee_statuses',
        'employee_statutory_components',
        'employee_week_offs',
        'leave_information',
        'leave_type_sync_logs',
        'leaves',
        'manual_notifications',
        'notification_reads',
        'user_consents',
        'user_emergency_contacts',
        'password_reset_tokens',
        'password_resets',
        'personal_access_tokens',
        'sessions',
        'failed_jobs',
        'cache',
        'cache_locks',
    ];

    foreach ($truncateTables as $table) {
        truncateIfExists($pdo, $table);
    }

    if (tableExists($pdo, 'role_type_users')) {
        $pdo->exec('TRUNCATE TABLE role_type_users');
        echo "  truncated role_type_users\n";
    }

    $deleted = $pdo->exec(
        "DELETE FROM users WHERE id != " . PAYROLL_SUPER_ADMIN_ID . " AND email != '" . PAYROLL_SUPER_ADMIN_EMAIL . "'"
    );
    echo "  deleted {$deleted} non-admin users\n";

    $pdo->exec(
        "UPDATE users SET enable_crm = 0, enable_payroll = 0, enable_self_portal = 0, employee_id = NULL
         WHERE id = " . PAYROLL_SUPER_ADMIN_ID
    );
    echo "  reset super admin permissions\n";

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

function setupAttendance(PDO $pdo): void
{
    echo "Attendance demo cleanup ({$pdo->query('SELECT DATABASE()')->fetchColumn()})\n";

    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    $truncateTables = [
        'activity_log',
        'attendance_batches',
        'attendance_overrides',
        'attendance_records',
        'attendances',
        'bulk_attendance_summaries',
        'duty_rosters',
        'employees',
        'leave_application_days',
        'leave_applications',
        'manual_punches',
        'overtime',
        'overtimes',
        'proposed_attendance',
        'public_holiday_applications',
        'time_station_logs',
        'time_station_mappings',
        'sessions',
        'failed_jobs',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
    ];

    foreach ($truncateTables as $table) {
        truncateIfExists($pdo, $table);
    }

    foreach (['users_backup_2025_08_25_11_03_58', 'users_backup_2025_08_25_11_04_41'] as $backupTable) {
        truncateIfExists($pdo, $backupTable);
    }

    $deleted = $pdo->exec(
        "DELETE FROM users WHERE email != '" . PAYROLL_SUPER_ADMIN_EMAIL . "'"
    );
    echo "  deleted {$deleted} non-admin users\n";

    if (tableExists($pdo, 'users')) {
        $pdo->exec(
            "UPDATE users SET role = 'super_admin', payroll_user_id = " . PAYROLL_SUPER_ADMIN_ID . "
             WHERE email = '" . PAYROLL_SUPER_ADMIN_EMAIL . "'"
        );
        echo "  ensured super admin attendance user\n";
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
}

require dirname(__DIR__) . '/vendor/autoload.php';

$root = dirname(__DIR__);

setupPayroll(connect($root));
echo "\n";
setupAttendance(connect($root . '/attendance'));

echo "\nDemo database cleanup complete.\n";
