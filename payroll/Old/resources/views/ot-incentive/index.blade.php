@extends('layouts.master')

@section('title', 'OT and Incentive Details')

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
                                    <i class="fas fa-coins text-white"></i>
                                </div>
                                <div>
                                    <h4 class="mb-0 text-white font-weight-bold">OT & Incentive Management</h4>
                                    <p class="mb-0 text-white-50">Manage overtime and incentive calculations for employees</p>
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
                            <li class="breadcrumb-item active">OT & Incentive Management</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="settings-card mb-4">
            <div class="card">
                <div class="card-header bg-gradient-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Selection Criteria
                    </h5>
                </div>
                <div class="card-body">
                <form method="GET" action="{{ route('ot-incentive.index') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="month">Select Month:</label>
                                <select name="month" id="month" class="form-control form-select" required>
                                    <option value="">-- Select Month --</option>
                                    @foreach($availableMonths as $month)
                                        <option value="{{ $month['month'] }}-{{ $month['year'] }}" 
                                                data-status="{{ $month['status'] ?? 'not_processed' }}">
                                            {{ $month['label'] }}
                                            @if(isset($month['status']))
                                                @if($month['status'] === 'completed')
                                                    - Payroll Completed
                                                @elseif($month['status'] === 'pending')
                                                    - Pending
                                                @elseif($month['status'] === 'progress')
                                                    - In Progress
                                                @else
                                                    - Not Processed
                                                @endif
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="type">Select Type:</label>
                                <select name="type" id="type" class="form-control form-select" required>
                                    <option value="">-- Select Type --</option>
                                    @if(!isset($fyContext) || $fyContext['isFinancialYearEditable'])
                                        <!-- <option value="ot">Over Time (OT) and Sunday Work</option> -->
                                         <option value="ot">Over Time (OT) </option>
                                        <option value="incentive">Incentive</option>
                                    @else
                                        <option value="ot-view">View OT & Sunday Work Data</option>
                                        <option value="ot-view">View OT Data</option>
                                        <option value="incentive-view">View Incentive Data</option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" id="proceed-btn" class="btn btn-primary form-control" disabled>Proceed</button>
                            </div>
                        </div>
                    </div>
                </form>

                

                <!-- Status Display Section -->

                <div id="statusSection" class="row mt-4" style="display: none;">

                    <div class="col-md-12">

                        <div class="alert alert-info">

                            <h5><i class="fa fa-info-circle"></i> Month Status</h5>

                            <div class="row">

                                <div class="col-md-6">

                                    <strong>OT and Sunday work Status:</strong> 

                                    <span id="otStatus" class="badge bg-secondary">-</span>

                                </div>

                                <div class="col-md-6">

                                    <strong>Incentive Status:</strong> 

                                    <span id="incentiveStatus" class="badge bg-secondary">-</span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

                </div>

            </div>

        </div>

    </div>

</div>



<script>

