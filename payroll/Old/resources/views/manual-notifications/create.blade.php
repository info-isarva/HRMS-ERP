@extends('layouts.master')

@section('title', 'Create Notification')

@section('style')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.5.2/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .page-header-pattern {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.04);
    }

    .page-header-circle-1,
    .page-header-circle-2 {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }
    .page-header-circle-1 { top: -1rem; right: -1rem; width: 6rem; height: 6rem; }
    .page-header-circle-2 { bottom: -1rem; left: -1rem; width: 8rem; height: 8rem; }

    .page-header-icon-box {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .page-header-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin-bottom: 0.25rem;
    }
    .page-header-subtitle { color: rgba(255,255,255,0.9); margin: 0; }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .settings-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
        font-size: 0.875rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    .btn-outline-secondary {
        border: 1px solid #d1d5db;
        color: #6b7280;
    }

    .btn-outline-secondary:hover {
        background: #f9fafb;
        border-color: #9ca3af;
        color: #374151 !important;
    }

    /* Section Headers */
    .section-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: #374151;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e5e7eb;
    }

    .form-row {
        margin-bottom: 2rem;
    }

    .form-row:last-child {
        margin-bottom: 0;
    }

    /* Error Messages */
    .text-danger {
        color: #ef4444 !important;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    .invalid-feedback {
        display: block;
        color: #ef4444;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }

    /* Select2 Styling */
    .select2-container {
        width: 100% !important;
    }
    
    .select2-container .select2-selection {
        border-radius: 0.5rem !important;
        border: 1px solid #d1d5db !important;
        min-height: 45px !important;
        padding: 0.375rem 0.75rem !important;
    }
    
    .select2-container--default .select2-selection--multiple {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        padding: 0.375rem !important;
        min-height: 45px !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #667eea !important;
        border-color: #667eea !important;
        color: white !important;
        border-radius: 0.375rem !important;
        padding: 2px 8px !important;
        margin: 2px 4px 2px 0 !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white !important;
        margin-right: 5px !important;
    }
    
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        background-color: rgba(255, 255, 255, 0.2) !important;
    }
    
    .select2-dropdown {
        border: 1px solid #d1d5db !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
    }
    
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d1d5db !important;
        border-radius: 0.375rem !important;
        padding: 0.5rem !important;
    }
    
    .select2-container--default .select2-results__option--highlighted[data-selected] {
        background-color: #667eea !important;
        color: white !important;
    }
    
    .select2-container--default .select2-results__option[data-selected=true] {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }

        .settings-card .card-body {
            padding: 1.5rem;
        }

        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
    }
