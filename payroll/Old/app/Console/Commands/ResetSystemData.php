<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetSystemData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:reset-data {--force : Force the operation to run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush all employees and transactional data in Payroll, keeping only Super Admins.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will DELETE ALL DATA. Are you sure?')) {
            return;
        }

        $this->info('Starting Payroll System Reset...');

        // IDs to Keep (Super Admins: 2, 223)
        $keepUserIds = [2, 223];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Users
        $this->info('Cleaning Users...');
        DB::table('users')->whereNotIn('id', $keepUserIds)->delete();
        
        // 2. Transactional Tables
        $tables = [
            'employees',
            'bank_details',
            'documents',
            'family_members',
            'staff_salaries',
            'leaves',
            'attendances',
            'advances',
            'increments',
            'promotions',
            'resignations',
            'terminations',
            'transfers',
            'warnings',
            'awards',
            'assets',
            'complaints',
            'notifications',
            'manual_notifications',
            'held_salaries',
            'expenses',
            'estimates',
            'estimates_adds',
            'employee_salary_components',
            'employee_statutory_components',
            'employee_week_offs',
            'employment_histories',
            'performance_appraisals',
            'trainings',
            'trainers',
            'user_emergency_contacts',
            'personal_information',
            'profile_information',
            'job_applications', // If exists
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                $this->info("Truncating $table...");
                DB::table($table)->truncate();
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('Payroll System Reset Complete.');
    }
}