document.addEventListener('DOMContentLoaded', function() {

    const monthSelect = document.getElementById('month');

    const statusSection = document.getElementById('statusSection');

    const otStatusSpan = document.getElementById('otStatus');

    const incentiveStatusSpan = document.getElementById('incentiveStatus');

    

    // Set selected values if coming back with errors

    const urlParams = new URLSearchParams(window.location.search);

    const selectedMonth = urlParams.get('month');

    const selectedType = urlParams.get('type');

    

    if (selectedMonth) {

        monthSelect.value = selectedMonth;

        fetchMonthStatus(selectedMonth);

    }

    

    if (selectedType) {

        document.getElementById('type').value = selectedType;

    }

    

    // Add event listener for month selection

    monthSelect.addEventListener('change', function() {

        const selectedValue = this.value;

        

        if (selectedValue) {

            fetchMonthStatus(selectedValue);

        } else {

            statusSection.style.display = 'none';

        }

    });

    

    function fetchMonthStatus(monthValue) {

        // Show loading state

        statusSection.style.display = 'block';

        otStatusSpan.textContent = 'Loading...';

        otStatusSpan.className = 'badge bg-secondary';

        incentiveStatusSpan.textContent = 'Loading...';

        incentiveStatusSpan.className = 'badge bg-secondary';

        

        // Make AJAX request

        fetch('{{ route("ot-incentive.get-month-status") }}', {

            method: 'POST',

            headers: {

                'Content-Type': 'application/json',

                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')

            },

            body: JSON.stringify({

                month: monthValue

            })

        })

        .then(response => response.json())

        .then(data => {

            if (data.success) {

                // Update OT Status

                otStatusSpan.textContent = data.data.ot_status;

                otStatusSpan.className = data.data.ot_finalized ? 'badge bg-success' : 'badge bg-warning text-dark';

                

                // Update Incentive Status

                incentiveStatusSpan.textContent = data.data.incentive_status;

                incentiveStatusSpan.className = data.data.incentive_finalized ? 'badge bg-success' : 'badge bg-warning text-dark';

            } else {

                otStatusSpan.textContent = 'Error';

                otStatusSpan.className = 'badge bg-danger';

                incentiveStatusSpan.textContent = 'Error';

                incentiveStatusSpan.className = 'badge bg-danger';

                console.error('Error fetching status:', data.message);

            }

        })

        .catch(error => {

            otStatusSpan.textContent = 'Error';

            otStatusSpan.className = 'badge bg-danger';

            incentiveStatusSpan.textContent = 'Error';

            incentiveStatusSpan.className = 'badge bg-danger';

            console.error('Network error:', error);

        });
    }
});

// Add financial year aware form validation
document.addEventListener('DOMContentLoaded', function() {
    const monthSelect = document.getElementById('month');
    const typeSelect = document.getElementById('type');
    const proceedBtn = document.getElementById('proceed-btn');
    
    function updateButtonState() {
        const monthSelected = monthSelect.value;
        const typeSelected = typeSelect.value;
        
        if (!monthSelected || !typeSelected) {
            proceedBtn.disabled = true;
            proceedBtn.className = 'btn btn-secondary form-control';
            proceedBtn.textContent = 'Select Month & Type';
            return;
        }
        
        const selectedOption = monthSelect.options[monthSelect.selectedIndex];
        const status = selectedOption.getAttribute('data-status');
        
        // For historical financial years, only allow viewing completed payrolls
        @if(isset($fyContext) && !$fyContext['isFinancialYearEditable'])
            if (status !== 'completed') {
                proceedBtn.disabled = true;
                proceedBtn.className = 'btn btn-warning form-control';
                proceedBtn.textContent = 'Payroll Not Completed';
                return;
            }
        @endif
        
        proceedBtn.disabled = false;
        proceedBtn.className = 'btn btn-success form-control';
        proceedBtn.textContent = status === 'completed' ? 'View Data' : 'Proceed';
    }
    
    monthSelect.addEventListener('change', updateButtonState);
    typeSelect.addEventListener('change', updateButtonState);
    updateButtonState(); // Initial call
});

</script>



<!-- Add CSRF token meta tag if not already present -->

@if(!isset($csrfAdded))

    <meta name="csrf-token" content="{{ csrf_token() }}">

@endif

@endsection

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
        /* background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem; */
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
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
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

    /* Icon Styling */
    .fas, .fa {
        font-weight: 900;
    }

    /* Alert Styling */
    .alert {
        border-radius: 0.5rem;
        border: none;
    }

    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        color: #0c5460;
    }

    /* Badge Styling */
    .badge {
        border-radius: 0.25rem;
        font-weight: 600;
        padding: 0.35em 0.65em;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .card-body {
            padding: 1rem;
        }

        .h3, .h4 {
            font-size: 1.5rem;
        }

        .circle-1, .circle-2, .circle-3 {
            display: none;
        }
    }
</style>