</style>
@endsection

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <!-- Modern Page Header -->
                <div class="page-header-card">
                    <div class="page-header-gradient">
                        <div class="page-header-pattern"></div>
                        <div class="page-header-circle-1"></div>
                        <div class="page-header-circle-2"></div>
                        <div class="d-flex align-items-center">
                            <div class="page-header-icon-box">
                                <i class="fas fa-plus-circle fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Create Notification</h1>
                                <p class="page-header-subtitle">Create and schedule notifications for your employees</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="{{ route('manual-notifications.index') }}">Notifications</a></li>
                                <li class="breadcrumb-item active">Create</li>
                            </ol>
                        </nav>
                        <div>
                            <a href="{{ route('manual-notifications.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fas fa-edit me-2"></i>Notification Details</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('manual-notifications.store') }}" method="POST" id="notificationForm">
                            @csrf
                            
                            <!-- Basic Information -->
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <h6 class="section-header">Basic Information</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                                               value="{{ old('title') }}" required maxlength="255" placeholder="Enter notification title">
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Priority <span class="text-danger">*</span></label>
                                        <select name="priority" class="form-control form-select @error('priority') is-invalid @enderror" required>
                                            <option value="">Select Priority</option>
                                            <option value="low" {{ old('priority') == 'low' ? 'selected' : '' }}>Low</option>
                                            <option value="medium" {{ old('priority', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                                            <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>High</option>
                                        </select>
                                        @error('priority')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">High priority notifications will send email alerts</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="form-label">Message <span class="text-danger">*</span></label>
                                        <textarea name="message" class="form-control @error('message') is-invalid @enderror" 
                                                  rows="4" required placeholder="Enter notification message">{{ old('message') }}</textarea>
                                        @error('message')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Targeting -->
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <h6 class="section-header">Target Audience</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Target Type <span class="text-danger">*</span></label>
                                        <select name="target_type" id="target_type" class="form-control form-select @error('target_type') is-invalid @enderror" required>
                                            <option value="">Select Target Type</option>
                                            <option value="all" {{ old('target_type') == 'all' ? 'selected' : '' }}>All Employees</option>
                                            <option value="department" {{ old('target_type') == 'department' ? 'selected' : '' }}>Specific Department</option>
                                            <option value="specific_employees" {{ old('target_type') == 'specific_employees' ? 'selected' : '' }}>Specific Employees</option>
                                        </select>
                                        @error('target_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4" id="department_selection" style="display: none;">
                                    <div class="form-group">
                                        <label class="form-label">Department</label>
                                        <select name="target_departments[]" id="target_departments" 
                                               class="form-control form-select select2" multiple 
                                               data-placeholder="Select one or more departments">
                                            @foreach($departments as $id => $name)
                                                <option value="{{ $id }}" 
                                                    {{ in_array($id, old('target_departments', [])) ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select one or more departments</small>
                                    </div>
                                </div>
                                <div class="col-md-8" id="employee_selection" style="display: none;">
                                    <div class="form-group">
                                        <label class="form-label">Employees</label>
                                        <select name="target_employees[]" id="target_employees" 
                                               class="form-control form-select select2" multiple 
                                               data-placeholder="Select specific employees">
                                            @foreach($employees as $employee)
                                                <option value="{{ $employee->id }}" 
                                                    {{ in_array($employee->id, old('target_employees', [])) ? 'selected' : '' }}>
                                                    {{ $employee->name }} ({{ $employee->employee_id }})
                                                    @if($employee->department_name) - {{ $employee->department_name }} @endif
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">Select specific employees</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule Settings -->
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <h6 class="section-header">Schedule & Display</h6>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Status</label>
                                        <select name="status" class="form-control form-select @error('status') is-invalid @enderror">
                                            <option value="draft" {{ old('status', 'draft') == 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="scheduled" {{ old('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                            <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active Now</option>
                                        </select>
                                        @error('status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Start Date & Time</label>
                                        <input type="datetime-local" name="start_date" 
                                               class="form-control @error('start_date') is-invalid @enderror" 
                                               value="{{ old('start_date', now()->format('Y-m-d\TH:i')) }}">
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">End Date & Time</label>
                                        <input type="datetime-local" name="end_date" 
                                               class="form-control @error('end_date') is-invalid @enderror" 
                                               value="{{ old('end_date') }}">
                                        @error('end_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">Leave empty for no expiry</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-label">Recurrence</label>
                                        <select name="recurrence_type" class="form-control form-select @error('recurrence_type') is-invalid @enderror">
                                            <option value="once" {{ old('recurrence_type', 'once') == 'once' ? 'selected' : '' }}>Once</option>
                                            <option value="daily" {{ old('recurrence_type') == 'daily' ? 'selected' : '' }}>Daily</option>
                                            <option value="weekly" {{ old('recurrence_type') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                            <option value="monthly" {{ old('recurrence_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                        </select>
                                        @error('recurrence_type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Display Options -->
                            <div class="row mb-4">
                                <div class="col-12 mb-3">
                                    <h6 class="section-header">Display Options</h6>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Icon</label>
                                        <select name="icon" class="form-control form-select @error('icon') is-invalid @enderror">
                                            <option value="fa-bell" {{ old('icon', 'fa-bell') == 'fa-bell' ? 'selected' : '' }}>🔔 Bell</option>
                                            <option value="fa-info-circle" {{ old('icon') == 'fa-info-circle' ? 'selected' : '' }}>ℹ️ Info</option>
                                            <option value="fa-exclamation-triangle" {{ old('icon') == 'fa-exclamation-triangle' ? 'selected' : '' }}>⚠️ Warning</option>
                                            <option value="fa-calendar" {{ old('icon') == 'fa-calendar' ? 'selected' : '' }}>📅 Calendar</option>
                                            <option value="fa-bullhorn" {{ old('icon') == 'fa-bullhorn' ? 'selected' : '' }}>📢 Announcement</option>
                                        </select>
                                        @error('icon')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="form-label">Color Theme</label>
                                        <select name="color" class="form-control form-select @error('color') is-invalid @enderror">
                                            <option value="primary" {{ old('color', 'primary') == 'primary' ? 'selected' : '' }}>Primary</option>
                                            <option value="success" {{ old('color') == 'success' ? 'selected' : '' }}>Success</option>
                                            <option value="info" {{ old('color') == 'info' ? 'selected' : '' }}>Info</option>
                                            <option value="warning" {{ old('color') == 'warning' ? 'selected' : '' }}>Warning</option>
                                            <option value="danger" {{ old('color') == 'danger' ? 'selected' : '' }}>Danger</option>
                                        </select>
                                        @error('color')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Show in Header</label>
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="show_in_header" value="0">
                                            <input type="checkbox" class="form-check-input" id="show_in_header" 
                                                   name="show_in_header" value="1" {{ old('show_in_header', '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="show_in_header">Display</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-label">Send Email</label>
                                        <div class="form-check form-switch mt-2">
                                            <input type="hidden" name="send_email" value="0">
                                            <input type="checkbox" class="form-check-input" id="send_email"
                                                   name="send_email" value="1" {{ old('send_email') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="send_email">Email</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="row">
                                <div class="col-12">
                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-save me-1"></i> Create Notification
                                        </button>
                                        <a href="{{ route('manual-notifications.index') }}" class="btn btn-outline-secondary">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 with enhanced settings
    $('.select2').each(function() {
        $(this).select2({
            theme: 'default',
            placeholder: $(this).data('placeholder') || 'Select options...',
            allowClear: true,
            width: '100%',
            dropdownParent: $(this).closest('.form-group'),
            escapeMarkup: function(markup) {
                return markup;
            },
            templateResult: function(option) {
                if (!option.id) {
                    return option.text;
                }
                return $('<span>' + option.text + '</span>');
            },
            templateSelection: function(option) {
                return option.text || option.id;
            }
        });
    });
    
    console.log('Select2 initialized for elements:', $('.select2').length);
    
    // Debug: Check departments data
    console.log('Department options available:', $('#target_departments option').length);
    $('#target_departments option').each(function(i) {
        console.log('Department ' + i + ':', $(this).val(), '-', $(this).text());
    });
    
    // Debug: Check employees data  
    console.log('Employee options available:', $('#target_employees option').length);
    
    // Target type change handler with Select2 reinitialization
    $('#target_type').change(function() {
        var targetType = $(this).val();
        console.log('Target type changed to:', targetType);
        
        // Hide all selection divs first
        $('#department_selection, #employee_selection').hide();
        
        // Destroy existing Select2 instances only if they exist
        if ($('#target_departments').hasClass('select2-hidden-accessible')) {
            $('#target_departments').select2('destroy');
        }
        if ($('#target_employees').hasClass('select2-hidden-accessible')) {
            $('#target_employees').select2('destroy');
        }
        
        // Clear previous selections
        $('#target_departments, #target_employees').val(null).trigger('change');
        
        // Remove required attributes
        $('#target_departments, #target_employees').removeAttr('required');
        
        // Show appropriate selection based on target type
        if (targetType === 'department') {
            $('#department_selection').show();
            $('#target_departments').attr('required', true);
            
            // Small delay to ensure DOM is ready
            setTimeout(function() {
                // Reinitialize Select2 for departments
                $('#target_departments').select2({
                    theme: 'default',
                    placeholder: 'Select one or more departments',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#department_selection')
                });
                console.log('Department selection initialized with', $('#target_departments option').length, 'options');
            }, 100);
        } else if (targetType === 'specific_employees') {
            $('#employee_selection').show();
            $('#target_employees').attr('required', true);
            
            // Small delay to ensure DOM is ready
            setTimeout(function() {
                // Reinitialize Select2 for employees
                $('#target_employees').select2({
                    theme: 'default',
                    placeholder: 'Select specific employees',
                    allowClear: true,
                    width: '100%',
                    dropdownParent: $('#employee_selection')
                });
                console.log('Employee selection initialized with', $('#target_employees option').length, 'options');
            }, 100);
        }
    });
    
    // Trigger on page load if target type is already selected
    if ($('#target_type').val()) {
        $('#target_type').trigger('change');
    }
    
    // Form validation
    $('#notificationForm').on('submit', function(e) {
        var targetType = $('#target_type').val();
        var isValid = true;
        
        console.log('Form submitted with target type:', targetType); // Debug log
        
        if (targetType === 'department') {
            var selectedDepts = $('#target_departments').val();
            console.log('Selected departments:', selectedDepts); // Debug log
            
            if (!selectedDepts || selectedDepts.length === 0) {
                e.preventDefault();
                alert('Please select at least one department.');
                $('#target_departments').focus();
                return false;
            }
        }
        
        if (targetType === 'specific_employees') {
            var selectedEmps = $('#target_employees').val();
            console.log('Selected employees:', selectedEmps); // Debug log
            
            if (!selectedEmps || selectedEmps.length === 0) {
                e.preventDefault();
                alert('Please select at least one employee.');
                $('#target_employees').focus();
                return false;
            }
        }
        
        // Additional validation for required fields
        if (!$('#target_type').val()) {
            e.preventDefault();
            alert('Please select a target type.');
            $('#target_type').focus();
            return false;
        }
        
        console.log('Form validation passed'); // Debug log
        return true;
    });

});
</script>
@endsection