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
    protected $description = 'Flush all employees and transactional data, keeping only Super Admins.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!$this->option('force') && !$this->confirm('This will DELETE ALL EMPLOYEES and ATTENDANCE DATA. Are you sure?')) {
            return;
        }

        $this->info('Starting System Reset...');

        // IDs to Keep (Super Admins)
        $keepUserIds = [2, 34];

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Users
        $this->info('Cleaning Users...');
        DB::table('users')->whereNotIn('id', $keepUserIds)->delete();
        
        // 2. Employee Data
        $this->info('Truncating Employees...');
        if (Schema::hasTable('employees')) DB::table('employees')->truncate();
        if (Schema::hasTable('bank_details')) DB::table('bank_details')->truncate();
        if (Schema::hasTable('documents')) DB::table('documents')->truncate();
        if (Schema::hasTable('family_members')) DB::table('family_members')->truncate();
        if (Schema::hasTable('job_details')) DB::table('job_details')->truncate();
        if (Schema::hasTable('employee_leave_balances')) DB::table('employee_leave_balances')->truncate();
        
        // 3. Attendance Data
        $this->info('Truncating Attendance Data...');
        if (Schema::hasTable('attendances')) DB::table('attendances')->truncate();
        if (Schema::hasTable('attendance_records')) DB::table('attendance_records')->truncate();
        if (Schema::hasTable('time_station_logs')) DB::table('time_station_logs')->truncate();
        if (Schema::hasTable('overtimes')) DB::table('overtimes')->truncate();
        // Also clear process batches if any
        if (Schema::hasTable('attendance_process_batches')) {
            DB::table('attendance_process_batches')->truncate();
        }

        // 4. Leave Data
        $this->info('Truncating Leave Data...');
        if (Schema::hasTable('leave_applications')) DB::table('leave_applications')->truncate();
        if (Schema::hasTable('leave_balances')) DB::table('leave_balances')->truncate();

        // 5. Payroll Data
        $this->info('Truncating Payroll Data...');
        if (Schema::hasTable('salaries')) {
            DB::table('salaries')->truncate();
        }
        if (Schema::hasTable('salary_slips')) {
            DB::table('salary_slips')->truncate();
        }
        
        // 6. Notifications/Activity
        if (Schema::hasTable('notifications')) {
            DB::table('notifications')->truncate();
        }
        if (Schema::hasTable('activity_log')) {
            DB::table('activity_log')->truncate();
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->info('System Reset Complete. Only Super Admins (IDs: ' . implode(',', $keepUserIds) . ') remain.');
    }
}
