<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DepartmentSyncService
{
    protected $activityLogger;

    public function __construct()
    {
        $this->activityLogger = new ActivityLogger();
    }

    /**
     * Process a single department from webhook or API
     */
    public function processSingleDepartment(array $departmentData)
    {
        try {
            DB::beginTransaction();

            $departmentId = $departmentData['id'] ?? null;
            $name = $departmentData['name'] ?? null;
            $code = $departmentData['code'] ?? null;
            $description = $departmentData['description'] ?? 'Imported from Payroll API';
            $isActive = $departmentData['is_active'] ?? true;

            if (empty($name)) {
                throw new \Exception('Department name is required');
            }

            // Find existing department by API ID or name
            $department = null;
            if ($departmentId) {
                $department = Department::where('api_department_id', $departmentId)->first();
            }

            if (!$department) {
                $department = Department::where('name', $name)->first();
            }

            if ($department) {
                // Update existing department
                $department->update([
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5)),
                    'description' => $description,
                    'api_department_id' => $departmentId,
                    'is_active' => $isActive,
                ]);

                $action = 'updated';
                Log::info("Department updated via webhook: {$name} (ID: {$departmentId})");
            } else {
                // Create new department
                $department = Department::create([
                    'name' => $name,
                    'code' => $code ?: strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $name), 0, 5)),
                    'description' => $description,
                    'api_department_id' => $departmentId,
                    'is_active' => $isActive,
                ]);

                $action = 'created';
                Log::info("Department created via webhook: {$name} (ID: {$departmentId})");
            }

            DB::commit();

            // Log activity
            $this->activityLogger->log(
                "Real-time department {$action} via webhook - {$name}",
                'departments',
                null,
                [
                    'department_id' => $department->id,
                    'api_department_id' => $departmentId,
                    'action' => $action
                ]
            );

            return [
                'success' => true,
                'action' => $action,
                'department' => $department
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Department sync failed: " . $e->getMessage(), [
                'department_data' => $departmentData
            ]);

            throw $e;
        }
    }

    /**
     * Handle department deletion from payroll system
     */
    public function handleDepartmentDeletion($departmentId)
    {
        try {
            DB::beginTransaction();

            $department = Department::where('api_department_id', $departmentId)->first();

            if ($department) {
                $employeeCount = $department->employees()->active()->count();

                if ($employeeCount > 0) {
                    Log::warning("Cannot delete department {$department->name} - has {$employeeCount} active employees");
                    throw new \Exception("Cannot delete department with associated active employees");
                }

                $departmentName = $department->name;
                $department->delete();

                DB::commit();

                $this->activityLogger->log(
                    "Real-time department deletion via webhook - {$departmentName}",
                    'departments',
                    null,
                    ['api_department_id' => $departmentId]
                );

                Log::info("Department {$departmentId} deleted via webhook");

                return [
                    'success' => true,
                    'message' => "Department {$departmentName} deleted successfully"
                ];
            } else {
                Log::warning("Department with API ID {$departmentId} not found for deletion");
                return [
                    'success' => false,
                    'message' => 'Department not found'
                ];
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Department deletion failed: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get department sync status
     */
    public function getSyncStatus()
    {
        try {
            $totalDepartments = Department::count();
            $syncedDepartments = Department::whereNotNull('api_department_id')->count();
            $unsyncedDepartments = $totalDepartments - $syncedDepartments;

            return [
                'total_departments' => $totalDepartments,
                'synced_departments' => $syncedDepartments,
                'unsynced_departments' => $unsyncedDepartments,
                'sync_percentage' => $totalDepartments > 0 ? round(($syncedDepartments / $totalDepartments) * 100, 2) : 0,
                'last_sync_attempt' => null, // Could be enhanced with actual timestamps
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get department sync status: ' . $e->getMessage());
            return [
                'error' => 'Failed to retrieve sync status'
            ];
        }
    }
}