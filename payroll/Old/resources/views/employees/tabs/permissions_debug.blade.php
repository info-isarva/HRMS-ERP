{{-- Permissions Tab --}}
<div class="row" id="permissions-debug-tab-content">
    <style>
        /* Enhanced permission styling for better text wrapping and alignment */
        .permission-module .form-check {
            min-height: auto;
            padding-left: 2.5rem;
            display: flex;
            align-items: flex-start;
        }

        .permission-module .form-check-input {
            margin-left: -2.5rem;
            margin-top: 0.25rem;
            margin-right: 0.5rem;
            flex-shrink: 0;
            cursor: pointer;
        }

        .permission-module .form-check-label {
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
            line-height: 1.4;
            padding-left: 0;
            display: block;
            width: 100%;
            cursor: pointer;
        }

        /* Better spacing for permission items */
        .permission-item {
            margin-bottom: 1rem;
            padding: 0.5rem;
            border-radius: 4px;
            transition: background-color 0.2s;
        }

        .permission-item:hover {
            background-color: #f8f9fa;
        }

        /* Ensure proper column behavior */
        .permission-module .col-md-6 { 
            min-width: 0;
            display: flex;
            flex-direction: column;
        }

        /* Better module header styling */
        .module-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.75rem;
            margin-bottom: 1rem;
        }

        /* Compact description styling */
        .permission-description {
            font-size: 0.8rem;
            line-height: 1.3;
            margin-top: 0.25rem;
            opacity: 0.8;
        }

        /* Ensure switches don't break the layout - Bootstrap 5 compatible */
        .form-switch {
            padding-left: 2.5rem;
        }

        .form-switch .form-check-input {
            width: 2.5em;
            height: 1.25em;
            margin-left: -2.5rem;
            margin-top: 0.25em;
            background-color: #e9ecef;
            border: 1px solid #dee2e6;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
            background-position: left center;
            background-size: auto;
            background-repeat: no-repeat;
            transition: background-position 0.15s ease-in-out, background-color 0.15s ease-in-out;
            cursor: pointer;
        }

        .form-switch .form-check-input::before,
        .form-switch .form-check-input::after {
            display: none !important;
            content: none !important;
        }

        .form-switch .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
            background-position: right center;
        }

        .form-switch .form-check-input:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: 0;
        }

        .form-switch .form-check-input:hover:not(:disabled) {
            opacity: 0.9;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .permission-module .col-md-6 {
                flex: 0 0 100%;
                max-width: 100%;
            }
            
            .permission-item {
                margin-bottom: 0.75rem;
            }
        }

        /* Additional styling for better visual appearance */
        .permission-module {
            border-left: 3px solid #007bff;
            padding-left: 15px;
            margin-bottom: 1.5rem;
        }

        .permission-module:first-child {
            border-left-color: #007bff;
        }

        .permission-module:nth-child(2) {
            border-left-color: #28a745;
        }

        .permission-module:nth-child(3) {
            border-left-color: #ffc107;
        }

        .attendance-permission:checked + label .permission-text {
            color: #28a745;
            font-weight: 600;
        }

        .payroll-permission:checked + label .permission-text {
            color: #007bff;
            font-weight: 600;
        }

        .permission-text {
            transition: color 0.2s ease;
        }

        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .card-header h6 {
            font-weight: 600;
            font-size: 1rem;
        }

        .module-header h6 {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .btn-group-vertical .btn {
            border-radius: 0.25rem !important;
        }

        #attendance-permissions-loading {
            min-height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        /* Robust Toggle Switch Styles - SCOPED to permissions debug tab */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"] {
            appearance: none !important;
            -webkit-appearance: none !important;
            background-color: #dfe1e4 !important;
            background-image: none !important; /* Remove Bootstrap checkmark */
            border: none !important;
            border-radius: 20px !important; /* Pill shape */
            width: 40px !important;
            height: 20px !important;
            position: relative;
            cursor: pointer;
            box-shadow: inset 0 0 1px rgba(0,0,0,0.2);
            transition: background-color 0.2s ease;
            margin-top: 0.15em;
            overflow: visible !important; /* Ensure knob isn't clipped */
        }

        /* The Toggle Knob */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]::after {
            content: "" !important;
            display: block !important;
            position: absolute;
            top: 2px;
            left: 2px;
            width: 16px;
            height: 16px;
            background-color: #ffffff !important;
            border-radius: 50%; /* Circle */
            transition: all 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0,0,0,0.2);
            z-index: 2;
        }

        /* Checked State */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
            background-color: #0d6efd !important; /* Active Blue */
            border-color: #0d6efd !important;
        }

        /* Move Knob when Checked */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
            left: 22px !important; /* Move to the right */
            background-color: #ffffff !important;
        }

        /* Hover State */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
            background-color: #c9cbcd !important;
        }
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
            background-color: #dc3545 !important; /* Hover on Active is Red */
            border-color: #dc3545 !important;
        }

        /* Focus State */
        #permissions-debug-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
            outline: none;
        }

        /* Label Alignment */
        #permissions-debug-tab-content label.form-check-label {
            margin-left: 8px;
            vertical-align: top;
            line-height: 24px; /* Align text with the toggle */
        }
    </style>
    
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Permissions & Access Control</h5>
                <p class="text-muted">Configure what actions this employee can perform when they access the system</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="refreshPermissions();" title="Click if permissions section is not loading properly">
                        <i class="fas fa-refresh"></i> Refresh Permissions
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success" onclick="manualSaveAttendancePermissions();" title="Save attendance permissions to attendance system">
                        <i class="fas fa-save"></i> Save Attendance Permissions
                    </button>
                </div>
            </div>
            <div class="card-body">
                {{-- Portal Access Controls --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Portal Access</h6>
                                <div class="form-check form-switch mb-2">
                                    <input type="hidden" name="basic[enable_self_portal_present]" value="1">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="enable_self_portal" 
                                           name="basic[enable_self_portal]" 
                                           value="1" 
                                           {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'checked' : '' }}
                                           onchange="togglePermissionsVisibility()">
                                    <label class="form-check-label" for="enable_self_portal">
                                        <strong>Enable Self Portal</strong>
                                    </label>
                                    <small class="form-text text-muted">Allow employee to log into the system</small>
                                </div>

                                <div class="form-check form-switch mb-2" id="enable-crm-section">
                                    <input type="hidden" name="basic[enable_crm_present]" value="1">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="enable_crm" 
                                           name="basic[enable_crm]" 
                                           value="1" 
                                           {{ old('basic.enable_crm', isset($employee->enable_crm) ? $employee->enable_crm : false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="enable_crm">
                                        <strong>Enable CRM</strong>
                                    </label>
                                    <small class="form-text text-muted">Grant access to CRM system</small>
                                </div>
                                
                                <div class="form-check form-switch" id="enable-payroll-section" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'block' : 'none' }};">
                                    <input type="hidden" name="basic[enable_payroll_present]" value="1">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="enable_payroll" 
                                           name="basic[enable_payroll]" 
                                           value="1" 
                                           {{ old('basic.enable_payroll', isset($employee->enable_payroll) ? $employee->enable_payroll : false) ? 'checked' : '' }}
                                           onchange="togglePayrollPermissions()">
                                    <label class="form-check-label" for="enable_payroll">
                                        <strong>Enable Payroll Access</strong>
                                    </label>
                                    <small class="form-text text-muted">Grant access to payroll system features</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body">
                                <h6 class="card-title">Quick Permission Templates</h6>
                                <div class="btn-group-vertical w-100" role="group" id="permission-templates" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'block' : 'none' }};">
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="applyPermissionTemplate('employee')">Employee Template</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="applyPermissionTemplate('supervisor')">Supervisor Template</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="applyPermissionTemplate('hr')">HR Template</button>
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-1" onclick="applyPermissionTemplate('admin')">Admin Template</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllPermissions()">Clear All</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Permission Groups in Two Columns --}}
                <div id="permissions-container" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'block' : 'none' }};">
                    <div class="row">
                        {{-- Payroll Column --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">Payroll System Permissions</h6>
                                </div>
                                <div class="card-body" id="payroll-permissions-body">
                                    {{-- Payroll Disabled Message --}}
                                    <div id="payroll-disabled-message" class="alert alert-warning" style="display: {{ old('permissions.enable_payroll', isset($employee->enable_payroll) ? $employee->enable_payroll : false) ? 'none' : 'block' }};">
                                        <i class="fas fa-lock"></i>
                                        <strong>Enable Payroll Access</strong> above to configure payroll permissions.
                                    </div>
                                    
                                    {{-- Payroll Permissions Content --}}
                                    <div id="payroll-permissions-content" style="display: {{ old('permissions.enable_payroll', isset($employee->enable_payroll) ? $employee->enable_payroll : false) ? 'block' : 'none' }};">
                                        {{-- Dynamic Payroll Permissions from Database --}}
                                        @php
                                            $payrollPermissions = \App\Models\Permission::where('is_active', true)
                                                ->orderBy('module')
                                                ->orderBy('display_name')
                                                ->get()
                                                ->groupBy('module');
                                        @endphp
                                        
                                        @if($payrollPermissions->count() > 0)
                                            @foreach($payrollPermissions as $module => $permissions)
                                                <div class="permission-module mb-4">
                                                    <div class="module-header d-flex justify-content-between align-items-center">
                                                        <h6 class="text-primary mb-0">{{ ucfirst($module) }} Module</h6>
                                                        <div class="form-check form-switch">
                                                            <input type="checkbox" 
                                                                   class="form-check-input module-toggle" 
                                                                   id="toggle_{{ Str::slug($module) }}" 
                                                                   onchange="toggleModulePermissions('{{ Str::slug($module) }}')">
                                                            <label class="form-check-label" for="toggle_{{ Str::slug($module) }}">
                                                                <small>All</small>
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        @foreach($permissions as $permission)
                                                            <div class="col-md-12">
                                                                <div class="permission-item">
                                                                    <div class="form-check form-switch">
                                                                        <input type="checkbox" 
                                                                               class="form-check-input payroll-permission {{ Str::slug($module) }}-permission" 
                                                                               name="permissions[]" 
                                                                               value="{{ $permission->id }}" 
                                                                               id="perm_{{ $permission->id }}"
                                                                               {{ isset($userPermissions) && in_array($permission->id, $userPermissions) ? 'checked' : '' }}
                                                                               onchange="updateModuleToggle('{{ Str::slug($module) }}')">
                                                                        <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                                            <div class="permission-text">
                                                                                {{ $permission->display_name }}
                                                                                @if($permission->description)
                                                                                    <div class="permission-description">{{ $permission->description }}</div>
                                                                                @endif
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-info">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>No permissions configured</strong><br>
                                                No payroll permissions have been created yet. 
                                                <a href="{{ route('permissions.manage') }}" target="_blank" class="alert-link">
                                                    Create permissions in Settings
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Attendance & Leave Column --}}
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">Attendance & Leave Permissions</h6>
                                </div>
                                <div class="card-body" id="attendance-permissions-body">
                                    {{-- Attendance Disabled Message --}}
                                    <div id="attendance-disabled-message" class="alert alert-warning" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'none' : 'block' }};">
                                        <i class="fas fa-lock"></i>
                                        <strong>Enable Self Portal</strong> above to configure attendance & leave permissions.
                                    </div>
                                    
                                    {{-- Attendance Permissions Content --}}
                                    <div id="attendance-permissions-content" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'block' : 'none' }};">
                                        {{-- Attendance Module --}}
                                        <div class="permission-module mb-4" id="attendance-permissions-loading">
                                            <div class="text-center py-3">
                                                <i class="fas fa-spinner fa-spin"></i> Loading attendance permissions...
                                            </div>
                                        </div>
                                        
                                        {{-- Dynamic Attendance Permissions will be loaded here --}}
                                        <div id="attendance-permissions-dynamic"></div>
                                        
                                        {{-- Error message if API fails --}}
                                        <div id="attendance-permissions-error" class="alert alert-warning" style="display: none;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Unable to load attendance permissions</strong><br>
                                            Using default permissions. Please check attendance system connection.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- No Permissions Message --}}
                <div id="no-permissions-message" style="display: {{ old('basic.enable_self_portal', isset($employee->enable_self_portal) ? $employee->enable_self_portal : ($enableSelfPortalMasterSetting ?? false)) ? 'none' : 'block' }};">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Enable self portal access to configure permissions for this employee.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global variable to store attendance permissions data
