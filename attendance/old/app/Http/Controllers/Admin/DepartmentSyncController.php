<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class DepartmentSyncController extends Controller
{
    /**
     * Show the department sync status page
     */
    public function index()
    {
        $totalDepartments = Department::count();
        $departments = Department::latest()->take(10)->get();
        
        $latestActivity = null;
        
        // Try to find the latest department-related activity
        $activity = \Spatie\Activitylog\Models\Activity::where('log_name', 'departments')
            ->where('description', 'like', '%Synchronized%')
            ->latest()
            ->first();
            
        if ($activity) {
            $latestActivity = [
                'message' => $activity->description,
                'time' => $activity->created_at,
            ];
        }
        
        return view('admin.departments.sync', compact('totalDepartments', 'departments', 'latestActivity'));
    }
    
    /**
     * Trigger a department sync
     */
    public function sync(Request $request, ActivityLogger $activityLogger)
    {
        $force = $request->input('force', false);
        
        try {
            Log::info('Manual department sync triggered by admin', ['force' => $force]);
            
            // Run the sync command
            $exitCode = Artisan::call('departments:sync', [
                '--force' => $force
            ]);
            
            $output = Artisan::output();
            Log::info('Department sync command output', ['output' => $output]);
            
            if ($exitCode === 0) {
                $activityLogger->log(
                    'departments',
                    'Manual department sync completed successfully' . ($force ? ' with force option' : '')
                );
                
                return redirect()->route('admin.departments.sync')
                    ->with('success', 'Department synchronization completed successfully.');
            } else {
                Log::error('Department sync command failed', ['exitCode' => $exitCode]);
                
                return redirect()->route('admin.departments.sync')
                    ->with('error', 'Department synchronization failed. Check the logs for more information.');
            }
        } catch (\Exception $e) {
            Log::error('Exception during department sync', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.departments.sync')
                ->with('error', 'An error occurred during department synchronization: ' . $e->getMessage());
        }
    }
}
