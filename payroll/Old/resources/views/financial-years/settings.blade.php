@extends('layouts.master')

@section('content')
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
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }
    
    .form-control:focus, .form-select:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }
    
    .form-control::placeholder {
        color: #9ca3af;
    }
    
    /* Switch Styling */
    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        margin-top: 0.2rem;
        border-radius: 0.25rem;
        border: 2px solid #ced4da;
        background-color: #fff;
        transition: all 0.2s ease;
    }
    
    .form-check-input:checked {
        background-color: #667eea;
        border-color: #667eea;
    }
    
    .form-check-input:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.25);
        border-color: #667eea;
    }
    
    .form-check-label {
        font-weight: 500;
        color: #374151;
        margin-left: 0.5rem;
        vertical-align: middle;
        line-height: 1.4;
    }
    
    .form-check {
        display: flex;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .form-check-inline {
        display: inline-flex;
        align-items: center;
        margin-right: 1rem;
    }
    
    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
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
        background: white;
        border: 2px solid #6c757d;
        color: #6c757d;
    }
    
    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
        transform: translateY(-2px);
    }
    
    .btn-outline-primary, .btn-outline-warning, .btn-outline-success {
        background: white;
        border: 2px solid;
        font-weight: 500;
    }
    
    .btn-outline-primary {
        border-color: #667eea;
        color: #667eea;
    }
    
    .btn-outline-primary:hover {
        background: #667eea;
        color: white;
    }
    
    .btn-outline-warning {
        border-color: #f59e0b;
        color: #f59e0b;
    }
    
    .btn-outline-warning:hover {
        background: #f59e0b;
        color: white;
    }
    
    .btn-outline-success {
        border-color: #10b981;
        color: #10b981;
    }
    
    .btn-outline-success:hover {
        background: #10b981;
        color: white;
    }
    
    /* Preview Card */
    .preview-card {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px dashed #dee2e6;
        border-radius: 0.75rem;
        padding: 1.5rem;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .preview-card:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
    }
    
    /* Info List */
    .info-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .info-list li {
        display: flex;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }
    
    .info-list li:hover {
        background: rgba(255, 255, 255, 0.8);
        transform: translateX(4px);
    }
    
    .info-list li i {
        margin-right: 0.75rem;
        margin-top: 0.125rem;
        font-size: 1rem;
    }
    
    .info-list li .text-success { color: #10b981 !important; }
    .info-list li .text-warning { color: #f59e0b !important; }
    .info-list li .text-info { color: #3b82f6 !important; }
    .info-list li .text-primary { color: #667eea !important; }
    
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
        
        .form-switch .form-check-input {
            margin-bottom: 0.5rem;
        }
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <!-- Background Patterns -->
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                
                <div class="position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box me-4">
                                    <i class="fas fa-cogs text-white" style="font-size: 1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Financial Year Settings</h1>
                                    <p class="page-header-subtitle">
                                        Configure financial year behavior and automation
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 d-none d-lg-flex align-items-center justify-content-end">
                            <div class="page-header-stats me-4">
                                <p class="page-header-stats-label mb-1">Status</p>
                                <p class="page-header-stats-value mb-0">{{ $settings->auto_close_enabled ? 'Active' : 'Inactive' }}</p>
                            </div>
                            <div class="page-header-stats-icon">
                                <i class="fas fa-cog text-white" style="font-size: 2rem;"></i>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col">
                            <a href="{{ route('financial-years.index') }}" class="btn btn-light btn-lg">
                                <i class="fa fa-arrow-left me-2"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="settings-card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-cog"></i>
                            Financial Year Configuration
                        </h5>
                        <p class="mb-0 mt-2 opacity-75 small">Configure how financial years work in your organization</p>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('financial-years.settings.update') }}">
                            @csrf
                            
                            <!-- Basic Settings -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i> Financial Year Start Month
                                        </label>
                                        <select name="start_month" class="form-select" required onchange="updatePreview()">
                                            @for($month = 1; $month <= 12; $month++)
                                                <option value="{{ $month }}" {{ $settings->start_month == $month ? 'selected' : '' }}>
                                                    {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                                                </option>
                                            @endfor
                                        </select>
                                        <small class="text-muted">The month when your financial year begins</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-clock me-1"></i> Auto-Close Days After End
                                        </label>
                                        <input type="number" name="auto_close_days_after" class="form-control" 
                                               value="{{ $settings->auto_close_days_after }}" min="1" max="365" required>
                                        <small class="text-muted">Days after FY ends to automatically close it</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-plus-circle me-1"></i> Create Next FY Days Before End
                                        </label>
                                        <input type="number" name="create_next_days_before" class="form-control" 
                                               value="{{ $settings->create_next_days_before }}" min="1" max="365" required>
                                        <small class="text-muted">Days before FY ends to create the next one</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">
                                            <i class="fas fa-toggle-on me-1"></i> Settings Status
                                        </label>
                                        <div class="mt-2">
                                            <div class="form-check form-check-inline mb-2">
                                                <input class="form-check-input" type="checkbox" name="auto_close_enabled" 
                                                       value="1" {{ $settings->auto_close_enabled ? 'checked' : '' }} id="auto_close_enabled">
                                                <label class="form-check-label" for="auto_close_enabled">Auto-Close Enabled</label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" name="auto_create_next" 
                                                       value="1" {{ $settings->auto_create_next ? 'checked' : '' }} id="auto_create_next">
                                                <label class="form-check-label" for="auto_create_next">Auto-Create Next</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Closing Policy -->
                            <div class="form-group">
                                <label class="form-label">
                                    <i class="fas fa-file-alt me-1"></i> Closing Policy
                                </label>
                                <textarea name="closing_policy" class="form-control" rows="4" 
                                          placeholder="Enter guidelines for financial year closing...">{{ $settings->closing_policy }}</textarea>
                                <small class="text-muted">Guidelines and procedures for closing financial years</small>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i> Save Settings
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="settings-card">
                    <div class="card-header">
                        <h5>
                            <i class="fas fa-eye"></i>
                            Preview
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-card" id="fyPreview">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-calendar-check me-2"></i>Current Configuration
                            </h6>
                            <div id="previewContent">
                                <div class="mb-3">
                                    <strong class="text-dark">FY Period:</strong>
                                    <div id="fyPeriod" class="mt-1">{{ $settings->start_month_name }} to {{ $settings->end_month_name }}</div>
                                </div>
                                <div class="mb-3">
                                    <strong class="text-dark">Example FY:</strong>
                                    <div id="fyExample" class="text-success fw-bold mt-1">2024-25</div>
                                </div>
                                <small class="text-muted">Financial year format and period based on current settings</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Information Card -->
                <div class="settings-card">
                    <div class="card-header bg-info">
                        <h5>
                            <i class="fas fa-info-circle"></i>
                            Important Notes
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-check-circle text-success"></i>
                                <div>
                                    <strong>Future Application</strong><br>
                                    <small class="text-muted">Changes apply to future financial years</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <div>
                                    <strong>Current FY Safe</strong><br>
                                    <small class="text-muted">Current FY won't be affected</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-sync text-info"></i>
                                <div>
                                    <strong>System Sync</strong><br>
                                    <small class="text-muted">Settings sync to attendance system</small>
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-shield-alt text-primary"></i>
                                <div>
                                    <strong>Auto Backup</strong><br>
                                    <small class="text-muted">Automatic backups are created</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="settings-card">
                    <div class="card-header bg-secondary">
                        <h5>
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column">
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" onclick="testConfiguration()">
                                <i class="fas fa-play me-1"></i> Test Configuration
                            </button>
                            <button type="button" class="btn btn-outline-warning btn-sm mb-2" onclick="runMaintenance()">
                                <i class="fas fa-tools me-1"></i> Run Maintenance
                            </button>
                            <a href="{{ route('financial-years.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plus me-1"></i> Create New FY
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updatePreview() {
    const startMonth = document.querySelector('select[name="start_month"]').value;
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    
    const startMonthName = months[startMonth - 1];
    const endMonth = startMonth == 1 ? 12 : startMonth - 1;
    const endMonthName = months[endMonth - 1];
    
    document.getElementById('fyPeriod').textContent = `${startMonthName} to ${endMonthName}`;
    
    // Generate example FY name
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    
    let exampleYear;
    if (currentMonth >= startMonth) {
        exampleYear = `${currentYear}-${String(currentYear + 1).slice(-2)}`;
    } else {
        exampleYear = `${currentYear - 1}-${String(currentYear).slice(-2)}`;
    }
    
    document.getElementById('fyExample').textContent = exampleYear;
}

function resetForm() {
    if (confirm('Reset form to original values?')) {
        location.reload();
    }
}

function testConfiguration() {
    const formData = new FormData(document.querySelector('form'));
    
    toastr.info('Testing configuration...');
    
    // Simple validation
    const startMonth = formData.get('start_month');
    const autoCloseDays = formData.get('auto_close_days_after');
    const createNextDays = formData.get('create_next_days_before');
    
    if (!startMonth || !autoCloseDays || !createNextDays) {
        toastr.error('Please fill all required fields');
        return;
    }
    
    if (parseInt(autoCloseDays) > 365 || parseInt(createNextDays) > 365) {
        toastr.error('Days values cannot exceed 365');
        return;
    }
    
    toastr.success('Configuration looks good!');
}

function runMaintenance() {
    if (confirm('Run financial year maintenance tasks?')) {
        fetch('/financial-years/maintenance/run', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                toastr.success(data.message);
            } else {
                toastr.error(data.message);
            }
        })
        .catch(error => {
            toastr.error('Maintenance failed: ' + error.message);
        });
    }
}

// Initialize preview on page load
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>
@endsection