let attendancePermissionsData = [];
let existingAttendancePermissions = [];
let permissionsInitialized = false;

// Safe initialization function with comprehensive error handling
function initializePermissions() {
    try {
        console.log('Initializing permissions interface...');
        
        // Prevent multiple simultaneous initializations
        if (permissionsInitialized) {
            console.log('Permissions already initialized, skipping...');
            return;
        }
        
        // Add event listeners for permission toggles with null checks
        const enablePortalCheckbox = document.getElementById('enable_self_portal');
        const enablePayrollCheckbox = document.getElementById('enable_payroll');
        
        if (enablePortalCheckbox) {
            // Remove existing listeners to prevent duplicates
            enablePortalCheckbox.removeEventListener('change', togglePermissionsVisibility);
            enablePortalCheckbox.addEventListener('change', togglePermissionsVisibility);
            console.log('Added event listener for enable_self_portal');
        } else {
            console.warn('enable_self_portal checkbox not found - may not be on employee form page');
        }
        
        if (enablePayrollCheckbox) {
            // Remove existing listeners to prevent duplicates
            enablePayrollCheckbox.removeEventListener('change', togglePayrollPermissions);
            enablePayrollCheckbox.addEventListener('change', togglePayrollPermissions);
            console.log('Added event listener for enable_payroll');
        } else {
            console.warn('enable_payroll checkbox not found - may not be on employee form page');
        }
        
        // Only load attendance permissions if we're on the right page
        if (enablePortalCheckbox) {
            // Load attendance permissions from API
            loadAttendancePermissions();
        }
        
        // Initialize toggle functions safely with existence checks - only if not already initialized
        if (typeof togglePermissionsVisibility === 'function' && enablePortalCheckbox) {
            // Only call if permissions haven't been loaded yet
            if (!document.getElementById('attendance-permissions-dynamic').innerHTML.trim()) {
                togglePermissionsVisibility();
            }
        }
        if (typeof togglePayrollPermissions === 'function' && enablePayrollCheckbox) {
            togglePayrollPermissions();
        }
        if (typeof toggleAttendancePermissions === 'function' && enablePortalCheckbox) {
            // Only call if permissions haven't been loaded yet
            if (!document.getElementById('attendance-permissions-dynamic').innerHTML.trim()) {
                toggleAttendancePermissions();
            }
        }
        
        // Initialize module toggles for payroll permissions in edit mode
        setTimeout(() => {
            initializeModuleToggles();
        }, 100);
        
        permissionsInitialized = true;
        console.log('Permissions interface initialized successfully');
    } catch (error) {
        console.error('Error initializing permissions:', error);
        
        // Only retry if we think we're on the right page
        const isEmployeePage = document.getElementById('enable_self_portal') || 
                              document.querySelector('input[name="basic[email]"]') ||
                              document.querySelector('.employee-form');
        
        if (isEmployeePage && !permissionsInitialized) {
            setTimeout(() => {
                console.log('Retrying permissions initialization...');
                initializePermissions();
            }, 2000);
        } else {
            console.log('Not on employee page or already initialized, skipping retry');
        }
    }
}

