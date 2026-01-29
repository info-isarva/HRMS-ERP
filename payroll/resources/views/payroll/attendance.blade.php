@extends('layouts.master')

@section('title', 'Employee Attendance')

@section('content')

<style>
    /* Page Header Card */
    .page-header-card { background: white; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 2rem; }
    .page-header-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 2.5rem 2rem; position: relative; }
    .page-header-pattern { position: absolute; inset: 0; background: rgba(0,0,0,0.05); }
    .page-header-circle-1 { position: absolute; top: -1rem; right: -1rem; width:6rem; height:6rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-circle-2 { position:absolute; bottom:-1rem; left:-1rem; width:8rem; height:8rem; background: rgba(255,255,255,0.1); border-radius:50%; }
    .page-header-icon-box { width:4rem; height:4rem; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:1rem; display:flex; align-items:center; justify-content:center; }
    .page-header-title { font-size:1.875rem; font-weight:700; color:white; margin-bottom:0.5rem; }
    .page-header-subtitle { font-size:1rem; color: rgba(255,255,255,0.9); margin:0; }

    /* Modern Card Styles */
    .modern-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; overflow: hidden; }
    .modern-card-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 1.25rem 1.5rem; border-bottom: none; }
    .modern-card-header h4 { color: white; font-weight: 600; margin: 0; font-size: 1.125rem; }
    .modern-card-body { padding: 1.5rem; }

    /* Table Styling */
    .modern-table { width: 100%; border-collapse: collapse; }
    .modern-table thead th { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; font-weight: 600; padding: 1rem 0.75rem; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; }
    .modern-table tbody td { padding: 0.75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; font-size: 0.875rem; }
    .modern-table tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }

    /* Form Elements */
    .modern-input { width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s; }
    .modern-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
    .btn-modern-light { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-light:hover { background: #e9ecef; }

    /* Badge Styles */
    .badge-modern { padding: 0.375rem 0.75rem; font-weight: 500; border-radius: 0.375rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .badge-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }
    .badge-modern-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: white; }
    .badge-modern-light { background: #f8f9fa; color: #6b7280; }

    /* Alert Styles */
    .alert-modern { border-radius: 0.5rem; border: none; padding: 1rem 1.5rem; margin-bottom: 1rem; }
    .alert-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }

    /* Progress Steps Container */
    .steps-container { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; }

    /* Avatar Styling */
    .avatar-modern { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #e5e7eb; overflow: hidden; }
    .avatar-modern img { width: 100%; height: 100%; object-fit: cover; }

    /* API Status and Controls */
    .api-controls { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .api-status-section { display: flex; align-items: center; gap: 0.5rem; }

    /* Floating Navigation Buttons */
    .floating-btn { position: fixed !important; right: 20px !important; width: 56px !important; height: 56px !important; border-radius: 50% !important; border: none !important; color: white !important; cursor: pointer !important; box-shadow: 0 4px 16px rgba(0,0,0,0.3) !important; display: flex !important; align-items: center !important; justify-content: center !important; z-index: 9999 !important; transition: all 0.3s ease !important; outline: none !important; opacity: 0.9 !important; }
    .floating-btn-top { bottom: 140px !important; background: linear-gradient(135deg, #667eea, #764ba2) !important; }
    .floating-btn-bottom { bottom: 70px !important; background: linear-gradient(135deg, #10b981, #059669) !important; }
    .floating-btn:hover { opacity: 1 !important; box-shadow: 0 6px 20px rgba(0,0,0,0.4) !important; transform: scale(1.1) !important; }
    .floating-btn:active { transform: scale(0.95) !important; }

    /* Additional Styles */
    .table-hover tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .api-source { font-size: 0.8rem; }
    .fetching { animation: pulse 1.5s infinite; }
    @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }
    .toast { z-index: 9999; }

    /* Action buttons container */
    .action-buttons-container { 
        position: sticky; 
        bottom: 0; 
        background: white; 
        padding: 1rem 1.25rem; 
        border-top: 1px solid #e5e7eb; 
        box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
        margin-top: 1.5rem;
        z-index: 10;
    }

    @media (max-width: 768px) {
        .action-buttons-container { padding: 0.875rem 1rem; }
        .action-buttons-container .btn-modern { padding: 0.625rem 1.25rem; font-size: 0.875rem; }
    }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">

        <!-- Page Header -->
        <div class="page-header-card mb-4">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="position-relative">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <div class="d-flex align-items-center">
                                <div class="page-header-icon-box me-4">
                                    <i class="fas fa-calendar-check text-white" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Employee Attendance</h1>
                                    <p class="page-header-subtitle">Manage and track employee attendance records for payroll processing</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <!-- <div class="steps-container mb-4"> -->
        <div class="mb-4">
            <div class="steps">
                @include('payroll.partials.progress-steps', [
                    'currentStep' => $currentStep, 
                    'month' => $month, 
                    'year' => $year,
                    'attendanceSaved' => $attendanceSaved ?? false,
                    'salariesReviewed' => false
                ])
            </div>
        </div>

        <!-- Attendance Management Card -->
        <div class="modern-card">
            <div class="modern-card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h4 class="mb-2"><i class="fas fa-users me-2"></i>Employee Attendance for {{ $monthName }}</h4>
                        <div class="d-flex gap-2 align-items-center flex-wrap">
                            @if(isset($isFinalized) && $isFinalized)
                                <span class="badge badge-modern badge-modern-success">
                                    <i class="fa fa-lock me-1"></i> Payroll Finalized - Attendance Locked
                                </span>
                            @else
                                <span class="badge badge-modern badge-modern-warning">
                                    <i class="fa fa-edit me-1"></i> Editable
                                </span>
                            @endif
                            <span class="badge badge-modern badge-modern-light">
                                <i class="fa fa-calendar-alt me-1"></i> Total Working Days: {{ $total_days }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modern-card-body">
                <!-- API Controls -->
                <div class="d-flex justify-content-between align-items-center mb-4 p-3" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(29, 78, 216, 0.05) 100%); border-radius: 0.5rem; border-left: 4px solid #3b82f6;">
                    <div class="d-flex align-items-center gap-2">
                        <button id="fetch-api-btn" class="btn btn-sm" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: white; border: none; padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500;">
                            <i class="fa fa-sync me-2"></i> Fetch from Attendance System
                        </button>
                        <span id="api-status" class="badge" style="background: #f3f4f6; color: #6b7280; font-weight: 500; padding: 0.5rem 0.875rem; border-radius: 0.375rem;">
                            <i class="fa fa-clock me-1"></i> Not fetched
                        </span>
                    </div>
                    <div>
                        <span class="badge" style="background: linear-gradient(135deg, #10b981, #059669); color: white; font-weight: 500; padding: 0.5rem 0.875rem; border-radius: 0.375rem;">
                            <i class="fas fa-link me-1"></i> API Connected
                        </span>
                    </div>
                </div>

                <!-- Attendance Form -->
                <form action="{{ route('payroll.save-attendance', [$month, $year]) }}" method="POST" id="attendance-form">
                    @csrf
                    <input type="hidden" name="location_id" value="{{ $locationId ?? '' }}">
                    <div class="table-responsive">
                        <table class="modern-table table-hover">
                            <thead>
                                <tr>
                                    <th style="width: 50px">#</th>
                                    <th>Profile</th>
                                    <th>Employee Name</th>
                                    <th>Employee ID</th>
                                    <th>Employee Designation</th>
                                    <th class="text-center">Total Payable Days</th>
                                    <th class="text-center">Present Days</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employees as $employee)
                                    @php
                                        $isFFSImmediate = $employee->exitDetails->contains(function ($detail) {
                                            return $detail->settlement_mode === 'immediate';
                                        });
                                        $rowStyle = $isFFSImmediate ? 'background-color: #fff3cd;' : '';
                                    @endphp
                                <tr style="{{ $rowStyle }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="avatar-modern">
                                            <img src="{{ asset($employee->profile_image ?? 'assets/img/user-icon.webp') }}"
                                                 alt="Profile">
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $employee->name }}</strong>
                                        @if(in_array($employee->id, $heldEmployeeIds ?? []))
                                            <span class="badge badge-modern badge-modern-warning ms-2">
                                                <i class="fas fa-hand-holding-usd me-1"></i> Salary Hold
                                            </span>
                                        @endif
                                    </td>
                                    <td><code>{{ $employee->employee_id }}</code></td>
                                    <td>{{ $departments[$employee->department] ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-modern badge-modern-info">{{ $total_days }}</span>
                                    </td>
                                    <td class="text-center" style="max-width: 150px">
                                        <input type="number"
                                               name="present_days[{{ $employee->id }}]"
                                               class="modern-input text-center api-input"
                                               data-employee-id="{{ $employee->employee_id }}"
                                               data-payroll-id="{{ $employee->id }}"
                                               data-local-id="{{ $employee->id }}"
                                               max="{{ $total_days }}"
                                               required
                                               value="{{ $existingAttendances[$employee->id]->employee_worked_days ?? old('present_days.'.$employee->id, '') }}"
                                               placeholder="Enter days"
                                               {{ (isset($isFinalized) && $isFinalized) ? 'disabled readonly' : '' }}>

                                        @if(isset($existingAttendances[$employee->id]))
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-clock me-1"></i>
                                                Last saved: {{ $existingAttendances[$employee->id]->updated_at->format('M d, Y h:i A') }}
                                            </small>
                                        @endif
                                        <small class="api-source text-success d-block mt-1" style="display: none">
                                            <i class="fa fa-check-circle me-1"></i> Fetched from API (Salary Days)
                                        </small>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="fas fa-users fa-2x mb-2"></i>
                                            <p>No employees found</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons-container">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('payroll.index') }}" class="btn-modern btn-modern-light">
                        <i class="fa fa-arrow-left me-2"></i> Back to Month Selection
                    </a>

                    @if(isset($isFinalized) && $isFinalized)
                        <button type="button" class="btn-modern btn-modern-secondary" disabled>
                            <i class="fa fa-lock me-2"></i> Attendance Locked (Payroll Finalized)
                        </button>
                    @else
                        <button type="submit" form="attendance-form" class="btn-modern btn-modern-success">
                            <i class="fa fa-save me-2"></i> Save & Proceed to Salary Review
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <!-- Information Alert -->
        <div class="alert alert-modern alert-modern-info mt-4">
            <i class="fa fa-info-circle me-2"></i>
            <strong>Attendance Guidelines:</strong> Total Payable Days are calculated based on the number of days in {{ $monthName }}.
            Please enter actual present days for each employee. You can also fetch attendance data automatically from the connected attendance system.
        </div>
    </div>
</div>

    </div>
</div>

<!-- Floating Navigation Buttons -->
<button id="move-to-top" class="floating-btn floating-btn-top" title="Move to Top">
    <i class="fa fa-arrow-up"></i>
</button>
<button id="move-to-last" class="floating-btn floating-btn-bottom" title="Move to Bottom">
    <i class="fa fa-arrow-down"></i>
</button>
@endsection
@section('script')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    const month = {{ $month }};
    const year = {{ $year }};
    const apiUrl = '{{ env("ATTENDANCE_API_BASE_URL") }}/payroll/attendance-data';
    const apiToken = '{{ env("ATTENDANCE_API_TOKEN") }}';

    // Move to Top button functionality
    $('#move-to-top').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    });

    // Move to Last button functionality
    $('#move-to-last').click(function() {
        $('html, body').animate({
            scrollTop: $(document).height()
        }, 500);
    });

    // Fetch API data button
    $('#fetch-api-btn').click(function() {
        fetchAttendanceData();
    });

    function fetchAttendanceData() {
        // Show loading state
        $('#fetch-api-btn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin me-2"></i> Fetching...');
        $('#api-status').removeClass('badge-modern-success badge-modern-danger').addClass('fetching badge-modern-warning').text('Fetching...');

        // Make AJAX request to attendance API
        $.ajax({
            url: apiUrl,
            type: 'GET',
            headers: {
                'X-API-Token': apiToken,
                'Content-Type': 'application/json'
            },
            data: {
                month: month,
                year: year
            },
            dataType: 'json',
            timeout: 30000, // 30 seconds timeout
            success: function(response) {
                console.log('API Response:', response);

                // Check if response has the expected structure
                if (response && response.success && response.data && response.data.length > 0) {
                    let fetchedCount = 0;
                    let updatedCount = 0;

                    // Loop through API response data
                    response.data.forEach(record => {
                        // Match by payroll_id from attendance API to local employee id
                        const $input = $(`.api-input[data-payroll-id="${record.payroll_id}"]`);

                        if ($input.length) {
                            fetchedCount++;

                            // Use salary_days as present days
                            if (record.salary_days !== undefined && record.salary_days !== null) {
                                // Parse the salary_days value and convert to integer
                                const salaryDays = Math.min(
                                    Math.max(0, parseInt(record.salary_days)),
                                    {{ $total_days }}
                                );
                                $input.val(salaryDays);
                                $input.siblings('.api-source').show();
                                updatedCount++;

                                console.log(`✅ Updated employee ${record.name} (Payroll ID: ${record.payroll_id}) with ${salaryDays} salary days`);
                            }
                        } else {
                            console.warn(`❌ No input found for employee: ${record.name} (Payroll ID: ${record.payroll_id})`);
                        }
                    });

                    // Update status
                    $('#api-status').removeClass('fetching badge-modern-warning').addClass('badge-modern-success')
                        .text(`Updated ${updatedCount}/${fetchedCount} records`);

                    if (updatedCount > 0) {
                        showToast('success', `Successfully updated ${updatedCount} attendance records from API`);
                    } else {
                        showToast('warning', `Found ${fetchedCount} API records but no matching employees to update`);
                    }
                } else {
                    $('#api-status').removeClass('fetching badge-modern-warning').addClass('badge-modern-danger').text('No data found');
                    showToast('warning', 'No attendance records found for this month');
                }
            },
            error: function(xhr, status, error) {
                console.error('API Error:', {xhr, status, error});
                $('#api-status').removeClass('fetching badge-modern-warning').addClass('badge-modern-danger').text('Error fetching data');

                let errorMessage = 'Failed to fetch attendance data';

                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response && response.message) {
                        errorMessage = response.message; // Use the dynamic message from API
                    }
                } catch (e) {
                    console.warn('Could not parse error response:', e);
                    // Fallback to generic messages based on status code
                    if (xhr.status === 0) {
                        errorMessage += ': Network error or CORS issue';
                    } else if (xhr.status === 401) {
                        errorMessage += ': Invalid API token';
                    } else if (xhr.status === 404) {
                        errorMessage += ': API endpoint not found';
                    } else if (xhr.status === 500) {
                        errorMessage += ': Server error';
                    } else {
                        errorMessage += `: ${error}`;
                    }
                }

                showToast('error', errorMessage);
            },
            complete: function() {
                $('#fetch-api-btn').prop('disabled', false).html('<i class="fa fa-sync me-2"></i> Fetch from Attendance System');
            }
        });
    }

    function showToast(type, message) {
        // Remove any existing toasts
        $('.toast').remove();

        // Map type to Bootstrap classes
        const typeClass = {
            'success': 'bg-success',
            'error': 'bg-danger',
            'warning': 'bg-warning'
        }[type] || 'bg-info';

        // Create toast HTML
        const toastHtml = `
            <div class="toast align-items-center text-white ${typeClass} border-0 position-fixed" role="alert" aria-live="assertive" aria-atomic="true" style="top: 20px; right: 20px; z-index: 9999;">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" onclick="$(this).closest('.toast').remove();" aria-label="Close">×</button>
                </div>
            </div>
        `;

        // Append to body and show
        const $toast = $(toastHtml);
        $('body').append($toast);

        // Show toast with fade in effect
        $toast.fadeIn();

        // Auto hide after 5 seconds
        setTimeout(() => {
            $toast.fadeOut(() => {
                $toast.remove();
            });
        }, 5000);
    }

    // Optional: Auto-fetch on page load (uncomment if needed)
    // fetchAttendanceData();
});
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
@endsection

