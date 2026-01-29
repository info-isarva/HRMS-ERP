<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Department;
use App\Services\ActivityLogger;
use App\Services\PayrollApiService;
use App\Services\EmployeeSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;
use Carbon\Carbon;

class EmployeeSyncController extends Controller
{
    protected $payrollService;
    protected $activityLogger;
    protected $syncService;

    public function __construct(PayrollApiService $payrollService, EmployeeSyncService $syncService)
    {
        $this->payrollService = $payrollService;
        $this->syncService = $syncService;
        $this->activityLogger = new ActivityLogger();
        // Only admin and super admin can access these methods
        $this->middleware(['auth', 'can:manage-employees'])->except([
            'verifyToken', 'syncEmployeeFromPayroll', 'updateEmployeeFromPayroll', 'deleteEmployeeFromPayroll', 'apiSync', 'webhook'
        ]);
    }
    
    /**
     * Show the employee sync dashboard
     */
    public function index()
    {
        // Get sync status from the new service
        $syncStatus = $this->syncService->getSyncStatus();
        
        // Get a sample of employees from the API
        $apiEmployees = $this->payrollService->getEmployees();
        
        // Get current system employees
        $systemEmployees = Employee::with('department')
            ->orderBy('name')
            ->get();
        
        // Get API connection status
        $apiConnected = ($apiEmployees !== null);
        
        // Enhanced stats for the dashboard
        $stats = array_merge([
            'api_employees_count' => $apiEmployees ? count($apiEmployees) : 0,
            'system_employees_count' => $systemEmployees->count(),
        ], $syncStatus);
        
        return view('admin.employee-sync', compact(
            'apiEmployees', 
            'systemEmployees', 
            'apiConnected', 
            'stats'
        ));
    } 
    
    /**
     * Trigger a comprehensive manual sync
     */
    public function sync(Request $request)
    {
        // Validate sync options
        $request->validate([
            'force_update' => 'nullable|boolean',
            'delete_extra' => 'nullable|boolean',
        ]);
        
        // Respect the user's choice for delete_extra
        $deleteExtra = $request->input('delete_extra', false);
        
        $options = [
            'force_update' => $request->input('force_update', false),
            'delete_extra' => $deleteExtra,
            'verbose' => true
        ];
        
        try {
            // Use comprehensive sync that respects the delete_extra option
            $result = $this->syncService->syncAllEmployees($options);
            $syncType = 'Comprehensive Sync' . ($options['delete_extra'] ? ' (with deletions)' : ' (no deletions)');
            
            
            if ($result['success']) {
                $stats = $result['stats'] ?? [];
                $message = $result['message'] . " - $syncType";
                
                // Enhanced success message with stats
                if (!empty($stats)) {
                    $message .= sprintf(
                        ' (Created: %d, Updated: %d, Deleted: %d)',
                        $stats['created'],
                        $stats['updated'], 
                        $stats['deleted']
                    );
                }
                
                return redirect()->route('admin.employee-sync')
                    ->with('success', $message);
            } else {
                return redirect()->route('admin.employee-sync')
                    ->with('error', $result['message']);
            }
            
        } catch (\Exception $e) {
            Log::error('Manual employee sync failed: ' . $e->getMessage());
            
            return redirect()->route('admin.employee-sync')
                ->with('error', 'Failed to synchronize employees: ' . $e->getMessage());
        }
    }
    
    /**
     * Show API connection test page
     */
    public function testConnection()
    {
        $token = $this->payrollService->getToken();
        $isConnected = !empty($token);
        
        return view('admin.employee-sync-test', compact('isConnected', 'token'));
    }

