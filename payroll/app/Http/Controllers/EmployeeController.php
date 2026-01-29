<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\{
    EmployeeBasicDetail,
    EmployeePersonalDetail,
    EmployeeBankDetail,
    EmployeeStatutoryComponent,
    EmployeeSalaryComponent,
    StatutoryComponent,
    SalaryComponent,
    EmployeeDocument,
    EmployeeLeaveAllocation,
    CachedLeaveType,
    Department,
    PositionType,
    Role,
    EmployeeStatus,
    DocumentType,
    Location,
};
use App\Services\PDFGenerator;
use App\Services\ActivityLogService;
use App\Services\LeaveTypeService;
use App\Services\LeaveProRatingCalculator;
use App\Helper\NumberHelper;
use App\Helpers\FinancialYearHelper;
use PDF;
use Mpdf\Mpdf;


class EmployeeController extends Controller
{
    /**
     * Get departments from master table in helper format
     */
    private function getDepartmentsFromMaster()
    {
        $departments = Department::where('status', 1)
            ->get()
            ->pluck('department', 'id')
            ->toArray();
        
        // Convert keys to strings to match the format expected by the view
        $result = [];
        foreach ($departments as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get designations from master table in helper format
     */
    private function getDesignationsFromMaster()
    {
        $designations = PositionType::where('status', 1)
            ->get()
            ->pluck('position', 'id')
            ->toArray();
        
        // Convert keys to strings to match the format expected by the view
        $result = [];
        foreach ($designations as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get roles from master table in helper format
     */
    private function getRolesFromMaster()
    {
        $roles = Role::where('status', 1)
            ->get()
            ->pluck('role_name', 'id')
            ->toArray();
        
        // Convert keys to strings to match the format expected by the view
        $result = [];
        foreach ($roles as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get employee statuses from master table in helper format
     */
    private function getEmployeeStatusesFromMaster()
    {
        $statuses = EmployeeStatus::where('status', 1)
            ->get()
            ->pluck('status_name', 'id')
            ->toArray();
        
        // Convert keys to strings to match the format expected by the view
        $result = [];
        foreach ($statuses as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get document types from master table in helper format
     */
    private function getDocumentTypesFromMaster()
    {
        $documentTypes = DocumentType::where('status', 1)
            ->get()
            ->pluck('document_name', 'id')
            ->toArray();
        
        // Convert keys to strings to match the format expected by the view
        $result = [];
        foreach ($documentTypes as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get locations from master table in helper format
     */
    private function getLocationsFromMaster()
    {
        $locations = Location::where('status', 1)
            ->get()
            ->pluck('name', 'id')
            ->toArray();
        
        $result = [];
        foreach ($locations as $id => $name) {
            $result[(string)$id] = $name;
        }
        
        return $result;
    }

    /**
     * Get department name by ID (with fallback to helper function)
     */
    public function getDepartmentName($departmentId)
    {
        $department = Department::find($departmentId);
        return $department ? $department->name : 'Unknown Department';
    }

    /**
     * Get designation name by ID (with fallback to helper function)
     */
    public function getDesignationName($designationId)
    {
        $designation = PositionType::find($designationId);
        return $designation ? $designation->name : 'Unknown Designation';
    }

    // List all employees
    // public function listEmployees()
    // {
    //     $employees = EmployeeBasicDetail::all();
    //     return view('employees.list', compact('employees'));
    // }

    public function listEmployees(Request $request)
    {
        $query = EmployeeBasicDetail::with(['reportingManager', 'locationObj']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('employee_id', 'like', '%' . $search . '%')
                  ->orWhere('name', 'like', '%' . $search . '%')
                  ->orWhereHas('locationObj', function ($subQuery) use ($search) {
                      $subQuery->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('designation')) {
            $query->where('designation', $request->designation);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location')) {
            $query->where('location_id', $request->location);
        }

        $employees = $query->get();

        if ($request->ajax()) {
            return view('employees.partials.table_content', [
                'employees' => $employees,
                'departments' => $this->getDepartmentsFromMaster(),
                'designations' => $this->getDesignationsFromMaster(),
                'statuses' => $this->getEmployeeStatusesFromMaster(),
                'locations' => $this->getLocationsFromMaster(),
            ])->render();
        }

        return view('employees.list', [
            'employees' => $employees,
            'departments' => $this->getDepartmentsFromMaster(),
            'designations' => $this->getDesignationsFromMaster(),
            'statuses' => $this->getEmployeeStatusesFromMaster(),
            'locations' => $this->getLocationsFromMaster(),
            'filters' => $request->all()
        ]);
    }
    
    /**
     * Get list of all employees for reporting manager dropdown
     */
    private function getReportingManagers($excludeId = null)
    {
        $query = EmployeeBasicDetail::where('status', '!=', 3) // Exclude employees who have left
            ->orderBy('name')
            ->select('id', 'employee_id', 'name');
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId); // Exclude current employee (can't report to themselves)
        }
        
        return $query->get();
    }
    
    public function createEmployee()
    {
        // Get the master setting value for enable_self_portal
        $enableSelfPortalMasterSetting = \App\Models\Setting::getValue('enable_self_portal', false);
        
        // Get current financial year
        $currentFY = FinancialYearHelper::getCurrentFinancialYear();
        
        return view('employees.form', [
            'currentFinancialYear' => $currentFY ? $currentFY->name : null,
            'genders' => getGenders(),
            'maritalStatuses' => getMaritalStatuses(),
            'designations' => $this->getDesignationsFromMaster(),
            'departments' => $this->getDepartmentsFromMaster(),
            'statuses' => $this->getEmployeeStatusesFromMaster(),
            'roles' => $this->getRolesFromMaster(),
            'transaction_types' => getTransactionTypes(),
            'bloodGroups' => getBloodGroups(),
            'paymentTypes' => getPaymentTypes(),
            'statutoryComponents' => StatutoryComponent::all(),
            'salaryComponents' => SalaryComponent::all(),
            'reportingManagers' => $this->getReportingManagers(),
            'enableSelfPortalMasterSetting' => $enableSelfPortalMasterSetting,
            'reportingManagers' => $this->getReportingManagers(),
            'enableSelfPortalMasterSetting' => $enableSelfPortalMasterSetting,
            'documentTypes' => $this->getDocumentTypesFromMaster(),
            'locations' => $this->getLocationsFromMaster(),
        ]);

        
    }

    public function saveEmployee(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate basic details
            $basicData = $request->validate([
                'basic.employee_id' => 'required|unique:employee_basic_details,employee_id',
                'basic.name' => 'required',
                'basic.email' => 'nullable|email', // Changed to nullable
                'basic.contact_number' => 'required',
                'basic.annual_ctc' => 'nullable|numeric|min:0',
                'basic.monthly_ctc' => 'nullable|numeric|min:0',
                'basic.pf_calculation_type' => 'nullable|in:restrict_15k,actual_12,manual',
                'basic.pf_include_employer_share' => 'nullable|boolean',
                'basic.pf_manual_amount' => 'nullable|numeric|min:0',
                'basic.date_of_birth' => 'nullable|date', // Changed to nullable
                'basic.gender' => 'required',
                'basic.marital_status' => 'required',
                'basic.designation' => 'required',
                'basic.designation' => 'required',
                'basic.department' => 'required',
                'basic.location_id' => 'nullable|exists:locations,id',
                'basic.unique_id' => 'nullable|string|unique:employee_basic_details,unique_id',
                'basic.reporting_manager_id' => 'nullable|exists:employee_basic_details,id',
                'basic.date_of_joining' => 'required|date',
                'basic.date_of_resignation' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        // Get the status from request
                        $statusId = $request->input('basic.status');
                        if ($statusId) {
                            // Get the status name from database
                            $status = \App\Models\EmployeeStatus::find($statusId);
                            if ($status) {
                                $statusName = strtolower($status->status_name);
                                // Check if status indicates employee has left
                                if (strpos($statusName, 'left') !== false || 
                                    strpos($statusName, 'resign') !== false || 
                                    strpos($statusName, 'terminated') !== false ||
                                    strpos($statusName, 'exit') !== false) {
                                    // If status is "left" but no resignation date provided
                                    if (empty($value)) {
                                        $fail('Resignation date is required when employee status is set to ' . $status->status_name . '.');
                                    }
                                }
                            }
                        }
                    }
                ],
                'basic.status' => 'required',
                'basic.role' => 'required',
                'basic.profile_image' => 'nullable|image|max:2048',
                'basic.ot_status' => 'required|in:yes,no',
                'basic.ot_per_hour' => 'nullable|numeric|required_if:basic.ot_status,yes',
                'basic.incentive_status' => 'required|in:yes,no',
                'basic.incentive_per_month' => 'nullable|numeric|required_if:basic.incentive_status,yes',
                'basic.exclude_from_payroll' => 'nullable|boolean',
                'basic.enable_self_portal' => 'nullable|boolean',
            ]);

            // Prepare basic data
            $basicDataToSave = $basicData['basic'];
            
            // Handle checkbox values explicitly - when unchecked, they don't send any value
            // So we need to explicitly set them to false (0) if they're not present
            $basicDataToSave['exclude_from_payroll'] = $request->has('basic.exclude_from_payroll') ? 1 : 0;
            $basicDataToSave['enable_self_portal'] = $request->has('basic.enable_self_portal') ? 1 : 0;
            
            // Handle enable_payroll from permissions array
            $basicDataToSave['enable_payroll'] = $request->has('permissions.enable_payroll') ? 1 : 0;
            
            // Apply master setting default for enable_self_portal if it wasn't explicitly unchecked
            // and master setting is enabled
            if (!$request->has('basic.enable_self_portal')) {
                $enableSelfPortalMasterSetting = \App\Models\Setting::getValue('enable_self_portal', false);
                if ($enableSelfPortalMasterSetting) {
                    $basicDataToSave['enable_self_portal'] = 1;
                }
            }
            
            // Capitalize the name
            $basicDataToSave['name'] = strtoupper($basicDataToSave['name']);

            // Handle profile image upload if present
            if ($request->hasFile('basic.profile_image')) {
                $profileImage = $request->file('basic.profile_image');
                $directory = 'assets/employee_profile_image';
                if (!file_exists(public_path($directory))) {
                    mkdir(public_path($directory), 0755, true);
                }
                $fileName = $basicDataToSave['employee_id'] . '.profile_image.' . time() . '.' . $profileImage->getClientOriginalExtension();
                $profileImage->move(public_path($directory), $fileName);
                $basicDataToSave['profile_image'] = $directory . '/' . $fileName;
            }

            // Ensure OT/Incentive values are null when disabled
            if ($basicDataToSave['ot_status'] === 'no') {
                $basicDataToSave['ot_per_hour'] = null;
            }
            if ($basicDataToSave['incentive_status'] === 'no') {
                $basicDataToSave['incentive_per_month'] = null;
            }

            // Create basic details
            $basic = EmployeeBasicDetail::create(array_merge(
                $basicDataToSave,
                [
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]
            ));

            // Validation for personal details — all fields optional
            $personalData = $request->validate([
                'personal.address' => 'nullable|string',
                'personal.temporary_address' => 'nullable|string',
                'personal.father_name' => 'nullable|string',
                'personal.mother_name' => 'nullable|string',
                'personal.blood_group' => 'nullable|string',
                'personal.emergency_contact_name' => 'nullable|string',
                'personal.emergency_contact_number' => 'nullable|string',
                'personal.aadhaar_number' => 'nullable|string',
                'personal.pan_number' => 'nullable|string',
                'personal.pf_account_number' => 'nullable|string',
                'personal.esic_number' => 'nullable|string',
                'personal.uploaded_document' => 'nullable|array',
                'personal.uploaded_document.*.file' => 'nullable|file|max:5120',
                'personal.uploaded_document.*.type' => 'required_with:personal.uploaded_document.*.file|string',
            ], [
                'personal.uploaded_document.*.type.required_with' => 'Please select a document type when uploading a file.',
                'personal.uploaded_document.*.type.string' => 'Document type must be a valid selection.',
                'personal.uploaded_document.*.file.file' => 'Please upload a valid file.',
                'personal.uploaded_document.*.file.max' => 'File size must not exceed 5MB.',
            ]);

            // Capitalize father_name and mother_name if provided
            $personalDataToSave = $personalData['personal'];
            if (!empty($personalDataToSave['father_name'])) {
                $personalDataToSave['father_name'] = strtoupper($personalDataToSave['father_name']);
            }
            if (!empty($personalDataToSave['mother_name'])) {
                $personalDataToSave['mother_name'] = strtoupper($personalDataToSave['mother_name']);
            }
            if (!empty($personalDataToSave['emergency_contact_name'])) {
                $personalDataToSave['emergency_contact_name'] = strtoupper($personalDataToSave['emergency_contact_name']);
            }
            unset($personalDataToSave['uploaded_document']);

            $basic->personalDetail()->create(array_merge(
                $personalDataToSave,
                [
                    'emp_id' => $basic->id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]
            ));

            // No validation; accept and insert whatever is present — store nulls if fields are missing
            $bankData = $request->input('bank', []);
            $basic->bankDetail()->create(array_merge(
                $bankData,
                [
                    'emp_id' => $basic->id,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id()
                ]
            ));

            // Validate and handle statutory components
            $request->validate([
                'statutory_components.*.statutory_component_id' => 'required|exists:statutory_components,id',
                'statutory_components.*.enabled' => 'nullable|boolean',
                'statutory_components.*.value' => 'nullable|numeric|min:0',
                'statutory_components.*.epf_option' => 'nullable|in:12_percent,restrict_15000,manual_value',
                'statutory_components.*.full_amount_deduct_from_ctc' => 'nullable|boolean',
            ]);

            $statutoryComponents = $request->input('statutory_components', []);
            foreach ($statutoryComponents as $component) {
                if (
                    !empty($component['statutory_component_id']) &&
                    isset($component['enabled']) &&
                    $component['enabled'] == 1
                ) {
                    $basic->statutoryComponents()->create([
                        'emp_id' => $basic->id,
                        'statutory_component_id' => $component['statutory_component_id'],
                        'value' => isset($component['value']) && is_numeric($component['value']) ? $component['value'] : 0,
                        'epf_option' => $component['epf_option'] ?? null,
                        'full_amount_deduct_from_ctc' => isset($component['full_amount_deduct_from_ctc']) && $component['full_amount_deduct_from_ctc'] == 1,
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);
                }
            }

            // Handle salary components
            $salaryComponents = $request->input('salary_components', []);
            foreach ($salaryComponents as $component) {
                if (
                    !empty($component['salary_component_id']) &&
                    isset($component['value']) &&
                    is_numeric($component['value'])
                ) {
                    $basic->salaryComponents()->create([
                        'emp_id' => $basic->id,
                        'salary_component_id' => $component['salary_component_id'],
                        'value' => $component['value'],
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);
                }
            }

            // Handle document uploads
            if ($request->has('personal.uploaded_document')) {
                $uploadedDocuments = $request->input('personal.uploaded_document', []);
                foreach ($uploadedDocuments as $index => $document) {
                    $fileKey = "personal.uploaded_document.{$index}.file";
                    if ($request->hasFile($fileKey) && !empty($document['type'])) {
                        $file = $request->file($fileKey);
                        $type = $document['type'];
                        $directory = 'assets/employee_document';
                        if (!file_exists(public_path($directory))) {
                            mkdir(public_path($directory), 0755, true);
                        }
                        $fileName = $basic->employee_id . '_' . $type . '_' . time() . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path($directory), $fileName);
                        EmployeeDocument::create([
                            'emp_id' => $basic->id,
                            'document_id' => $type,
                            'uploaded_document' => $directory . '/' . $fileName,
                            'name' => strtoupper($file->getClientOriginalName()), // Capitalize document name
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id()
                        ]);
                    }
                }
            }
            
            // Create user account for employee if they have email and self portal is enabled
            if (!empty($basic->email) && $basic->enable_self_portal) {
                $user = \App\Models\User::createFromEmployee($basic);
                
                // Handle permission assignment
                if ($request->has('permissions') && is_array($request->permissions)) {
                    $permissions = $request->permissions;
                    foreach ($permissions as $permissionId) {
                        $user->givePermission($permissionId);
                    }
                } else {
                    // Apply default permissions for employees with self portal enabled
                    $this->applyDefaultPermissions($user);
                }
                
                // Store default password temporarily for display to admin
                $cleanEmployeeId = preg_replace('/[^a-zA-Z0-9]/', '', $basic->employee_id);
                $nameFirstFourChars = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $basic->name), 0, 4));
                $defaultPassword = $cleanEmployeeId . $nameFirstFourChars;
                session()->flash('new_user_password', $defaultPassword);
                session()->flash('new_user_id', $basic->employee_id);
                
                // Sync newly created user with attendance system
                $this->syncNewUserWithAttendance($user, $defaultPassword);
            }

            DB::commit();
            
            // Log employee creation activity with comprehensive data
            // Reload the employee with all relations for complete logging
            try {
                $basic->load(['personalDetail', 'bankDetail', 'statutoryComponents.statutoryComponent', 'salaryComponents.salaryComponent', 'departmentObj', 'designationObj']);
                
                $createdEmployeeData = [
                    'basic' => [
                        'employee_id' => $basic->employee_id,
                        'name' => $basic->name,
                        'email' => $basic->email,
                        'contact_number' => $basic->contact_number,
                        'date_of_birth' => $basic->date_of_birth,
                        'gender' => $basic->gender,
                        'marital_status' => $basic->marital_status,
                        'department' => $basic->departmentObj->department ?? null,
                        'designation' => $basic->designationObj->position ?? null,
                        'date_of_joining' => $basic->date_of_joining,
                        'enable_self_portal' => $basic->enable_self_portal,
                        'exclude_from_payroll' => $basic->exclude_from_payroll,
                        'status' => $basic->status,
                    ],
                    'personal' => $basic->personalDetail ? [
                        'blood_group' => $basic->personalDetail->blood_group,
                        'father_name' => $basic->personalDetail->father_name,
                        'mother_name' => $basic->personalDetail->mother_name,
                        'address' => $basic->personalDetail->address,
                        'emergency_contact_name' => $basic->personalDetail->emergency_contact_name,
                        'emergency_contact_number' => $basic->personalDetail->emergency_contact_number,
                    ] : null,
                    'statutory_components' => $basic->statutoryComponents->map(function($comp) {
                        return [
                            'name' => $comp->statutoryComponent ? $comp->statutoryComponent->name : 'Unknown Component',
                            'value' => $comp->value,
                        ];
                    })->toArray(),
                    'salary_components' => $basic->salaryComponents->map(function($comp) {
                        return [
                            'name' => $comp->salaryComponent ? $comp->salaryComponent->name : 'Unknown Component',
                            'value' => $comp->value,
                        ];
                    })->toArray(),
                ];
                
                ActivityLogService::logEmployeeCreated($createdEmployeeData);
            } catch (\Exception $e) {
                // Log the error but don't fail the employee creation
                \Log::error('Failed to log employee creation activity: ' . $e->getMessage());
            }

            // Handle leave allocations after employee creation
            $this->handleLeaveAllocations($basic, $request);
            
            // Handle week offs after employee creation
            $this->handleWeekOffs($basic, $request);
            
            if (session()->has('new_user_password')) {
                return redirect()->route('employees.index')->with('success', 'Employee created successfully. User account created with ID: ' . 
                    session('new_user_id') . ' and temporary password: ' . session('new_user_password') . 
                    ' (Employee ID without special characters + first 4 uppercase letters of name). Please ask the employee to change their password on first login.');
            }
            
            return redirect()->route('employees.index')->with('success', 'Employee created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function editEmployee(EmployeeBasicDetail $employee)
    {
        // Get the master setting value for enable_self_portal
        $enableSelfPortalMasterSetting = \App\Models\Setting::getValue('enable_self_portal', false);
        
        // Find the associated user by employee_id
        $user = \App\Models\User::where('employee_id', $employee->id)->first();
        $userPermissions = [];
        if ($user) {
            // Get user's permissions from JSON
            $userPermissions = $user->getPermissionIds();
        }
        
        // Get existing leave allocations for the current financial year
        $existingLeaveAllocations = [];
        $currentFY = FinancialYearHelper::getCurrentFinancialYear();
        
        if ($currentFY) {
            // Service instances
            $leaveTypeService = new LeaveTypeService();
            $proRatingCalculator = new LeaveProRatingCalculator();
            
            // 1. Fetch saved allocations
            $savedAllocations = EmployeeLeaveAllocation::forEmployee($employee->id)
                ->forFinancialYear($currentFY->name)
                ->active()
                ->get()
                ->keyBy('attendance_leave_type_id');

            // 2. Fetch available leave types and calculate defaults
            $calculatedMap = [];
            if ($employee->department) {
                try {
                    $availableLeaveTypes = $leaveTypeService->getLeaveTypesForDepartment($employee->department);
                    
                    if (!empty($availableLeaveTypes) && $employee->date_of_joining) {
                        $calculatedAllocations = $proRatingCalculator->calculateProRatedLeaves(
                            $availableLeaveTypes,
                            $employee->date_of_joining,
                            $currentFY->name
                        );
                        
                        foreach ($calculatedAllocations as $alloc) {
                            $calculatedMap[$alloc['id']] = $alloc;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::error("Failed to fetch/calculate leave types in editEmployee: " . $e->getMessage());
                }
            }
            
            // 3. Merge Saved with Calculated
            $allIds = array_unique(array_merge(array_keys($calculatedMap), $savedAllocations->keys()->toArray()));
            
            foreach ($allIds as $id) {
                if ($savedAllocations->has($id)) {
                    $allocation = $savedAllocations[$id];
                    $existingLeaveAllocations[] = [
                        'id' => $allocation->attendance_leave_type_id,
                        'leave_type_name' => $allocation->leave_type_name,
                        'leave_type_code' => $allocation->leave_type_code,
                        'allocated_days' => $allocation->allocated_days,
                        'original_days' => $allocation->original_days ?? ($calculatedMap[$id]['original_days'] ?? $allocation->allocated_days),
                        'override_days' => $allocation->override_days,
                        'is_manual_override' => $allocation->is_manual_override,
                        'effective_days' => $allocation->effective_days,
                        'is_pro_rated' => $allocation->is_pro_rated,
                        'pro_rated_factor' => $allocation->pro_rated_factor,
                        'description' => $allocation->description ?? ($calculatedMap[$id]['description'] ?? ''),
                    ];
                } elseif (isset($calculatedMap[$id])) {
                    $calc = $calculatedMap[$id];
                    $existingLeaveAllocations[] = [
                        'id' => $calc['id'],
                        'leave_type_name' => $calc['leave_type_name'],
                        'leave_type_code' => $calc['leave_type_code'],
                        'allocated_days' => $calc['allocated_days'],
                        'original_days' => $calc['original_days'],
                        'override_days' => $calc['override_days'],
                        'is_manual_override' => $calc['is_manual_override'],
                        'effective_days' => $calc['effective_days'],
                        'is_pro_rated' => $calc['is_pro_rated'],
                        'pro_rated_factor' => $calc['pro_rated_factor'],
                        'description' => $calc['description'] ?? '',
                    ];
                }
            }
        }
        
        return view('employees.form', [
            'employee' => $employee->load(['personalDetail', 'bankDetail', 'statutoryComponents', 'salaryComponents', 'employeeDocument', 'weekOff']),
            'user' => $user,
            'userPermissions' => $userPermissions,
            'existingLeaveAllocations' => $existingLeaveAllocations,
            'currentFinancialYear' => $currentFY ? $currentFY->name : null,
            'genders' => getGenders(),
            'maritalStatuses' => getMaritalStatuses(),
            'designations' => $this->getDesignationsFromMaster(),
            'departments' => $this->getDepartmentsFromMaster(),
            'statuses' => $this->getEmployeeStatusesFromMaster(),
            'roles' => $this->getRolesFromMaster(),
            'transaction_types' => getTransactionTypes(),
            'bloodGroups' => getBloodGroups(),
            'paymentTypes' => getPaymentTypes(),
            'statutoryComponents' => StatutoryComponent::all(),
            'salaryComponents' => SalaryComponent::all(),
            'reportingManagers' => $this->getReportingManagers($employee->id), // Exclude current employee from the list
            'enableSelfPortalMasterSetting' => $enableSelfPortalMasterSetting,
            'documentTypes' => $this->getDocumentTypesFromMaster(),
            'locations' => $this->getLocationsFromMaster(),
        ]);
    }

    
    public function updateEmployee(Request $request, EmployeeBasicDetail $employee)
    {
        DB::beginTransaction();

        try {
            // Capture original data before update for comprehensive activity logging
            $originalData = [
                'basic' => [
                    'employee_id' => $employee->employee_id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'contact_number' => $employee->contact_number,
                    'date_of_birth' => $employee->date_of_birth,
                    'gender' => $employee->gender,
                    'marital_status' => $employee->marital_status,
                    'department' => $employee->departmentObj->department ?? null,
                    'designation' => $employee->designationObj->position ?? null,
                    'reporting_manager' => $employee->reportingManager->name ?? null,
                    'date_of_joining' => $employee->date_of_joining,
                    'date_of_resignation' => $employee->date_of_resignation,
                    'enable_self_portal' => $employee->enable_self_portal,
                    'enable_payroll' => $employee->enable_payroll,
                    'exclude_from_payroll' => $employee->exclude_from_payroll,
                    'status' => $employee->status,
                    'ot_status' => $employee->ot_status,
                    'ot_per_hour' => $employee->ot_per_hour,
                    'incentive_status' => $employee->incentive_status,
                    'incentive_per_month' => $employee->incentive_per_month,
                ],
                'personal' => $employee->personalDetail ? [
                    'address' => $employee->personalDetail->address,
                    'temporary_address' => $employee->personalDetail->temporary_address,
                    'father_name' => $employee->personalDetail->father_name,
                    'mother_name' => $employee->personalDetail->mother_name,
                    'blood_group' => $employee->personalDetail->blood_group,
                    'emergency_contact_name' => $employee->personalDetail->emergency_contact_name,
                    'emergency_contact_number' => $employee->personalDetail->emergency_contact_number,
                    'aadhaar_number' => $employee->personalDetail->aadhaar_number,
                    'pan_number' => $employee->personalDetail->pan_number,
                    'pf_account_number' => $employee->personalDetail->pf_account_number,
                    'esic_number' => $employee->personalDetail->esic_number,
                ] : null,
                'bank' => $employee->bankDetail ? [
                    'bank_name' => $employee->bankDetail->bank_name,
                    'account_number' => $employee->bankDetail->account_number,
                    'ifsc_code' => $employee->bankDetail->ifsc_code,
                    'account_holder_name' => $employee->bankDetail->account_holder_name,
                ] : null,
                'statutory_components' => $employee->statutoryComponents->map(function($comp) {
                    return [
                        'name' => $comp->statutoryComponent ? $comp->statutoryComponent->name : 'Unknown Component',
                        'value' => $comp->value,
                        'epf_option' => $comp->epf_option,
                    ];
                })->toArray(),
                'salary_components' => $employee->salaryComponents->map(function($comp) {
                    return [
                        'name' => $comp->salaryComponent ? $comp->salaryComponent->name : 'Unknown Component',
                        'value' => $comp->value,
                    ];
                })->toArray(),
            ];
            
            // Validate basic details
            $basicData = $request->validate([
                'basic.employee_id' => 'required|unique:employee_basic_details,employee_id,' . $employee->id,
                'basic.name' => 'required',
                'basic.email' => 'nullable|email', // Changed to nullable
                'basic.contact_number' => 'required',
                'basic.annual_ctc' => 'nullable|numeric|min:0',
                'basic.monthly_ctc' => 'nullable|numeric|min:0',

                'basic.date_of_birth' => 'nullable|date', // Changed to nullable
                'basic.gender' => 'required',
                'basic.marital_status' => 'required',
                'basic.designation' => 'required',
                'basic.designation' => 'required',
                'basic.department' => 'required',
                'basic.location_id' => 'nullable|exists:locations,id',
                'basic.unique_id' => 'nullable|string|unique:employee_basic_details,unique_id,' . $employee->id,
                'basic.reporting_manager_id' => 'nullable|exists:employee_basic_details,id',
                'basic.date_of_joining' => 'required|date',
                'basic.date_of_resignation' => [
                    'nullable',
                    'date',
                    function ($attribute, $value, $fail) use ($request) {
                        // Get the status from request
                        $statusId = $request->input('basic.status');
                        if ($statusId) {
                            // Get the status name from database
                            $status = \App\Models\EmployeeStatus::find($statusId);
                            if ($status) {
                                $statusName = strtolower($status->status_name);
                                // Check if status indicates employee has left
                                if (strpos($statusName, 'left') !== false || 
                                    strpos($statusName, 'resign') !== false || 
                                    strpos($statusName, 'terminated') !== false ||
                                    strpos($statusName, 'exit') !== false) {
                                    // If status is "left" but no resignation date provided
                                    if (empty($value)) {
                                        $fail('Resignation date is required when employee status is set to ' . $status->status_name . '.');
                                    }
                                }
                            }
                        }
                    }
                ],
                'basic.status' => 'required',
                'basic.role' => 'required',
                'basic.profile_image' => 'nullable|image|max:2048',
                'basic.ot_status' => 'required|in:yes,no',
                'basic.ot_per_hour' => 'nullable|numeric|required_if:basic.ot_status,yes',
                'basic.incentive_status' => 'required|in:yes,no',
                'basic.incentive_per_month' => 'nullable|numeric|required_if:basic.incentive_status,yes',
                'basic.exclude_from_payroll' => 'nullable|boolean',
                'basic.enable_self_portal' => 'nullable|boolean',
            ]);

            $basicDataToUpdate = $basicData['basic'];
            
            // Handle checkbox values explicitly - when unchecked, they don't send any value
            // So we need to explicitly set them to false (0) if they're not present
            $basicDataToUpdate['exclude_from_payroll'] = $request->has('basic.exclude_from_payroll') ? 1 : 0;
            $basicDataToUpdate['enable_self_portal'] = $request->has('basic.enable_self_portal') ? 1 : 0;
            
            // Handle enable_payroll from permissions array
            $basicDataToUpdate['enable_payroll'] = $request->has('permissions.enable_payroll') ? 1 : 0;
            
            // Apply master setting default for enable_self_portal if it wasn't explicitly unchecked
            // and master setting is enabled
            if (!$request->has('basic.enable_self_portal')) {
                $enableSelfPortalMasterSetting = \App\Models\Setting::getValue('enable_self_portal', false);
                if ($enableSelfPortalMasterSetting) {
                    $basicDataToUpdate['enable_self_portal'] = 1;
                }
            }
            
            // Capitalize the name
            $basicDataToUpdate['name'] = strtoupper($basicDataToUpdate['name']);

            // Profile image upload
            if ($request->hasFile('basic.profile_image')) {
                $profileImage = $request->file('basic.profile_image');
                $directory = 'assets/employee_profile_image';
                if (!file_exists(public_path($directory))) {
                    mkdir(public_path($directory), 0755, true);
                }
                $fileName = $employee->employee_id . '.profile_image.' . time() . '.' . $profileImage->getClientOriginalExtension();
                $profileImage->move(public_path($directory), $fileName);
                $basicDataToUpdate['profile_image'] = $directory . '/' . $fileName;
            }

            // Ensure OT/Incentive values are null when disabled
            if ($basicDataToUpdate['ot_status'] === 'no') {
                $basicDataToUpdate['ot_per_hour'] = null;
            }
            if ($basicDataToUpdate['incentive_status'] === 'no') {
                $basicDataToUpdate['incentive_per_month'] = null;
            }

            $employee->update(array_merge($basicDataToUpdate, [
                'updated_by' => auth()->id()
            ]));

            // Personal details update
            $personalData = $request->validate([
                'personal.address' => 'nullable|string',
                'personal.temporary_address' => 'nullable|string',
                'personal.father_name' => 'nullable|string',
                'personal.mother_name' => 'nullable|string',
                'personal.blood_group' => 'nullable|string',
                'personal.emergency_contact_name' => 'nullable|string',
                'personal.emergency_contact_number' => 'nullable|string',
                'personal.aadhaar_number' => 'nullable|string',
                'personal.pan_number' => 'nullable|string',
                'personal.pf_account_number' => 'nullable|string',
                'personal.esic_number' => 'nullable|string',
                'personal.uploaded_document' => 'nullable|array',
                'personal.uploaded_document.*.file' => 'nullable|file|max:5120',
                'personal.uploaded_document.*.type' => 'required_with:personal.uploaded_document.*.file|string',
            ], [
                'personal.uploaded_document.*.type.required_with' => 'Please select a document type when uploading a file.',
                'personal.uploaded_document.*.type.string' => 'Document type must be a valid selection.',
                'personal.uploaded_document.*.file.file' => 'Please upload a valid file.',
                'personal.uploaded_document.*.file.max' => 'File size must not exceed 5MB.',
            ]);

            // Capitalize father_name, mother_name, and emergency_contact_name if provided
            $personalDataToUpdate = $personalData['personal'];
            if (!empty($personalDataToUpdate['father_name'])) {
                $personalDataToUpdate['father_name'] = strtoupper($personalDataToUpdate['father_name']);
            }
            if (!empty($personalDataToUpdate['mother_name'])) {
                $personalDataToUpdate['mother_name'] = strtoupper($personalDataToUpdate['mother_name']);
            }
            if (!empty($personalDataToUpdate['emergency_contact_name'])) {
                $personalDataToUpdate['emergency_contact_name'] = strtoupper($personalDataToUpdate['emergency_contact_name']);
            }
            unset($personalDataToUpdate['uploaded_document']);

            $employee->personalDetail()->updateOrCreate(
                ['emp_id' => $employee->id],
                array_merge($personalDataToUpdate, [
                    'updated_by' => auth()->id()
                ])
            );

            // Bank details update
            $bankData = $request->input('bank', []);
            $employee->bankDetail()->updateOrCreate(
                ['emp_id' => $employee->id],
                array_merge($bankData, [
                    'updated_by' => auth()->id()
                ])
            );

            // Validate and handle statutory components
            $request->validate([
                'statutory_components.*.statutory_component_id' => 'required|exists:statutory_components,id',
                'statutory_components.*.enabled' => 'nullable|boolean',
                'statutory_components.*.value' => 'nullable|numeric|min:0',
                'statutory_components.*.epf_option' => 'nullable|in:12_percent,restrict_15000,manual_value',
                'statutory_components.*.full_amount_deduct_from_ctc' => 'nullable|boolean',
            ]);

            $existingStatutoryIds = $employee->statutoryComponents()->pluck('statutory_component_id')->toArray();
            $newStatutoryComponents = $request->input('statutory_components', []);
            $newIds = [];

            foreach ($newStatutoryComponents as $component) {
                if (
                    !empty($component['statutory_component_id']) &&
                    isset($component['enabled']) &&
                    $component['enabled'] == 1
                ) {
                    $employee->statutoryComponents()->updateOrCreate(
                        [
                            'emp_id' => $employee->id,
                            'statutory_component_id' => $component['statutory_component_id']
                        ],
                        [
                            'value' => isset($component['value']) && is_numeric($component['value']) ? $component['value'] : 0,
                            'epf_option' => $component['epf_option'] ?? null,
                            'full_amount_deduct_from_ctc' => isset($component['full_amount_deduct_from_ctc']) ? (bool)$component['full_amount_deduct_from_ctc'] : false,
                            'updated_by' => auth()->id()
                        ]
                    );
                    $newIds[] = $component['statutory_component_id'];
                }
            }

            // Soft-delete removed statutory components
            $toDelete = array_diff($existingStatutoryIds, $newIds);
            if (!empty($toDelete)) {
                $employee->statutoryComponents()
                    ->whereIn('statutory_component_id', $toDelete)
                    ->delete();
            }

            // Handle salary components
            $existingSalaryIds = $employee->salaryComponents()->pluck('salary_component_id')->toArray();
            $newSalaryComponents = $request->input('salary_components', []);
            $newIds = [];

            foreach ($newSalaryComponents as $component) {
                if (
                    !empty($component['salary_component_id']) &&
                    isset($component['value']) &&
                    is_numeric($component['value'])
                ) {
                    $employee->salaryComponents()->updateOrCreate(
                        [
                            'emp_id' => $employee->id,
                            'salary_component_id' => $component['salary_component_id']
                        ],
                        [
                            'value' => $component['value'],
                            'updated_by' => auth()->id()
                        ]
                    );
                    $newIds[] = $component['salary_component_id'];
                }
            }

            // Soft-delete removed salary components
            $toDelete = array_diff($existingSalaryIds, $newIds);
            if (!empty($toDelete)) {
                $employee->salaryComponents()
                    ->whereIn('salary_component_id', $toDelete)
                    ->delete();
            }

            // Handle document uploads
            $uploadedDocuments = $request->input('personal.uploaded_document', []);
            foreach ($uploadedDocuments as $index => $document) {
                $fileKey = "personal.uploaded_document.{$index}.file";
                if ($request->hasFile($fileKey) && !empty($document['type'])) {
                    $file = $request->file($fileKey);
                    $type = $document['type'];
                    $directory = 'assets/employee_document';
                    if (!file_exists(public_path($directory))) {
                        mkdir(public_path($directory), 0755, true);
                    }
                    $fileName = $employee->employee_id . '_' . $type . '_' . time() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path($directory), $fileName);
                    EmployeeDocument::create([
                        'emp_id' => $employee->id,
                        'document_id' => $type,
                        'uploaded_document' => $directory . '/' . $fileName,
                        'name' => strtoupper($file->getClientOriginalName()), // Capitalize document name
                        'created_by' => auth()->id(),
                        'updated_by' => auth()->id()
                    ]);
                }
            }
            
            // Update or create user account based on employee's self portal status
            $user = \App\Models\User::where('employee_id', $employee->id)->first();
            
            if ($employee->enable_self_portal) {
                if ($user) {
                    // Determine user status based on employee status
                    $userStatus = 'Active'; // Default to Active
                    
                    // Check employee status and use actual status name
                    if ($employee->status) {
                        $employeeStatus = \App\Models\EmployeeStatus::find($employee->status);
                        if ($employeeStatus) {
                            $userStatus = $employeeStatus->status_name;
                        }
                    }
                    
                    // Update existing user
                    $updateData = [
                        'name' => $employee->name,
                        'status' => $userStatus,
                        'avatar' => $employee->profile_image ?? $user->avatar,
                    ];
                    
                    // Only update email if it's not empty (database constraint prevents null)
                    if (!empty($employee->email)) {
                        $updateData['email'] = $employee->email;
                    }
                    
                    $user->update($updateData);
                    
                    // Handle permission updates
                    $this->updateUserPermissions($user, $request);
                    
                    $message = 'Employee updated successfully.';
                } else {
                    // Create new user for existing employee only if they have an email
                    if (!empty($employee->email)) {
                        $user = \App\Models\User::createFromEmployee($employee);
                        
                        // Handle permission assignment for new user
                        if ($request->has('permissions') && is_array($request->permissions)) {
                            $permissions = $request->permissions;
                            foreach ($permissions as $permissionId) {
                                $user->givePermission($permissionId);
                            }
                        } else {
                            // Apply default permissions for employees with self portal enabled
                            $this->applyDefaultPermissions($user);
                        }
                        
                        // Store default password temporarily for display to admin
                        $cleanEmployeeId = preg_replace('/[^a-zA-Z0-9]/', '', $employee->employee_id);
                        $nameFirstFourChars = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $employee->name), 0, 4));
                        $defaultPassword = $cleanEmployeeId . $nameFirstFourChars;
                        
                        // Sync newly created user with attendance system
                        $this->syncNewUserWithAttendance($user, $defaultPassword);
                        
                        $message = 'Employee updated successfully. User account created with ID: ' . 
                                   $employee->employee_id . ' and temporary password: ' . $defaultPassword . 
                                   ' (Employee ID without special characters + first 4 uppercase letters of name). Please ask the employee to change their password on first login.';
                    } else {
                        $message = 'Employee updated successfully. Note: User account not created because no email address is provided.';
                    }
                }
            } elseif ($user) {
                // Disable the user account if self portal is disabled
                $user->update(['status' => 'Inactive']);
                $message = 'Employee updated successfully. User account has been deactivated.';
            } else {
                $message = 'Employee updated successfully.';
            }

            // Log employee update activity with comprehensive data
            // Reload employee with fresh data for comparison
            try {
                $employee->refresh();
                $employee->load(['personalDetail', 'bankDetail', 'statutoryComponents.statutoryComponent', 'salaryComponents.salaryComponent', 'departmentObj', 'designationObj', 'reportingManager']);
                
                $newData = [
                    'basic' => [
                        'employee_id' => $employee->employee_id,
                        'name' => $employee->name,
                        'email' => $employee->email,
                        'contact_number' => $employee->contact_number,
                        'date_of_birth' => $employee->date_of_birth,
                        'gender' => $employee->gender,
                        'marital_status' => $employee->marital_status,
                        'department' => $employee->departmentObj->department ?? null,
                        'designation' => $employee->designationObj->position ?? null,
                        'reporting_manager' => $employee->reportingManager->name ?? null,
                        'date_of_joining' => $employee->date_of_joining,
                        'date_of_resignation' => $employee->date_of_resignation,
                        'enable_self_portal' => $employee->enable_self_portal,
                        'exclude_from_payroll' => $employee->exclude_from_payroll,
                        'status' => $employee->status,
                        'ot_status' => $employee->ot_status,
                        'ot_per_hour' => $employee->ot_per_hour,
                        'incentive_status' => $employee->incentive_status,
                        'incentive_per_month' => $employee->incentive_per_month,
                    ],
                    'personal' => $employee->personalDetail ? [
                        'address' => $employee->personalDetail->address,
                        'temporary_address' => $employee->personalDetail->temporary_address,
                        'father_name' => $employee->personalDetail->father_name,
                        'mother_name' => $employee->personalDetail->mother_name,
                        'blood_group' => $employee->personalDetail->blood_group,
                        'emergency_contact_name' => $employee->personalDetail->emergency_contact_name,
                        'emergency_contact_number' => $employee->personalDetail->emergency_contact_number,
                        'aadhaar_number' => $employee->personalDetail->aadhaar_number,
                        'pan_number' => $employee->personalDetail->pan_number,
                        'pf_account_number' => $employee->personalDetail->pf_account_number,
                        'esic_number' => $employee->personalDetail->esic_number,
                    ] : null,
                    'bank' => $employee->bankDetail ? [
                        'bank_name' => $employee->bankDetail->bank_name,
                        'account_number' => $employee->bankDetail->account_number,
                        'ifsc_code' => $employee->bankDetail->ifsc_code,
                        'account_holder_name' => $employee->bankDetail->account_holder_name,
                    ] : null,
                    'statutory_components' => $employee->statutoryComponents->map(function($comp) {
                        return [
                            'name' => $comp->statutoryComponent ? $comp->statutoryComponent->name : 'Unknown Component',
                            'value' => $comp->value,
                            'epf_option' => $comp->epf_option,
                        ];
                    })->toArray(),
                    'salary_components' => $employee->salaryComponents->map(function($comp) {
                        return [
                            'name' => $comp->salaryComponent ? $comp->salaryComponent->name : 'Unknown Component',
                            'value' => $comp->value,
                        ];
                    })->toArray(),
                ];
                
                ActivityLogService::logEmployeeUpdated($employee->id, $originalData, $newData);
            } catch (\Exception $e) {
                // Log the error but don't fail the employee update
                \Log::error('Failed to log employee update activity: ' . $e->getMessage());
            }

            // Handle leave allocations after employee update
            $this->handleLeaveAllocations($employee, $request);
            
            // Handle week offs after employee update
            $this->handleWeekOffs($employee, $request);

            DB::commit();
            return redirect()->route('employees.index')->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', $e->getMessage());
        }
    }


    // Delete employee
    public function deleteEmployee(EmployeeBasicDetail $employee)
    {
        try {
            // Capture employee data before deletion for activity logging
            $employeeData = [
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'email' => $employee->email,
                'department' => $employee->departmentObj->department ?? null,
                'designation' => $employee->designationObj->position ?? null,
                'status' => $employee->status,
            ];

            $employee->delete();

            // Log employee deletion activity
            ActivityLogService::logEmployeeDeleted($employeeData);

            return redirect()->route('employees.index')->with('success', 'Employee deleted successfully');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete employee: ' . $e->getMessage());
        }
    }

    // View single employee
    public function viewEmployee(Request $request)
    {
        $employee = EmployeeBasicDetail::findOrFail($request->input('id'));
        return view('employees.view', compact('employee'));
    }

    public function viewDocument($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        // Check if file exists
        $filePath = public_path($document->uploaded_document);
        if (!file_exists($filePath)) {
            return back()->with('error', 'Document file not found');
        }
        
        // Return the file
        return response()->file($filePath);
    }

    public function deleteDocument($id)
    {
       // print_r($id); exit;
        try {
            $document = EmployeeDocument::findOrFail($id);

            // Capture document data before deletion for activity logging
            $documentData = [
                'employee_id' => $document->employee->employee_id ?? 'Unknown',
                'employee_name' => $document->employee->name ?? 'Unknown',
                'document_name' => $document->name,
                'document_type' => $document->document_id,
                'file_path' => $document->uploaded_document,
            ];

            // Delete file from storage
            if (file_exists(public_path($document->uploaded_document))) {
                unlink(public_path($document->uploaded_document));
            }

            // Delete database record
            $document->delete();

            // Log document deletion activity
            ActivityLogService::log(
                'delete',
                'employee_document',
                'Employee document deleted: ' . $documentData['document_name'],
                $documentData,
                null,
                auth()->id()
            );

            return response()->json(['success' => true, 'message' => 'Document deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Joining Letter Format - PDF Generate
     * @param \App\Services\PDFGenerator $pdfGenerator
     * @return string
     */
    // public function joiningLetterPDF(EmployeeBasicDetail $employee, PDFGenerator $pdfGenerator)
    // {
    //     $now = \Carbon\Carbon::now();
    //     $html = view('pdf.joining-letter-format', ['employee' => $employee, 'now' => $now->format('d-m-Y')])->render();
        
    //     // return $html;
    //     return $pdfGenerator->createPDF($html, 'joining-letter.pdf');
    // }

    /**
     * Joining Letter Format - PDF Generate
     * @param \App\Services\PDFGenerator $pdfGenerator
     * @return string
     */
    public function joiningLetterPDF(EmployeeBasicDetail $employee, PDFGenerator $pdfGenerator)
    {

        $now = \Carbon\Carbon::now();
        // Get all components
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4]; // Your actual IDs

        // Create maps for component values - FIXED: Only include active (non-deleted) components
        $salaryComponentMap = [];
        $statutoryComponentMap = [];

        // Process salary components - FIXED: Check if component is active for this employee
        foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
            $salaryComponentMap[$component->salary_component_id] = $component->value;
        }

        // Process statutory components - FIXED: Check if component is active for this employee
        foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
            $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            // Store EPF option if this is an EPF component
            if ($component->statutory_component_id == 1) { // EPF component ID
                $epfOptionMap[1] = $component->epf_option ?? 'restrict_15000'; // Default to restrict_15000
            }
        }

        // Calculate earnings
        $earnings = [];
        $totalEarnings = 0;

        foreach ($earningComponents as $component) {
            $value = 0;
            $isApplicable = false; // FIXED: Default to false

            if ($component instanceof \App\Models\SalaryComponent) {
                $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                $baseValue = $salaryComponentMap[$component->id] ?? 0;
            } else {
                $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                $baseValue = $statutoryComponentMap[$component->id] ?? 0;
            }

            // FIXED: Only calculate value if component is applicable
            if ($isApplicable) {
                $value = $baseValue;
                $totalEarnings += $value;
            }

            $earnings[$component->id] = [
                'value' => $value,
                'applicable' => $isApplicable,
                'name' => $component->name,
                'default_value' => $value,
                'overridden' => false,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
            ];
        }

        //   print_r($earnings);

        // Calculate EPF Wages - Dynamic calculation based on epf_option
        $rawEpfWage = 0;
        foreach ($epfComponentIds as $componentId) {
            if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                $rawEpfWage += $earnings[$componentId]['value'];
            }
        }

        // Apply EPF option logic
        $epfOption = $epfOptionMap[1] ?? 'restrict_15000'; // Default to restrict_15000
        switch ($epfOption) {
            case 'restrict_15000':
                $epfWage = min(15000, $rawEpfWage);
                break;
            case '12_percent':
                $epfWage = $rawEpfWage;
                break;
            case 'manual_value':
                $epfWage = $statutoryComponentMap[1] ?? 0; // Use manual value
                break;
            default:
                $epfWage = min(15000, $rawEpfWage);
        }

        // Calculate deductions
        $deductions = [];
        $totalDeductions = 0;

        foreach ($deductionComponents as $component) {
            $value = 0;
            $isApplicable = false; // FIXED: Default to false

            if ($component instanceof \App\Models\StatutoryComponent) {
                $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                // FIXED: Only calculate value if component is applicable
                if ($isApplicable) {
                    if ($component->id == 1) { // EPF - Dynamic calculation
                        $epfOption = $epfOptionMap[1] ?? 'restrict_15000';
                        $fullAmountDeduct = $employee->statutoryComponents
                            ->where('statutory_component_id', 1)
                            ->whereNull('deleted_at')
                            ->first()
                            ->full_amount_deduct_from_ctc ?? false;
                            
                        if ($epfOption == 'manual_value') {
                            $value = $statutoryComponentMap[1] ?? 0; // Use manual value directly
                        } elseif ($fullAmountDeduct) {
                            // Deduct both employee and employer portions (24% total)
                            $value = round(0.24 * $epfWage);
                        } else {
                            $value = 0.12 * $epfWage; // Calculate 12% of EPF wage
                        }
                    } elseif ($component->id == 2) { // ESI
                        if ($totalEarnings <= 20000) {
                            $value = 0.0075 * $totalEarnings;
                        } else {
                            $value = 0;
                            $isApplicable = false; // Not applicable if earnings > 20000
                        }
                    } elseif ($component->id == 4) { // Professional Tax
                        $value = ($totalEarnings >= 25000) ? 200 : 0;
                    } else {
                        $value = $baseValue;
                    }
                }
            } else {
                $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                if ($isApplicable) {
                    $baseValue = $salaryComponentMap[$component->id] ?? 0;
                    $value = $baseValue;
                }
            }

            $deductions[$component->id] = [
                'value' => $value,
                'applicable' => $isApplicable,
                'name' => $component->name,
                'default_value' => $value,
                'overridden' => false,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
            ];

            // FIXED: Only add to total if applicable
            if ($isApplicable) {
                $totalDeductions += $value;
            }
        }

        //    print_r($deductions);

        $totalEarnings = 0;
        foreach ($earnings as $id => $earning) {
            if ($earning['applicable']) {
                $totalEarnings += $earning['value'];
            }
        }

        $totalDeductions = 0;
        foreach ($deductions as $id => $deduction) {
            if ($deduction['applicable']) {
                $totalDeductions += $deduction['value'];
            }
        }

        $netPay = round($totalEarnings - $totalDeductions);

        $netPayAnualy = $netPay * 12;
        $html = view('pdf.joining-letter-format', ['employee' => $employee, 'now' => $now->format('d-m-Y'), 'netPayAnualy' => $netPayAnualy])->render();

        // Log joining letter PDF generation
        ActivityLogService::log('Joining Letter PDF Generated', 'EMPLOYEE', 'Joining letter PDF generated for employee ' . $employee->name, [
            'action' => 'generate_pdf',
            'entity_type' => 'joining_letter',
            'entity_id' => $employee->id,
            'details' => [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->name,
                'annual_salary' => $netPayAnualy,
                'generated_date' => $now->format('d-m-Y')
            ],
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        // return $html;
        return $pdfGenerator->createPDF($html, 'joining-letter.pdf');
    }
 /**
     * Offer Letter PDF Format
     * @param \App\Services\PDFGenerator $pdfGenerator
     * @return string
     */
    public function offerLetterPDF(EmployeeBasicDetail $employee, PDFGenerator $pdfGenerator)
    {
        $now = \Carbon\Carbon::now();

        // Get all components
        $earningSalaryComponents = SalaryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningStatutoryComponents = StatutoryComponent::where('type', 'earning')
            ->orderBy('id')
            ->get();
        $earningComponents = $earningSalaryComponents->merge($earningStatutoryComponents);

        $deductionStatutoryComponents = StatutoryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionSalaryComponents = SalaryComponent::where('type', 'deduction')
            ->orderBy('id')
            ->get();
        $deductionComponents = $deductionStatutoryComponents->merge($deductionSalaryComponents);

        $epfComponentIds = [1, 2, 4]; // Your actual IDs

        // Create maps for component values - FIXED: Only include active (non-deleted) components
        $salaryComponentMap = [];
        $statutoryComponentMap = [];
        $epfOptionMap = []; // Map to store EPF options for each employee

        // Process salary components - FIXED: Check if component is active for this employee
        foreach ($employee->salaryComponents->whereNull('deleted_at') as $component) {
            $salaryComponentMap[$component->salary_component_id] = $component->value;
        }

        // Process statutory components - FIXED: Check if component is active for this employee
        foreach ($employee->statutoryComponents->whereNull('deleted_at') as $component) {
            $statutoryComponentMap[$component->statutory_component_id] = $component->value;
            // Store EPF option if this is an EPF component
            if ($component->statutory_component_id == 1) { // EPF component ID
                $epfOptionMap[1] = $component->epf_option ?? 'restrict_15000'; // Default to restrict_15000
            }
        }

        // Calculate earnings
        $earnings = [];
        $totalEarnings = 0;

        foreach ($earningComponents as $component) {
            $value = 0;
            $isApplicable = false; // FIXED: Default to false

            if ($component instanceof \App\Models\SalaryComponent) {
                $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                $baseValue = $salaryComponentMap[$component->id] ?? 0;
            } else {
                $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                $baseValue = $statutoryComponentMap[$component->id] ?? 0;
            }

            // FIXED: Only calculate value if component is applicable
            if ($isApplicable) {
                $value = $baseValue;
                $totalEarnings += $value;
            }

            $earnings[$component->id] = [
                'value' => $value,
                'applicable' => $isApplicable,
                'name' => $component->name,
                'default_value' => $value,
                'overridden' => false,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
            ];
        }

        //   print_r($earnings);

        // Calculate EPF Wages - Dynamic calculation based on epf_option
        $rawEpfWage = 0;
        foreach ($epfComponentIds as $componentId) {
            if (isset($earnings[$componentId]) && $earnings[$componentId]['applicable']) {
                $rawEpfWage += $earnings[$componentId]['value'];
            }
        }

        // Apply EPF option logic
        $epfOption = $epfOptionMap[1] ?? 'restrict_15000'; // Default to restrict_15000
        switch ($epfOption) {
            case 'restrict_15000':
                $epfWage = min(15000, $rawEpfWage);
                break;
            case '12_percent':
                $epfWage = $rawEpfWage;
                break;
            case 'manual_value':
                $epfWage = $statutoryComponentMap[1] ?? 0; // Use manual value
                break;
            default:
                $epfWage = min(15000, $rawEpfWage);
        }

        // Calculate deductions
        $deductions = [];
        $totalDeductions = 0;

        foreach ($deductionComponents as $component) {
            $value = 0;
            $isApplicable = false; // FIXED: Default to false

            if ($component instanceof \App\Models\StatutoryComponent) {
                $isApplicable = array_key_exists($component->id, $statutoryComponentMap);
                $baseValue = $statutoryComponentMap[$component->id] ?? 0;

                // FIXED: Only calculate value if component is applicable
                if ($isApplicable) {
                    if ($component->id == 1) { // EPF - Dynamic calculation
                        $epfOption = $epfOptionMap[1] ?? 'restrict_15000';
                        $fullAmountDeduct = $employee->statutoryComponents
                            ->where('statutory_component_id', 1)
                            ->whereNull('deleted_at')
                            ->first()
                            ->full_amount_deduct_from_ctc ?? false;
                            
                        if ($epfOption == 'manual_value') {
                            $value = $statutoryComponentMap[1] ?? 0; // Use manual value directly
                        } elseif ($fullAmountDeduct) {
                            // Deduct both employee and employer portions (24% total)
                            $value = round(0.24 * $epfWage);
                        } else {
                            $value = 0.12 * $epfWage; // Calculate 12% of EPF wage
                        }
                    } elseif ($component->id == 2) { // ESI
                        if ($totalEarnings <= 20000) {
                            $value = 0.0075 * $totalEarnings;
                        } else {
                            $value = 0;
                            $isApplicable = false; // Not applicable if earnings > 20000
                        }
                    } elseif ($component->id == 4) { // Professional Tax
                        $value = ($totalEarnings >= 25000) ? 200 : 0;
                    } else {
                        $value = $baseValue;
                    }
                }
            } else {
                $isApplicable = array_key_exists($component->id, $salaryComponentMap);
                if ($isApplicable) {
                    $baseValue = $salaryComponentMap[$component->id] ?? 0;
                    $value = $baseValue;
                }
            }

            $deductions[$component->id] = [
                'value' => $value,
                'applicable' => $isApplicable,
                'name' => $component->name,
                'default_value' => $value,
                'overridden' => false,
                'type' => ($component instanceof \App\Models\SalaryComponent) ? 'salary' : 'statutory'
            ];

            // FIXED: Only add to total if applicable
            if ($isApplicable) {
                $totalDeductions += $value;
            }
        }

        //    print_r($deductions);

        $totalEarnings = 0;
        foreach ($earnings as $id => $earning) {
            if ($earning['applicable']) {
                $totalEarnings += $earning['value'];
            }
        }

        $totalDeductions = 0;
        foreach ($deductions as $id => $deduction) {
            if ($deduction['applicable']) {
                $totalDeductions += $deduction['value'];
            }
        }

        $netPay = round($totalEarnings - $totalDeductions);

        $netPayAnualy = $netPay * 12;
        $inWords = NumberHelper::numberToWordsIndian($netPayAnualy);
        
        // Get master table data for designations and departments
        $designations = PositionType::active()->pluck('position', 'id')->toArray();
        $departments = Department::active()->pluck('department', 'id')->toArray();
        
        $html = view('pdf.offer-letter-format', [
            'employee' => $employee,
            'now' => $now->format('d-m-Y'),
            'deductions' => $deductions,
            'earnings' => $earnings,
            'totalEarnings' => $totalEarnings,
            'netPay' => $netPay,
            'netPayAnualy' => $netPayAnualy,
            'inWords' => $inWords,
            'designations' => $designations,
            'departments' => $departments
        ])->render();

        // Log offer letter PDF generation
        ActivityLogService::log('Offer Letter PDF Generated', 'EMPLOYEE', 'Offer letter PDF generated for employee ' . $employee->name, [
            'action' => 'generate_pdf',
            'entity_type' => 'offer_letter',
            'entity_id' => $employee->id,
            'details' => [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->name,
                'annual_salary' => $netPayAnualy,
                'monthly_salary' => $netPay,
                'generated_date' => $now->format('d-m-Y')
            ],
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        return $pdfGenerator->createPDF($html, 'offer-letter.pdf');
    }
 /**
     * Experience Letter PDF Format
     * @param \App\Services\PDFGenerator $pdfGenerator
     * @return string
     */
    public function experienceLetterPDF(EmployeeBasicDetail $employee, PDFGenerator $pdfGenerator)
    {
        // Get master table data for designations
    // Use actual column 'position' (not 'name') from position_types table
    $designations = PositionType::pluck('position', 'id')->toArray();
        
        $html = view('pdf.experience-letter', [
            'employee' => $employee,
            'designations' => $designations
        ])->render();

        // Log experience letter PDF generation
        ActivityLogService::log('Experience Letter PDF Generated', 'EMPLOYEE', 'Experience letter PDF generated for employee ' . $employee->name, [
            'action' => 'generate_pdf',
            'entity_type' => 'experience_letter',
            'entity_id' => $employee->id,
            'details' => [
                'employee_id' => $employee->employee_id,
                'employee_name' => $employee->name,
                'generated_date' => now()->format('d-m-Y')
            ],
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        return $pdfGenerator->createPDF($html, 'experience-letter.pdf');
    }

    /**
     * Get leave types for department (AJAX endpoint)
     */
    public function getDepartmentLeaveTypes(Request $request)
    {
        try {
            $request->validate([
                'department_id' => 'required|integer',
                'joining_date' => 'required|date',
            ]);

            $departmentId = $request->department_id;
            $joiningDate = $request->joining_date;

            $leaveTypeService = app(LeaveTypeService::class);
            $proRatingCalculator = app(LeaveProRatingCalculator::class);

            // Get current financial year
            $currentFY = FinancialYearHelper::getCurrentFinancialYear();
            if (!$currentFY) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current financial year found. Please set up financial year first.',
                ], 400);
            }

            // Validate joining date
            $validation = $proRatingCalculator->validateJoiningDate($joiningDate, $currentFY->name);
            if (!$validation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $validation['message'],
                ], 400);
            }

            // Get leave types for department
            $leaveTypes = $leaveTypeService->getLeaveTypesForDepartment($departmentId);

            if (empty($leaveTypes)) {
                return response()->json([
                    'success' => true,
                    'message' => 'No leave types found for this department',
                    'data' => [
                        'leave_types' => [],
                        'financial_year' => $currentFY->name,
                        'pro_rating_required' => false,
                    ]
                ]);
            }

            // Calculate pro-rated allocations
            $proRatedLeaves = $proRatingCalculator->calculateProRatedLeaves(
                $leaveTypes, 
                $joiningDate, 
                $currentFY->name
            );

            // Get pro-rating summary
            $proRatingSummary = $proRatingCalculator->getProRatingSummary($proRatedLeaves);

            return response()->json([
                'success' => true,
                'message' => 'Leave types retrieved successfully',
                'data' => [
                    'leave_types' => $proRatedLeaves,
                    'financial_year' => $currentFY->name,
                    'pro_rating_required' => $validation['pro_rating_required'] ?? false,
                    'pro_rating_summary' => $proRatingSummary,
                    'department_id' => $departmentId,
                    'joining_date' => $joiningDate,
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error("Error getting department leave types: " . $e->getMessage(), [
                'department_id' => $request->department_id ?? null,
                'joining_date' => $request->joining_date ?? null,
                'error_trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error retrieving leave types: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync leave types from attendance system (AJAX endpoint)
     */
    public function syncLeaveTypes(Request $request)
    {
        try {
            $leaveTypeService = app(LeaveTypeService::class);
            
            $currentFY = FinancialYearHelper::getCurrentFinancialYear();
            if (!$currentFY) {
                return response()->json([
                    'success' => false,
                    'message' => 'No current financial year found',
                ], 400);
            }

            $result = $leaveTypeService->syncLeaveTypes($currentFY->name, 'manual');

            return response()->json($result);

        } catch (\Exception $e) {
            \Log::error("Error syncing leave types: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync leave types: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test attendance API connectivity
     */
    public function testLeaveTypeAPI()
    {
        try {
            $leaveTypeService = app(LeaveTypeService::class);
            $result = $leaveTypeService->testAPIConnectivity();

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'API test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle leave allocations during employee creation/update
     */
    private function handleLeaveAllocations($employee, $request)
    {
        try {
            $leaveAllocations = $request->input('leave_allocations', []);
            
            // Debug logging
            \Log::info("handleLeaveAllocations called for employee {$employee->id}");
            \Log::info("Raw leave_allocations input:", ['data' => $request->input('leave_allocations')]);
            
            if (empty($leaveAllocations)) {
                \Log::info("No leave allocations provided for employee {$employee->id}");
                return;
            }

            // If leave_allocations is a string (JSON), decode it
            if (is_string($leaveAllocations)) {
                $leaveAllocations = json_decode($leaveAllocations, true);
                \Log::info("Decoded JSON leave allocations:", ['decoded_data' => $leaveAllocations]);
            }

            if (empty($leaveAllocations)) {
                \Log::info("No valid leave allocations after processing for employee {$employee->id}");
                return;
            }

            $currentFY = FinancialYearHelper::getCurrentFinancialYear();
            if (!$currentFY) {
                \Log::warning("No current financial year found for leave allocation");
                return;
            }

            $allocationsToSave = [];

            foreach ($leaveAllocations as $allocation) {
                // Validate required fields
                if (empty($allocation['id']) || !isset($allocation['allocated_days'])) {
                    \Log::warning("Skipping allocation due to missing required fields:", ['allocation' => $allocation]);
                    continue;
                }

                $allocationData = [
                    'id' => $allocation['id'],
                    'leave_type_name' => $allocation['leave_type_name'] ?? '',
                    'leave_type_code' => $allocation['leave_type_code'] ?? '',
                    'allocated_days' => $allocation['allocated_days'],
                    'override_days' => (isset($allocation['override_days']) && $allocation['override_days'] !== '') ? $allocation['override_days'] : null,
                    'is_pro_rated' => $allocation['is_pro_rated'] ?? false,
                    'pro_rated_factor' => $allocation['pro_rated_factor'] ?? null,
                    'department_assignment' => $allocation['assigned_departments'] ?? null,
                    'description' => $allocation['description'] ?? null,
                ];

                \Log::info("Processing allocation:", ['allocation_data' => $allocationData]);

                $allocationsToSave[] = $allocationData;
            }

            // Bulk update allocations
            if (!empty($allocationsToSave)) {
                \Log::info("Saving {count} allocations for employee {$employee->id}", [
                    'count' => count($allocationsToSave),
                    'allocations' => $allocationsToSave
                ]);

                $savedAllocations = EmployeeLeaveAllocation::bulkUpdateAllocations(
                    $employee->id,
                    $allocationsToSave,
                    $currentFY->name,
                    auth()->id()
                );

                \Log::info("Successfully saved allocations:", ['saved_count' => count($savedAllocations)]);

                // Update employee sync timestamp
                $employee->update([
                    'leave_allocations_synced_at' => now(),
                    'leave_sync_financial_year' => $currentFY->name,
                ]);

                \Log::info("Leave allocations saved for employee {$employee->id}", [
                    'allocations_count' => count($allocationsToSave),
                    'financial_year' => $currentFY->name
                ]);

                // Log activity
                ActivityLogService::log(
                    'Employee Leave Allocations Updated',
                    'EMPLOYEE',
                    "Leave allocations updated for employee {$employee->name}",
                    [
                        'action' => 'update_leave_allocations',
                        'entity_type' => 'employee_leave_allocation',
                        'entity_id' => $employee->id,
                        'details' => [
                            'employee_id' => $employee->employee_id,
                            'employee_name' => $employee->name,
                            'allocations_count' => count($allocationsToSave),
                            'financial_year' => $currentFY->name,
                            'allocations' => array_map(function ($alloc) {
                                return [
                                    'leave_type' => $alloc['leave_type_name'],
                                    'allocated_days' => $alloc['allocated_days'],
                                    'override_days' => $alloc['override_days'],
                                    'is_pro_rated' => $alloc['is_pro_rated'],
                                ];
                            }, $allocationsToSave)
                        ],
                        'user_id' => auth()->id(),
                        'timestamp' => now()
                    ]
                );
            }

        } catch (\Exception $e) {
            \Log::error("Error handling leave allocations for employee {$employee->id}: " . $e->getMessage(), [
                'employee_id' => $employee->id,
                'error_trace' => $e->getTraceAsString()
            ]);

            // Don't throw the exception to avoid breaking employee creation/update
            // Just log the error
        }
    }

    /**
     * Handle week off configuration for employee
     */
    private function handleWeekOffs($employee, $request)
    {
        try {
            \Log::info("handleWeekOffs called for employee {$employee->id}");

            $weekOffsData = $request->input('week_offs');
            
            if (empty($weekOffsData)) {
                \Log::info("No week offs data provided for employee {$employee->id}");
                return;
            }

            // Parse JSON if it's a string
            if (is_string($weekOffsData)) {
                $weekOffsData = json_decode($weekOffsData, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    \Log::error("Invalid JSON in week offs data for employee {$employee->id}");
                    return;
                }
            }

            \Log::info("Processing week offs data:", ['data' => $weekOffsData]);

            // Validate required fields
            if (!isset($weekOffsData['week_off_days']) || !is_array($weekOffsData['week_off_days'])) {
                \Log::error("Invalid week_off_days structure for employee {$employee->id}");
                return;
            }

            // Calculate working days
            $workingDaysPerWeek = 7 - count($weekOffsData['week_off_days']);
            
            // Generate pattern string
            $dayNames = array_map(function($dayNum) {
                return \App\Models\EmployeeWeekOff::getDayName($dayNum);
            }, $weekOffsData['week_off_days']);
            $pattern = implode(', ', $dayNames);

            // Update or create week off record
            \App\Models\EmployeeWeekOff::updateOrCreate(
                ['employee_id' => $employee->id],
                [
                    'week_off_days' => $weekOffsData['week_off_days'],
                    'week_off_pattern' => $pattern,
                    'working_days_per_week' => $workingDaysPerWeek
                ]
            );

            \Log::info("Week offs saved successfully for employee {$employee->id}", [
                'week_off_days' => $weekOffsData['week_off_days'],
                'pattern' => $pattern,
                'working_days_per_week' => $workingDaysPerWeek
            ]);

        } catch (\Exception $e) {
            \Log::error("Error handling week offs for employee {$employee->id}: " . $e->getMessage(), [
                'employee_id' => $employee->id,
                'error_trace' => $e->getTraceAsString()
            ]);

            // Don't throw the exception to avoid breaking employee creation/update
        }
    }

    /**
     * Sync newly created user from employee conversion with attendance system
     *
     * @param \App\Models\User $user
     * @param string $plainPassword
     * @return void
     */
    private function syncNewUserWithAttendance($user, $plainPassword)
    {
        try {
            // Get employee record to access more data
            $employee = \App\Models\EmployeeBasicDetail::find($user->employee_id);
            
            // Prepare user data for sync with all required fields
            $departmentName = null;
            $designationName = null;
            
            if ($user->department) {
                $departmentName = \DB::table('departments')->where('id', $user->department)->value('department');
            }
            
            if ($user->position) {
                $designationName = \DB::table('position_types')->where('id', $user->position)->value('position');
            }
            
            $syncData = [
                'user_id' => $user->user_id,
                'payroll_id' => (string) $user->employee_id,          // Send employee_id as string
                'payroll_user_id' => $user->id,              // Send user id as payroll_user_id
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone_number ?? '',
                'department' => $departmentName ?? '',
                'department_id' => $user->department ?? null, // Include department_id
                'designation' => $designationName ?? '',
                'status' => $user->status,
                'role_name' => $user->role_name ?? 'Employee',
                'join_date' => $user->join_date,
                'date_of_joining' => $employee ? $employee->date_of_joining : $user->join_date, // Include date_of_joining
                'reporting_manager_id' => $employee ? $employee->reporting_manager_id : null, // Include reporting_manager_id
            ];
            
            // Create a UserManagementController instance to access sync methods
            $userController = new \App\Http\Controllers\UserManagementController();
            
            // Use reflection to access private sync methods
            $syncUserMethod = new \ReflectionMethod($userController, 'syncUserWithAttendance');
            $syncUserMethod->setAccessible(true);
            
            $syncPasswordMethod = new \ReflectionMethod($userController, 'syncPasswordWithAttendance');
            $syncPasswordMethod->setAccessible(true);
            
            // Sync user data first
            $userSyncResult = $syncUserMethod->invoke($userController, $syncData, 'create');
            
            // If user sync was successful, sync the password
            if ($userSyncResult) {
                $hashedPassword = \Hash::make($plainPassword);
                $passwordSyncResult = $syncPasswordMethod->invoke($userController, $user->user_id, $hashedPassword);
                
                if ($passwordSyncResult) {
                    \Log::info("Successfully synced employee-converted user with attendance system", [
                        'user_id' => $user->user_id,
                        'employee_id' => $user->employee_id
                    ]);
                } else {
                    \Log::warning("User synced but password sync failed for employee-converted user", [
                        'user_id' => $user->user_id,
                        'employee_id' => $user->employee_id
                    ]);
                }
            } else {
                \Log::warning("Failed to sync employee-converted user with attendance system", [
                    'user_id' => $user->user_id,
                    'employee_id' => $user->employee_id
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error("Error syncing employee-converted user with attendance system", [
                'user_id' => $user->user_id ?? 'unknown',
                'employee_id' => $user->employee_id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Don't throw the exception to avoid breaking employee creation/update
        }
    }

    /**
     * Apply default permissions for employees with self portal enabled
     */
    private function applyDefaultPermissions($user)
    {
        try {
            // Default permissions for regular employees
            $defaultPermissions = [
                'dashboard.view',
                'personal_information.view',
                'personal_information.edit'
            ];

            foreach ($defaultPermissions as $permissionName) {
                $permission = \App\Models\Permission::where('name', $permissionName)->first();
                if ($permission) {
                    $user->givePermission($permission);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Error applying default permissions to user", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update user permissions based on request data
     */
    private function updateUserPermissions($user, $request)
    {
        try {
            $permissionIds = [];
            if ($request->has('permissions') && is_array($request->permissions)) {
                $permissions = $request->permissions;
                \Log::info("Permissions received for user {$user->id}", ['permissions' => $permissions]);
                foreach ($permissions as $key => $value) {
                    if ($key === 'enable_payroll') continue;
                    if (is_numeric($value)) {
                        $permissionIds[] = (int)$value;
                    }
                }
            }
            if (!empty($permissionIds)) {
                \Log::info("Syncing permissions for user {$user->id}", ['permission_ids' => $permissionIds]);
                $user->givePermissions($permissionIds);
            } else {
                \Log::info("No permissions array found for user {$user->id}");
                $this->applyDefaultPermissions($user);
            }
        } catch (\Exception $e) {
            \Log::error("Error updating user permissions", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

}