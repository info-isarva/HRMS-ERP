<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\LeaveApplication;
use App\Models\PublicHoliday;
use App\Models\PublicHolidayApplication;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseManagerController extends Controller
{
    /**
     * Show database management page
     */
    public function index()
    {
        // Gather database stats
        $stats = [
            'users' => User::count(),
            'departments' => Department::count(),
            'leave_applications' => LeaveApplication::count(),
            'public_holidays' => PublicHoliday::count(),
            'public_holiday_applications' => PublicHolidayApplication::count(),
        ];
        
        // Get the latest system activity
        $latestActivity = null;
        $activity = \Spatie\Activitylog\Models\Activity::where('log_name', 'system')
            ->latest()
            ->first();
            
        if ($activity) {
            $latestActivity = [
                'message' => $activity->description,
                'time' => $activity->created_at,
            ];
        }
        
        return view('admin.database-manager', compact('stats', 'latestActivity'));
    }
    
    /**
     * Flush database tables
     */
    public function flush(Request $request, ActivityLogger $activityLogger)
    {
        try {
            Log::info('Database flush initiated by admin');
            
            // Run the flush command
            $exitCode = Artisan::call('db:flush', [
                '--force' => true
            ]);
            
            $output = Artisan::output();
            Log::info('Database flush output', ['output' => $output]);
            
            if ($exitCode === 0) {
                return redirect()->route('admin.database.index')
                    ->with('success', 'Database flushed successfully. All tables have been cleared except admin users.');
            } else {
                Log::error('Database flush command failed', ['exitCode' => $exitCode]);
                
                return redirect()->route('admin.database.index')
                    ->with('error', 'Failed to flush database. Check the logs for more information.');
            }
        } catch (\Exception $e) {
            Log::error('Exception during database flush', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.database.index')
                ->with('error', 'An error occurred during database flush: ' . $e->getMessage());
        }
    }
    
    /**
     * Flush and sync from API
     */
    public function flushAndSync(Request $request, ActivityLogger $activityLogger)
    {
        try {
            Log::info('Database flush and sync initiated by admin');
            
            // Step 1: Flush the database
            $flushExitCode = Artisan::call('db:flush', [
                '--force' => true
            ]);
            
            if ($flushExitCode !== 0) {
                Log::error('Database flush failed during flush and sync', ['exitCode' => $flushExitCode]);
                return redirect()->route('admin.database.index')
                    ->with('error', 'Database flush failed. Sync was not performed.');
            }
            
            // Step 2: Sync departments from API
            $deptExitCode = Artisan::call('departments:sync');
            
            if ($deptExitCode !== 0) {
                Log::error('Department sync failed during flush and sync', ['exitCode' => $deptExitCode]);
                return redirect()->route('admin.database.index')
                    ->with('error', 'Database was flushed but department sync failed.');
            }
            
            // Step 3: Sync employees from API
            $employeeExitCode = Artisan::call('employees:sync');
            $syncResults = 'Departments synced successfully';
            
            if ($employeeExitCode === 0) {
                $syncResults .= ' and employees synced successfully';
            } else {
                Log::warning('Employee sync failed during flush and sync, but departments were synced', 
                    ['exitCode' => $employeeExitCode]);
                $syncResults .= ' but employee sync failed';
            }
            
            $activityLogger->log(
                'system',
                'Database flushed and data synced from API',
                $request->user(),
                [
                    'flush_exit_code' => $flushExitCode, 
                    'dept_sync_exit_code' => $deptExitCode,
                    'employee_sync_exit_code' => $employeeExitCode
                ]
            );
            
            return redirect()->route('admin.database.index')
                ->with('success', 'Database flushed and ' . $syncResults . '.');
        } catch (\Exception $e) {
            Log::error('Exception during database flush and sync', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.database.index')
                ->with('error', 'An error occurred during database flush and sync: ' . $e->getMessage());
        }
    }
    
    /**
     * Sync employees from API
     */
    public function syncEmployees(Request $request, ActivityLogger $activityLogger)
    {
        try {
            Log::info('Employee sync initiated by admin');
            
            $exitCode = Artisan::call('employees:sync');
            $output = Artisan::output();
            
            Log::info('Employee sync output', ['output' => $output]);
            
            if ($exitCode === 0) {
                $activityLogger->log(
                    'employees',
                    'Admin initiated employee sync from API',
                    $request->user()
                );
                
                return redirect()->route('admin.database.index')
                    ->with('success', 'Employees synced successfully from API.');
            } else {
                Log::error('Employee sync command failed', ['exitCode' => $exitCode]);
                
                return redirect()->route('admin.database.index')
                    ->with('error', 'Failed to sync employees. Check the logs for more information.');
            }
        } catch (\Exception $e) {
            Log::error('Exception during employee sync', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.database.index')
                ->with('error', 'An error occurred during employee sync: ' . $e->getMessage());
        }
    }
}
