@extends('layouts.master')
@section('title', 'Payroll Payout Month Selection')

@section('content')
<div class="page-wrapper">
    <!-- Enhanced Header Section -->
    <div class="payroll-header bg-gradient-primary text-white py-4 mb-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-7">
                    <div class="d-flex align-items-center mb-2">
                        <div class="header-icon me-3">
                            <i class="fas fa-file-invoice-dollar fa-2x"></i>
                        </div>
                        <div>
                            <h1 class="h2 mb-0 font-weight-bold">Payroll Management</h1>
                            <p class="mb-0 opacity-75">Select and view employee payslips by payout month</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-5 text-md-right">
                    <div class="header-stats">
                        <div class="d-flex justify-content-end">
                            <div class="stat-item me-4">
                                <div class="stat-value">{{ count($dropdownMonths) }}</div>
                                <div class="stat-label">Available Months</div>
                            </div>
                            <div class="stat-item me-4">
                                <div class="stat-value">{{ $dropdownMonths->where('status', 'completed')->count() }}</div>
                                <div class="stat-label">Completed</div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">{{ now()->format('M Y') }}</div>
                                <div class="stat-label">Current Month</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
            <!-- <div class="row mt-3">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb bg-transparent p-0 m-0">
                            <li class="breadcrumb-item">
                                <a href="{{ route('home') }}" class="text-white-50">
                                    <i class="fas fa-home me-1"></i>Dashboard
                                </a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="#" class="text-white-50">Payroll</a>
                            </li>
                            <li class="breadcrumb-item active text-white" aria-current="page">Payslips</li>
                        </ol>
                    </nav>
                </div>
            </div> -->
        </div>
    </div>

    <div class="container-fluid">
        <!-- Financial Year Context Bar -->
        @if(isset($fyContext) && $fyContext['selectedFinancialYear'])
            <div class="row mb-4">
                <div class="col-12">
                    <div class="financial-year-banner">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt me-3 text-primary"></i>
                                <div>
                                    <h6 class="mb-0 font-weight-bold">Financial Year: {{ $fyContext['selectedFinancialYear']->year_name }}</h6>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->start_date)->format('M d, Y') }} -
                                        {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->end_date)->format('M d, Y') }}
                                    </small>
                                </div>
                            </div>
                            <div class="fy-status">
                                @if(!$fyContext['isFinancialYearEditable'])
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-lock me-1"></i>Read-only (Historical Data)
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        <i class="fas fa-edit me-1"></i>Current Year
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Flash Messages -->
        @if(session('error') || session('success'))
            <div class="row mb-4">
                <div class="col-12">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Error:</strong> {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Success:</strong> {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="row">
            <!-- Left Sidebar - Quick Actions & Info -->
            <div class="col-lg-3 col-md-4 mb-4">
                <div class="sidebar-card">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-2 text-primary"></i>Quick Info
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="info-item mb-3">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-check text-success"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-0">Completed Payrolls</h6>
                                    <small class="text-muted">{{ $dropdownMonths->where('status', 'completed')->count() }} months ready</small>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="info-icon">
                                    <i class="fas fa-clock text-warning"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-0">Pending Payrolls</h6>
                                    <small class="text-muted">{{ $dropdownMonths->where('status', 'pending')->count() }} months pending</small>
                                </div>
                            </div>

                            <div class="info-item mb-3">
                                <div class="info-icon">
                                    <i class="fas fa-spinner text-info"></i>
                                </div>
                                <div class="info-content">
                                    <h6 class="mb-0">In Progress</h6>
                                    <small class="text-muted">{{ $dropdownMonths->where('status', 'progress')->count() }} months processing</small>
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="quick-actions">
                                <h6 class="mb-2">Quick Actions</h6>
                                <a href="{{ route('payroll.index') }}" class="btn btn-outline-primary btn-sm btn-block mb-2">
                                    <i class="fas fa-plus me-1"></i>Process New Payroll
                                </a>
                                <a href="{{ route('payroll.reports.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                                    <i class="fas fa-chart-bar me-1"></i>View Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content - Month Selection -->
            <div class="col-lg-9 col-md-8">
                <div class="main-content-card">
                    <div class="card border-0 shadow-lg">
                        <div class="card-header bg-white border-bottom-0 py-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-1 font-weight-bold">
                                        <i class="fas fa-calendar-alt me-2 text-primary"></i>Select Payout Month
                                    </h4>
                                    <p class="text-muted mb-0">Choose a completed payout month to view detailed employee payslips</p>
                                </div>
                                <div class="card-header-icon">
                                    <i class="fas fa-file-invoice-dollar fa-3x text-primary opacity-25"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <form method="GET" action="{{ route('payroll.attendance-list.get') }}" id="payslipForm">
                                <div class="selection-container">
                                    <div class="form-group mb-4">
                                        <label for="payout_month_year" class="form-label-enhanced">
                                            <i class="fas fa-calendar me-2"></i>Available Payout Months
                                        </label>
                                        <div class="select-wrapper-modern">
                                            <select name="payout_month_year" id="payout_month_year" class="modern-select-enhanced">
                                                <option value="">Select a payout month to continue...</option>
                                                @foreach($dropdownMonths as $month)
                                                    @php
                                                        $monthValue = str_pad($month['payout_month'], 2, '0', STR_PAD_LEFT) . '-' . $month['payout_year'];
                                                        $isSelected = $month['payout_month'] == $selectedMonth && $month['payout_year'] == $selectedYear;
                                                    @endphp
                                                    <option value="{{ $monthValue }}"
                                                            data-status="{{ $month['status'] ?? 'not_processed' }}"
                                                            {{ $isSelected ? 'selected' : '' }}>
                                                        {{ $month['label'] }}
                                                        @if(isset($month['status']))
                                                            @if($month['status'] === 'completed')
                                                                (Completed)
                                                            @elseif($month['status'] === 'pending')
                                                                (Pending)
                                                            @elseif($month['status'] === 'progress')
                                                                (In Progress)
                                                            @else
                                                                (Not Processed)
                                                            @endif
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="select-arrow-modern">
                                                <i class="fas fa-chevron-down"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Month Preview Card -->
                                    <div id="month-preview" class="month-preview-card" style="display: none;">
                                        <div class="preview-header">
                                            <h5 class="mb-0">
                                                <i class="fas fa-eye me-2"></i>Preview Selected Month
                                            </h5>
                                        </div>
                                        <div class="preview-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="preview-item">
                                                        <label>Month:</label>
                                                        <span id="preview-month">-</span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="preview-item">
                                                        <label>Status:</label>
                                                        <span id="preview-status" class="status-badge">-</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="action-buttons-container">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <button type="submit" id="view-payslips-btn" class="btn btn-primary btn-lg btn-block action-btn" disabled>
                                                    <i class="fas fa-eye me-2"></i>
                                                    <span class="btn-text">View Payslips</span>
                                                </button>
                                            </div>
                                            <div class="col-md-6">
                                                <button type="button" id="clear-selection-btn" class="btn btn-secondary btn-lg btn-block action-btn" style="display: none;">
                                                    <i class="fas fa-times me-2"></i>
                                                    <span class="btn-text">Clear Selection</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-info"></i>Recent Payroll Activity
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($dropdownMonths->take(6) as $month)
                                <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                                    <div class="activity-item {{ $month['status'] ?? 'not_processed' }}">
                                        <div class="activity-month">{{ $month['label'] }}</div>
                                        <div class="activity-status">
                                            @if(isset($month['status']))
                                                @if($month['status'] === 'completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i>Completed
                                                    </span>
                                                @elseif($month['status'] === 'pending')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="fas fa-clock me-1"></i>Pending
                                                    </span>
                                                @elseif($month['status'] === 'progress')
                                                    <span class="badge bg-info text-dark">
                                                        <i class="fas fa-spinner me-1"></i>In Progress
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">
                                                        <i class="fas fa-times-circle me-1"></i>Not Processed
                                                    </span>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div><style>
/* Enhanced Header Styles */
.payroll-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    margin-top: -20px;
    margin-left: -15px;
    margin-right: -15px;
    margin-bottom: 20px;
}

.payroll-header .container-fluid {
    padding: 0 30px;
}

.header-icon {
    background: rgba(255,255,255,0.2);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.header-stats {
    display: flex;
    align-items: center;
}

.stat-item {
    text-align: center;
    padding: 0 15px;
    border-left: 1px solid rgba(255,255,255,0.2);
}

.stat-item:first-child {
    border-left: none;
}

.stat-value {
    font-size: 1.5rem;
    font-weight: bold;
    display: block;
    color: white;
}

.stat-label {
    font-size: 0.8rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Breadcrumb Styles */
.breadcrumb {
    background: transparent;
    padding: 0;
    margin: 0;
}

.breadcrumb-item a {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: white;
    text-decoration: underline;
}

.breadcrumb-item.active {
    color: white;
}

/* Financial Year Banner */
.financial-year-banner {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 1px solid #dee2e6;
    border-radius: 10px;
    padding: 15px 20px;
}

.fy-status .badge {
    font-size: 0.75rem;
    padding: 5px 10px;
}

/* Sidebar Styles */
.sidebar-card .card {
    border-radius: 15px;
    overflow: hidden;
}

.info-item {
    display: flex;
    align-items: center;
    padding: 10px 0;
}

.info-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    font-size: 1.2rem;
}

.info-content h6 {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.info-content small {
    color: #6c757d;
    font-size: 0.8rem;
}

.quick-actions {
    border-top: 1px solid #dee2e6;
    padding-top: 15px;
}

.quick-actions h6 {
    color: #495057;
    font-weight: 600;
    margin-bottom: 10px;
}

/* Main Content Styles */
.main-content-card .card {
    border-radius: 15px;
    overflow: hidden;
    min-height: 500px;
}

.card-header-icon {
    opacity: 0.3;
}

.selection-container {
    max-width: 600px;
    margin: 0 auto;
}

/* Enhanced Form Styles */
.form-label-enhanced {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: block;
    font-size: 1.1rem;
}

.select-wrapper-modern {
    position: relative;
}

.modern-select-enhanced {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    background: white;
    transition: all 0.3s ease;
    appearance: none;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.modern-select-enhanced:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    outline: none;
}

.select-arrow-modern {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    pointer-events: none;
}

/* Month Preview Card */
.month-preview-card {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 10px;
    margin: 20px 0;
    overflow: hidden;
}

.preview-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 12px 20px;
}

.preview-body {
    padding: 20px;
}

.preview-item {
    margin-bottom: 10px;
}

.preview-item label {
    font-weight: 600;
    color: #495057;
    display: block;
    margin-bottom: 5px;
}

.preview-item span {
    color: #2c3e50;
    font-weight: 500;
}

/* Action Buttons */
.action-buttons-container {
    margin-top: 30px;
}

.action-btn {
    height: 50px;
    font-size: 1rem;
    font-weight: 600;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.action-btn i {
    font-size: 1.1rem;
}

/* Recent Activity */
.activity-item {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
}

.activity-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.activity-month {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
}

.activity-status .badge {
    font-size: 0.7rem;
    padding: 4px 8px;
}

/* Status Colors */
.activity-item.completed {
    border-left: 4px solid #28a745;
}

.activity-item.pending {
    border-left: 4px solid #ffc107;
}

.activity-item.progress {
    border-left: 4px solid #007bff;
}

.activity-item.not_processed {
    border-left: 4px solid #6c757d;
}

/* Responsive Design */
@media (max-width: 768px) {
    .payroll-header .container-fluid {
        padding: 0 15px;
    }

    .header-stats {
        margin-top: 15px;
    }

    .stat-item {
        padding: 0 10px;
        border-left: none;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        margin-bottom: 10px;
    }

    .stat-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }

    .sidebar-card {
        margin-bottom: 20px;
    }

    .action-buttons-container .col-md-6 {
        margin-bottom: 15px;
    }

    .activity-item {
        margin-bottom: 15px;
    }
}

/* Animation */
@keyframes slideInFromTop {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.payroll-header {
    animation: slideInFromTop 0.6s ease-out;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.sidebar-card,
.main-content-card {
    animation: fadeInUp 0.8s ease-out;
}

/* Loading States */
.btn-loading {
    position: relative;
    color: transparent !important;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdown = document.getElementById('payout_month_year');
    const viewButton = document.getElementById('view-payslips-btn');
    const clearButton = document.getElementById('clear-selection-btn');
    const monthPreview = document.getElementById('month-preview');
    const previewMonth = document.getElementById('preview-month');
    const previewStatus = document.getElementById('preview-status');
    const form = document.getElementById('payslipForm');

    // Status configurations
    const statusConfig = {
        completed: {
            text: 'Completed',
            class: 'bg-success',
            icon: 'fas fa-check-circle',
            buttonEnabled: true,
            buttonText: 'View Payslips',
            buttonClass: 'btn-primary'
        },
        pending: {
            text: 'Pending',
            class: 'bg-warning text-dark',
            icon: 'fas fa-clock',
            buttonEnabled: false,
            buttonText: 'Payroll Pending',
            buttonClass: 'btn-warning'
        },
        progress: {
            text: 'In Progress',
            class: 'bg-info text-dark',
            icon: 'fas fa-spinner',
            buttonEnabled: false,
            buttonText: 'Processing...',
            buttonClass: 'btn-info'
        },
        not_processed: {
            text: 'Not Processed',
            class: 'bg-secondary',
            icon: 'fas fa-times-circle',
            buttonEnabled: false,
            buttonText: 'Not Available',
            buttonClass: 'btn-secondary'
        }
    };

    function updateUI() {
        const selectedOption = dropdown.options[dropdown.selectedIndex];
        const selectedValue = dropdown.value;

        if (!selectedValue) {
            // No selection
            hideMonthPreview();
            disableViewButton();
            hideClearButton();
            return;
        }

        const status = selectedOption.getAttribute('data-status') || 'not_processed';
        const config = statusConfig[status] || statusConfig.not_processed;

        // Update month preview
        showMonthPreview(selectedOption.text.replace(/\s*\([^)]*\)\s*$/, ''), config);

        // Update button
        updateViewButton(config);

        // Show clear button
        showClearButton();
    }

    function showMonthPreview(monthText, config) {
        previewMonth.textContent = monthText;
        previewStatus.innerHTML = `<i class="${config.icon} me-1"></i>${config.text}`;
        previewStatus.className = `badge ${config.class}`;
        monthPreview.style.display = 'block';

        // Animate in
        monthPreview.style.opacity = '0';
        monthPreview.style.transform = 'translateY(10px)';
        setTimeout(() => {
            monthPreview.style.transition = 'all 0.3s ease';
            monthPreview.style.opacity = '1';
            monthPreview.style.transform = 'translateY(0)';
        }, 10);
    }

    function hideMonthPreview() {
        if (monthPreview.style.display !== 'none') {
            monthPreview.style.opacity = '0';
            monthPreview.style.transform = 'translateY(10px)';
            setTimeout(() => {
                monthPreview.style.display = 'none';
            }, 300);
        }
    }

    function updateViewButton(config) {
        viewButton.disabled = !config.buttonEnabled;
        viewButton.innerHTML = `<i class="fas fa-eye me-2"></i><span class="btn-text">${config.buttonText}</span>`;
        viewButton.className = `btn ${config.buttonClass} btn-lg btn-block action-btn`;

        if (!config.buttonEnabled) {
            viewButton.style.cursor = 'not-allowed';
        } else {
            viewButton.style.cursor = 'pointer';
        }
    }

    function disableViewButton() {
        viewButton.disabled = true;
        viewButton.innerHTML = `<i class="fas fa-eye me-2"></i><span class="btn-text">Select Month First</span>`;
        viewButton.className = 'btn btn-secondary btn-lg btn-block action-btn';
        viewButton.style.cursor = 'not-allowed';
    }

    function showClearButton() {
        clearButton.style.display = 'block';
    }

    function hideClearButton() {
        clearButton.style.display = 'none';
    }

    function clearSelection() {
        dropdown.value = '';
        updateUI();
    }

    // Event listeners
    dropdown.addEventListener('change', updateUI);

    clearButton.addEventListener('click', clearSelection);

    // Form submission with enhanced feedback
    form.addEventListener('submit', function(e) {
        if (viewButton.disabled) {
            e.preventDefault();
            return;
        }

        // Add loading state
        viewButton.disabled = true;
        viewButton.innerHTML = '<span class="btn-loading"></span><span class="btn-text">Loading...</span>';
        viewButton.classList.add('btn-loading');

        // Allow form to submit
    });

    // Initialize UI
    updateUI();

    // Add smooth scrolling for better UX
    const scrollElements = document.querySelectorAll('[data-scroll]');
    scrollElements.forEach(element => {
        element.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('data-scroll');
            const targetElement = document.getElementById(targetId);
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Add keyboard navigation
    document.addEventListener('keydown', function(e) {
        // ESC to clear selection
        if (e.key === 'Escape' && dropdown.value) {
            clearSelection();
        }

        // Enter to submit form when button is enabled
        if (e.key === 'Enter' && !viewButton.disabled && document.activeElement === dropdown) {
            form.dispatchEvent(new Event('submit'));
        }
    });

    // Add tooltip functionality for better UX
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function(e) {
            const tooltipText = this.getAttribute('data-tooltip');
            showTooltip(e, tooltipText);
        });

        element.addEventListener('mouseleave', hideTooltip);
    });

    function showTooltip(e, text) {
        const tooltip = document.createElement('div');
        tooltip.className = 'custom-tooltip';
        tooltip.textContent = text;
        tooltip.style.position = 'fixed';
        tooltip.style.left = e.pageX + 10 + 'px';
        tooltip.style.top = e.pageY - 10 + 'px';
        tooltip.style.background = 'rgba(0,0,0,0.8)';
        tooltip.style.color = 'white';
        tooltip.style.padding = '5px 10px';
        tooltip.style.borderRadius = '4px';
        tooltip.style.fontSize = '12px';
        tooltip.style.zIndex = '9999';
        tooltip.style.pointerEvents = 'none';
        document.body.appendChild(tooltip);

        // Position tooltip
        const rect = tooltip.getBoundingClientRect();
        if (rect.right > window.innerWidth) {
            tooltip.style.left = e.pageX - rect.width - 10 + 'px';
        }
        if (rect.bottom > window.innerHeight) {
            tooltip.style.top = e.pageY - rect.height - 10 + 'px';
        }
    }

    function hideTooltip() {
        const tooltips = document.querySelectorAll('.custom-tooltip');
        tooltips.forEach(tooltip => tooltip.remove());
    }
});
</script>

@endsection