// NEW FUNCTION: Initialize module toggles based on current permission states
function initializeModuleToggles() {
    console.log('Initializing module toggles...');
    
    // Initialize payroll module toggles
    document.querySelectorAll('.module-toggle').forEach(toggle => {
        const moduleId = toggle.id.replace('toggle_', '');
        updateModuleToggle(moduleId);
    });
    
    // Initialize attendance module toggles after they are loaded
    const checkAttendanceToggles = setInterval(() => {
        const attendanceToggles = document.querySelectorAll('.module-toggle-attendance');
        if (attendanceToggles.length > 0) {
            attendanceToggles.forEach(toggle => {
                const moduleId = toggle.id.replace('toggle_attendance_', '');
                updateAttendanceModuleToggle(moduleId);
            });
            clearInterval(checkAttendanceToggles);
        }
    }, 100);
    
    // Safety timeout
    setTimeout(() => {
        clearInterval(checkAttendanceToggles);
    }, 5000);
}

// Function to get selected attendance permission IDs
function getSelectedAttendancePermissions() {
    const selectedPermissions = [];
    document.querySelectorAll('input[name="attendance_permissions[]"]:checked').forEach(checkbox => {
        selectedPermissions.push(parseInt(checkbox.value));
    });
    return selectedPermissions;
}