    /**
     * API endpoint to sync a single employee from payroll
     */
    public function apiSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|string',
            'payroll_id' => 'required|integer',
            'name' => 'required|string',
            'email' => 'required|email',
            'designation' => 'nullable|string',
            'phone' => 'nullable|string',
            'status' => 'nullable|string',
            'department' => 'nullable|string',
            'financial_year' => 'nullable|string',
            'date_of_joining' => 'nullable|date',
            'date_of_resignation' => 'nullable|date',
            'reporting_manager_payroll_id' => 'nullable|integer',
            'exclude_from_payroll' => 'nullable|boolean',
            'additional_data' => 'nullable|array',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }
        $data = $validator->validated();
        // Map department name to ID
        $departmentId = null;
        if (!empty($data['department'])) {
            $department = Department::where('name', $data['department'])->first();
            if ($department) $departmentId = $department->id;
        }
        // Upsert employee
        $employee = Employee::updateOrCreate(
            ['employee_id' => $data['employee_id']],
            [
                'payroll_id' => $data['payroll_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'designation' => $data['designation'] ?? null,
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'Active',
                'department_id' => $departmentId,
                'payroll_department_id' => $data['department_id'] ?? null, // Store API department ID
                'financial_year' => $data['financial_year'] ?? null,
                'date_of_joining' => $data['date_of_joining'] ?? null,
                'date_of_resignation' => $data['date_of_resignation'] ?? null,
                'reporting_manager_payroll_id' => $data['reporting_manager_payroll_id'] ?? null,
                'exclude_from_payroll' => $data['exclude_from_payroll'] ?? 0,
                'additional_data' => !empty($data['additional_data']) ? json_encode($data['additional_data']) : null,
            ]
        );
        $this->activityLogger->log('Synced employee from payroll', 'employees', $employee);
        return response()->json(['status' => 'success', 'employee' => $employee]);
    }

    /**
     * Webhook endpoint for real-time employee updates from payroll system
     */
    public function webhook(Request $request)
    {
        // Log the raw request first
        Log::info("Webhook received", [
            'raw_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Validate the webhook request (you should implement proper authentication)
        $request->validate([
            'action' => 'required|in:create,update,delete',
            'employee_data' => 'required|array',
            'timestamp' => 'required',
            // Add webhook signature validation here for security
        ]);

        try {
            $action = $request->input('action');
            $employeeData = $request->input('employee_data');
            
            Log::info("Received webhook for employee {$action}", [
                'employee_id' => $employeeData['employee_id'] ?? 'unknown',
                'action' => $action,
                'timestamp' => $request->input('timestamp')
            ]);

            switch ($action) {
                case 'create':
                case 'update':
                    $result = $this->syncService->processSingleEmployee($employeeData);
                    
                    $this->activityLogger->log(
                        "Real-time employee {$action} via webhook - " . ($employeeData['name'] ?? 'Unknown'),
                        'employees',
                        null,
                        ['employee_id' => $employeeData['employee_id'] ?? null, 'action' => $action]
                    );
                    break;
                    
                case 'delete':
                    $this->handleEmployeeDeletion($employeeData['employee_id']);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Employee {$action} processed successfully",
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error("Webhook processing failed: " . $e->getMessage(), [
                'employee_data' => $employeeData ?? null,
                'action' => $action ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get real-time sync status (AJAX endpoint)
     */
    public function status()
    {
        try {
            $status = $this->syncService->getSyncStatus();
            
            // Add additional info
            $status['webhook_url'] = route('employee-sync.webhook');
            $status['last_check'] = now()->toISOString();
            
            return response()->json([
                'success' => true,
                'status' => $status
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle employee deletion from payroll system
     */
    protected function handleEmployeeDeletion($employeeId)
    {
        $employee = Employee::where('employee_id', $employeeId)->first();
        
        if ($employee) {
            // Clean up related data
            $this->syncService->cleanupEmployeeData($employee);
            
            // Delete the employee
            $employee->delete();
            
            $this->activityLogger->log(
                "Real-time employee deletion via webhook - {$employee->name}",
                'employees',
                null,
                ['employee_id' => $employeeId]
            );
            
            Log::info("Employee {$employeeId} deleted via webhook");
        }
    }
    
    /**
     * Get sync preview showing what would be affected
     */
    public function preview()
    {
        try {
            $preview = $this->syncService->getSyncPreview();
            
            return response()->json([
                'status' => 'success',
                'data' => $preview
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get sync preview: ' . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get sync preview: ' . $e->getMessage()
            ], 500);
        }
    }
}
