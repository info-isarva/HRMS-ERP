@extends('layouts.master')

@section('title', 'Payroll Payout Month')

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

    /* Form Styling */
    .modern-form-group { margin-bottom: 1.5rem; }
    .modern-form-group label { font-weight: 600; color: #374151; margin-bottom: 0.5rem; display: block; }
    .modern-select { width: 100%; padding: 0.75rem 1rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 1rem; transition: all 0.2s; }
    .modern-select:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
    .btn-modern-secondary { background: #6b7280; color: white; }
    .btn-modern-secondary:hover { background: #4b5563; }

    /* Alert Styles */
    .alert-modern { border-radius: 0.5rem; border: none; padding: 1rem 1.5rem; margin-bottom: 1rem; }
    .alert-modern-danger { background: linear-gradient(135deg,#ef4444 0%,#dc2626 100%); color: white; }
    .alert-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .alert-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }
    .alert-modern-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: white; }

    /* Badge Styles */
    .badge-modern { padding: 0.375rem 0.75rem; font-weight: 500; border-radius: 0.375rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .badge-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .badge-modern-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: white; }

    /* Progress Steps Container */
    /* .steps-container { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; } */

    /* Optional: Add colors to distinguish status in dropdown */
    .status-completed { background-color: #d4edda !important; color: #155724 !important; }
    .status-pending { background-color: #fff3cd !important; color: #856404 !important; }
    .status-progress { background-color: #cce7ff !important; color: #004085 !important; }
    .status-not-processed { background-color: #f8f9fa !important; color: #6c757d !important; }
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
                                    <i class="fas fa-money-bill-wave text-white" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Payroll Management</h1>
                                    <p class="page-header-subtitle">Select and manage payroll payout months for employee salary processing</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Steps -->
        <!-- <div class="steps-container mb-4"> -->
        <div class=" mb-4">
            <div class="steps">
                @include('payroll.partials.progress-steps', ['currentStep' => $currentStep])
            </div>
        </div>

        <!-- Financial Year Info -->
        @if(isset($fyContext) && $fyContext['selectedFinancialYear'])
            <div class="modern-card mb-4">
                <div class="modern-card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-primary me-3" style="font-size:1.5rem;"></i>
                                <div>
                                    <span class="font-weight-medium" style="font-size:1.1rem;">Viewing Financial Year: 
                                        <strong>{{ $fyContext['selectedFinancialYear']->year_name }}</strong>
                                    </span>
                                    @if(!$fyContext['isFinancialYearEditable'])
                                        <span class="badge badge-modern bg-warning text-dark ms-3">Read-only (Historical Data)</span>
                                    @else
                                        <span class="badge badge-modern bg-success ms-3">Current Year</span>
                                    @endif
                                    @if(!$fyContext['isFinancialYearEditable'])
                                        <small class="text-muted d-block mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            You can view reports and salary breakdowns for this year, but cannot create new payrolls.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted" style="font-size:0.9rem;">
                                {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->start_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->end_date)->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Content Card -->
        <div class="modern-card">
            <div class="modern-card-header">
                <h4><i class="fas fa-calendar-check me-2"></i>Select Payroll Payout Month</h4>
            </div>
            <div class="modern-card-body">

                @if(session('error'))
                    <div class="alert alert-modern alert-modern-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-modern alert-modern-success">{{ session('success') }}</div>
                @endif

                @if(isset($fyContext) && !$fyContext['isFinancialYearEditable'])
                    <div class="alert alert-modern alert-modern-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Historical Financial Year View</strong><br>
                        You are viewing data for a previous financial year. You can view reports and salary breakdowns, but cannot create new payrolls or modify existing data.
                    </div>
                @endif

                @if(!isset($fyContext) || $fyContext['isFinancialYearEditable'])
                    <form id="payroll-form" method="POST" action="{{ route('payroll.store') }}">
                        @csrf
                        <input type="hidden" name="override_confirmed" id="override_confirmed" value="0">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label for="payout_month_year">Payout Month</label>
                                    <select name="payout_month_year" id="payout_month_year" class="modern-select">
                                        @foreach($dropdownMonths as $month)
                                            @php
                                                $monthValue = str_pad($month['payout_month'], 2, '0', STR_PAD_LEFT) . '-' . $month['payout_year'];
                                                $isSelected = $month['payout_month'] == $selectedMonth && $month['payout_year'] == $selectedYear;
                                                
                                                // Set option class based on status
                                                $optionClass = '';
                                                switch($month['status']) {
                                                    case 'completed':
                                                        $optionClass = 'status-completed';
                                                        break;
                                                    case 'pending':
                                                        $optionClass = 'status-pending';
                                                        break;
                                                    case 'progress':
                                                        $optionClass = 'status-progress';
                                                        break;
                                                    default:
                                                        $optionClass = 'status-not-processed';
                                                }
                                            @endphp
                                            <option value="{{ $monthValue }}" 
                                                    class="{{ $optionClass }}" 
                                                    data-status="{{ $month['status'] }}"
                                                    {{ $isSelected ? 'selected' : '' }}>
                                                {{ $month['label'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="modern-form-group">
                                    <label for="location_id">Location</label>
                                    <select name="location_id" id="location_id" class="modern-select">
                                        <option value="">All Locations</option>
                                        @foreach($locations as $id => $name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endforeach
                                    </select>
                                    <div id="status-description" class="mt-2 small"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" id="submit-btn" class="btn-modern btn-modern-primary">
                                <i class="fas fa-plus me-2"></i>Create Payroll
                            </button>
                        </div>
                    </form>
                @else
                    <!-- Read-only view for historical financial years -->
                    <form id="historical-payroll-form" method="POST" action="{{ route('payroll.store') }}">
                        @csrf
                        <div class="modern-form-group">
                            <label for="payout_month_year_readonly">Payout Month (View Only)</label>
                            <select id="payout_month_year_readonly" name="payout_month_year" class="modern-select">
                                @foreach($dropdownMonths as $month)
                                    @php
                                        $monthValue = str_pad($month['payout_month'], 2, '0', STR_PAD_LEFT) . '-' . $month['payout_year'];
                                        $isSelected = $month['payout_month'] == $selectedMonth && $month['payout_year'] == $selectedYear;
                                    @endphp
                                    <option value="{{ $monthValue }}" 
                                            data-status="{{ $month['status'] }}"
                                            {{ $isSelected ? 'selected' : '' }}>
                                        {{ $month['label'] }} 
                                        @if($month['status'] === 'completed')
                                            - Completed
                                        @elseif($month['status'] === 'pending')
                                            - Pending
                                        @elseif($month['status'] === 'progress')
                                            - In Progress
                                        @else
                                            - Not Processed
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="button" id="historical-create-btn" class="btn-modern btn-modern-secondary" disabled>
                                <i class="fas fa-lock me-2"></i>Create Payroll (Not Available for Historical Years)
                            </button>
                            <button type="submit" id="historical-breakdown-btn" class="btn-modern btn-modern-success" style="display: none;">
                                <i class="fas fa-eye me-2"></i>View Salary Breakdown
                            </button>
                        </div>
                    </form>
                @endif

            </div>
        </div>

    </div>

</div>



<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    const checkbox = document.getElementById('override_confirmed');
    
    // Logic to update button status via AJAX
    async function updateStatus() {
        const payoutSelect = document.getElementById('payout_month_year');
        const locationSelect = document.getElementById('location_id');
        const statusDesc = document.getElementById('status-description');
        const submitBtn = document.getElementById('submit-btn');
        
        if (!payoutSelect.value) return;
        
        const [month, year] = payoutSelect.value.split('-').map(Number);
        
        // 1. Fetch Month Status Summary
        try {
            const summaryResponse = await fetch(`{{ route('payroll.month-summary') }}?month=${month}&year=${year}`);
            const summary = await summaryResponse.json();
            
            // Restore original options first
            // Restore original options first if they aren't matching or if it was locked
            // We only need to restore if we are transitioning from a locked state or the options count is wrong
            // But doing it safely every time ensures we start fresh before applying logic
            if(window.originalLocationOptions && locationSelect) {
                 // Save current selection if possible, though mostly we might be resetting
                 const currentVal = locationSelect.value;
                 
                locationSelect.innerHTML = '';
                window.originalLocationOptions.forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.text = opt.text;
                    locationSelect.appendChild(option);
                });
                
                // Try to restore previous value if it exists in new options, else default
                // Actually, logic below might change options again (removing "All"), so let's stick to default behavior or let logic handle it
                locationSelect.value = currentVal || ""; 
            }

            // Update Status Description
            if(statusDesc) statusDesc.innerHTML = summary.description;
            
            // Handle Global Completion Logic
            if (summary.global_completed) {
                if(locationSelect) {
                    // Remove all existing options by setting innerHTML to check if browser handles it weirdly
                    while (locationSelect.firstChild) {
                        locationSelect.removeChild(locationSelect.firstChild);
                    }
                    // Add only All Locations
                    const allOption = document.createElement('option');
                    allOption.value = "";
                    allOption.text = "All Locations";
                    allOption.selected = true;
                    locationSelect.appendChild(allOption);
                    
                    locationSelect.disabled = true;
                    locationSelect.setAttribute('disabled', 'disabled'); // Double ensure attribute is set
                    locationSelect.style.pointerEvents = 'none'; // CSS failsafe
                    locationSelect.style.backgroundColor = '#e9ecef'; // Visual cue
                    locationSelect.title = "Global payroll is completed. Individual locations cannot be selected.";
                }
            } else {
                if(locationSelect) {
                    locationSelect.disabled = false;
                    locationSelect.removeAttribute('disabled');
                    locationSelect.style.pointerEvents = 'auto'; 
                    locationSelect.style.backgroundColor = '';
                    locationSelect.removeAttribute('title');
                    
                    // If any individual location completed, we should NOT allow selecting "All Locations"
                    if (summary.any_completed) {
                        // Check if "All Locations" option exists
                        const allOption = locationSelect.querySelector('option[value=""]');
                        if (allOption) {
                            allOption.remove();
                        }
                    } else {
                        // If "All Locations" missing, add it back
                         let allOption = locationSelect.querySelector('option[value=""]');
                         if (!allOption) {
                             const newAllOption = document.createElement('option');
                             newAllOption.value = "";
                             newAllOption.text = "All Locations";
                             locationSelect.insertBefore(newAllOption, locationSelect.firstChild);
                             locationSelect.value = ""; // Default to All
                         }
                    }
                    
                    // Also restore full list if it was cleared (simplified approach: reload if needed or just toggle visibility)
                    // Since we are manipulating DOM options, we might need a clean reset logic.
                    // But for now, simple removal/addition of "All Locations" covers the requirement.
                    // However, we need to ensure other options are present.
                    // If we previously cleared them (in global_completed block), we need to restore them.
                    if (locationSelect.options.length <= 1 && !summary.global_completed) {
                         // Ideally we should have stored the original options. 
                         // Reloading page is not good. 
                         // Let's rely on server rendering initial state correctly, and this JS just tweaking it.
                         // But if user switches from Completed Month to Pending Month, we need to restore options.
                         // The view rendered with all options initially. 
                         // Correct approach: Store original options on load.
                    }
                }
            }
        } catch (e) {
            console.error('Failed to fetch status summary', e);
        }

        // 2. Check individual payroll status for button state
        const locationId = locationSelect.value;
        const response = await fetch(`{{ route('checkPayroll.Status') }}?month=${month}&year=${year}&location_id=${locationId}`);
        const data = await response.json();
        
        if (data.status === 'completed') {
            submitBtn.innerHTML = '<i class="fas fa-eye me-2"></i>View Salary Breakdown';
            submitBtn.className = 'btn-modern btn-modern-success';
        } else if (data.status === 'progress') {
            submitBtn.innerHTML = '<i class="fas fa-arrow-right me-2"></i>Continue Payroll';
            submitBtn.className = 'btn-modern btn-modern-warning'; 
        } else {
            submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Create Payroll';
            submitBtn.className = 'btn-modern btn-modern-primary';
        }
    }

    document.getElementById('payroll-form').addEventListener('submit', async function(e) {
        e.preventDefault();

        const form = e.target;
        const payoutMonthYear = document.getElementById('payout_month_year').value;
        const [month, year] = payoutMonthYear.split('-').map(Number);
        const locationId = document.getElementById('location_id').value;

        // Check payroll status via AJAX
        const response = await fetch(`{{ route('checkPayroll.Status') }}?month=${month}&year=${year}&location_id=${locationId}`);
        const data = await response.json();

        if (data.requires_override) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Previous month payroll is not completed. Do you want to override and continue?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, override it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('override_confirmed').value = "1";
                    form.submit();
                } else {
                    window.location.href = "{{ route('payroll.index') }}";
                }
            });        } else {
            form.submit(); // Proceed normally
        }
    });
    
    // Add event listener for dropdown change
    document.getElementById('payout_month_year').addEventListener('change', updateStatus);
    document.getElementById('location_id').addEventListener('change', updateStatus);
    
    // Initial update
    updateStatus();
    
    // Function to handle historical financial year dropdown changes
    function updateHistoricalButtons() {
        const historicalSelect = document.getElementById('payout_month_year_readonly');
        const createBtn = document.getElementById('historical-create-btn');
        const breakdownBtn = document.getElementById('historical-breakdown-btn');
        
        if (!historicalSelect || !historicalSelect.value) {
            if (createBtn) createBtn.style.display = 'inline-block';
            if (breakdownBtn) breakdownBtn.style.display = 'none';
            return;
        }
        
        const selectedOption = historicalSelect.options[historicalSelect.selectedIndex];
        const status = selectedOption.getAttribute('data-status');
        
        if (status === 'completed') {
            // Month is completed - show breakdown button, hide create button
            if (createBtn) createBtn.style.display = 'none';
            if (breakdownBtn) breakdownBtn.style.display = 'inline-block';
        } else {
            // Month is not completed - show disabled create button, hide breakdown button
            if (createBtn) createBtn.style.display = 'inline-block';
            if (breakdownBtn) breakdownBtn.style.display = 'none';
        }
    }
    
    // Add event listener for historical dropdown change
    const historicalDropdown = document.getElementById('payout_month_year_readonly');
    if (historicalDropdown) {
        historicalDropdown.addEventListener('change', updateHistoricalButtons);
    }
    
    // Run on page load to set initial state
    document.addEventListener('DOMContentLoaded', function() {
        // Store original location options
        const locationSelect = document.getElementById('location_id');
        if (locationSelect) {
            window.originalLocationOptions = Array.from(locationSelect.options).map(opt => ({
                value: opt.value,
                text: opt.text
            }));
        }

        updateStatus();
        updateHistoricalButtons();
    });

</script>

@endsection