// Function to save attendance permissions to attendance system
function saveAttendancePermissions(userEmail) {
    const selectedPermissions = getSelectedAttendancePermissions();
    
    console.log('Saving attendance permissions for:', userEmail, selectedPermissions);
    
    return fetch('{{ env("ATTENDANCE_API_BASE_URL") }}/sync/permissions/from-payroll', {
        method: 'POST',
        headers: {
            'X-API-Token': '{{ env("ATTENDANCE_API_TOKEN", "hrms_sync_token_2025_secure_key") }}',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            user_email: userEmail,
            attendance_permissions: selectedPermissions,
            synced_from: 'payroll',
            synced_at: new Date().toISOString()
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Attendance permissions saved successfully:', data);
        } else {
            console.error('Failed to save attendance permissions:', data.message);
        }
        return data;
    })
    .catch(error => {
        console.error('Error saving attendance permissions:', error);
        return { success: false, message: error.message };
    });
}

// Function to load existing attendance permissions for editing
function loadExistingAttendancePermissions(userEmail) {
    if (!userEmail) return Promise.resolve([]);
    
    console.log('Loading existing attendance permissions for:', userEmail);
    
    return fetch(`{{ env("ATTENDANCE_API_BASE_URL") }}/user/permissions/${encodeURIComponent(userEmail)}`, {
        method: 'GET',
        headers: {
            'X-API-Token': '{{ env("ATTENDANCE_API_TOKEN", "hrms_sync_token_2025_secure_key") }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Raw API response for existing permissions:', data);
            
            // Handle different possible data structures
            let permissions = [];
            if (data.permissions) {
                if (Array.isArray(data.permissions)) {
                    permissions = data.permissions;
                } else if (typeof data.permissions === 'object') {
                    // If permissions is an object, try to extract IDs
                    permissions = Object.keys(data.permissions).map(key => data.permissions[key]);
                }
            } else if (data.data && Array.isArray(data.data)) {
                permissions = data.data;
            }
            
            // Ensure all permission IDs are converted to consistent format
            permissions = permissions.map(perm => {
                if (typeof perm === 'object' && perm.id) {
                    return perm.id;
                }
                return perm;
            });
            
            console.log('Processed existing attendance permissions:', permissions);
            existingAttendancePermissions = permissions || [];
            return permissions || [];
        } else {
            console.warn('Could not load existing permissions:', data.message);
            return [];
        }
    })
    .catch(error => {
        console.error('Error loading existing permissions:', error);
        return [];
    });
}