@section('style')
<style>
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .card-header {
        border-radius: 0.375rem 0.375rem 0 0;
    }
    .avatar-sm img {
        border: 2px solid #dee2e6;
    }
    .api-source {
        font-size: 0.8rem;
    }
    #api-status {
        font-size: 0.9rem;
        padding: 5px 10px;
    }
    .fetching {
        animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    .toast {
        z-index: 9999;
    }
    
    /* Floating Navigation Buttons (Bootstrap 4 compatible) */
    .floating-btn {
        position: fixed !important;
        right: 20px !important;
        width: 56px !important;
        height: 56px !important;
        border-radius: 50% !important;
        border: none !important;
        color: #fff !important;
        font-size: 20px !important;
        cursor: pointer !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.3) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        z-index: 9999 !important;
        transition: all 0.3s ease !important;
        outline: none !important;
        opacity: 0.9 !important;
    }
    .floating-btn-top {
        bottom: 140px !important;
        background: linear-gradient(135deg, #007bff, #0056b3) !important;
    }
    .floating-btn-bottom {
        bottom: 70px !important;
        background: linear-gradient(135deg, #28a745, #1e7e34) !important;
    }
    .floating-btn:hover {
        opacity: 1 !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.4) !important;
        transform: scale(1.1) !important;
    }
    .floating-btn:active {
        transform: scale(0.95) !important;
    }
    .floating-btn i {
        pointer-events: none !important;
        font-size: 18px !important;
    }
</style>
@endsection