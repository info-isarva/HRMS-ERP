@extends('layouts.master')

@section('title', 'OT & Holiday Details')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header-card mb-4">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <div class="header-icon me-3">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-white font-weight-bold">OT & Holiday Calculation</h4>
                                    <p class="mb-0 text-white-50">{{ $monthName }} - Manage overtime and holiday work calculations</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="decorative-elements">
                                <div class="circle circle-1"></div>
                                <div class="circle circle-2"></div>
                                <div class="circle circle-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="p-3 d-flex justify-content-between align-items-center">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('ot-incentive.index') }}">OT & Incentive</a></li>
                            <li class="breadcrumb-item active">OT & Holiday Calculation</li>
                        </ol>
                    </nav>
                    <div>
                        <a href="{{ route('ot-incentive.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Selection
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if(!$isFinalized)
            <div class="settings-card mb-4">
                <div class="card">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Data Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Please click "Fetch Data" to retrieve overtime hours before saving or finalizing. (Holiday/Sunday work hidden for now.)
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row mb-4">
            <!-- OT Card -->
            <div class="col-md-4">
                <div class="metric-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="metric-label">Total OT Amount</p>
                                    <h3 class="metric-value text-success">{{ get_currency_symbol() }}<span id="total-ot-amount">{{ number_format($totalOtAmount, 0) }}</span></h3>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Holiday Card (hidden for now) -->
            <div class="col-md-4 d-none">
                <div class="metric-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="metric-label">Total Holiday Amount</p>
                                    <h3 class="metric-value text-info">{{ get_currency_symbol() }}<span id="total-holiday-amount">{{ number_format($totalHolidayAmount, 0) }}</span></h3>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-sun"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grand Total Card -->
            <div class="col-md-4">
                <div class="metric-card">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="metric-label">Consolidated Grand Total</p>
                                    <h3 class="metric-value text-warning">{{ get_currency_symbol() }}<span id="grand-total">{{ number_format($totalOtAmount, 0) }}</span></h3>
                                </div>
                                <div class="metric-icon">
                                    <i class="fas fa-calculator"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="error-message" class="alert alert-danger d-none mt-3"></div>

        @if(!$isFinalized)
            <div class="settings-card mb-4">
                <div class="card">
                    <div class="card-header bg-gradient-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-download me-2"></i>
                            Data Management
                        </h5>
                    </div>
                    <div class="card-body">
                        <button id="fetch-data" class="btn btn-primary">
                            <i class="fas fa-download me-2"></i>Fetch Data from API
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="settings-card mb-4">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>
                        Employee Data Management
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('ot-incentive.save-ot-holiday', [$month, $year]) }}" id="ot-form">
                        @csrf
                        <!-- Fixed Bootstrap Tab Navigation -->
                        <ul class="nav nav-tabs mb-4" id="otHolidayTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="ot-tab" data-bs-toggle="tab" data-bs-target="#ot-pane" type="button" role="tab" aria-controls="ot-pane" aria-selected="true">
                                    <i class="fas fa-clock me-2"></i>Overtime (OT)
                                </button>
                            </li>
                            <!-- Holiday Work tab hidden for now -->
                            <li class="nav-item d-none" role="presentation">
                                <button class="nav-link" id="holiday-tab" data-bs-toggle="tab" data-bs-target="#holiday-pane" type="button" role="tab" aria-controls="holiday-pane" aria-selected="false">
                                    <i class="fas fa-sun me-2"></i>Holiday Work
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="otHolidayTabContent">
                            <!-- OT Tab -->
                            <div class="tab-pane fade show active" id="ot-pane" role="tabpanel" aria-labelledby="ot-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Employee Name</th>
                                                <th>OT Rate (/hr)</th>
                                                <th>OT Hours</th>
                                                <th>Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($otEmployees as $employee)
                                                @php
                                                    $existing = $existingOt[$employee->id] ?? null;
                                                    $otHours = $existing->ot_hours ?? 0;
                                                @endphp
                                                @if(!$isFinalized || ($isFinalized && $otHours > 0))
                                                    <tr data-employee-id="{{ $employee->id }}" data-employee-tag-id="{{ $employee->employee_id }}">
                                                        <td>{{ $employee->employee_id }}</td>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ get_currency_symbol() }}{{ number_format($employee->ot_per_hour, 2) }}</td>
                                                        <td>
                                                            <input type="number"
                                                                name="ot_hours[{{ $employee->id }}]"
                                                                class="form-control ot-hours"
                                                                value="{{ $otHours }}"
                                                                data-original-value="{{ $otHours }}"
                                                                min="0"
                                                                max="200"
                                                                step="0.5"
                                                                required
                                                                {{ $isFinalized ? 'disabled' : 'readonly' }}>
                                                        </td>
                                                        <td class="ot-total">
                                                            {{ get_currency_symbol() }}{{ number_format(($existing->total_amount ?? 0), 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Holiday Tab (hidden for now) -->
                            <div class="tab-pane fade d-none" id="holiday-pane" role="tabpanel" aria-labelledby="holiday-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table">
                                        <thead>
                                            <tr>
                                                <th>Employee ID</th>
                                                <th>Employee Name</th>
                                                <th>Total Earnings</th>
                                                <th>Holiday Days</th>
                                                <th>Rate per Day</th>
                                                <th>Total Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($holidayEmployees as $employee)
                                                @php
                                                    $existing = $existingHoliday[$employee->id] ?? null;
                                                    $holidayDays = $existing->holiday_work_days ?? 0;
                                                @endphp
                                                @if(!$isFinalized || ($isFinalized && $holidayDays > 0))
                                                    <tr data-employee-id="{{ $employee->id }}" data-daily-rate="{{ $employee->daily_rate }}">
                                                        <td>{{ $employee->employee_id }}</td>
                                                        <td>{{ $employee->name }}</td>
                                                        <td>{{ get_currency_symbol() }}{{ number_format($employee->total_earnings, 2) }}</td>
                                                        <td>
                                                            <input type="number"
                                                                name="holiday_work_days[{{ $employee->id }}]"
                                                                class="form-control holiday-days"
                                                                value="{{ $holidayDays }}"
                                                                data-original-value="{{ $holidayDays }}"
                                                                min="0"
                                                                max="{{ $daysInMonth }}"
                                                                step="1"
                                                                required
                                                                {{ $isFinalized ? 'disabled' : 'readonly' }}>
                                                        </td>
                                                        <td class="holiday-rate">
                                                            {{ get_currency_symbol() }}{{ number_format($employee->daily_rate, 2) }}
                                                        </td>
                                                        <td class="holiday-total">
                                                            {{ get_currency_symbol() }}{{ number_format(($existing->total_amount ?? 0), 2) }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        @if(!$isFinalized)
                            <div class="text-center mt-4">
                                <button type="button" id="save-details" class="btn btn-danger btn-lg" disabled>
                                    <i class="fas fa-lock me-2"></i>Save & Finalize All
                                </button>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        @if($isFinalized)
            <div class="settings-card mb-4">
                <div class="card">
                    <div class="card-header bg-gradient-warning text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Finalization Status
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-lock me-2"></i> OT for this month has been finalized and cannot be edited. (Holiday/Sunday work hidden for now.)
                        </div>
                        <div class="text-center">
                            <a href="{{ route('ot-incentive.ot_and_sunday_csv_download', [$month, $year]) }}"
                                class="btn btn-outline-secondary d-none">
                                <i class="fas fa-file-csv me-2"></i> Download OT and Sunday Work CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Toast Container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toast-notification" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto" id="toast-title">Notification</strong>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toast-message"></div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="confirmModalLabel"><i class="fa fa-exclamation-triangle me-2"></i>Confirm Finalization</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to save and finalize OT for this month?</p>
                <div class="alert alert-warning">
                    <strong>Important:</strong> This action cannot be undone. Once finalized, details cannot be modified.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-finalize">Yes, Save & Finalize</button>
            </div>
        </div>
    </div>
</div>


<style>
    /* Page Header Card */
    .page-header-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .page-header-card .card {
        border: none;
        border-radius: 1rem;
        box-shadow: none;
    }

    .page-header-card .card-body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 2rem 1.5rem;
        position: relative;
        color: white;
    }

    .header-icon {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.22);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .decorative-elements {
        position: relative;
    }

    .circle {
        position: absolute;
        background: rgba(255, 255, 255, 0.08);
        border-radius: 50%;
    }

    .circle-1 {
        top: -1rem;
        right: -1rem;
        width: 6rem;
        height: 6rem;
    }

    .circle-2 {
        bottom: -1rem;
        left: -1rem;
        width: 8rem;
        height: 8rem;
    }

    .circle-3 {
        top: 50%;
        right: -2rem;
        width: 4rem;
        height: 4rem;
        transform: translateY(-50%);
    }

    /* Modern Settings Card */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
    }

    .settings-card .card {
        border: none;
        border-radius: 1rem;
        box-shadow: none;
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
        color: white !important;
    }

    .settings-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .settings-card .card-body {
        padding: 2rem;
    }

    /* Metric Cards */
    .metric-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        margin-bottom: 1rem;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .metric-card .card {
        border: none;
        border-radius: 1rem;
        box-shadow: none;
    }

    .metric-card .card-body {
        padding: 1.5rem;
    }

    .metric-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .metric-value {
        font-size: 1.75rem;
        font-weight: 700;
        margin: 0;
    }

    .metric-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.25rem;
    }

    /* Enhanced Gradient Backgrounds */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    .bg-gradient-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    .bg-gradient-danger {
        background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }

    /* Modern Button Styling */
    .btn {
        border-radius: 0.5rem;
        font-weight: 600;
        padding: 0.5rem 1.5rem;
        transition: all 0.2s ease;
    }

    .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Form Styling */
    .form-control {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        font-weight: 500;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Table Styling */
    .custom-table {
        background: white;
        margin-bottom: 0;
    }

    .custom-table thead th {
        background: #f8f9fa;
        border-bottom: 2px solid #e5e7eb;
        color: #374151;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.75rem;
        padding: 1rem 0.75rem;
    }

    .custom-table tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid #e5e7eb;
        color: #374151;
    }

    .custom-table tbody tr:hover {
        background: #f8f9fa;
    }

    /* Tab Styling */
    .nav-tabs .nav-link {
        border: none;
        border-radius: 0.5rem 0.5rem 0 0;
        font-weight: 600;
        color: #6b7280;
        padding: 0.75rem 1.5rem;
        margin-right: 0.25rem;
        transition: all 0.2s ease;
    }

    .nav-tabs .nav-link:hover {
        background: #f8f9fa;
        color: #374151;
    }

    .nav-tabs .nav-link.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-color: transparent;
    }

    .nav-tabs .nav-link i {
        margin-right: 0.5rem;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        color: #856404;
    }

    /* Icon Styling */
    .fas, .fa {
        font-weight: 900;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .metric-card .card-body {
            padding: 1rem;
        }

        .h3, .h4 {
            font-size: 1.5rem;
        }

        .circle-1, .circle-2, .circle-3 {
            display: none;
        }

        .metric-value {
            font-size: 1.5rem;
        }
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Set the API base URL from .env
window.ATTENDANCE_API_BASE_URL = '{{ env('ATTENDANCE_API_BASE_URL', 'http://default-attendance-url/api') }}';
console.log('ATTENDANCE_API_BASE_URL set to:', window.ATTENDANCE_API_BASE_URL);

document.addEventListener('DOMContentLoaded', function() {
    const fetchButton = document.getElementById('fetch-data');
    const saveButton = document.getElementById('save-details');
    const errorMessage = document.getElementById('error-message');
    const otForm = document.getElementById('ot-form');
    const daysInMonth = {{ $daysInMonth }};
    const apiToken = '{{ env("ATTENDANCE_API_TOKEN") }}';

    // Initialize Bootstrap tabs manually if needed
    const triggerTabList = [].slice.call(document.querySelectorAll('#otHolidayTab button[data-bs-toggle="tab"]'));
    triggerTabList.forEach(function (triggerEl) {
        const tabTrigger = new bootstrap.Tab(triggerEl);
        triggerEl.addEventListener('click', function (event) {
            event.preventDefault();
            tabTrigger.show();
        });
    });

    // Toast notification function
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast-notification');
        const toastMessage = document.getElementById('toast-message');
        const toastTitle = document.getElementById('toast-title');

        // Set toast content
        toastTitle.textContent = type === 'success' ? 'Success' : 'Error';
        toastMessage.textContent = message;

        // Set toast styling
        toast.classList.remove('bg-success', 'bg-danger', 'text-white');
        if (type === 'success') {
            toast.classList.add('bg-success', 'text-white');
        } else {
            toast.classList.add('bg-danger', 'text-white');
        }

        // Show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
    }

    // Check for flash messages
    @if(session('success'))
        showToast("{{ session('success') }}", 'success');
    @endif

    @if(session('error'))
        showToast("{{ session('error') }}", 'error');
    @endif

    // Initialize modal
    const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

    // Handle save button click
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            confirmModal.show();
        });

        document.getElementById('confirm-finalize').addEventListener('click', function() {
            confirmModal.hide();
            otForm.submit();
        });
    }

    if (fetchButton) {
        fetchButton.addEventListener('click', function() {
            fetchButton.disabled = true;
            errorMessage.classList.add('d-none');
            errorMessage.textContent = '';

            // Fetch OT data with API token header (aligned with payroll/attendance)
            fetch(`${window.ATTENDANCE_API_BASE_URL}/payroll/overtime-data?month={{ $month }}&year={{ $year }}` , {
                method: 'GET',
                headers: {
                    'X-API-Token': apiToken,
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            throw new Error(data.message || `HTTP error! status: ${response.status}`);
                        });
                    }
                    return response.json();
                })
                .then(otData => {
                    let successCount = 0;
                    let errorMessages = [];

                    // Process OT data (API returns { success: true, data: [...] })
                    if ((otData.status === 'success' || otData.success === true) && Array.isArray(otData.data) && otData.data.length > 0) {
                        otData.data.forEach(record => {
                            // Match by payroll employee id
                            const rows = document.querySelectorAll('#ot-pane tr[data-employee-id]');
                            rows.forEach(row => {
                                const empId = row.getAttribute('data-employee-id');
                                if (empId === String(record.employee_payroll_id)) {
                                    const otInput = row.querySelector('.ot-hours');
                                    if (otInput) {
                                        // API field is overtime_hours (fallback to older keys just in case)
                                        const otHours = parseFloat((record.overtime_hours ?? record.overtime_hr ?? record.overtimeHours ?? 0)) || 0;
                                        otInput.value = otHours;
                                        const event = new Event('input', { bubbles: true });
                                        otInput.dispatchEvent(event);
                                        successCount++;
                                    }
                                }
                            });
                        });
                    } else {
                        errorMessages.push('OT: ' + (otData.message || 'No data found'));
                    }

                    // Show results
                    if (successCount > 0) {
                        showToast(`Fetched ${successCount} records successfully!`, 'success');
                        if (saveButton) saveButton.disabled = false;
                    }

                    if (errorMessages.length > 0) {
                        showToast(errorMessages.join(', '), 'error');
                        errorMessage.textContent = errorMessages.join(', ');
                        errorMessage.classList.remove('d-none');
                    }
                })
                .catch(error => {
                    console.error('Error fetching data:', error);
                    showToast(error.message || 'Failed to fetch data', 'error');
                    errorMessage.textContent = error.message || 'Failed to fetch data. Please try again.';
                    errorMessage.classList.remove('d-none');
                })
                .finally(() => {
                    fetchButton.disabled = false;
                });
        });
    }

    // Calculate OT totals
    document.querySelectorAll('.ot-hours').forEach(input => {
        input.addEventListener('input', function() {
            const rateText = this.closest('tr').children[2].textContent;
            const rate = parseFloat(rateText.replace(new RegExp('[\\' + window.globalCurrencySymbol + ',]', 'g'), ''));
            const hours = parseFloat(this.value) || 0;
            const total = rate * hours;
            this.closest('tr').querySelector('.ot-total').textContent = window.globalCurrencySymbol + total.toFixed(2);
            updateTotals();
        });
    });

    // Calculate holiday totals
    document.querySelectorAll('.holiday-days').forEach(input => {
        input.addEventListener('input', function() {
            const row = this.closest('tr');
            const dailyRate = parseFloat(row.getAttribute('data-daily-rate'));
            const days = parseFloat(this.value) || 0;
            const total = dailyRate * days;

            row.querySelector('.holiday-rate').textContent = window.globalCurrencySymbol + dailyRate.toFixed(2);
            row.querySelector('.holiday-total').textContent = window.globalCurrencySymbol + total.toFixed(2);
            updateTotals();
        });
    });

    // Update all totals
    function updateTotals() {
        let totalOt = 0;
        document.querySelectorAll('.ot-total').forEach(cell => {
            totalOt += parseFloat(cell.textContent.replace(new RegExp('[\\' + window.globalCurrencySymbol + ',]', 'g'), '')) || 0;
        });

        // Holiday totals disabled for now
        let totalHoliday = 0;

        if (otEl) otEl.textContent = window.globalCurrencySymbol + totalOt.toLocaleString(window.globalCurrencyLocale);

        const holidayEl = document.getElementById('total-holiday-amount');
        if (holidayEl) holidayEl.textContent = window.globalCurrencySymbol + totalHoliday.toLocaleString(window.globalCurrencyLocale);

        const grandEl = document.getElementById('grand-total');
        if (grandEl) grandEl.textContent = window.globalCurrencySymbol + (totalOt + totalHoliday).toLocaleString(window.globalCurrencyLocale);
    }
});
</script>

@endsection