// Load attendance permissions from API
function loadAttendancePermissions() {
    console.log('Loading attendance permissions from API...');
    
    // Check if permissions are already loaded to prevent re-rendering
    const dynamicContainer = document.getElementById('attendance-permissions-dynamic');
    if (dynamicContainer && dynamicContainer.innerHTML.trim() !== '') {
        console.log('Attendance permissions already loaded, skipping reload');
        return;
    }
    
    // Get employee email for editing mode
    const emailInput = document.querySelector('input[name="basic[email]"]') || 
                      document.querySelector('input[name="email"]') ||
                      document.querySelector('#employee_email');
    const userEmail = emailInput ? emailInput.value : '';
    
    // First load existing permissions if editing
    const existingPermissionsPromise = userEmail ? loadExistingAttendancePermissions(userEmail) : Promise.resolve([]);
    
    // Then load available permissions from API
    const permissionsApiPromise = fetch('{{ env("ATTENDANCE_API_BASE_URL") }}/payroll/attendance-permissions', {
        method: 'GET',
        headers: {
            'X-API-Token': '{{ env("ATTENDANCE_API_TOKEN", "hrms_sync_token_2025_secure_key") }}',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText} - ${response.url}`);
        }
        return response.json();
    });
    
    // Wait for both promises to complete
    Promise.all([existingPermissionsPromise, permissionsApiPromise])
        .then(([existingPerms, apiData]) => {
            console.log('Attendance permissions API response:', apiData);
            console.log('Existing permissions for comparison:', existingPerms);
            console.log('Type of existing permissions:', typeof existingPerms, Array.isArray(existingPerms));
            
            if (apiData.success && Array.isArray(apiData.data)) {
                attendancePermissionsData = apiData.data;
                existingAttendancePermissions = existingPerms || [];
                renderAttendancePermissions(apiData.data);
                
                // Initialize attendance module toggles after rendering
                setTimeout(() => {
                    attendancePermissionsData.forEach(moduleData => {
                        const moduleSlug = moduleData.module.toLowerCase().replace(/\s+/g, '-');
                        updateAttendanceModuleToggle(moduleSlug);
                    });
                }, 100);
            } else {
                throw new Error('Invalid API response format');
            }
        })
        .catch(error => {
            console.error('Failed to load attendance permissions:', error);
            showAttendancePermissionsError();
        });
}

// Render attendance permissions dynamically
function renderAttendancePermissions(permissionsData) {
    const dynamicContainer = document.getElementById('attendance-permissions-dynamic');
    const loadingElement = document.getElementById('attendance-permissions-loading');
    const errorElement = document.getElementById('attendance-permissions-error');
    
    if (!dynamicContainer) {
        console.error('attendance-permissions-dynamic container not found');
        return;
    }
    
    let html = '';
    
    permissionsData.forEach(moduleData => {
        const moduleSlug = moduleData.module.toLowerCase().replace(/\s+/g, '-');
        // Capitalize every word of module name
        const moduleDisplayName = moduleData.module.toLowerCase().split(/[_ ]/).map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
        html += `
            <div class="permission-module mb-4">
                <div class="module-header d-flex justify-content-between align-items-center">
                    <h6 class="text-success mb-0">${moduleDisplayName} Module</h6>
                    <div class="form-check form-switch">
                        <input type="checkbox" 
                               class="form-check-input module-toggle-attendance" 
                               id="toggle_attendance_${moduleSlug}" 
                               onchange="toggleAttendanceModulePermissions('${moduleSlug}'); event.stopPropagation();"
                               onclick="event.stopPropagation();">
                        <label class="form-check-label" for="toggle_attendance_${moduleSlug}">
                            <small>All</small>
                        </label>
                    </div>
                </div>
                <div class="row">`;
        
        moduleData.permissions.forEach(permission => {
            // Check multiple formats to ensure compatibility
            const permissionId = permission.id;
            const isChecked = existingAttendancePermissions.includes(permissionId.toString()) || 
                             existingAttendancePermissions.includes(parseInt(permissionId)) ||
                             existingAttendancePermissions.includes(permissionId);
            
            console.log(`Permission ${permission.display_name} (ID: ${permissionId}): Existing permissions:`, existingAttendancePermissions, 'Is checked:', isChecked);
            
            html += `
                <div class="col-md-12">
                    <div class="permission-item">
                        <div class="form-check form-switch">
                            <input type="checkbox" 
                                   class="form-check-input attendance-permission ${moduleSlug}-attendance-permission" 
                                   name="attendance_permissions[]" 
                                   value="${permission.id}" 
                                   id="attendance_perm_${permission.id}"
                                   ${isChecked ? 'checked' : ''}
                                   onchange="updateAttendanceModuleToggle('${moduleSlug}'); event.stopPropagation();"
                                   onclick="event.stopPropagation();">
                            <label class="form-check-label" for="attendance_perm_${permission.id}">
                                <div class="permission-text">
                                    ${permission.display_name}
                                    ${permission.description ? `<div class="permission-description">${permission.description}</div>` : ''}
                                </div>
                            </label>
                        </div>
                    </div>
                </div>`;
        });
        
        html += `</div></div>`;
    });
    
    dynamicContainer.innerHTML = html;
    
    // Hide loading, show content, hide error
    if (loadingElement) loadingElement.style.display = 'none';
    if (errorElement) errorElement.style.display = 'none';
    
    console.log('Attendance permissions rendered successfully');
}

// Show error message if API fails
function showAttendancePermissionsError() {
    const dynamicContainer = document.getElementById('attendance-permissions-dynamic');
    const loadingElement = document.getElementById('attendance-permissions-loading');
    const errorElement = document.getElementById('attendance-permissions-error');
    
    if (loadingElement) loadingElement.style.display = 'none';
    if (errorElement) errorElement.style.display = 'block';
    if (dynamicContainer) dynamicContainer.innerHTML = '';
}

// Toggle all permissions in an attendance module
function toggleAttendanceModulePermissions(moduleSlug) {
    const moduleCheckboxes = document.querySelectorAll(`.${moduleSlug}-attendance-permission`);
    const toggleCheckbox = document.getElementById(`toggle_attendance_${moduleSlug}`);
    
    if (!toggleCheckbox) return;
    
    const shouldCheck = toggleCheckbox.checked;
    
    moduleCheckboxes.forEach(checkbox => {
        // Only toggle if attendance permissions are enabled and checkbox is not disabled
        const enablePortalCheckbox = document.getElementById('enable_self_portal');
        if (enablePortalCheckbox && enablePortalCheckbox.checked && !checkbox.disabled) {
            checkbox.checked = shouldCheck;
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    });
    
    // Update module toggle state
    updateAttendanceModuleToggle(moduleSlug);
}

// Update module toggle based on individual permission selections
function updateAttendanceModuleToggle(moduleSlug) {
    const moduleCheckboxes = document.querySelectorAll(`.${moduleSlug}-attendance-permission`);
    const toggleCheckbox = document.getElementById(`toggle_attendance_${moduleSlug}`);
    
    if (!toggleCheckbox || moduleCheckboxes.length === 0) return;
    
    const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
    const totalCount = moduleCheckboxes.length;
    
    if (checkedCount === 0) {
        toggleCheckbox.checked = false;
        toggleCheckbox.indeterminate = false;
    } else if (checkedCount === totalCount) {
        toggleCheckbox.checked = true;
        toggleCheckbox.indeterminate = false;
    } else {
        toggleCheckbox.checked = false;
        toggleCheckbox.indeterminate = true;
    }
    
    // Prevent event bubbling that might trigger re-initialization
    if (typeof event !== 'undefined') {
        event.stopPropagation();
    }
}

// Helper function to check if element is visible
function isElementVisible(element) {
    return element && 
           element.offsetParent !== null && 
           getComputedStyle(element).display !== 'none' &&
           getComputedStyle(element).visibility !== 'hidden';
}

// Toggle permissions visibility based on self portal checkbox
function togglePermissionsVisibility() {
    const enablePortalCheckbox = document.getElementById('enable_self_portal');
    const permissionsContainer = document.getElementById('permissions-container');
    const permissionTemplates = document.getElementById('permission-templates');
    const enablePayrollSection = document.getElementById('enable-payroll-section');
    const noPermissionsMessage = document.getElementById('no-permissions-message');

    // Check if elements exist before proceeding
    if (!enablePortalCheckbox || !permissionsContainer || !permissionTemplates || !enablePayrollSection || !noPermissionsMessage) {
        return;
    }

    if (enablePortalCheckbox.checked) {
        permissionsContainer.style.display = 'block';
        permissionTemplates.style.display = 'block';
        enablePayrollSection.style.display = 'block';
        noPermissionsMessage.style.display = 'none';
        // Only trigger attendance permissions check
        toggleAttendancePermissions();
        
        // Initialize module toggles after showing permissions
        setTimeout(() => {
            initializeModuleToggles();
        }, 100);
    } else {
        permissionsContainer.style.display = 'none';
        permissionTemplates.style.display = 'none';
        enablePayrollSection.style.display = 'none';
        noPermissionsMessage.style.display = 'block';
        // Only clear permissions and uncheck payroll when disabling
        clearAllPermissions();
        const enablePayrollCheckbox = document.getElementById('enable_payroll');
        if (enablePayrollCheckbox && enablePayrollCheckbox.checked) {
            enablePayrollCheckbox.checked = false;
            togglePayrollPermissions();
        }
    }
}

// Toggle payroll permissions based on payroll access checkbox
function togglePayrollPermissions() {
    const enablePayrollCheckbox = document.getElementById('enable_payroll');
    const payrollPermissionsContent = document.getElementById('payroll-permissions-content');
    const payrollDisabledMessage = document.getElementById('payroll-disabled-message');

    // Check if elements exist before proceeding
    if (!enablePayrollCheckbox || !payrollPermissionsContent || !payrollDisabledMessage) {
        return;
    }

    if (enablePayrollCheckbox.checked) {
        payrollPermissionsContent.style.display = 'block';
        payrollDisabledMessage.style.display = 'none';
        
        // Initialize module toggles after showing payroll permissions
        setTimeout(() => {
            initializeModuleToggles();
        }, 100);
    } else {
        payrollPermissionsContent.style.display = 'none';
        payrollDisabledMessage.style.display = 'block';
        
        // Clear all payroll permissions when disabled
        document.querySelectorAll('.payroll-permission').forEach(checkbox => {
            checkbox.checked = false;
        });
        
        // Update module toggles
        document.querySelectorAll('.module-toggle').forEach(toggle => {
            const moduleId = toggle.id.replace('toggle_', '');
            updateModuleToggle(moduleId);
        });
    }
}

// Toggle attendance permissions based on self portal checkbox
function toggleAttendancePermissions() {
    const enablePortalCheckbox = document.getElementById('enable_self_portal');
    const attendancePermissionsContent = document.getElementById('attendance-permissions-content');
    const attendanceDisabledMessage = document.getElementById('attendance-disabled-message');

    // Check if elements exist before proceeding
    if (!enablePortalCheckbox || !attendancePermissionsContent || !attendanceDisabledMessage) {
        return;
    }

    if (enablePortalCheckbox.checked) {
        attendancePermissionsContent.style.display = 'block';
        attendanceDisabledMessage.style.display = 'none';
    } else {
        attendancePermissionsContent.style.display = 'none';
        attendanceDisabledMessage.style.display = 'block';
        
        // Only clear attendance permissions when explicitly disabling, not during initialization
        if (attendancePermissionsContent.style.display !== 'none') {
            document.querySelectorAll('.attendance-permission').forEach(checkbox => {
                checkbox.checked = false;
            });
            
            // Update module toggles
            attendancePermissionsData.forEach(moduleData => {
                const moduleSlug = moduleData.module.toLowerCase().replace(/\s+/g, '-');
                updateAttendanceModuleToggle(moduleSlug);
            });
        }
    }
}

// Permission templates
function applyPermissionTemplate(template) {
    // First, clear all permissions
    clearAllPermissions();
    
    const templates = {
        'employee': {
            payroll: ['employees_view'],
            attendance: [1, 2] // Use permission IDs from attendance system
        },
        'supervisor': {
            payroll: ['employees_view', 'employees_edit', 'payroll_view', 'reports_view'],
            attendance: [1, 2, 3] // Use permission IDs from attendance system
        },
        'hr': {
            payroll: ['employees_view', 'employees_create', 'employees_edit', 'payroll_view', 'payroll_process', 'payslip_generate', 'reports_view', 'reports_export'],
            attendance: 'all'
        },
        'admin': {
            payroll: 'all',
            attendance: 'all'
        }
    };
    
    const templatePerms = templates[template] || {payroll: [], attendance: []};
    
    // Check if payroll access is enabled before applying payroll permissions
    const enablePayrollCheckbox = document.getElementById('enable_payroll');
    if (enablePayrollCheckbox && enablePayrollCheckbox.checked) {
        if (templatePerms.payroll === 'all') {
            document.querySelectorAll('.payroll-permission').forEach(checkbox => {
                checkbox.checked = true;
            });
        } else {
            templatePerms.payroll.forEach(permValue => {
                const checkbox = document.querySelector(`input[value="${permValue}"]`);
                if (checkbox && checkbox.classList.contains('payroll-permission')) {
                    checkbox.checked = true;
                }
            });
        }
        
        // Update payroll module toggles
        document.querySelectorAll('.module-toggle').forEach(toggle => {
            const moduleId = toggle.id.replace('toggle_', '');
            updateModuleToggle(moduleId);
        });
    }
    
    // Check if self portal is enabled before applying attendance permissions
    const enablePortalCheckbox = document.getElementById('enable_self_portal');
    if (enablePortalCheckbox && enablePortalCheckbox.checked) {
        if (templatePerms.attendance === 'all') {
            document.querySelectorAll('.attendance-permission').forEach(checkbox => {
                checkbox.checked = true;
            });
        } else if (Array.isArray(templatePerms.attendance)) {
            templatePerms.attendance.forEach(permId => {
                const checkbox = document.querySelector(`input[value="${permId}"]`);
                if (checkbox && checkbox.classList.contains('attendance-permission')) {
                    checkbox.checked = true;
                }
            });
        }
        
        // Update all module toggles for attendance
        attendancePermissionsData.forEach(moduleData => {
            const moduleSlug = moduleData.module.toLowerCase().replace(/\s+/g, '-');
            updateAttendanceModuleToggle(moduleSlug);
        });
    }
}

// Clear all permissions
function clearAllPermissions() {
    document.querySelectorAll('.payroll-permission, .attendance-permission').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Clear module toggles
    document.querySelectorAll('.module-toggle, .module-toggle-attendance').forEach(toggle => {
        toggle.checked = false;
        toggle.indeterminate = false;
    });
}

// Module-wise select all functionality
function toggleModulePermissions(module) {
    const moduleCheckboxes = document.querySelectorAll(`.${module}-permission`);
    const toggleCheckbox = document.getElementById(`toggle_${module}`);
    
    if (!toggleCheckbox) return;
    
    const shouldCheck = toggleCheckbox.checked;
    
    moduleCheckboxes.forEach(checkbox => {
        // Only toggle if the section is enabled
        const isPayrollPermission = checkbox.classList.contains('payroll-permission');
        const isAttendancePermission = checkbox.classList.contains('attendance-permission');
        
        if (isPayrollPermission) {
            const enablePayrollCheckbox = document.getElementById('enable_payroll');
            if (enablePayrollCheckbox && enablePayrollCheckbox.checked && !checkbox.disabled) {
                checkbox.checked = shouldCheck;
            }
        } else if (isAttendancePermission) {
            const enablePortalCheckbox = document.getElementById('enable_self_portal');
            if (enablePortalCheckbox && enablePortalCheckbox.checked && !checkbox.disabled) {
                checkbox.checked = shouldCheck;
            }
        }
    });
    
    // Update the module toggle state
    updateModuleToggle(module);
}

// Update module toggle based on individual permission selections
function updateModuleToggle(module) {
    const moduleCheckboxes = document.querySelectorAll(`.${module}-permission`);
    const toggleCheckbox = document.getElementById(`toggle_${module}`);
    
    if (!toggleCheckbox || moduleCheckboxes.length === 0) return;
    
    const checkedCount = Array.from(moduleCheckboxes).filter(cb => cb.checked).length;
    const totalCount = moduleCheckboxes.length;
    
    if (checkedCount === 0) {
        toggleCheckbox.checked = false;
        toggleCheckbox.indeterminate = false;
    } else if (checkedCount === totalCount) {
        toggleCheckbox.checked = true;
        toggleCheckbox.indeterminate = false;
    } else {
        toggleCheckbox.checked = false;
        toggleCheckbox.indeterminate = true;
    }
}

// Initialize permissions interface when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializePermissions();
    
    // Add event delegation for permission checkbox changes
    document.addEventListener('change', function(e) {
        // Handle payroll permission changes
        if (e.target.classList.contains('payroll-permission')) {
            const module = Array.from(e.target.classList).find(cls => cls.includes('-permission') && cls !== 'payroll-permission');
            if (module) {
                const moduleId = module.replace('-permission', '');
                updateModuleToggle(moduleId);
            }
        }
        
        // Handle attendance permission changes
        if (e.target.classList.contains('attendance-permission')) {
            const module = Array.from(e.target.classList).find(cls => cls.includes('-attendance-permission') && cls !== 'attendance-permission');
            if (module) {
                const moduleId = module.replace('-attendance-permission', '');
                updateAttendanceModuleToggle(moduleId);
            }
        }
    });
});

// Also initialize when tab becomes visible (for Bootstrap tabs)
document.addEventListener('shown.bs.tab', function(e) {
    if (e.target.getAttribute('href') === '#permissions' || e.target.getAttribute('href') === '#permissions_debug') {
        setTimeout(initializePermissions, 100);
    }
});

// Initialize when clicking on permissions tab directly
document.addEventListener('DOMContentLoaded', function() {
    // Tab event listeners using vanilla JS
    const tabLinks = document.querySelectorAll('a[href="#permissions"], a[href="#permissions_debug"]');
    
    tabLinks.forEach(link => {
        link.addEventListener('shown.bs.tab', function() {
            setTimeout(initializePermissions, 100);
        });
    });
    
    // Also try to initialize immediately if this tab is already active
    const permissionsTab = document.getElementById('permissions') || document.getElementById('permissions_debug');
    if (permissionsTab && permissionsTab.classList.contains('active')) {
        setTimeout(initializePermissions, 500);
    }
    
    // Hook into form submission to save attendance permissions
    setTimeout(() => {
        const employeeForm = document.getElementById('employee_form') || 
                           document.querySelector('form[action*="employee"]') ||
                           document.querySelector('form');
        
        if (employeeForm) {
            employeeForm.addEventListener('submit', function(e) {
                // Get employee email
                const emailInput = document.querySelector('input[name="basic[email]"]') || 
                                  document.querySelector('input[name="email"]') ||
                                  document.querySelector('#employee_email');
                const userEmail = emailInput ? emailInput.value : '';
                
                const portalCheckbox = document.getElementById('enable_self_portal');
                if (userEmail && portalCheckbox && portalCheckbox.checked) {
                    // Don't prevent form submission, but save attendance permissions asynchronously
                    saveAttendancePermissions(userEmail).then(result => {
                        if (result.success) {
                            console.log('Attendance permissions saved successfully');
                        } else {
                            console.error('Failed to save attendance permissions:', result.message);
                        }
                    }).catch(error => {
                        console.error('Error during attendance permissions save:', error);
                    });
                }
            });
            console.log('Form submission handler attached');
        } else {
            console.warn('Employee form not found for submission handler');
        }
    }, 1000);
});

// Function to manually trigger attendance permissions save (for testing or manual saves)
function manualSaveAttendancePermissions() {
    const emailInput = document.querySelector('input[name="basic[email]"]') || 
                      document.querySelector('input[name="email"]') ||
                      document.querySelector('#employee_email');
    const userEmail = emailInput ? emailInput.value : '';
    
    if (!userEmail) {
        alert('Please enter an email address first');
        return;
    }
    
    const portalCheckbox = document.getElementById('enable_self_portal');
    if (!portalCheckbox) {
        alert('Portal checkbox not found');
        return;
    }
    
    if (!portalCheckbox.checked) {
        alert('Please enable self portal access first');
        return;
    }
    
    // Show loading state
    const saveButton = document.querySelector('button[onclick="manualSaveAttendancePermissions()"]');
    const originalText = saveButton ? saveButton.innerHTML : '';
    if (saveButton) {
        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
        saveButton.disabled = true;
    }
    
    saveAttendancePermissions(userEmail).then(result => {
        if (result.success) {
            alert('Attendance permissions saved successfully!');
        } else {
            alert('Failed to save attendance permissions: ' + result.message);
        }
    }).catch(error => {
        alert('Error saving attendance permissions: ' + error.message);
    }).finally(() => {
        // Restore button state
        if (saveButton) {
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        }
    });
}

// Function to refresh permissions (force reload)
function refreshPermissions() {
    console.log('Manually refreshing permissions...');
    
    // Reset initialization flag and clear existing data
    permissionsInitialized = false;
    attendancePermissionsData = [];
    existingAttendancePermissions = [];
    
    // Clear the dynamic container to force reload
    const dynamicContainer = document.getElementById('attendance-permissions-dynamic');
    if (dynamicContainer) {
        dynamicContainer.innerHTML = '';
    }
    
    // Show loading state
    const loadingElement = document.getElementById('attendance-permissions-loading');
    if (loadingElement) {
        loadingElement.style.display = 'block';
    }
    
    // Hide error state
    const errorElement = document.getElementById('attendance-permissions-error');
    if (errorElement) {
        errorElement.style.display = 'none';
    }
    
    // Re-initialize permissions
    setTimeout(() => {
        initializePermissions();
    }, 500);
}
</script>