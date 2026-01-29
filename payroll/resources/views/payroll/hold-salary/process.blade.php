@extends('layouts.master')

@section('title', 'Held Salary Process')

@section('content')

<!-- Unified Styles from Salary Breakdown -->
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
    .modern-card { background: white; border-radius: 1rem; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 1.5rem; }
    .modern-card-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 1.25rem 1.5rem; border-bottom: none; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .modern-card-header h4 { color: white; font-weight: 600; margin: 0; font-size: 1.125rem; display: flex; align-items: center; }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-modern-light { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-light:hover { background: #e9ecef; }

    /* Table Styling */
    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .modern-table thead th { 
        background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
        color: white; 
        font-weight: 600; 
        padding: 0.75rem; 
        font-size: 0.8rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        border: 1px solid rgba(255,255,255,0.1);
        text-align: center;
        vertical-align: middle;
        white-space: nowrap;
    }
    .modern-table tbody td { 
        padding: 0.75rem; 
        border-bottom: 1px solid #f3f4f6; 
        vertical-align: middle; 
        font-size: 0.85rem;
        text-align: center;
        white-space: nowrap;
        background: #fff;
    }
    
    /* Frozen Columns Logic */
    .frozen-column { position: sticky; left: 0; z-index: 20; background-color: #fff !important; border-right: 2px solid #e5e7eb; }
    thead .frozen-column { z-index: 30 !important; }
    .frozen-column.checkbox-col { left: 0; width: 40px; min-width: 40px; }
    .frozen-column.employee-info { left: 40px; min-width: 300px; }

    /* Frozen Actions Column */
    .frozen-column-end { position: sticky; right: 0; z-index: 20; background-color: #f8f9fa !important; border-left: 2px solid #dee2e6; }
    thead .frozen-column-end { z-index: 30 !important; }

    /* Employee Info */
    .employee-details { display:flex; align-items:center; }
    .employee-avatar { width:40px; height:40px; position:relative; flex-shrink:0; }
    .employee-avatar img { width:100%; height:100%; object-fit:cover; border-radius:50%; border:2px solid #e2e8f0; }
    .employee-info-text { margin-left:1rem; text-align: left; }
    .employee-name { font-weight:600; color:#1f2937; font-size:0.9rem; margin-bottom:0.125rem; }
    .employee-meta { display:flex; align-items:center; margin-top:0.25rem; font-size: 0.75rem; color: #6b7280; }

    /* Header Structure */
    .header-main th { border-bottom: none; }
    .header-sub th { border-top: none; font-size: 0.75rem; padding: 0.5rem; }
    .header-contents { display: flex; flex-direction: column; align-items: center; justify-content: center; width: 100%; }
    
    /* Value Styles */
    .amount { font-family: 'Segoe UI', monospace; font-weight: 600; }
    .currency-symbol { font-size: 0.9em; margin-right: 2px; }
    .amount-highlight { font-weight: 700; padding: 0.25rem 0.5rem; border-radius: 4px; }
    .amount-highlight.gross { color: #059669; background: #ecfdf5; }
    .amount-highlight.deduction { color: #dc2626; background: #fef2f2; }
    .amount-highlight.net { color: #2563eb; background: #eff6ff; }
    
    .overridden-value { background-color: #fffbeb !important; position: relative; }
    .overridden-value::after { content: ''; position: absolute; top: 0; right: 0; border-top: 6px solid #f59e0b; border-left: 6px solid transparent; }
    .not-applicable { color: #d1d5db; font-size: 0.8em; }

    /* Scrollbar */
    .table-responsive::-webkit-scrollbar { height: 10px; }
    .table-responsive::-webkit-scrollbar-track { background: #f1f1f1; }
    .table-responsive::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 5px; }
    .table-responsive::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
</style>

<div class="page-wrapper">
    <div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header-card">
        <div class="page-header-gradient">
            <div class="page-header-pattern"></div>
            <div class="page-header-circle-1"></div>
            <div class="page-header-circle-2"></div>
            
            <div class="d-flex align-items-center justify-content-between position-relative" style="z-index:1;">
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box me-4">
                        <i class="fas fa-hand-holding-usd fa-2x text-white"></i>
                    </div>
                    <div>
                        <h1 class="page-header-title">Held Salary Process</h1>
                        <p class="page-header-subtitle">Process and release held salaries for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</p>
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                     <!-- Header Buttons Removed as per request -->
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Toolbar -->
    <div id="bulkActionBar" class="modern-card bg-primary text-white mb-3" style="display: none; transition: all 0.3s ease; overflow: visible !important; position: relative; z-index: 1000;">
        <div class="px-4 py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <span class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 32px; height: 32px; font-weight: bold;" id="selectedCount">0</span>
                <span class="fs-5 fw-bold">Employees Selected</span>
            </div>
            <div class="d-flex gap-2">
                 <!-- Bank Transfer Dropdown -->
                 <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle text-primary fw-bold" type="button" data-toggle="dropdown" aria-expanded="false" id="bankTransferDropdown">
                        <i class="fas fa-university me-2"></i> Bank Transfer
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="bankTransferDropdown">
                        <li><button class="dropdown-item py-2" onclick="submitBulkForm('bank', 'canara_csv')">Canara Bank (CSV)</button></li>
                        <li><button class="dropdown-item py-2" onclick="submitBulkForm('bank', 'icici')">ICICI Bank</button></li>
                    </ul>
                 </div>

                 <!-- Statutory Dropdown -->
                 <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle text-primary fw-bold" type="button" data-toggle="dropdown" aria-expanded="false" id="statutoryDropdown">
                        <i class="fas fa-file-alt me-2"></i> Statutory
                    </button>
                    <ul class="dropdown-menu shadow-sm border-0" aria-labelledby="statutoryDropdown">
                        <li><button class="dropdown-item py-2" onclick="submitBulkForm('statutory', 'epf_excel')">EPF (Excel)</button></li>
                        <!-- <li><button class="dropdown-item py-2" onclick="submitBulkForm('statutory', 'epf_csv')">EPF (CSV)</button></li> -->
                        <li><button class="dropdown-item py-2" onclick="submitBulkForm('statutory', 'esic_excel')">ESIC (Excel)</button></li>
                    </ul>
                 </div>

                 <button type="button" class="btn btn-light text-primary fw-bold" onclick="submitBulkForm('payslip')">
                    <i class="fas fa-envelope me-2"></i> Send Payslips
                 </button>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="modern-card">
        <div class="p-4">
            <form action="{{ route('hold-salary.process') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase text-muted mb-2">Select Process Month</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="far fa-calendar-alt text-primary"></i></span>
                        <input type="month" name="month_year" class="form-control border-start-0 ps-0" value="{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}" onchange="this.form.submit()" style="height: 45px;">
                    </div>
                    <input type="hidden" name="month" id="filter_month" value="{{ $month }}">
                    <input type="hidden" name="year" id="filter_year" value="{{ $year }}">
                </div>
                
                 <div class="col-md-2">
                    <label class="form-label fw-bold small text-uppercase text-muted mb-2">Hold Type</label>
                    <select name="hold_type" class="form-select" onchange="this.form.submit()" style="height: 45px;">
                        <option value="">All Types</option>
                        <option value="month" {{ request('hold_type') == 'month' ? 'selected' : '' }}>One Month</option>
                        <option value="indefinite" {{ request('hold_type') == 'indefinite' ? 'selected' : '' }}>Indefinite</option>
                    </select>
                </div>
                
                <div class="col-md-7">
                     <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x text-primary me-3"></i>
                            <div>
                                <h6 class="mb-1 fw-bold text-dark">Data for {{ date('F Y', mktime(0, 0, 0, $month, 1, $year)) }}</h6>
                                <p class="mb-0 text-muted small">Showing all active held salaries and releases processed in this period.</p>
                            </div>
                        </div>
                        
                        @if($payoutStatus && $payoutStatus->status === 'completed')
                            <span class="badge bg-success px-3 py-2 rounded-pill">
                                <i class="fas fa-lock me-1"></i> Payroll Finalized
                            </span>
                        @else
                            <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                <i class="fas fa-clock me-1"></i> Payroll Processing
                            </span>
                        @endif
                     </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Detailed Salary Breakdown Table -->
    <div class="modern-card">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <h4><i class="fas fa-table me-2"></i> Detailed Salary Breakdown</h4>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                <table class="modern-table">
                    <thead class="sticky-top">
                        <!-- Main Header Row -->
                        <tr class="header-main">
                             <th class="frozen-column checkbox-col text-center" rowspan="2">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </div>
                            </th>
                            <th class="frozen-column employee-info" rowspan="2">
                                <div class="header-contents">
                                    <i class="fa fa-user me-2"></i>Employee Details
                                </div>
                            </th>
                            <th class="attendance-header" colspan="2">
                                <div class="header-contents text-center">
                                    <i class="fa fa-calendar-check me-2"></i>Attendance
                                </div>
                            </th>
                            @if($earningComponents->count() > 0)
                                <th colspan="{{ $earningComponents->count() }}" class="earnings-group">
                                    <div class="header-contents text-center">
                                        <i class="fa fa-plus-circle me-2"></i>EARNINGS
                                    </div>
                                </th>
                            @endif
                            <th class="gross-pay-header" rowspan="2">
                                <div class="header-contents text-center">Gross Pay</div>
                            </th>
                            <th class="epf-header" rowspan="2">
                                <div class="header-contents text-center">EPF Wages</div>
                            </th>
                            @if($deductionComponents->count() > 0)
                                <th colspan="{{ $deductionComponents->count() + 1 }}" class="deductions-group">
                                    <div class="header-contents text-center">
                                        <i class="fa fa-minus-circle me-2"></i>DEDUCTIONS
                                    </div>
                                </th>
                            @endif
                            <th class="total-deductions-header" rowspan="2">
                                <div class="header-contents text-center">Total Deductions</div>
                            </th>
                            <th class="net-pay-header" rowspan="2">
                                <div class="header-contents text-center">Net Pay</div>
                            </th>
                            <th class="early-salary-header" rowspan="2">
                                <div class="header-contents text-center">Early Salary</div>
                            </th>
                            <th class="frozen-column-end text-center" rowspan="2" style="right:0;">
                                <div class="header-contents">Actions</div>
                            </th>
                        </tr>

                        <!-- Sub Header Row -->
                        <tr class="header-sub">
                            <th class="sub-header">
                                <div class="header-contents text-center">
                                    <small>Worked Days</small>
                                </div>
                            </th>
                            <th class="sub-header">
                                <div class="header-contents text-center">
                                    <small>Total Days</small>
                                </div>
                            </th>
                            @foreach($earningComponents as $component)
                                <th class="component-header earnings-component">
                                    <div class="header-contents text-center">
                                        <div class="component-name">{{ $component->short_name }}</div>
                                        @if($component->is_percentage)
                                            <small class="component-rate">({{ $component->percentage_value }}%)</small>
                                        @endif
                                    </div>
                                </th>
                            @endforeach
                            @foreach($deductionComponents as $component)
                                <th class="component-header deductions-component">
                                    <div class="header-contents text-center">
                                        <div class="component-name">{{ $component->short_name }}</div>
                                    </div>
                                </th>
                            @endforeach
                            <!-- Advance Column -->
                            <th class="component-header deductions-component">
                                <div class="header-contents text-center">
                                    <div class="component-name">Advance</div>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($processAttendances as $attendance)
                        <tr>
                             <!-- Checkbox -->
                            <td class="frozen-column checkbox-col text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input bulk-checkbox" type="checkbox" name="selected_employees[]" value="{{ $attendance->emp_id }}">
                                </div>
                            </td>
                            <!-- Employee Info (Frozen) -->
                            <td class="frozen-column employee-info">
                                <div class="employee-details">
                                    <div class="d-flex align-items-center">
                                        <div class="employee-avatar me-3">
                                            <img src="{{ asset($attendance->employee->profile_image ?? 'assets/img/user-icon.webp') }}" 
                                                 class="rounded-circle"
                                                 alt="Avatar">
                                        </div>
                                        <div class="employee-info-text">
                                            <h6 class="employee-name mb-1">{{ $attendance->employee->name }}</h6>
                                            <div class="employee-meta flex-wrap gap-1">
                                                <span class="badge bg-light text-dark">
                                                    {{ $attendance->employee->employee_id }}
                                                </span>
                                                 @if($attendance->employee->designation)
                                                    <span class="badge bg-light text-muted border">
                                                        {{ $designations[$attendance->employee->designation] ?? '' }}
                                                    </span>
                                                @endif
                                                @if($attendance->is_held)
                                                    <span class="badge bg-warning text-dark"><i class="fas fa-hand-holding-usd me-1"></i>On Hold</span>
                                                @endif
                                                @if($attendance->is_released)
                                                    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Released</span>
                                                @endif
                                                @if($isFinalized)
                                                     <span class="badge bg-info text-white"><i class="fa fa-lock me-1"></i> Finalized</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                             <!-- Attendance -->
                            <td class="text-center">
                                <span class="fw-bold">{{ $attendance->employee_worked_days }}</span>
                            </td>
                            <td class="text-center">
                                <span class="text-muted">{{ $attendance->total_working_days }}</span>
                            </td>

                            <!-- Earnings -->
                            @foreach($earningComponents as $component)
                            <td class="text-center {{ isset($attendance->earnings[$component->id]['overridden']) && $attendance->earnings[$component->id]['overridden'] ? 'overridden-value' : '' }}">
                                @if(isset($attendance->earnings[$component->id]['applicable']) && !$attendance->earnings[$component->id]['applicable'])
                                    <span class="not-applicable">N/A</span>
                                @else
                                    <span class="amount">
                                        <span class="currency-symbol">₹</span>{{ number_format($attendance->earnings[$component->id]['value'] ?? 0, 2) }}
                                    </span>
                                @endif
                            </td>
                            @endforeach
                            
                            <!-- Gross Pay -->
                            <td class="text-center bg-light">
                                <span class="amount-highlight gross"><span class="currency-symbol">₹</span>{{ number_format($attendance->totalEarnings, 2) }}</span>
                            </td>
                             <!-- EPF Wages -->
                            <td class="text-center">
                                <span class="amount"><span class="currency-symbol">₹</span>{{ number_format($attendance->epfWage, 2) }}</span>
                            </td>

                            <!-- Deductions -->
                            @foreach($deductionComponents as $component)
                            <td class="text-center {{ isset($attendance->deductions[$component->id]['overridden']) && $attendance->deductions[$component->id]['overridden'] ? 'overridden-value' : '' }}">
                                @if(isset($attendance->deductions[$component->id]['applicable']) && !$attendance->deductions[$component->id]['applicable'])
                                    <span class="not-applicable">N/A</span>
                                @else
                                    <span class="amount">
                                        <span class="currency-symbol">₹</span>{{ number_format($attendance->deductions[$component->id]['value'] ?? 0, 2) }}
                                    </span>
                                @endif
                            </td>
                            @endforeach
                            
                            <!-- Advance -->
                             <td class="text-center">
                                @if(isset($attendance->deductions['advance']['applicable']) && $attendance->deductions['advance']['applicable'])
                                    <span class="amount"><span class="currency-symbol">₹</span>{{ number_format($attendance->deductions['advance']['value'] ?? 0, 2) }}</span>
                                @else
                                    <span class="not-applicable">N/A</span>
                                @endif
                            </td>

                            <!-- Total Deductions -->
                            <td class="text-center bg-light">
                                <span class="amount-highlight deduction"><span class="currency-symbol">₹</span>{{ number_format($attendance->totalDeductions, 2) }}</span>
                            </td>

                            <!-- Net Pay -->
                            <td class="text-center bg-primary-light">
                                <span class="amount-highlight net"><span class="currency-symbol">₹</span>{{ number_format($attendance->netPay, 2) }}</span>
                            </td>
                            
                             <!-- Early Salary -->
                            <td class="text-center">
                                 @if($attendance->early_salary_processed)
                                    <span class="badge bg-info">Processed</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            
                            <!-- Actions -->
                            <td class="frozen-column-end text-center" style="right:0;">
                                <form action="{{ route('payroll.send-salary-slip') }}" method="POST" class="d-inline" onsubmit="return confirm('Send payslip to {{ $attendance->employee->name }}?')">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $attendance->emp_id }}">
                                    <input type="hidden" name="employee_name" value="{{ $attendance->employee->name }}">
                                    <input type="hidden" name="employee_email" value="{{ $attendance->employee->email ?? '' }}"> 
                                    <input type="hidden" name="month" value="{{ $month }}">
                                    <input type="hidden" name="year" value="{{ $year }}">
                                    <button type="submit" class="btn btn-sm btn-light border text-primary" title="Send Payslip">
                                        <i class="fas fa-envelope"></i>
                                    </button>
                                </form>
                            </td>

                        </tr>
                        @empty
                        <tr>
                            <td colspan="100" class="text-center py-5">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No held salary records found for this period.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
      <!-- Bulk Forms (Hidden) -->
    <form id="bulkBankForm" action="{{ route('payroll.export-bank-bulk') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="type" id="bankExportType">
    </form>
    <form id="bulkStatutoryForm" action="{{ route('payroll.export-statutory-bulk') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
        <input type="hidden" name="type" id="statutoryExportType">
    </form>
    <form id="bulkPayslipForm" action="{{ route('payroll.send-payslips-bulk') }}" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="month" value="{{ $month }}">
        <input type="hidden" name="year" value="{{ $year }}">
    </form>
    
    </div>
</div>

@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Event Delegation for Checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('bulk-checkbox') || e.target.id === 'selectAll') {
                handleCheckboxChange(e.target);
            }
        });

        // Initial check
        updateBulkUI();

        function handleCheckboxChange(target) {
            // Handle Select All Logic
            if (target.id === 'selectAll') {
                const isChecked = target.checked;
                document.querySelectorAll('.bulk-checkbox').forEach(cb => {
                    cb.checked = isChecked;
                });
            } else if (target.classList.contains('bulk-checkbox')) {
                // If individual unchecked, uncheck selectAll
                if (!target.checked) {
                    const selectAll = document.getElementById('selectAll');
                    if(selectAll) selectAll.checked = false;
                }
            }
            updateBulkUI();
        }
        
        function updateBulkUI() {
            const checkedBoxes = document.querySelectorAll('.bulk-checkbox:checked');
            const count = checkedBoxes.length;
            const selectedCount = document.getElementById('selectedCount');
            const bulkActionBar = document.getElementById('bulkActionBar');
            
            if(selectedCount) selectedCount.textContent = count;
            
            if (bulkActionBar) {
                if (count > 0) {
                    bulkActionBar.style.display = 'block';
                    // Force layout recalc if needed or ensure z-index
                } else {
                    bulkActionBar.style.display = 'none';
                }
            }
        }
    });

    function submitBulkForm(type, subType = null) {
        console.log('submitBulkForm called', type, subType);
        const checkedBoxes = document.querySelectorAll('.bulk-checkbox:checked');
        if (checkedBoxes.length === 0) {
             console.warn('No checkboxes selected');
             return;
        }
        
        const employeeIds = Array.from(checkedBoxes).map(cb => cb.value);
        let form;
        
        if (type === 'bank') {
            form = document.getElementById('bulkBankForm');
            document.getElementById('bankExportType').value = subType;
        } else if (type === 'statutory') {
            form = document.getElementById('bulkStatutoryForm');
            document.getElementById('statutoryExportType').value = subType;
        } else if (type === 'payslip') {
             if(!confirm('Send payslips to ' + employeeIds.length + ' selected employees?')) return;
            form = document.getElementById('bulkPayslipForm');
        }
        
        // Clear previous inputs
        const existingInputs = form.querySelectorAll('input[name="employee_ids[]"]');
        existingInputs.forEach(el => el.remove());
        
        // Add current selected IDs
        employeeIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'employee_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        form.submit();
    }
</script>
@endsection
