<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Services\ActivityLogger;
use App\Services\PayrollApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncDepartmentsCommand extends Command
{
    protected $signature = 'departments:sync {--detailed : Show detailed output} {--delete : Allow deletion of extra departments} {--force : Force update all departments}';

    protected $description = 'Comprehensive sync of departments from payroll API (safe mode by default)';

    public function handle(PayrollApiService $payrollService, ActivityLogger $activityLogger)
    {
        $this->info('Starting comprehensive department synchronization from payroll API...');
        $isVerbose = $this->option('verbose') || $this->option('detailed');
        $deleteExtra = $this->option('delete');
        $forceUpdate = $this->option('force');
        
        if ($deleteExtra) {
            $this->warn('Running in FULL MODE (deletions may be performed)');
        } else {
            $this->info('Running in SAFE MODE (no deletions will be performed)');
        }
        
        try {
            DB::beginTransaction();
            
            $payrollDepartments = $payrollService->getDepartments();
            
            if (empty($payrollDepartments)) {
                $this->warn('No departments retrieved from the API.');
                DB::commit();
                
                $activityLogger->log(
                    'departments', 
                    "Department sync completed. No departments retrieved from API.",
                    null,
                    ['count' => 0]
                );
                
                return 0;
            }
            
            $this->info('Found ' . count($payrollDepartments) . ' departments from the payroll API');
            
            $attendanceDepartments = Department::all()->keyBy('name');
            $attendanceDepartmentNames = $attendanceDepartments->keys()->toArray();
            
            $stats = [
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'deleted' => 0,
                'errors' => 0
            ];
            
            $payrollDepartmentNames = [];
            
            foreach ($payrollDepartments as $payrollDept) {
                try {
                    if (empty($payrollDept['name'])) {
                        if ($isVerbose) {
                            $this->warn('Skipping department with missing name');
                        }
                        $stats['errors']++;
                        continue;
                    }
                    
                    $name = $payrollDept['name'];
                    $payrollDepartmentNames[] = $name;
                    
                    $description = isset($payrollDept['description']) ? $payrollDept['description'] : 'Imported from Payroll API';
                    $departmentId = isset($payrollDept['id']) ? $payrollDept['id'] : null;
                    $code = isset($payrollDept['code']) ? $payrollDept['code'] : strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5));
                    
                    $existingDept = $attendanceDepartments->get($name);
                    
                    if ($existingDept) {
                        if ($forceUpdate) {
                            $existingDept->update([
                                'code' => $code,
                                'description' => $description,
                                'api_department_id' => $departmentId,
                                'is_active' => isset($payrollDept['is_active']) ? $payrollDept['is_active'] : true,
                            ]);
                            
                            if ($isVerbose) {
                                $this->info("Updated department: {$name}");
                            }
                            $stats['updated']++;
                        } else {
                            if ($isVerbose) {
                                $this->line("Skipped existing department: {$name}");
                            }
                            $stats['skipped']++;
                        }
                    } else {
                        Department::create([
                            'name' => $name,
                            'code' => $code,
                            'description' => $description,
                            'api_department_id' => $departmentId,
                            'is_active' => isset($payrollDept['is_active']) ? $payrollDept['is_active'] : true,
                        ]);
                        
                        if ($isVerbose) {
                            $this->info("Added new department: {$name} ({$code})");
                        }
                        $stats['created']++;
                    }
                    
                } catch (\Exception $e) {
                    $stats['errors']++;
                    $deptName = isset($payrollDept['name']) ? $payrollDept['name'] : 'unknown';
                    Log::error("Error processing department {$deptName}: " . $e->getMessage());
                }
            }
            
            if ($deleteExtra) {
                $extraDepartments = array_diff($attendanceDepartmentNames, $payrollDepartmentNames);
                
                if (!empty($extraDepartments)) {
                    $this->warn("Found " . count($extraDepartments) . " departments in attendance system not present in payroll");
                    
                    foreach ($extraDepartments as $extraDeptName) {
                        try {
                            $department = $attendanceDepartments[$extraDeptName];
                            
                            $employeeCount = $department->employees()->count();
                            if ($employeeCount > 0) {
                                $this->warn("Skipping deletion of department '{$extraDeptName}' - has {$employeeCount} associated employees");
                                continue;
                            }
                            
                            $this->warn("Deleting department {$extraDeptName} - not found in payroll system");
                            
                            $department->delete();
                            $stats['deleted']++;
                            
                            $activityLogger->log(
                                'departments',
                                "Deleted department {$extraDeptName} - removed from payroll system",
                                null,
                                ['department_name' => $extraDeptName]
                            );
                            
                        } catch (\Exception $e) {
                            $stats['errors']++;
                            Log::error("Error deleting extra department {$extraDeptName}: " . $e->getMessage());
                        }
                    }
                }
            } else {
                $extraDepartments = array_diff($attendanceDepartmentNames, $payrollDepartmentNames);
                if (!empty($extraDepartments)) {
                    $this->info("Found " . count($extraDepartments) . " departments in attendance system not present in payroll (not deleting)");
                    if ($isVerbose) {
                        foreach ($extraDepartments as $extraDept) {
                            $this->line("  - {$extraDept}");
                        }
                    }
                }
            }
            
            DB::commit();
            
            $activityLogger->log(
                'departments',
                'Completed comprehensive department synchronization',
                null,
                $stats
            );
            
            $this->info("Department sync completed successfully.");
            $this->line('');
            $this->line('Sync Statistics:');
            $this->line("- Created: {$stats['created']}");
            $this->line("- Updated: {$stats['updated']}");
            $this->line("- Skipped: {$stats['skipped']}");
            $this->line("- Deleted: {$stats['deleted']}");
            
            if ($stats['errors'] > 0) {
                $this->line("- Errors: {$stats['errors']}");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            $this->error('Error syncing departments: ' . $e->getMessage());
            Log::error('Error syncing departments: ' . $e->getMessage(), ['exception' => $e]);
            
            $activityLogger->log(
                'departments', 
                "Department sync failed: " . $e->getMessage(),
                null,
                ['error' => $e->getMessage()]
            );
            
            return 1;
        }
    }
}
