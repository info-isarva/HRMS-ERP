<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmployeeBasicDetail;
use App\Models\Department;
use App\Models\PositionType;
use App\Models\Role;
use App\Models\EmployeeStatus;
use App\Helpers\FinancialYearHelper;
use Carbon\Carbon;
use App\Services\LeaveTypeService;
use App\Services\LeaveProRatingCalculator;

class EmployeeController extends Controller
{
    private function getOrGenerateLeaveAllocations($employee, $currentFYName, $leaveTypeService, $proRatingCalculator)
    {
        if (!$currentFYName) {
            return collect([]);
        }

        if ($employee->leaveAllocations && $employee->leaveAllocations->isNotEmpty()) {
            return $employee->leaveAllocations;
        }

        // Auto-generate for the current financial year if missing
        if ($employee->department && $employee->date_of_joining) {
            try {
                $availableLeaveTypes = $leaveTypeService->getLeaveTypesForDepartment($employee->department);
                if (!empty($availableLeaveTypes)) {
                    $calculatedAllocations = $proRatingCalculator->calculateProRatedLeaves(
                        $availableLeaveTypes,
                        $employee->date_of_joining,
                        $currentFYName
                    );

                    $allocationsToSave = [];
                    foreach ($calculatedAllocations as $allocation) {
                        $allocationsToSave[] = [
                            'id' => $allocation['id'],
                            'leave_type_name' => $allocation['leave_type_name'] ?? '',
                            'leave_type_code' => $allocation['leave_type_code'] ?? '',
                            'allocated_days' => $allocation['allocated_days'],
                            'override_days' => null,
                            'is_pro_rated' => $allocation['is_pro_rated'] ?? false,
                            'pro_rated_factor' => $allocation['pro_rated_factor'] ?? null,
                            'department_assignment' => $allocation['assigned_departments'] ?? null,
                            'description' => $allocation['description'] ?? null,
                        ];
                    }

                    if (!empty($allocationsToSave)) {
                        $saved = \App\Models\EmployeeLeaveAllocation::bulkUpdateAllocations(
                            $employee->id,
                            $allocationsToSave,
                            $currentFYName,
                            \App\Models\User::first()->id ?? 1
                        );
                        
                        // Update employee sync timestamp
                        $employee->update([
                            'leave_allocations_synced_at' => now(),
                            'leave_sync_financial_year' => $currentFYName,
                        ]);

                        return collect($saved);
                    }
                }
            } catch (\Exception $e) {
                \Log::error("Auto-generation of leave allocations failed for employee {$employee->id}: " . $e->getMessage());
            }
        }

        return collect([]);
    }

