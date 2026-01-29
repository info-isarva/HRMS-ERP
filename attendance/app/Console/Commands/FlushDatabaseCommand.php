<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\LeaveType;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FlushDatabaseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:flush {--force : Skip confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flush all database tables except admin and super admin users';

    /**
     * Execute the console command.
     */
    public function handle(ActivityLogger $activityLogger)
    {
        $this->info('Preparing to flush database tables...');
        
        // Skip confirmation if force option is provided or if running from web
        if (!$this->option('force') && app()->runningInConsole()) {
            if (!$this->confirm('Are you sure you want to flush all database tables? This action cannot be undone!', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }
        
        $this->info('Starting database flush...');
        
        try {
            DB::beginTransaction();
            
            // Preserve admin and super admin users
            $adminUsers = User::where('role', 'admin')
                ->orWhere('role', 'super_admin')
                ->get();
            
            $this->info('Preserving ' . $adminUsers->count() . ' admin/super admin users');
            
            // Truncate tables
            $this->truncateTable(LeaveApplication::class, 'leave applications');
            $this->truncateTable(PublicHolidayApplication::class, 'public holiday applications');
            
            // Clear departments but keep related tables
            $this->info('Flushing departments...');
            DB::table('department_public_holidays')->delete();
            DB::table('department_leave_types')->delete();
            Department::truncate();
            
            // Reset leave types to default
            $this->info('Resetting leave types to default...');
            LeaveType::truncate();
            
            // Clear public holidays
            $this->info('Flushing public holidays...');
            PublicHoliday::truncate();
            
            // Remove all non-admin users
            $this->info('Removing all non-admin users...');
            User::where('role', '!=', 'admin')
                ->where('role', '!=', 'super_admin')
                ->delete();
            
            // Reset auto-increment values
            $this->info('Resetting auto-increment values...');
            $tables = [
                'leave_applications', 
                'public_holidays', 
                'public_holiday_applications',
                'departments',
                'leave_types'
            ];
            
            foreach ($tables as $table) {
                if (Schema::hasTable($table)) {
                    DB::statement("ALTER TABLE {$table} AUTO_INCREMENT = 1;");
                }
            }
            
            DB::commit();
            
            // Log the action
            $activityLogger->log(
                'system',
                'Database flushed: All tables truncated except admin users',
                null,
                ['admin_users_preserved' => $adminUsers->count()]
            );
            
            $this->info('Database flush completed successfully.');
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error flushing database: ' . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Truncate a table and log the action
     */
    private function truncateTable($model, $description)
    {
        $this->info('Flushing ' . $description . '...');
        $model::truncate();
    }
}
