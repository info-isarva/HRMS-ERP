<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeApiService
{
    protected $payrollApiService;
    
    public function __construct(PayrollApiService $payrollApiService)
    {
        $this->payrollApiService = $payrollApiService;
    }
    
    /**
     * Sync employees from the API - only adding new employees
     * 
     * @param bool $isDetailed Whether to show detailed output
     * @param \Illuminate\Console\Command|null $command Command instance for output
     * @param bool $isTest Whether to run in test mode (process only one employee)
     * @return array Information about the sync operation
     */
    public function syncEmployees($isDetailed = false, $command = null, $isTest = false)
    {
        Log::info('Starting employee sync');
        
        if ($command && $isDetailed) {
            $command->info('Retrieving employees from API...');
        }
        
        $employees = $this->payrollApiService->getEmployees();
        
        if (!$employees) {
            Log::error('Failed to retrieve employees from the API');
            return [
                'success' => false,
                'message' => 'Failed to retrieve employees from the API',
                'count' => 0
            ];
        }
        
        // For test mode, create a single test employee with reporting manager
        if ($isTest) {
            // Create a test employee with a reporting manager
            $testEmployee = [
                'id' => 999,
                'employee_id' => 'TEST-001',
                'name' => 'TEST EMPLOYEE',
                'designation' => 'Test Position',
                'department_id' => '4',
                'date_of_resignation' => 'Active',
                'date_of_joining' => '01-08-2025',
                'ot_status' => 1,
                'role' => [
                    'id' => 2,
                    'name' => 'Employee'
                ],
                'reporting_manager' => [
                    'id' => 16,
                    'employee_id' => 'DRI-035',
                    'name' => 'GANESH'
                ]
            ];
            
            // Delete the test employee if it exists
            Employee::where('employee_id', 'TEST-001')->delete();
            
            $employees = [$testEmployee];
            
            if ($command && $isDetailed) {
                $command->info("Test mode: Created test employee with reporting manager ID 16");
            }
        }
        
        $count = count($employees);
        Log::info('Retrieved employees from API', ['count' => $count]);
        
        if ($command && $isDetailed) {
            $command->info("Found {$count} employees from the API");
        }
        
        DB::beginTransaction();
        
        try {
            $created = 0;
            $skipped = 0;
            $errors = 0;
            
            // Get all departments keyed by API ID for quick lookup
            $departments = Department::all()->keyBy('api_department_id')->toArray();
            
            if ($command && $isDetailed) {
                $command->info("Processing employees...");
                $bar = $command->getOutput()->createProgressBar($count);
                $bar->start();
            }
            
            foreach ($employees as $index => $employeeData) {
                try {
                    $result = $this->processEmployee($employeeData, $departments);
                    
                    switch ($result) {
                        case 'created':
                            $created++;
                            if ($command && $isDetailed) {
                                $name = $employeeData['name'] ?? 'Unknown';
                                $id = $employeeData['employee_id'] ?? 'No ID';
                                $bar->clear();
                                $command->line("Added: {$name} ({$id})");
                                $bar->display();
                            }
                            break;
                        case 'skipped':
                            $skipped++;
                            break;
                        default:
                            $errors++;
                            break;
                    }
                    
                    if ($command && $isDetailed) {
                        $bar->advance();
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Error processing employee: ' . $e->getMessage(), [
                        'employee' => $employeeData['id'] ?? 'unknown',
                        'exception' => $e->getMessage()
                    ]);
                    
                    if ($command && $isDetailed) {
                        $bar->clear();
                        $command->error("Error processing employee: " . ($employeeData['employee_id'] ?? 'unknown'));
                        $bar->display();
                        $bar->advance();
                    }
                }
            }
            
            if ($command && $isDetailed) {
                $bar->finish();
                $command->line('');
            }
            
            DB::commit();
            
            $result = [
                'success' => true,
                'message' => "Employee sync completed. Added {$created} new employees. Skipped {$skipped} existing employees.",
                'count' => count($employees),
                'stats' => [
                    'created' => $created,
                    'skipped' => $skipped,
                    'errors' => $errors
                ]
            ];
            
            Log::info('Employee sync completed', $result);
            
            return $result;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception during employee sync: ' . $e->getMessage(), [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error during employee sync: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }
    
    /**
     * Process a single employee
     * 
     * @param array $employeeData Employee data from API
     * @param array $departments Departments lookup array
     * @return string Status of the operation ('created', 'updated', 'skipped', 'error')
     */
    protected function processEmployee($employeeData, $departments)
    {
        // Generate an email based on employee_id if it's available
        $employeeId = $employeeData['employee_id'] ?? null;
        
        if (empty($employeeId)) {
            Log::warning('Skipping employee without employee_id', ['employee' => $employeeData['id'] ?? 'unknown']);
            return 'skipped';
        }
        
        // Generate email from employee_id
        $email = strtolower($employeeId) . '@example.com';
        
        // Get the payroll_id from the API response
        $payrollId = $employeeData['id'] ?? null;
        
        $name = $employeeData['name'] ?? '';
        $departmentId = $employeeData['department_id'] ?? null;
        $designation = $employeeData['designation'] ?? null;
        
        // Determine role from API response
        $role = 'staff'; // Default role
        
        // Handle role mapping if available in API response
        if (!empty($employeeData['role']) && !empty($employeeData['role']['name'])) {
            $apiRole = $employeeData['role']['name'];
            
            // Map "Employee" to "staff", convert others to lowercase
            if (strtolower($apiRole) === 'employee') {
                $role = 'staff';
            } else {
                $role = strtolower($apiRole);
            }
            
            Log::info("Role mapping for {$name}: API role '{$apiRole}' mapped to '{$role}'");
        }
        
        // Parse dates
        $dateOfJoining = null;
        $dateOfResignation = null;
        
        if (!empty($employeeData['date_of_joining'])) {
            try {
                $dateOfJoining = \Carbon\Carbon::createFromFormat('d-m-Y', $employeeData['date_of_joining'])->toDateString();
            } catch (\Exception $e) {
                Log::warning('Could not parse date_of_joining: ' . $employeeData['date_of_joining'], ['exception' => $e->getMessage()]);
            }
        }
        
        if (!empty($employeeData['date_of_resignation']) && $employeeData['date_of_resignation'] !== 'Active') {
            try {
                // Check if it's already in Y-m-d format
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $employeeData['date_of_resignation'])) {
                    $dateOfResignation = $employeeData['date_of_resignation'];
                } else {
                    $dateOfResignation = \Carbon\Carbon::createFromFormat('d-m-Y', $employeeData['date_of_resignation'])->toDateString();
                }
            } catch (\Exception $e) {
                Log::warning('Could not parse date_of_resignation: ' . $employeeData['date_of_resignation'], ['exception' => $e->getMessage()]);
            }
        }
        
        // Look up the local department ID based on API department ID
        $localDepartmentId = null;
        if ($departmentId && isset($departments[$departmentId])) {
            $localDepartmentId = $departments[$departmentId]['id'];
        }
        
        // Set up reporting manager
        $reportingManagerId = null; // Default to null (no manager)
        
        // Check if reporting_manager exists in the API response
        if (!empty($employeeData['reporting_manager'])) {
            // Check if the ID is valid (not null and not 0)
            if (isset($employeeData['reporting_manager']['id']) && 
                $employeeData['reporting_manager']['id'] !== null && 
                $employeeData['reporting_manager']['id'] !== 0) {
                
                // Store the reporting manager's payroll_id directly
                $reportingManagerId = $employeeData['reporting_manager']['id'];
                Log::info("Setting reporting_manager_id to {$reportingManagerId} from API", [
                    'employee_id' => $employeeId,
                    'manager_name' => $employeeData['reporting_manager']['name'] ?? 'Unknown'
                ]);
            } else {
                // Explicitly set to null if ID is null or 0
                $reportingManagerId = null;
                Log::info("Reporting manager ID is null or 0 in API response, setting to NULL", [
                    'employee_id' => $employeeId
                ]);
            }
        } else {
            Log::info("No reporting manager found in API response, setting to NULL", [
                'employee_id' => $employeeId
            ]);
        }
        
        // Look for existing employee with this employee_id
        $employee = Employee::where('employee_id', $employeeId)->first();
        
        if ($employee) {
            // Skip existing employees - we're only adding new employees
            return 'skipped';
        } else {
            Log::info('Creating new employee', [
                'employee_id' => $employeeId,
                'name' => $name,
                'department_id' => $localDepartmentId
            ]);
            
            // Create new employee
            Employee::create([
                'name' => $name,
                'email' => $email,
                'employee_id' => $employeeId,
                'payroll_id' => $payrollId, // Store the ID from the API
                'payroll_department_id' => $departmentId ?? null, // Store payroll department id from API
                'department_id' => $localDepartmentId,
                'designation' => $designation,
                'phone' => null, // Add phone if available in API
                'status' => 'Active',
                'date_of_joining' => $dateOfJoining,
                'date_of_resignation' => $dateOfResignation,
                'reporting_manager_payroll_id' => $reportingManagerId,
                'financial_year' => '2025-2026', // Set a default financial year
                'additional_data' => json_encode([
                    'api_data' => $employeeData
                ]),
            ]);
            
            return 'created';
        }
    }
}