    public function index()
    {
        // Get data from master tables
        $designations = PositionType::active()->pluck('position', 'id')->toArray();
        $roles = Role::where('status', 1)->pluck('role_name', 'id')->toArray();
        $departments = Department::active()->pluck('department', 'id')->toArray();
        $employeeStatuses = EmployeeStatus::active()->pluck('status_name', 'id')->toArray();

        // Get current financial year for leave allocations
        $currentFY = FinancialYearHelper::getCurrentFinancialYear();
        $currentFYName = $currentFY ? $currentFY->name : null;

        $leaveTypeService = new LeaveTypeService();
        $proRatingCalculator = new LeaveProRatingCalculator();

        $employees = EmployeeBasicDetail::with([
                'reportingManager:id,employee_id,name',
                'leaveAllocations' => function($query) use ($currentFYName) {
                    if ($currentFYName) {
                        $query->forFinancialYear($currentFYName)->active();
                    }
                },
                'weekOff'
            ])
            ->select('id', 'employee_id', 'name', 'email', 'contact_number', 'date_of_birth', 'gender', 
                     'designation', 'department', 'date_of_resignation', 'date_of_joining', 'status',
                     'ot_status', 'incentive_status', 'reporting_manager_id', 'role', 'exclude_from_payroll')
            ->get()
            ->map(function ($employee) use ($designations, $roles, $departments, $employeeStatuses, $currentFYName, $leaveTypeService, $proRatingCalculator) {
                $leaveAllocations = $employee->leaveAllocations;
                if ($currentFYName && (!$leaveAllocations || $leaveAllocations->isEmpty())) {
                    $leaveAllocations = $this->getOrGenerateLeaveAllocations($employee, $currentFYName, $leaveTypeService, $proRatingCalculator);
                }

                return [
                    'id' => $employee->id,
                    'payroll_id' => $employee->id,
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'email' => $employee->email ?: null, // Send null instead of 'No email provided'
                    'phone' => $employee->contact_number,
                    'designation' => $designations[$employee->designation] ?? 'Unknown',
                    'department_id' => $employee->department,
                    'department' => $departments[$employee->department] ?? 'Unknown Department',
                    'date_of_resignation' => $employee->date_of_resignation,
                    'date_of_joining' => $employee->date_of_joining ? \Carbon\Carbon::parse($employee->date_of_joining)->format('Y-m-d') : null,
                    'status' => $employeeStatuses[$employee->status] ?? 'Unknown', // Map status ID to status name
                    'status_id' => $employee->status, // Keep original status ID for reference
                    'ot_status' => ($employee->ot_status === 'yes') ? 1 : 0,
                    'exclude_from_payroll' => $employee->exclude_from_payroll,
                    'role' => [
                        'id' => $employee->role,
                        'name' => $roles[$employee->role] ?? 'Unknown'
                    ],
                    'reporting_manager_payroll_id' => $employee->reporting_manager_id,
                    'reporting_manager' => $employee->reportingManager ? [
                        'id' => $employee->reportingManager->id,
                        'employee_id' => $employee->reportingManager->employee_id,
                        'name' => $employee->reportingManager->name
                    ] : null,
                    'leave_allocations' => $leaveAllocations ? $leaveAllocations->map(function($allocation) {
                        return [
                            'id' => $allocation->id,
                            'leave_type_id' => $allocation->attendance_leave_type_id,
                            'leave_type_name' => $allocation->leave_type_name,
                            'leave_type_code' => $allocation->leave_type_code,
                            'allocated_days' => (float) $allocation->allocated_days,
                            'override_days' => $allocation->override_days ? (float) $allocation->override_days : null,
                            'effective_days' => (float) $allocation->effective_days,
                            'is_manual_override' => (bool) $allocation->is_manual_override,
                            'is_pro_rated' => (bool) $allocation->is_pro_rated,
                            'pro_rated_factor' => $allocation->pro_rated_factor ? (float) $allocation->pro_rated_factor : null,
                            'financial_year' => $allocation->financial_year,
                            'created_at' => $allocation->created_at ? $allocation->created_at->format('Y-m-d H:i:s') : null,
                            'updated_at' => $allocation->updated_at ? $allocation->updated_at->format('Y-m-d H:i:s') : null,
                        ];
                    })->toArray() : [],
                    'leave_summary' => [
                        'financial_year' => $currentFYName,
                        'total_allocated_days' => $leaveAllocations ? $leaveAllocations->sum('effective_days') : 0,
                        'total_leave_types' => $leaveAllocations ? $leaveAllocations->count() : 0,
                        'has_overrides' => $leaveAllocations ? $leaveAllocations->where('is_manual_override', true)->count() > 0 : false,
                    ],
                    'week_off_configuration' => $employee->weekOff ? $employee->weekOff->toApiArray() : [
                        'week_off_days' => [0], // Default Sunday
                        'week_off_pattern' => 'Sunday',
                        'working_days_per_week' => 6,
                        'day_names' => [['day_number' => 0, 'day_name' => 'Sunday']]
                    ],
                    'additional_data' => [
                        'date_of_birth' => $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('Y-m-d') : null,
                        'gender' => $employee->gender,
                        'incentive_status' => $employee->incentive_status,
                        'original_role_id' => $employee->role,
                        'original_department_id' => $employee->department,
                        'original_designation_id' => $employee->designation,
                        'original_status_id' => $employee->status
                    ]
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $employees,
        ]);
    }

    /**
     * Get all active departments from master table
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function departments()
    {
        $departments = Department::active()
            ->select('id', 'department', 'status')
            ->orderBy('department')
            ->get()
            ->map(function ($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->department,
                    'status' => $department->status ? 'Active' : 'Inactive'
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $departments,
        ]);
    }

    /**
     * Get all potential reporting managers (active employees)
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function reportingManagers()
    {
        $managers = EmployeeBasicDetail::where('status', 1) // Only active employees
            ->select('id', 'employee_id', 'name', 'designation', 'department')
            ->orderBy('name')
            ->get()
            ->map(function ($manager) {
                return [
                    'id' => $manager->id,
                    'employee_id' => $manager->employee_id,
                    'name' => $manager->name,
                    'display_name' => $manager->employee_id . ' - ' . $manager->name,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $managers,
        ]);
    }

    /**
     * Get a single employee with reporting hierarchy details
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Get current financial year for leave allocations
        $currentFY = FinancialYearHelper::getCurrentFinancialYear();
        $currentFYName = $currentFY ? $currentFY->name : null;

        $employee = EmployeeBasicDetail::with([
                'reportingManager:id,employee_id,name,reporting_manager_id',
                'reportingManager.reportingManager:id,employee_id,name,reporting_manager_id',
                'leaveAllocations' => function($query) use ($currentFYName) {
                    if ($currentFYName) {
                        $query->forFinancialYear($currentFYName)->active();
                    }
                },
                'weekOff'
            ])
            ->find($id);

        if (!$employee) {
            return response()->json([
                'status' => 'error',
                'message' => 'Employee not found'
            ], 404);
        }

        $leaveTypeService = new LeaveTypeService();
        $proRatingCalculator = new LeaveProRatingCalculator();
        $leaveAllocations = $employee->leaveAllocations;
        if ($currentFYName && (!$leaveAllocations || $leaveAllocations->isEmpty())) {
            $leaveAllocations = $this->getOrGenerateLeaveAllocations($employee, $currentFYName, $leaveTypeService, $proRatingCalculator);
        }

        // Get employees reporting to this employee
        $directReports = EmployeeBasicDetail::where('reporting_manager_id', $employee->id)
            ->select('id', 'employee_id', 'name')
            ->get();

        // Get designation, department, and role names
        $designation = PositionType::find($employee->designation);
        $department = Department::find($employee->department);
        $role = Role::find($employee->role);

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'contact_number' => $employee->contact_number,
                'designation' => [
                    'id' => $employee->designation,
                    'name' => $designation ? $designation->position : 'Unknown'
                ],
                'department' => [
                    'id' => $employee->department,
                    'name' => $department ? $department->department : 'Unknown'
                ],
                'role' => [
                    'id' => $employee->role,
                    'name' => $role ? $role->role_name : 'Unknown'
                ],
                'date_of_joining' => $employee->date_of_joining,
                'date_of_resignation' => $employee->date_of_resignation,
                'status' => $employee->status,
                'leave_allocations' => $leaveAllocations ? $leaveAllocations->map(function($allocation) {
                    return [
                        'id' => $allocation->id,
                        'leave_type_id' => $allocation->attendance_leave_type_id,
                        'leave_type_name' => $allocation->leave_type_name,
                        'leave_type_code' => $allocation->leave_type_code,
                        'allocated_days' => (float) $allocation->allocated_days,
                        'override_days' => $allocation->override_days ? (float) $allocation->override_days : null,
                        'effective_days' => (float) $allocation->effective_days,
                        'is_manual_override' => (bool) $allocation->is_manual_override,
                        'is_pro_rated' => (bool) $allocation->is_pro_rated,
                        'pro_rated_factor' => $allocation->pro_rated_factor ? (float) $allocation->pro_rated_factor : null,
                        'financial_year' => $allocation->financial_year,
                        'created_at' => $allocation->created_at ? $allocation->created_at->format('Y-m-d H:i:s') : null,
                        'updated_at' => $allocation->updated_at ? $allocation->updated_at->format('Y-m-d H:i:s') : null,
                    ];
                })->toArray() : [],
                'leave_summary' => [
                    'financial_year' => $currentFYName,
                    'total_allocated_days' => $leaveAllocations ? $leaveAllocations->sum('effective_days') : 0,
                    'total_leave_types' => $leaveAllocations ? $leaveAllocations->count() : 0,
                    'has_overrides' => $leaveAllocations ? $leaveAllocations->where('is_manual_override', true)->count() > 0 : false,
                    'pro_rated_allocations' => $leaveAllocations ? $leaveAllocations->where('is_pro_rated', true)->count() : 0,
                ],
                'week_off_configuration' => $employee->weekOff ? $employee->weekOff->toApiArray() : [
                    'week_off_days' => [0], // Default Sunday
                    'week_off_pattern' => 'Sunday',
                    'working_days_per_week' => 6,
                    'day_names' => [['day_number' => 0, 'day_name' => 'Sunday']]
                ],
                'reporting_manager' => $employee->reportingManager ? [
                    'id' => $employee->reportingManager->id,
                    'employee_id' => $employee->reportingManager->employee_id,
                    'name' => $employee->reportingManager->name,
                    'manager' => $employee->reportingManager->reportingManager ? [
                        'id' => $employee->reportingManager->reportingManager->id,
                        'employee_id' => $employee->reportingManager->reportingManager->employee_id,
                        'name' => $employee->reportingManager->reportingManager->name
                    ] : null
                ] : null,
                'direct_reports' => $directReports->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'employee_id' => $report->employee_id,
                        'name' => $report->name
                    ];
                })
            ]
        ]);
    }

    /**
     * Get all active roles from master table
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function roles()
    {
        $roles = Role::where('status', 1)
            ->select('id', 'role_name', 'short_name', 'description', 'status')
            ->orderBy('role_name')
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->role_name,
                    'short_name' => $role->short_name,
                    'description' => $role->description,
                    'status' => $role->status ? 'Active' : 'Inactive'
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $roles,
        ]);
    }

    /**
     * Get all system settings
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function settings()
    {
        $settings = \App\Models\Setting::select('key', 'display_name', 'value', 'type', 'description', 'group')
            ->orderBy('group')
            ->orderBy('display_order')
            ->get()
            ->map(function ($setting) {
                $value = $setting->value;
                
                // Convert the value based on type
                if ($setting->type === 'boolean') {
                    $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } elseif ($setting->type === 'number') {
                    $value = is_numeric($value) ? (float) $value : $value;
                } elseif ($setting->type === 'json') {
                    $value = json_decode($value, true);
                }
                
                $result = [
                    'key' => $setting->key,
                    'name' => $setting->display_name,
                    'value' => $value,
                    'type' => $setting->type,
                    'description' => $setting->description,
                    'group' => $setting->group
                ];
                
                // Add additional context for specific settings
                if ($setting->key === 'enable_self_portal') {
                    $result['affects_defaults'] = true;
                    $result['affects_entity'] = 'employee';
                    $result['affected_field'] = 'enable_self_portal';
                }
                
                return $result;
            });

        return response()->json([
            'status' => 'success',
            'data' => $settings,
        ]);
    }
}
