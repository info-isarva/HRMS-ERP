<div class="card mb-4 card card-success card-outline">
    <div class="card-header ">
        <h5 class="mb-0">Employee Basic Details</h5>
    </div>
    <div class="card-body">
        <div class="p-3" id="basic-tab-content">
            <div class="row">
                <!-- Profile Image HTML Section -->
                <div class="col-md-3" style="align-content:center;">
                    <div class="card mb-3">
                        <div class="card-body text-center">
                            <div class="profile-image-container">
                                <img id="profileImagePreview" 
                                    src="{{ isset($employee->profile_image) && !empty($employee->profile_image) ? asset($employee->profile_image) : asset('assets/img/user-icon.webp') }}" 
                                    class="img-thumbnail mb-2" 
                                    alt="Profile Photo"
                                    style="width: 150px; height: 150px; object-fit: cover;">
                                <input type="file" 
                                    name="basic[profile_image]"
                                    id="profileImageInput"
                                    class="form-control @error('basic.profile_image') is-invalid @enderror"
                                    accept="image/*">
                                @error('basic.profile_image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Click to upload or change profile photo</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee ID <span class="text-danger">*</span></label>
                                <input type="text" name="basic[employee_id]" 
                                    class="form-control @error('basic.employee_id') is-invalid @enderror" 
                                    value="{{ old('basic.employee_id', $employee->employee_id ?? '') }}" required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.employee_id')) {{ $errors->first('basic.employee_id') }} @else Employee ID is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Employee Name <span class="text-danger">*</span></label>
                                <input type="text" name="basic[name]" 
                                    class="form-control @error('basic.name') is-invalid @enderror" 
                                    value="{{ old('basic.name', $employee->name ?? '') }}" required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.name')) {{ $errors->first('basic.name') }} @else Employee Name is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Email <span class="text-danger">*</span></label>
                                <input type="email" name="basic[email]" 
                                    class="form-control @error('basic.email') is-invalid @enderror" 
                                    value="{{ old('basic.email' , $employee->email ?? '') }}" required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.email')) {{ $errors->first('basic.email') }} @else Email is required. @endif
                                </div>
                            </div>
                        </div>
                       <!-- <div class="col-md-3">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" name="basic[contact_number]" 
                                    class="form-control @error('basic.contact_number') is-invalid @enderror" 
                                    value="{{ old('basic.contact_number', $employee->contact_number ?? '') }}" required>
                                @error('basic.contact_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div> -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>

                                <input type="text"
                                       name="basic[contact_number]"
                                       class="form-control @error('basic.contact_number') is-invalid @enderror"
                                       value="{{ old('basic.contact_number', $employee->contact_number ?? '') }}"
                                       placeholder="+911234567890 or 9876543210"
                                       pattern="^\+?[0-9]{10,15}$"
                                       title="Enter a valid phone number: +911234567890 or 9876543210"
                                       required>

                                <!-- This single line shows the error in both cases -->
                                <div class="invalid-feedback">
                                    Enter a valid phone number: +911234567890 or 9876543210
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Of Birth <span class="text-danger">*</span></label>
                                <input type="date" name="basic[date_of_birth]" 
                                    class="form-control @error('basic.date_of_birth') is-invalid @enderror" 
                                    value="{{ old('basic.date_of_birth', $employee->date_of_birth ?? '') }}" 
                                    max="{{ date('Y-m-d', strtotime('-18 years')) }}"
                                    required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.date_of_birth')) {{ $errors->first('basic.date_of_birth') }} @else Date Of Birth is required (Must be 18+ years old). @endif
                                </div>
                            </div>
                        </div>    
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Gender <span class="text-danger">*</span></label>
                                <select name="basic[gender]" class="form-control form-select @error('basic.gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    @foreach($genders as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.gender', $employee->gender ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.gender')) {{ $errors->first('basic.gender') }} @else Gender is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Marital Status <span class="text-danger">*</span></label>
                                <select name="basic[marital_status]" class="form-control form-select @error('basic.marital_status') is-invalid @enderror" required>
                                    <option value="">Select Marital Status</option>
                                    @foreach($maritalStatuses as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.marital_status', $employee->marital_status ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.marital_status')) {{ $errors->first('basic.marital_status') }} @else Marital Status is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Location/Unit <span class="text-danger">*</span></label>
                                <select name="basic[location_id]" id="employee_location_id" class="form-control form-select select2 @error('basic.location_id') is-invalid @enderror" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.location_id', $employee->location_id ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.location_id')) {{ $errors->first('basic.location_id') }} @else Location is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Designation <span class="text-danger">*</span></label>
                                <select name="basic[designation]" class="form-control form-select select2 @error('basic.designation') is-invalid @enderror" required>
                                    <option value="">Select Designation</option>
                                    @foreach($designations as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.designation', $employee->designation ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.designation')) {{ $errors->first('basic.designation') }} @else Designation is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Department <span class="text-danger">*</span></label>
                                <select name="basic[department]" class="form-control form-select select2 @error('basic.department') is-invalid @enderror" required>
                                    <option value="">Select Department</option>
                                    @foreach($departments as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.department', $employee->department ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.department')) {{ $errors->first('basic.department') }} @else Department is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Unique ID <span class="text-danger">*</span> <i class="fas fa-question-circle text-muted ms-1" title="This ID is used for Biometric ID matching" data-bs-toggle="tooltip"></i></label>
                                <input type="text" name="basic[unique_id]" 
                                    class="form-control @error('basic.unique_id') is-invalid @enderror" 
                                    value="{{ old('basic.unique_id', $employee->unique_id ?? '') }}" required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.unique_id')) {{ $errors->first('basic.unique_id') }} @else Unique ID is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Of Joining <span class="text-danger">*</span></label>
                                <input type="date" name="basic[date_of_joining]" class="form-control @error('basic.date_of_joining') is-invalid @enderror" 
                                    value="{{ old('basic.date_of_joining', $employee->date_of_joining ?? '') }}" required>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.date_of_joining')) {{ $errors->first('basic.date_of_joining') }} @else Date Of Joining is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Role <span class="text-danger">*</span></label>
                                <select name="basic[role]" class="form-control form-select @error('basic.role') is-invalid @enderror" required>
                                    <option value="">Select Role</option>
                                    @foreach($roles as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.role' , $employee->role ?? '' ) == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.role')) {{ $errors->first('basic.role') }} @else Role is required. @endif
                                </div>
                            </div>
                        </div>  
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select name="basic[status]" class="form-control form-select @error('basic.status') is-invalid @enderror" required>
                                    @foreach($statuses as $value => $label)
                                        <option value="{{ $value }}" 
                                            {{ old('basic.status', $employee->status ?? '') == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.status')) {{ $errors->first('basic.status') }} @else Status is required. @endif
                                </div>
                            </div>
                        </div>  
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Reporting Manager</label>
                                <select name="basic[reporting_manager_id]" class="form-control form-select select2 @error('basic.reporting_manager_id') is-invalid @enderror">
                                    <option value="">Select Reporting Manager</option>
                                    @foreach($reportingManagers as $manager)
                                        <option value="{{ $manager->id }}" 
                                            {{ old('basic.reporting_manager_id', $employee->reporting_manager_id ?? '') == $manager->id ? 'selected' : '' }}>
                                            {{ $manager->employee_id }} - {{ $manager->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('basic.reporting_manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-3" id="resignationDateField" style="display: none;">
                            <div class="form-group">
                                <label>Resignation Date <span class="text-danger">*</span></label>
                                <input type="date" 
                                       name="basic[date_of_resignation]" 
                                       id="resignationDateInput"
                                       class="form-control @error('basic.date_of_resignation') is-invalid @enderror" 
                                       value="{{ old('basic.date_of_resignation', $employee->date_of_resignation ?? '') }}">
                                <div class="invalid-feedback">
                                    @if($errors->has('basic.date_of_resignation')) {{ $errors->first('basic.date_of_resignation') }} @else Resignation Date is required. @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="d-block">Additional Options</label>
                                <div class="form-check form-switch mb-2">
                                    <input type="checkbox" 
                                           class="form-check-input" 
                                           id="excludeFromPayroll" 
                                           name="basic[exclude_from_payroll]" 
                                           value="1" 
                                           {{ old('basic.exclude_from_payroll', $employee->exclude_from_payroll ?? '') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="excludeFromPayroll">Exclude from Payroll</label>
                                </div>
                                <small class="text-info">Self Portal and Payroll access is configured in the Permissions tab</small>
                            </div>
                        </div>         
                    </div> 
                </div>               
            </div>
        </div>
    </div>
</div>



<style>
/* Robust Toggle Switch Styles - SCOPED to basic tab */
#basic-tab-content .form-switch .form-check-input[type="checkbox"] {
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
#basic-tab-content .form-switch .form-check-input[type="checkbox"]::after {
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
#basic-tab-content .form-switch .form-check-input[type="checkbox"]:checked {
    background-color: #0d6efd !important; /* Active Blue */
    border-color: #0d6efd !important;
}

/* Move Knob when Checked */
#basic-tab-content .form-switch .form-check-input[type="checkbox"]:checked::after {
    left: 22px !important; /* Move to the right */
    background-color: #ffffff !important;
}

/* Hover State */
#basic-tab-content .form-switch .form-check-input[type="checkbox"]:hover {
    background-color: #c9cbcd !important;
}
#basic-tab-content .form-switch .form-check-input[type="checkbox"]:checked:hover {
    background-color: #dc3545 !important; /* Hover on Active is Red */
    border-color: #dc3545 !important;
}

/* Focus State */
#basic-tab-content .form-switch .form-check-input[type="checkbox"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25); /* Blue Focus */
    outline: none;
}

/* Label Alignment */
#basic-tab-content label.form-check-label {
    margin-left: 8px;
    vertical-align: top;
    line-height: 24px; /* Align text with the toggle */
}
</style>

<script>
    // This script will handle the image preview functionality for both create and edit modes
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize the file input event listener
        const profileImageInput = document.getElementById('profileImageInput');
        if (profileImageInput) {
            profileImageInput.addEventListener('change', previewImage);
        }
        
        // Get the master setting for self portal
        const enableSelfPortalMasterSetting = {{ $enableSelfPortalMasterSetting ? 'true' : 'false' }};
        const enableSelfPortalCheckbox = document.getElementById('enableSelfPortal');
        
        // For new employee form (not edit form), set the default based on master setting
        const isNewForm = {{ isset($employee) ? 'false' : 'true' }};
        if (isNewForm && enableSelfPortalMasterSetting) {
            enableSelfPortalCheckbox.checked = true;
        }
    });

    function previewImage(event) {
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = function() {
                const output = document.getElementById('profileImagePreview');
                output.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        const statusSelect = document.querySelector('select[name="basic[status]"]');
        const resignationField = document.getElementById('resignationDateField');
        const resignationInput = document.getElementById('resignationDateInput');

        // Get all status options and find the one that indicates "Left/Resigned"
        // Check by status name (case-insensitive) for flexibility
        function isLeftStatus(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            if (!selectedOption || !selectedOption.value) return false;
            
            const statusText = selectedOption.textContent.trim().toLowerCase();
            // Check for common "left" status names
            return statusText.includes('left') || 
                   statusText.includes('resign') || 
                   statusText.includes('terminated') ||
                   statusText.includes('exit');
        }

        function toggleResignationField() {
            if (isLeftStatus(statusSelect)) {
                resignationField.style.display = 'block';
                resignationInput.setAttribute('required', 'required');
            } else {
                resignationField.style.display = 'none';
                resignationInput.removeAttribute('required');
                resignationInput.value = ''; // Clear value if status is not 'Left'
            }
        }

        // Initialize on page load
        toggleResignationField();

        // Update on change
        statusSelect.addEventListener('change', toggleResignationField);
        
        // Add form validation before submit
        const form = statusSelect.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (isLeftStatus(statusSelect) && !resignationInput.value) {
                    e.preventDefault();
                    resignationInput.classList.add('is-invalid');
                    
                    // Create or update error message
                    let errorDiv = resignationInput.nextElementSibling;
                    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv = document.createElement('div');
                        errorDiv.className = 'invalid-feedback';
                        errorDiv.style.display = 'block';
                        resignationInput.parentNode.appendChild(errorDiv);
                    }
                    errorDiv.textContent = 'Resignation date is required when status is set to Left/Resigned.';
                    
                    // Scroll to the field
                    resignationField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    return false;
                }
            });
            
            // Remove error on input
            resignationInput.addEventListener('change', function() {
                if (this.value) {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.nextElementSibling;
                    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
                        errorDiv.style.display = 'none';
                    }
                }
            });
        }
    });
  document.addEventListener('DOMContentLoaded', function () {
    const phone = document.querySelector('input[name="basic[contact_number]"]');
    if (phone) {
        phone.addEventListener('input', function () {
            if (this.validity.patternMismatch || this.validity.valueMissing) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }
});
</script>