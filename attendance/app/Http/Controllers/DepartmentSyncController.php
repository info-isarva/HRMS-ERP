<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Services\ActivityLogger;
use App\Services\PayrollApiService;
use App\Services\DepartmentSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class DepartmentSyncController extends Controller
{
    protected $payrollService;
    protected $activityLogger;
    protected $syncService;

    public function __construct(PayrollApiService $payrollService, DepartmentSyncService $syncService)
    {
        $this->payrollService = $payrollService;
        $this->syncService = $syncService;
        $this->activityLogger = new ActivityLogger();
        // Only admin and super admin can access these methods, except webhooks
        $this->middleware(['auth', 'can:manage-employees'])->except([
            'webhook', 'apiSync', 'apiSyncCreate', 'apiSyncUpdate', 'apiSyncDelete'
        ]);
    }

    /**
     * Show the department sync dashboard
     */
    public function index()
    {
        // Get sync status
        $syncStatus = $this->syncService->getSyncStatus();

        // Get departments from API
        $apiDepartments = $this->payrollService->getDepartments();

        // Get current system departments
        $systemDepartments = Department::withCount(['employees' => function ($query) {
                $query->where('status', 'Active');
            }])
            ->orderBy('name')
            ->get();

        // Get API connection status
        $apiConnected = ($apiDepartments !== null);

        // Enhanced stats for the dashboard
        $stats = array_merge([
            'api_departments_count' => $apiDepartments ? count($apiDepartments) : 0,
            'system_departments_count' => $systemDepartments->count(),
        ], $syncStatus);

        return view('admin.department-sync', compact(
            'apiDepartments',
            'systemDepartments',
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
            // Use the existing artisan command for comprehensive sync
            $commandOptions = [];
            if ($options['force_update']) {
                $commandOptions['--force'] = true;
            }
            if ($options['delete_extra']) {
                $commandOptions['--delete'] = true;
            }
            $commandOptions['--detailed'] = true;

            $exitCode = Artisan::call('departments:sync', $commandOptions);
            $output = Artisan::output();

            if ($exitCode === 0) {
                $syncType = 'Comprehensive Sync' . ($options['delete_extra'] ? ' (with deletions)' : ' (no deletions)');
                $message = "Department synchronization completed successfully - {$syncType}";

                return redirect()->route('admin.department-sync')
                    ->with('success', $message);
            } else {
                $errorMessage = 'Department synchronization failed with exit code ' . $exitCode;
                if (!empty(trim($output))) {
                    $errorMessage .= '. Command output: ' . $output;
                }

                return redirect()->route('admin.department-sync')
                    ->with('error', $errorMessage);
            }

        } catch (\Exception $e) {
            Log::error('Manual department sync failed: ' . $e->getMessage());

            return redirect()->route('admin.department-sync')
                ->with('error', 'Failed to synchronize departments: ' . $e->getMessage());
        }
    }

    /**
     * API endpoint to sync a single department from payroll
     */
    public function apiSync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|integer',
            'name' => 'required|string',
            'code' => 'nullable|string',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $result = $this->syncService->processSingleDepartment($data);

            return response()->json([
                'status' => 'success',
                'action' => $result['action'],
                'department' => $result['department']
            ]);
        } catch (\Exception $e) {
            Log::error('API department sync failed: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to sync department: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Webhook endpoint for real-time department updates from payroll system
     */
    public function webhook(Request $request)
    {
        // Log the raw request first
        Log::info("Department webhook received", [
            'raw_data' => $request->all(),
            'headers' => $request->headers->all()
        ]);

        // Validate the webhook request
        $request->validate([
            'action' => 'required|in:create,update,delete',
            'department_data' => 'required|array',
            'timestamp' => 'required',
            // Add webhook signature validation here for security
        ]);

        try {
            $action = $request->input('action');
            $departmentData = $request->input('department_data');

            Log::info("Received department webhook for {$action}", [
                'department_id' => $departmentData['id'] ?? 'unknown',
                'department_name' => $departmentData['name'] ?? 'unknown',
                'action' => $action,
                'timestamp' => $request->input('timestamp')
            ]);

            switch ($action) {
                case 'create':
                case 'update':
                    $result = $this->syncService->processSingleDepartment($departmentData);

                    $this->activityLogger->log(
                        "Real-time department {$action} via webhook - " . ($departmentData['name'] ?? 'Unknown'),
                        'departments',
                        null,
                        [
                            'department_id' => $result['department']->id ?? null,
                            'api_department_id' => $departmentData['id'] ?? null,
                            'action' => $action
                        ]
                    );
                    break;

                case 'delete':
                    $result = $this->syncService->handleDepartmentDeletion($departmentData['id']);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => "Department {$action} processed successfully",
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error("Department webhook processing failed: " . $e->getMessage(), [
                'department_data' => $departmentData ?? null,
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
            $status['webhook_url'] = route('department-sync.webhook');
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
     * Show API connection test page
     */
    public function testConnection()
    {
        $token = $this->payrollService->getToken();
        $isConnected = !empty($token);

        return view('admin.department-sync-test', compact('isConnected', 'token'));
    }

    /**
     * Get sync preview showing what would be affected
     */
    public function preview()
    {
        try {
            // Get API departments
            $apiDepartments = $this->payrollService->getDepartments();
            $systemDepartments = Department::all()->keyBy('name');

            $preview = [
                'api_departments' => $apiDepartments ?: [],
                'system_departments' => $systemDepartments->toArray(),
                'would_create' => [],
                'would_update' => [],
                'would_delete' => [],
            ];

            if ($apiDepartments) {
                foreach ($apiDepartments as $apiDept) {
                    $name = $apiDept['name'];
                    if (isset($systemDepartments[$name])) {
                        $preview['would_update'][] = $name;
                    } else {
                        $preview['would_create'][] = $name;
                    }
                }

                // Find departments that would be deleted (not in API)
                $apiNames = array_column($apiDepartments, 'name');
                foreach ($systemDepartments as $sysDept) {
                    if (!in_array($sysDept->name, $apiNames)) {
                        $preview['would_delete'][] = $sysDept->name;
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $preview
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get department sync preview: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get sync preview: ' . $e->getMessage()
            ], 500);
        }
    }
}