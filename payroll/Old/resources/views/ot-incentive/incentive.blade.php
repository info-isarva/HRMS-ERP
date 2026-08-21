@extends('layouts.master')

@section('title', 'Incentive Details')

@section('content')

<div class="page-wrapper">
    <div class="content container-fluid">
        <h2>Incentive Calculation - {{ $monthName }}</h2>

        <a href="{{ route('ot-incentive.index') }}" class="btn btn-secondary mb-3">
            « Back to Month Selection
        </a>

        @if(!$isFinalized)
        <!-- Instruction message -->
        <div class="alert alert-info mt-3">
            Please click "Fetch Attendance Data" to retrieve present days from the attendance system before saving or finalizing.
        </div>
        @endif

        <div class="row mb-3">
            <div class="col-md-3">
                <div class="card text-white border-0 shadow-sm" style="background-color: #4caf50;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Total Incentive to be Paid</h6>
                                <h4 class="mb-0">{{ get_currency_symbol() }}<span id="total-incentive-amount">{{ number_format($totalIncentiveAmount, 0) }}</span></h4>
                            </div>
                            <i class="fa fa-money fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white border-0 shadow-sm" style="background-color: #987fe5;">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-1">Total Employees</h6>
                                <h4 class="mb-0">{{ $totalEmployees }}</h4>
                            </div>
                            <i class="fa fa-users fa-2x opacity-75"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Error message container -->
        <div id="error-message" class="alert alert-danger d-none mt-3"></div>

        <!-- Fetch Attendance Data button - only show if not finalized -->
        @if(!$isFinalized)
        <button id="fetch-attendance-data" class="btn btn-primary mb-3">Fetch Attendance Data</button>
        @endif

        <form method="POST" action="{{ route('ot-incentive.save-incentive', [$month, $year]) }}" id="incentive-form">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="alert alert-info">
                        Total Working Days: {{ $totalDays }}
                    </div>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Monthly Incentive</th>
                                <th>Daily Rate</th>
                                <th>Incentive Days</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($employees as $employee)
                                @php
                                    $existing = $existingIncentive[$employee->id] ?? null;
                                    $dailyRate = ceil($employee->incentive_per_month / $totalDays);
                                @endphp
                                <tr data-employee-id="{{ $employee->id }}" data-employee-tag-id="{{ $employee->employee_id }}">
                                    <td>{{ $employee->employee_id }}</td>
                                    <td>{{ $employee->name }}</td>
                                    {{-- <td>{{ get_currency_symbol() }}{{ number_format($employee->incentive_per_month, 2) }}</td> --}}
                                    <td>{{ get_currency_symbol() }}{{ number_format($employee->incentive_per_month, 2) }}</td>

                                    <td>{{ get_currency_symbol() }}{{ number_format($dailyRate) }}</td>
                                    <td>
                                        <input type="number" 
                                            name="incentive_days[{{ $employee->id }}]" 
                                            class="form-control incentive-days"
                                            value="{{ $existing->incentive_days ?? 0 }}" 
                                            data-original-value="{{ $existing->incentive_days ?? 0 }}"
                                            min="0" 
                                            max="{{ $totalDays }}"
                                            step="0.5"
                                            required
                                            {{ $isFinalized ? 'disabled' : 'readonly' }}>
                                    </td>
                                    <td class="incentive-total">
                                        {{ get_currency_symbol() }}{{ number_format(($existing->total_amount ?? 0), 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if(!$isFinalized)
                        <button type="button" id="save-incentive-details" class="btn btn-danger" disabled>Save & Finalize Incentive</button>                    
                    @endif
                </div>
            </div>
        </form>

        @if($isFinalized)
            <div class="alert alert-warning mt-3">
                <i class="fa fa-lock me-2"></i> Incentive for this month has been finalized and cannot be edited. 
            </div>
            <div>
                <a href="{{ route('ot-incentive.incentive_csv_download', [$month, $year]) }}" 

                    class="btn btn-outline-secondary">

                    <i class="fa fa-file-text me-2"></i> Download Incentive(Bata) CSV

                </a>
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
                <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to save and finalize Incentive for this month?</p>
                <div class="alert alert-warning">
                    <strong>Important:</strong> This action cannot be undone. Once finalized, Incentive details cannot be modified.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-finalize">Yes, Save & Finalize</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    const fetchButton = document.getElementById('fetch-attendance-data');
        const saveButton = document.getElementById('save-incentive-details');
        const errorMessage = document.getElementById('error-message');
        const tableBody = document.querySelector('tbody');
        const incentiveForm = document.getElementById('incentive-form');
    const month = {{ $month }};
    const year = {{ $year }};
    // Match payroll/resources/views/payroll/attendance.blade.php pattern
    const apiUrl = '{{ env("ATTENDANCE_API_BASE_URL") }}/payroll/attendance-data';
    const apiToken = '{{ env("ATTENDANCE_API_TOKEN") }}';
        
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

        // Check for flash messages from backend
        @if(session('success'))
            showToast("{{ session('success') }}", 'success');
        @endif
        
        @if(session('error'))
            showToast("{{ session('error') }}", 'error');
        @endif

        // Initialize modal and log for debugging
        const confirmModalElement = document.getElementById('confirmModal');
        const confirmModal = new bootstrap.Modal(confirmModalElement);
        console.log('Modal initialized:', confirmModal);

        // Fallback for close button
        const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"], [data-dismiss="modal"]');
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                console.log('Close button clicked');
                confirmModal.hide();
            });
        });
        
        // Handle save button click
        if (saveButton) {
            saveButton.addEventListener('click', function() {
                console.log('Save button clicked, showing modal');
                confirmModal.show();
            });
            
            // Handle confirm button in modal
            document.getElementById('confirm-finalize').addEventListener('click', function() {
                console.log('Confirm finalize clicked');
                confirmModal.hide();
                incentiveForm.submit();
            });
        }

        if (fetchButton) {
            fetchButton.addEventListener('click', function() {
                // Show loading state
                fetchButton.disabled = true;
                tableBody.classList.add('loading');
                errorMessage.classList.add('d-none');
                errorMessage.textContent = '';

                // Fetch attendance records from API with token header (same as payroll/attendance)
                fetch(`${apiUrl}?month=${month}&year=${year}`, {
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
                    .then(data => {
                        console.log("API Response:", data);

                        if ((data.status === 'success' || data.success === true) && Array.isArray(data.data) && data.data.length > 0) {
                            // Enable save button
                            if (saveButton) saveButton.disabled = false;
                            
                            // Process the returned data - WITHOUT changing readonly state
                            data.data.forEach(record => {
                                console.log("Processing record:", record);
                                
                                // Find the row with matching employee ID (using data-employee-id)
                                const rows = document.querySelectorAll('tr[data-employee-id]');
                                rows.forEach(row => {
                                    const empId = row.getAttribute('data-employee-id');
                                    console.log(`Comparing local payroll id ${empId} with API payroll_id ${record.payroll_id}`);
                                    
                                    if (empId === String(record.payroll_id)) {
                                        console.log(`Match found for payroll_id ${empId}`);
                                        
                                        // Update the incentive days input
                                        const incentiveInput = row.querySelector('.incentive-days');
                                        if (incentiveInput) {
                                            // Incentive days should come from salary_days (not present_days)
                                            const daysVal = parseFloat(record.salary_days) || 0;
                                            incentiveInput.value = daysVal;
                                            console.log(`Set incentive days ${daysVal} for payroll_id ${empId}`);
                                            
                                            // Trigger the calculation for total amount
                                            const event = new Event('input', { bubbles: true });
                                            incentiveInput.dispatchEvent(event);
                                        }
                                    } else {
                                        console.log(`No match for payroll_id ${record.payroll_id} with row ${empId}`);
                                    }
                                });
                            });
                            
                            // Show success toast
                            showToast('Attendance data fetched successfully!', 'success');
                        } else {
                            // Show error message as toast
                            showToast(data.message || 'No attendance data found', 'error');
                            errorMessage.textContent = data.message || 'No attendance data found';
                            errorMessage.classList.remove('d-none');
                        }
                        
                        // Remove loading state
                        tableBody.classList.remove('loading');
                        fetchButton.disabled = false;
                    })
                    .catch(error => {
                        console.error('Error fetching attendance records:', error);
                        showToast(error.message || 'Failed to fetch attendance records', 'error');
                        errorMessage.textContent = error.message || 'Failed to fetch attendance records. Please try again.';
                        errorMessage.classList.remove('d-none');
                        tableBody.classList.remove('loading');
                        fetchButton.disabled = false;
                    });
            });
        }

        // Calculate total amount when incentive days change
        document.querySelectorAll('.incentive-days').forEach(input => {
            input.addEventListener('input', function() {
                const monthlyText = this.closest('tr').children[2].textContent;
                const monthly = parseFloat(monthlyText.replace(/[{{ get_currency_symbol() }},]/g, ''));
                const days = parseFloat(this.value) || 0;
                // const total = (monthly / {{ $totalDays }}) * days;
                // this.closest('tr').querySelector('.incentive-total').textContent = '{{ get_currency_symbol() }}' + Math.round(total);
                // const dailyRate = Math.ceil(monthly / {{ $totalDays }});
                // const total = dailyRate * days;
                // this.closest('tr').querySelector('.incentive-total').textContent = '{{ get_currency_symbol() }}' + total.toFixed(2);
                const dailyRate = Math.ceil(monthly / {{ $totalDays }});
                const total = Math.min(dailyRate * days, 5000);

                this.closest('tr').querySelector('.incentive-total').textContent = '{{ get_currency_symbol() }}' + total.toFixed(2);

                
                // Update total incentive amount
                let totalIncentiveAmount = 0;
                document.querySelectorAll('.incentive-total').forEach(cell => {
                    const amount = parseFloat(cell.textContent.replace(/[{{ get_currency_symbol() }},]/g, '')) || 0;
                    totalIncentiveAmount += amount;
                });
                document.getElementById('total-incentive-amount').textContent = totalIncentiveAmount.toLocaleString();
            });
        });
    });
</script>

@endsection