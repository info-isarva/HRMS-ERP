{{-- Permissions Tab --}}
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">User Permissions</h5>
                <p class="text-muted">Configure what actions this employee can perform when they access the system</p>
            </div>
            <div class="card-body">
                {{-- Enable Self Portal Toggle --}}
                <div class="form-group mb-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="enable_self_portal" name="enable_self_portal" value="1" 
                               {{ (old('enable_self_portal', $employee->enable_self_portal ?? false) ? 'checked' : '') }}>
                        <label class="form-check-label" for="enable_self_portal">
                            <strong>Enable Self Portal Access</strong>
                        </label>
                        <small class="form-text text-muted">When enabled, this employee can log into the system</small>
                    </div>
                </div>

                {{-- Permission Templates --}}
                <div class="form-group mb-4" id="permission-templates" style="display: none;">
                    <label class="form-label">Quick Permission Templates</label>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyPermissionTemplate('employee')">Employee</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyPermissionTemplate('supervisor')">Supervisor</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyPermissionTemplate('hr')">HR</button>
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="applyPermissionTemplate('admin')">Admin</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="clearAllPermissions()">Clear Alls</button>
                    </div>
                </div>

                {{-- Permission Groups --}}
                <div id="permissions-container" style="display: none;">
                    @php
                        $userPermissions = [];
                        if (isset($employee) && $employee->user) {
                            // Use JSON permissions only
                            $userPermissions = $employee->user->getPermissionIds();
                        }
                        
                        // Load permissions dynamically from database grouped by module
                        $permissionGroups = \App\Models\Permission::where('is_active', true)
                            ->orderBy('module')
                            ->orderBy('name')
                            ->get()
                            ->groupBy('module')
                    @endphp

                    @foreach($permissionGroups as $module => $permissions)
                    <div class="permission-group mb-4">
                        <div class="permission-group-header">
                            <div class="form-check">
                                <input class="form-check-input module-checkbox" type="checkbox" 
                                       id="module-{{ $module }}" 
                                       onchange="toggleModulePermissions('{{ $module }}')">
                                <label class="form-check-label fw-bold text-primary" for="module-{{ $module }}">
                                    {{ ucwords(str_replace('_', ' ', $module)) }}
                                </label>
                            </div>
                        </div>
                        
                        <div class="permission-items mt-2 ms-4">
                            <div class="row">
                                @foreach($permissions as $permission)
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-check">
                                        <input class="form-check-input permission-checkbox module-{{ $module }}" 
                                               type="checkbox" 
                                               name="permissions[]" 
                                               value="{{ $permission['id'] }}" 
                                               id="permission-{{ $permission['id'] }}"
                                               {{ in_array($permission['id'], $userPermissions) ? 'checked' : '' }}
                                               onchange="updateModuleCheckbox('{{ $module }}')">
                                        <label class="form-check-label" for="permission-{{ $permission['id'] }}" title="{{ $permission['name'] }}">
                                            {{ $permission['display_name'] }}
                                        </label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- No Permissions Message --}}
                <div id="no-permissions-message">
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
document.addEventListener('DOMContentLoaded', function() {
    const enablePortalCheckbox = document.getElementById('enable_self_portal');
    const permissionsContainer = document.getElementById('permissions-container');
    const permissionTemplates = document.getElementById('permission-templates');
    const noPermissionsMessage = document.getElementById('no-permissions-message');

    // Toggle permissions visibility based on self portal checkbox
    function togglePermissionsVisibility() {
        if (enablePortalCheckbox.checked) {
            permissionsContainer.style.display = 'block';
            permissionTemplates.style.display = 'block';
            noPermissionsMessage.style.display = 'none';
            updateAllModuleCheckboxes();
        } else {
            permissionsContainer.style.display = 'none';
            permissionTemplates.style.display = 'none';
            noPermissionsMessage.style.display = 'block';
        }
    }

    enablePortalCheckbox.addEventListener('change', togglePermissionsVisibility);
    
    // Initialize visibility on page load
    togglePermissionsVisibility();
    
    // Update module checkboxes on page load
    updateAllModuleCheckboxes();
});

// Toggle all permissions for a module
function toggleModulePermissions(module) {
    const moduleCheckbox = document.getElementById('module-' + module);
    const permissionCheckboxes = document.querySelectorAll('.module-' + module);
    
    permissionCheckboxes.forEach(checkbox => {
        checkbox.checked = moduleCheckbox.checked;
    });
}

// Update module checkbox based on individual permissions
function updateModuleCheckbox(module) {
    const moduleCheckbox = document.getElementById('module-' + module);
    const permissionCheckboxes = document.querySelectorAll('.module-' + module);
    const checkedPermissions = document.querySelectorAll('.module-' + module + ':checked');
    
    if (checkedPermissions.length === 0) {
        moduleCheckbox.checked = false;
        moduleCheckbox.indeterminate = false;
    } else if (checkedPermissions.length === permissionCheckboxes.length) {
        moduleCheckbox.checked = true;
        moduleCheckbox.indeterminate = false;
    } else {
        moduleCheckbox.checked = false;
        moduleCheckbox.indeterminate = true;
    }
}

// Update all module checkboxes
function updateAllModuleCheckboxes() {
    const modules = Array.from(document.querySelectorAll('.module-checkbox')).map(cb => 
        cb.id.replace('module-', '')
    );
    modules.forEach(module => updateModuleCheckbox(module));
}

// Permission templates
function applyPermissionTemplate(template) {
    // First, clear all permissions
    clearAllPermissions();
    
    const templates = {
        'employee': [
            'dashboard.view', 'personal_information.view', 'personal_information.edit'
        ],
        'supervisor': [
            'dashboard.view', 'employees.view', 'reports.view', 'personal_information.view', 'personal_information.edit'
        ],
        'hr': [
            'dashboard.view', 'employees.view', 'employees.create', 'employees.edit', 'departments.view', 
            'designations.view', 'reports.view', 'payroll.view', 'personal_information.view', 'personal_information.edit'
        ],
        'admin': [] // Will check all permissions
    };
    
    if (template === 'admin') {
        // Check all permissions for admin
        document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
            checkbox.checked = true;
        });
    } else {
        // Apply specific template permissions
        const templatePermissions = templates[template] || [];
        templatePermissions.forEach(permissionName => {
            const checkbox = document.querySelector(`input[value*="${permissionName}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });
    }
    
    updateAllModuleCheckboxes();
}

// Clear all permissions
function clearAllPermissions() {
    document.querySelectorAll('.permission-checkbox').forEach(checkbox => {
        checkbox.checked = false;
    });
    updateAllModuleCheckboxes();
}
</script>

<style>
.permission-group {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    background-color: #f8f9fa;
}

.permission-group-header {
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 10px;
    margin-bottom: 15px;
}

.permission-items {
    background-color: white;
    padding: 15px;
    border-radius: 6px;
}

.form-check-label {
    cursor: pointer;
}

.module-checkbox:indeterminate {
    opacity: 0.5;
}

.btn-group .btn {
    margin-right: 5px;
}
</style>