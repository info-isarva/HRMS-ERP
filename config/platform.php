<?php

return [

    /*
    | Emails allowed to access the internal Demo Tenant Manager (workspace hub).
    | Comma-separated in .env: PLATFORM_ADMIN_EMAILS=sup_admin@gmail.com,admin@isarva.in
    */
    'admin_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('PLATFORM_ADMIN_EMAILS', 'sup_admin@gmail.com'))
    ))),

    'login_url' => env('PLATFORM_DEMO_LOGIN_URL', 'https://hrmsdev.isarva.in'),
    'payroll_url' => env('PLATFORM_DEMO_PAYROLL_URL', 'https://payrolldev.isarva.in'),
    'attendance_url' => env('PLATFORM_DEMO_ATTENDANCE_URL', 'https://attendancedev.isarva.in'),

    'internal_company_code' => env('PLATFORM_INTERNAL_COMPANY_CODE', 'ISARVADEV'),

    'default_demo_days' => (int) env('PLATFORM_DEFAULT_DEMO_DAYS', 15),

    /*
    | CyberPanel / MySQL admin used to CREATE DATABASE and GRANT app users on new demo shards.
    | Typically the root password from CyberPanel → Databases → Reset MySQL Password.
    */
    'mysql_provision' => [
        'host' => env('PLATFORM_MYSQL_ADMIN_HOST', env('DB_HOST', '127.0.0.1')),
        'port' => (int) env('PLATFORM_MYSQL_ADMIN_PORT', env('DB_PORT', 3306)),
        'username' => env('PLATFORM_MYSQL_ADMIN_USERNAME'),
        'password' => env('PLATFORM_MYSQL_ADMIN_PASSWORD'),
    ],

    /*
    | App users that receive ALL PRIVILEGES on newly created shard databases.
    */
    'shard_users' => [
        'payroll' => [
            'username' => env('PLATFORM_PAYROLL_DB_USERNAME', env('DB_USERNAME')),
            'password' => env('PLATFORM_PAYROLL_DB_PASSWORD', env('DB_PASSWORD')),
        ],
        'attendance' => [
            'username' => env('PLATFORM_ATTENDANCE_DB_USERNAME', 'hrms_dev_latest_attendance_v2'),
            'password' => env('PLATFORM_ATTENDANCE_DB_PASSWORD'),
        ],
    ],

    /*
    | PHP CLI for attendance shard migrations (LiteSpeed web SAPI leaves PHP_BINARY empty).
    */
    'php_cli' => env('PLATFORM_PHP_CLI', '/usr/local/lsws/lsphp82/bin/php'),

];
