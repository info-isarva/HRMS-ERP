<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\PayrollApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ApiSyncController extends Controller
{
    protected $payrollService;
    protected $activityLogger;

    public function __construct(PayrollApiService $payrollService, ActivityLogger $activityLogger)
    {
        $this->payrollService = $payrollService;
        $this->activityLogger = $activityLogger;
        
        // Only admin and super admin can access these methods
        $this->middleware(['auth', 'can:manage-employees']);
    }
    
    /**
     * Show the API sync dashboard
     */
    public function index()
    {
        // Get API connection status
        $token = $this->payrollService->getToken();
        $apiConnected = !empty($token);
        
        // Stats
        $stats = [
            'api_departments_count' => 0,
            'system_departments_count' => Department::count(),
        ];
        
        // If API is connected, get counts
        if ($apiConnected) {
            $apiDepartments = $this->payrollService->getDepartments();
            $stats['api_departments_count'] = $apiDepartments ? count($apiDepartments) : 0;
        }
        
        return view('admin.api-sync', compact('apiConnected', 'stats'));
    }
    
    /**
     * Show employee sync page
     */
    public function employeeSync()
    {
        $totalEmployees = User::count();
        $regularEmployees = User::where('role', '!=', 'admin')
            ->where('role', '!=', 'super_admin')
            ->count();
        
        $employees = User::latest()->take(10)->get();
        
        $latestActivity = null;
        
        // Try to find the latest employee-related activity
        $activity = \Spatie\Activitylog\Models\Activity::where('log_name', 'employees')
            ->where('description', 'like', '%Synchronized%')
            ->latest()
            ->first();
            
        if ($activity) {
            $latestActivity = [
                'message' => $activity->description,
                'time' => $activity->created_at,
            ];
        }
        
        return view('admin.employees.sync', compact('totalEmployees', 'regularEmployees', 'employees', 'latestActivity'));
    }
    
    /**
     * Trigger a manual employee sync
     */
    public function syncEmployees(Request $request)
    {
        try {
            // Check for created employees before sync to compare later
            $employeeCountBefore = User::count();
            
            // Run the sync command
            $exitCode = Artisan::call('employees:sync');
            
            $output = Artisan::output();
            
            // Compare employee counts to see if sync actually happened
            $employeeCountAfter = User::count();
            $newEmployeesCount = $employeeCountAfter - $employeeCountBefore;
            
            if ($exitCode === 0) {
                $this->activityLogger->log(
                    'employees',
                    'Manual employee sync completed successfully',
                    $request->user(),
                    [
                        'new_employees_count' => $newEmployeesCount,
                        'total_after_sync' => $employeeCountAfter
                    ]
                );
                
                return redirect()->route('admin.employees.sync')
                    ->with('success', 'Employee synchronization completed successfully. Added ' . 
                            $newEmployeesCount . ' new employees.');
            } else {
                // Include the command output in the error message for better debugging
                $errorMessage = 'Employee synchronization failed with exit code ' . $exitCode;
                if (!empty(trim($output))) {
                    $errorMessage .= '. Command output: ' . $output;
                }
                
                return redirect()->route('admin.employees.sync')
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->route('admin.employees.sync')
                ->with('error', 'An error occurred during employee synchronization: ' . $e->getMessage());
        }
    }
    
    /**
     * Show API connection test page
     */
    public function testConnection()
    {
        $token = $this->payrollService->getToken();
        $isConnected = !empty($token);
        
        return view('admin.api-sync-test', compact('isConnected', 'token'));
    }
}
