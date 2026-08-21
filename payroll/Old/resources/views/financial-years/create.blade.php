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

    /* Modern Create Card */
    .create-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }
    
    .create-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }
    
    .create-card .card-header h5 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }
    
    .create-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }
    
    .create-card .card-body {
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
        align-items: flex-start;
        margin-bottom: 0.5rem;
    }
    
    .form-text {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
        margin-left: 1.7rem;
    }
    
    /* Button Styling */
    .btn {
        border-radius: 0.5rem;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.2s ease;
        border: none;
    }
    
    .btn-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    
    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
    
    /* Quick Presets */
    .quick-preset {
        border: 2px solid #e5e7eb;
        border-radius: 0.75rem;
        padding: 1.25rem;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
        background: white;
        text-align: center;
    }
    
    .quick-preset:hover {
        border-color: #667eea;
        background: linear-gradient(135deg, #f0f4ff 0%, #e0e7ff 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    .quick-preset.selected {
        border-color: #667eea;
        background: linear-gradient(135deg, #e0e7ff 0%, #c7d2fe 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    
    .quick-preset h6 {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }
    
    .quick-preset small {
        color: #6b7280;
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
    
    /* Error Messages */
    .text-danger {
        color: #ef4444 !important;
        font-size: 0.75rem;
        margin-top: 0.25rem;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .page-header-gradient {
            padding: 1.5rem 1rem;
        }
        
        .create-card .card-body {
            padding: 1.5rem;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .quick-preset {
            margin-bottom: 1rem;
        }
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box">
                        <i class="fas fa-plus fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Create Financial Year</h1>
                        <p class="page-header-subtitle">Set up a new financial year for your organization</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('financial-years.index') }}">Financial Years</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </nav>
                <a href="{{ route('financial-years.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="create-card">
                    <div class="card-header">
                        <h5><i class="fas fa-plus me-2"></i>Create New Financial Year</h5>
                    </div>
                    
                    <div class="card-body">
                        <form method="POST" action="{{ route('financial-years.store') }}" id="createFYForm">
                            @csrf
                            
                            <!-- Quick Presets -->
                            <div class="form-group">
                                <label class="form-label">Quick Presets</label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="quick-preset" onclick="applyPreset('current')">
                                            <h6 class="mb-1">Current Year (Based on Settings)</h6>
                                            <small class="text-muted">Auto-calculate based on current settings</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="quick-preset" onclick="applyPreset('next')">
                                            <h6 class="mb-1">Next Year</h6>
                                            <small class="text-muted">Create the next financial year</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Year Name -->
                            <div class="form-group">
                                <label class="form-label">Financial Year Name</label>
                                <input type="text" name="name" class="form-control" placeholder="e.g., 2024-25" 
                                       value="{{ old('name') }}" required onchange="updatePreview()">
                                <small class="text-muted">Format: YYYY-YY (e.g., 2024-25)</small>
                                @error('name')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Date Range -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">Start Date</label>
                                        <input type="date" name="start_date" class="form-control" 
                                               value="{{ old('start_date') }}" required onchange="updatePreview()">
                                        @error('start_date')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label">End Date</label>
                                        <input type="date" name="end_date" class="form-control" 
                                               value="{{ old('end_date') }}" required onchange="updatePreview()">
                                        @error('end_date')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="form-group">
                                <label class="form-label">Description (Optional)</label>
                                <textarea name="description" class="form-control" rows="3" 
                                          placeholder="Enter a description for this financial year...">{{ old('description') }}</textarea>
                                @error('description')
                                    <div class="text-danger mt-1">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Set as Current -->
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_current" 
                                           value="1" {{ old('is_current') ? 'checked' : '' }}>
                                    <label class="form-check-label">Set as Current Financial Year</label>
                                    <div class="form-text">If checked, this will become the active financial year</div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save me-2"></i>Create Financial Year
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg ms-2" onclick="resetForm()">
                                    <i class="fas fa-undo me-2"></i>Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Preview Card -->
                <div class="create-card">
                    <div class="card-header">
                        <h5><i class="fas fa-eye me-2"></i>Preview</h5>
                    </div>
                    <div class="card-body">
                        <div class="preview-card" id="fyPreview">
                            <h6 class="text-success">Financial Year Preview</h6>
                            <div id="previewContent">
                                <div class="mb-2">
                                    <strong>Name:</strong>
                                    <div id="previewName">-</div>
                                </div>
                                <div class="mb-2">
                                    <strong>Duration:</strong>
                                    <div id="previewDuration">-</div>
                                </div>
                                <div class="mb-2">
                                    <strong>Total Days:</strong>
                                    <div id="previewDays">-</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Settings Info -->
                <div class="create-card">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle me-2"></i>Current Settings</h5>
                    </div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-calendar text-info"></i>
                                <div>
                                    <strong>Start Month:</strong> {{ $settings->start_month_name }}
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-calendar-check text-info"></i>
                                <div>
                                    <strong>End Month:</strong> {{ $settings->end_month_name }}
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-lock text-warning"></i>
                                <div>
                                    <strong>Auto-Close:</strong> {{ $settings->auto_close_enabled ? 'Enabled' : 'Disabled' }}
                                </div>
                            </li>
                            <li>
                                <i class="fas fa-plus-circle text-success"></i>
                                <div>
                                    <strong>Auto-Create Next:</strong> {{ $settings->auto_create_next ? 'Enabled' : 'Disabled' }}
                                </div>
                            </li>
                        </ul>
                        <hr>
                        <small class="text-muted">
                            Financial years will follow these settings. You can modify them in 
                            <a href="{{ route('financial-years.settings') }}">Settings</a>.
                        </small>
                    </div>
                </div>

                <!-- Guidelines -->
                <div class="create-card">
                    <div class="card-header">
                        <h5><i class="fas fa-lightbulb me-2"></i>Guidelines</h5>
                    </div>
                    <div class="card-body">
                        <ul class="info-list">
                            <li>
                                <i class="fas fa-check text-success"></i>
                                <div>Name should follow YYYY-YY format</div>
                            </li>
                            <li>
                                <i class="fas fa-check text-success"></i>
                                <div>Start date should be {{ $settings->start_month_name }} 1st</div>
                            </li>
                            <li>
                                <i class="fas fa-check text-success"></i>
                                <div>End date should be {{ $settings->end_month_name }} last day</div>
                            </li>
                            <li>
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                                <div>Only one FY can be current at a time</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const settings = {
    startMonth: {{ $settings->start_month }}
};

function applyPreset(type) {
    // Remove previous selections
    document.querySelectorAll('.quick-preset').forEach(el => el.classList.remove('selected'));
    
    // Mark current as selected
    event.currentTarget.classList.add('selected');
    
    const currentYear = new Date().getFullYear();
    const currentMonth = new Date().getMonth() + 1;
    
    if (type === 'current') {
        // Calculate current FY based on settings
        let fyStartYear, fyEndYear;
        
        if (currentMonth >= settings.startMonth) {
            fyStartYear = currentYear;
            fyEndYear = currentYear + 1;
        } else {
            fyStartYear = currentYear - 1;
            fyEndYear = currentYear;
        }
        
        const name = `${fyStartYear}-${String(fyEndYear).slice(-2)}`;
        const startDate = `${fyStartYear}-${String(settings.startMonth).padStart(2, '0')}-01`;
        
        // Calculate end date (last day of end month)
        const endMonth = settings.startMonth === 1 ? 12 : settings.startMonth - 1;
        const endDate = new Date(fyEndYear, endMonth, 0).toISOString().split('T')[0];
        
        document.querySelector('input[name="name"]').value = name;
        document.querySelector('input[name="start_date"]').value = startDate;
        document.querySelector('input[name="end_date"]').value = endDate;
        document.querySelector('input[name="is_current"]').checked = true;
        
    } else if (type === 'next') {
        // Calculate next FY
        let fyStartYear, fyEndYear;
        
        if (currentMonth >= settings.startMonth) {
            fyStartYear = currentYear + 1;
            fyEndYear = currentYear + 2;
        } else {
            fyStartYear = currentYear;
            fyEndYear = currentYear + 1;
        }
        
        const name = `${fyStartYear}-${String(fyEndYear).slice(-2)}`;
        const startDate = `${fyStartYear}-${String(settings.startMonth).padStart(2, '0')}-01`;
        
        // Calculate end date
        const endMonth = settings.startMonth === 1 ? 12 : settings.startMonth - 1;
        const endDate = new Date(fyEndYear, endMonth, 0).toISOString().split('T')[0];
        
        document.querySelector('input[name="name"]').value = name;
        document.querySelector('input[name="start_date"]').value = startDate;
        document.querySelector('input[name="end_date"]').value = endDate;
        document.querySelector('input[name="is_current"]').checked = false;
    }
    
    updatePreview();
}

function updatePreview() {
    const name = document.querySelector('input[name="name"]').value;
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    document.getElementById('previewName').textContent = name || '-';
    
    if (startDate && endDate) {
        const start = new Date(startDate);
        const end = new Date(endDate);
        
        const duration = `${start.toLocaleDateString()} - ${end.toLocaleDateString()}`;
        document.getElementById('previewDuration').textContent = duration;
        
        const diffTime = Math.abs(end - start);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        document.getElementById('previewDays').textContent = diffDays + ' days';
    } else {
        document.getElementById('previewDuration').textContent = '-';
        document.getElementById('previewDays').textContent = '-';
    }
}

function resetForm() {
    if (confirm('Reset all form fields?')) {
        document.getElementById('createFYForm').reset();
        document.querySelectorAll('.quick-preset').forEach(el => el.classList.remove('selected'));
        updatePreview();
    }
}

// Auto-generate name when dates change
document.querySelector('input[name="start_date"]').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDate = document.querySelector('input[name="end_date"]').value;
    
    if (this.value && endDate) {
        const endYear = new Date(endDate).getFullYear();
        const name = `${startDate.getFullYear()}-${String(endYear).slice(-2)}`;
        document.querySelector('input[name="name"]').value = name;
    }
    
    updatePreview();
});

document.querySelector('input[name="end_date"]').addEventListener('change', function() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    
    if (startDate && this.value) {
        const startYear = new Date(startDate).getFullYear();
        const endYear = new Date(this.value).getFullYear();
        const name = `${startYear}-${String(endYear).slice(-2)}`;
        document.querySelector('input[name="name"]').value = name;
    }
    
    updatePreview();
});

// Initialize preview
document.addEventListener('DOMContentLoaded', function() {
    updatePreview();
});
</script>
@endsection
