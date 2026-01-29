@extends('layouts.master')

@section('title', 'Salary Breakdown')

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
    .modern-card { 
        background: white; 
        border-radius: 1rem; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
        border: 1px solid #e5e7eb; 
        overflow: hidden;
        margin-bottom: 1.5rem;
    }
    .modern-card-header { 
        background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
        padding: 1.25rem 1.5rem; 
        border-bottom: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .modern-card-header h4 { 
        color: white; 
        font-weight: 600; 
        margin: 0; 
        font-size: 1.125rem;
        display: flex;
        align-items: center;
    }
    .modern-card-header h4 i {
        font-size: 1rem;
    }
    .modern-card-body { padding: 0; }

    /* Summary Cards */
    .summary-card { 
        background: white; 
        border-radius: 1rem; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
        border: 1px solid #e5e7eb; 
        padding: 1.5rem; 
        margin-bottom: 1rem; 
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .summary-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .summary-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0,0,0,0.12);
    }
    .summary-card:hover::before {
        opacity: 1;
    }
    .summary-card-icon { 
        width: 3.5rem; 
        height: 3.5rem; 
        border-radius: 1rem; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        margin-bottom: 1rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .summary-card-title { 
        font-size: 0.8125rem; 
        font-weight: 600; 
        color: #6b7280; 
        margin-bottom: 0.75rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
    }
    .summary-card-value { 
        font-size: 1.75rem; 
        font-weight: 700; 
        color: #1f2937; 
        margin: 0;
        font-family: 'Segoe UI', system-ui, sans-serif;
    }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); }
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
    .btn-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }
    .btn-modern-info:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); }
    .btn-modern-warning { background: linear-gradient(135deg,#f59e0b 0%,#d97706 100%); color: white; }
    .btn-modern-warning:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4); }
    .btn-modern-light { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-light:hover { background: #e9ecef; }

    /* Table Styling */
    .modern-table { width: 100%; border-collapse: collapse; }
    .modern-table thead th { 
        background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); 
        color: white; 
        font-weight: 600; 
        padding: 1rem 0.875rem; 
        font-size: 0.875rem; 
        text-transform: uppercase; 
        letter-spacing: 0.5px; 
        border: none;
        text-align: center;
        vertical-align: middle;
    }
    .modern-table tbody td { 
        padding: 0.875rem 0.75rem; 
        border-bottom: 1px solid #f3f4f6; 
        vertical-align: middle; 
        font-size: 0.875rem;
        text-align: center;
    }
    .modern-table tbody td:first-child {
        text-align: left;
    }
    .modern-table tbody tr:hover { 
        background-color: rgba(102, 126, 234, 0.05);
        transition: background-color 0.2s ease;
    }
    .modern-table tbody tr {
        transition: all 0.2s ease;
    }

    /* Form Elements */
    .modern-input { width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #e5e7eb; border-radius: 0.5rem; font-size: 0.875rem; transition: all 0.2s; }
    .modern-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }

    /* Alert Styles */
    .alert-modern { border-radius: 0.5rem; border: none; padding: 1rem 1.5rem; margin-bottom: 1rem; }
    .alert-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }

    /* Progress Steps Container */
    .steps-container { background: white; border-radius: 1rem; padding: 2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; }

    /* Avatar Styling */
    .avatar-modern { width: 40px; height: 40px; border-radius: 50%; border: 2px solid #e5e7eb; overflow: hidden; }
    .avatar-modern img { width: 100%; height: 100%; object-fit: cover; }

    /* Action Buttons Container */
    .actions-container { 
        background: white; 
        border-radius: 1rem; 
        padding: 1.5rem; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.08); 
        border: 1px solid #e5e7eb; 
        margin-bottom: 2rem;
        position: relative;
    }
    .actions-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    }
    
    /* Dropdown Styles */
    .actions-container .dropdown-menu {
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0;
        margin-top: 0.5rem;
    }
    .actions-container .dropdown-item {
        padding: 0.6rem 1.25rem;
        font-size: 0.875rem;
        color: #4b5563;
        transition: all 0.2s;
        display: flex;
        align-items: center;
    }
    .actions-container .dropdown-item:hover {
        background-color: #f3f4f6;
        color: #667eea;
        padding-left: 1.5rem;
    }
    .actions-container .dropdown-item i {
        width: 1.25rem;
        text-align: center;
        margin-right: 0.5rem;
    }

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
    
    /* Override Indicator Styles */
    .overridden-value {
        background-color: #fff3cd !important;
        border-left: 3px solid #ffc107 !important;
        position: relative;
    }
    .overridden-value .amount {
        color: #856404;
        font-weight: 600;
    }
    .overridden-value::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #ffc107 0%, transparent 100%);
    }
    .overridden-value i.fa-pencil-alt {
        font-size: 0.7rem;
        opacity: 0.8;
    }
    /* Tooltip for override indicator */
    [data-bs-toggle="tooltip"] {
        cursor: help;
    }

    /* Enhanced table header centering and styling */
    .header-contents {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center !important;
        line-height: 1.4;
        width: 100%;
    }

    .component-name {
        font-weight: 600;
        font-size: 0.8125rem;
        margin-bottom: 0.125rem;
        text-align: center;
    }

    .component-rate {
        font-size: 0.6875rem;
        opacity: 0.85;
        font-weight: 400;
        text-align: center;
    }

    /* Center all table body cells except first column */
    .payroll-table tbody td:not(.frozen-column) {
        text-align: center !important;
    }

    /* Improve amount cell styling */
    .amount-cell {
        font-family: 'Segoe UI', 'SF Mono', Monaco, 'Cascadia Code', monospace;
        font-weight: 600;
        font-size: 0.875rem;
        letter-spacing: 0.01em;
    }

    /* Enhanced search bar styling */
    #employeeSearch {
        transition: all 0.3s ease;
    }

    #employeeSearch:focus {
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        border-color: #667eea !important;
    }

    .input-group-text {
        transition: all 0.3s ease;
    }

    #employeeSearch:focus + .input-group-text {
        border-color: #667eea !important;
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
                                    <i class="fas fa-chart-line text-white" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Salary Breakdown</h1>
                                    <p class="page-header-subtitle">Detailed payroll calculation and employee salary management for {{ $monthName }}</p>
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
                    'isFinalized' => $isFinalized ?? false, 
                    'month' => $month, 
                    'year' => $year,
                    'attendanceSaved' => $attendanceSaved ?? false,
                    'salariesReviewed' => $salariesReviewed ?? false
                ])
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fa fa-users fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total Employees">Total Employees</h6>
                        <h4 class="summary-card-value mb-0 text-break">{{ $attendances->count() }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fa fa-money-bill fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total Gross Pay">Total Gross Pay</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('totalEarnings'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                        <i class="fa fa-minus-circle fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total Deductions">Total Deductions</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('totalDeductions'), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #3fcce3, #17a2b8);">
                        <i class="fa fa-calculator fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Net Payable">Net Payable</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('netPay'), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- EPF and ESIC Summary Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #5adbc3, #17a2b8);">
                        <i class="fa fa-shield fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total Advance Deducted">Total Advance Deducted</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum(function($attendance) { return isset($attendance->deductions['advance']['value']) && $attendance->deductions['advance']['applicable'] ? $attendance->deductions['advance']['value'] : 0; }), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fa fa-university fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total EPF Deductions">Total EPF Deductions</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum(function($attendance) { return isset($attendance->deductions[1]['value']) && $attendance->deductions[1]['applicable'] ? $attendance->deductions[1]['value'] : 0; }), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
                        <i class="fa fa-medkit fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total ESIC Deductions">Total ESIC Deductions</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum(function($attendance) { return isset($attendance->deductions[2]['value']) && $attendance->deductions[2]['applicable'] ? $attendance->deductions[2]['value'] : 0; }), 2) }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="summary-card d-flex align-items-center h-100">
                    <div class="summary-card-icon mb-0 me-3 flex-shrink-0" style="background: linear-gradient(135deg, #ff6b6b, #ee5a24);">
                        <i class="fa fa-gavel fa-lg text-white"></i>
                    </div>
                    <div class="min-width-0 flex-grow-1">
                        <h6 class="summary-card-title mb-1 text-truncate" title="Total PT Deductions">Total PT Deductions</h6>
                        <h4 class="summary-card-value mb-0 text-break"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum(function($attendance) { return isset($attendance->deductions[4]['value']) && $attendance->deductions[4]['applicable'] ? $attendance->deductions[4]['value'] : 0; }), 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="actions-container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex flex-wrap">
                    @if(!empty($finalizedLocationIds))
                        <div class="dropdown me-2 mb-2">
                             <button class="btn-modern btn-modern-light dropdown-toggle" type="button" id="finalizedFilterDropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-filter me-2"></i> 
                                {{ $locationId ? ($locations[$locationId] ?? 'Unknown Location') : 'All Locations' }}
                            </button>
                            <div class="dropdown-menu">
                                @if($showAllOption)
                                    <a class="dropdown-item" href="{{ route('payroll.salary-breakdown', ['month' => $month, 'year' => $year]) }}">
                                        @if(!$locationId) <i class="fa fa-check text-success me-2"></i> @endif All Locations
                                    </a>
                                @endif
                                @foreach($activeLocations as $loc)
                                    @if(in_array($loc->id, $finalizedLocationIds))
                                    <a class="dropdown-item" href="{{ route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $loc->id]) }}">
                                        @if($locationId == $loc->id) <i class="fa fa-check text-success me-2"></i> @endif {{ $loc->name }}
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if ($isFinalized)
                        <div class="dropdown me-2 mb-2">
                            <button class="btn-modern btn-modern-info dropdown-toggle" type="button" id="bankDownloadDropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-file-text me-2"></i> Download Bank Transfer
                            </button>
                            <div class="dropdown-menu" aria-labelledby="bankDownloadDropdown">
                                <a class="dropdown-item" href="{{ route('payroll.bank-icici-xlsx', [$month, $year, 'location_id' => $locationId]) }}">
                                    <i class="fa fa-file-excel me-2"></i> Download payroll for ICICI bank xlsx
                                </a>
                                <a class="dropdown-item" href="{{ route('payroll.bank-csv', [$month, $year, 'location_id' => $locationId]) }}">
                                    <i class="fa fa-file-csv me-2"></i> Download payroll for canara bank csv
                                </a>
                            </div>
                        </div>

                        <div class="dropdown me-2 mb-2">
                            <button class="btn-modern btn-modern-success dropdown-toggle" type="button" id="epfDownloadDropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-file-text me-2"></i> Download EPF Formats
                            </button>
                            <div class="dropdown-menu" aria-labelledby="epfDownloadDropdown">
                                <a class="dropdown-item" href="{{ url('payroll/epf-excel-csv/' . $month . '/' . $year . '?format=1' . ($locationId ? '&location_id='.$locationId : '')) }}">
                                    <i class="fa fa-file-excel me-2"></i> Download EPF XLSX (Format 1)
                                </a>
                                <a class="dropdown-item" href="{{ url('payroll/epf-excel-csv/' . $month . '/' . $year . '?format=2' . ($locationId ? '&location_id='.$locationId : '')) }}">
                                    <i class="fa fa-file-csv me-2"></i> Download EPF CSV (Format 2)
                                </a>
                                <a class="dropdown-item" href="{{ url('payroll/epf-excel-csv/' . $month . '/' . $year . '?format=3' . ($locationId ? '&location_id='.$locationId : '')) }}">
                                    <i class="fa fa-file-text me-2"></i> Download EPF TXT (Format 3)
                                </a>
                            </div>
                        </div>

                        <a href="{{ url('payroll/generateESIExcel/' . $month . '/' . $year . '?format=3' . ($locationId ? '&location_id='.$locationId : '')) }}" class="btn-modern btn-modern-warning me-2 mb-2">
                            <i class="fa fa-file-medical me-2"></i> Download ESIC
                        </a>

                        <!-- Send All Salary Slips Button -->
                        <button type="button" class="btn-modern btn-modern-primary me-2 mb-2" id="sendAllSalarySlipsBtn">
                            <i class="fa fa-envelope me-2"></i> Send All Salary Slips
                        </button>
                    @else
                        <div class="alert alert-modern alert-modern-info mb-0">
                            <i class="fa fa-info-circle me-2"></i>
                            <strong>Bank transfer / EPF / ESIC downloads will be available after payroll finalization.</strong>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- Salary Breakdown Table -->
        <div class="modern-card">
            <div class="modern-card-header">
                <h4><i class="fas fa-chart-line me-2"></i>Salary Breakdown for {{ $monthName }}</h4>
                <div class="mt-2">
                    <small class="text-white-50">Detailed payroll calculation for all employees</small>
                </div>
            </div>
            <div class="modern-card-body">
                <!-- Search and Filter Bar -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fa fa-search text-muted"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="employeeSearch" placeholder="Search employees..." onkeyup="filterEmployees()">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fa fa-info-circle me-1 text-info"></i>
                                Scroll horizontally to view all columns
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-container">
                    <div class="table-scroll-wrapper">
                        {{-- <div class="table-scroll-hint">
                            <i class="fa fa-arrows-alt me-1"></i>Scroll to view more
                        </div> --}}
                    <!-- <div class="d-flex justify-content-end mb-3">
                        @if(count($locations) > 1)
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="locationFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa fa-map-marker-alt me-2"></i> Filter by Location
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="locationFilterDropdown">
                                <li><a class="dropdown-item active" id="location-all" href="javascript:void(0)" onclick="filterTableByLocation('all')">All Locations</a></li>
                                @foreach($locations as $id => $name)
                                    <li><a class="dropdown-item" id="location-{{ $id }}" href="javascript:void(0)" data-id="{{ $id }}" onclick="filterTableByLocation('{{ $name }}')">{{ $name }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                        @else
                           @if(request('location_id'))
                             <button class="btn btn-outline-secondary" disabled>
                                <i class="fa fa-map-marker-alt me-2"></i> {{ \App\Models\Location::find(request('location_id'))->name ?? 'Unknown Location' }}
                            </button>
                           @endif
                        @endif
                    </div> -->
                    <table class="modern-table payroll-table mb-0">
                            <thead>
                                <!-- Main Header Row -->
                                <tr class="header-main">
                                    <th class="employee-header frozen-column" rowspan="2">
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
                                    @if($deductionComponents->count() > 0 || ($hasAdvanceDeductions ?? false))
                                        <th colspan="{{ $deductionComponents->count() + (($hasAdvanceDeductions ?? false) ? 1 : 0) }}" class="deductions-group">
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
                                    <th class="actions-header" rowspan="2">
                                        <div class="header-contents text-center">Actions</div>
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
                                        <?php // print_r($component); ?>
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
                                                @if($component->is_percentage)
                                                    <small class="component-rate">({{ $component->percentage_value }}%)</small>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach
                                    @if($hasAdvanceDeductions ?? false)
                                        <th class="component-header deductions-component">
                                            <div class="header-contents text-center">
                                                <div class="component-name">Advance</div>
                                            </div>
                                        </th>
                                    @endif
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($attendances as $attendance)
                                <tr class="employee-row" 
                                    data-employee-name="{{ strtolower($attendance->employee->name) }}"
                                    data-attendance-id="{{ $attendance->id }}"
                                    data-employee-id="{{ $attendance->employee->id }}"
                                    data-employee-code="{{ $attendance->employee->employee_id }}"
                                    data-employee-designation="{{ $designations[$attendance->employee->designation] ?? 'N/A' }}"
                                    data-employee-location="{{ $attendance->employee->locationObj ? $attendance->employee->locationObj->name : 'N/A' }}"
                                    data-employee-avatar="{{ asset($attendance->employee->profile_image ?? 'assets/img/user-icon.webp') }}"
                                    data-earnings="{{ json_encode($attendance->earnings) }}"
                                    data-deductions="{{ json_encode($attendance->deductions) }}"
                                    data-gross-pay="{{ $attendance->totalEarnings }}"
                                    data-total-deductions="{{ $attendance->totalDeductions }}"
                                    data-net-pay="{{ $attendance->netPay }}"
                                    data-epf-wage="{{ $attendance->epfWage }}">
                                    <!-- Employee Info (Frozen) -->
                                    <td class="frozen-column employee-info">
                                        <div class="employee-details">
                                            <div class="d-flex align-items-center">
                                                <div class="employee-avatar me-3">
                                                    <img src="{{ asset($attendance->employee->profile_image ?? 'assets/img/user-icon.webp') }}" 
                                                         class="rounded-circle"
                                                         width="45" 
                                                         height="45"
                                                         alt="Avatar">
                                                </div>
                                                <div class="employee-info-text">
                                                    <h6 class="employee-name mb-1">{{ $attendance->employee->name }}</h6>
                                                    
                                                     <span class="badge bg-light text-dark">
                                                            <i class="fa fa-map-marker-alt me-1 text-danger"></i>
                                                            {{ $attendance->employee->locationObj ? $attendance->employee->locationObj->name : 'N/A' }}
                                                    </span>
                                                    @if($attendance->is_held)
                                                        <span class="badge bg-warning text-dark" data-bs-toggle="tooltip" title="Salary is currently on hold">
                                                            <i class="fas fa-hand-holding-usd me-1"></i> On Hold
                                                        </span>
                                                    @endif
                                                    @if(in_array($attendance->employee->id, $releasedEmployeeIds ?? []))
                                                        <span class="badge bg-success mt-1" data-bs-toggle="tooltip" title="Salary has been released">
                                                            <i class="fa fa-check-circle me-1"></i> Released
                                                        </span>
                                                    @endif
                                                    @if($isFinalized)
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-lock me-1"></i> Finalized
                                                        </span>
                                                    @endif
                                                    
                                                    @php
                                                        $activeExit = $attendance->employee->exitDetails->whereIn('status', ['Pending', 'Approved', 'Completed'])->first();
                                                    @endphp
                                                    
                                                    @if($activeExit)
                                                        <span class="badge bg-warning text-dark mt-1" data-bs-toggle="tooltip" title="Exit Status: {{ $activeExit->status }} (LWD: {{ $activeExit->last_working_day ? $activeExit->last_working_day->format('d M Y') : 'N/A' }})">
                                                            <i class="fas fa-sign-out-alt me-1"></i> Exit Process
                                                        </span>
                                                    @endif
                                                    <div class="employee-meta">
                                                        <span class="badge bg-light text-dark me-2">
                                                            <i class="fa fa-id-card me-1 text-primary"></i>
                                                            {{ $attendance->employee->employee_id }}
                                                        </span>
                                                        <!-- <span class="badge bg-light text-dark">
                                                            <i class="fa fa-map-marker-alt me-1 text-danger"></i>
                                                            {{ $attendance->employee->locationObj ? $attendance->employee->locationObj->name : 'N/A' }}
                                                        </span> -->
                                                            
                                                        @if($attendance->employee->designation)
                                                            <small class="text-muted">
                                                                {{ $designations[$attendance->employee->designation] ?? 'N/A' }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <!-- Attendance (Frozen) -->
                                    <td class="attendance-cell text-center">
                                        <div class="attendance-info">
                                            <span class="attendance-days">{{ $attendance->employee_worked_days }}</span>
                                            <div class="attendance-progress">
                                                <div class="progress" style="height: 3px;">
                                                    <div class="progress-bar bg-success" 
                                                         style="width: {{ ($attendance->employee_worked_days / $attendance->total_working_days) * 100 }}%">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="attendance-cell text-center">
                                        <span class="total-days">{{ $attendance->total_working_days }}</span>
                                    </td>
                                    <!-- Earnings Columns -->
                                    @foreach($earningComponents as $component)
                                    <td class="earnings-cell text-center {{ isset($attendance->earnings[$component->id]['overridden']) && $attendance->earnings[$component->id]['overridden'] ? 'overridden-value' : '' }}">
                                        @if(isset($attendance->earnings[$component->id]['applicable']) && !$attendance->earnings[$component->id]['applicable'])
                                            <span class="not-applicable">N/A</span>
                                        @else
                                            <span class="amount">
                                                <span class="currency-symbol">₹</span>{{ number_format($attendance->earnings[$component->id]['value'] ?? 0, 2) }}
                                                @if(isset($attendance->earnings[$component->id]['overridden']) && $attendance->earnings[$component->id]['overridden'])
                                                    <i class="fa fa-pencil-alt ms-1 text-warning" data-bs-toggle="tooltip" title="Manually adjusted"></i>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    @endforeach
                                    <td class="gross-pay-cell text-center">
                                        <span class="amount-highlight gross"><span class="currency-symbol">₹</span>{{ number_format($attendance->totalEarnings, 2) }}</span>
                                    </td>
                                    <td class="epf-cell text-center">
                                        <span class="amount"><span class="currency-symbol">₹</span>{{ number_format($attendance->epfWage, 2) }}</span>
                                    </td>
                                    <!-- Deductions Columns -->
                                    @foreach($deductionComponents as $component)
                                    <td class="deductions-cell text-center {{ isset($attendance->deductions[$component->id]['overridden']) && $attendance->deductions[$component->id]['overridden'] ? 'overridden-value' : '' }}">
                                        @if(isset($attendance->deductions[$component->id]['applicable']) && !$attendance->deductions[$component->id]['applicable'])
                                            <span class="not-applicable">N/A</span>
                                        @else
                                            <span class="amount">
                                                <span class="currency-symbol">₹</span>{{ number_format($attendance->deductions[$component->id]['value'] ?? 0, 2) }}
                                                @if(isset($attendance->deductions[$component->id]['overridden']) && $attendance->deductions[$component->id]['overridden'])
                                                    <i class="fa fa-pencil-alt ms-1 text-warning" data-bs-toggle="tooltip" title="Manually adjusted"></i>
                                                @endif
                                            </span>
                                        @endif
                                    </td>
                                    @endforeach
                                    @if($hasAdvanceDeductions ?? false)
                                        <td class="deductions-cell text-center">
                                            @if(isset($attendance->deductions['advance']['applicable']) && $attendance->deductions['advance']['applicable'])
                                                <span class="amount"><span class="currency-symbol">₹</span>{{ number_format($attendance->deductions['advance']['value'] ?? 0, 2) }}</span>
                                            @else
                                                <span class="not-applicable">N/A</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="total-deductions-cell text-center">
                                        <span class="amount-highlight deduction"><span class="currency-symbol">₹</span>{{ number_format($attendance->totalDeductions, 2) }}</span>
                                    </td>
                                    <td class="net-pay-cell text-center">
                                        <span class="amount-highlight net"><span class="currency-symbol">₹</span>{{ number_format($attendance->netPay, 2) }}</span>
                                    </td>
                                    <td class="early-salary-cell text-center">
                                        <div class="form-check">
                                            <input class="form-check-input early-salary-checkbox" 
                                                   type="checkbox" 
                                                   value="1"
                                                   data-employee-id="{{ $attendance->emp_id }}"
                                                   data-month="{{ $month }}"
                                                   data-year="{{ $year }}"
                                                   {{ $attendance->early_salary_processed ? 'checked' : '' }}
                                                   {{ $isFinalized ? 'disabled' : '' }}>
                                        </div>
                                    </td>
                                    <td class="actions-cell text-center">
                                        <div class="btn-group" role="group">
                                            <button class="btn btn-sm {{ $isFinalized ? 'btn-outline-secondary disabled' : 'btn-outline-primary' }} adjust-salary-btn" 
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ $isFinalized ? 'Payroll finalized - editing disabled' : 'Adjust Salary' }}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm {{ $isFinalized ? 'btn-outline-secondary disabled' : 'btn-outline-info' }} manage-advances-btn"
                                                    data-bs-toggle="tooltip"
                                                    data-employee-id="{{ $attendance->employee->id }}"
                                                    data-employee-name="{{ $attendance->employee->name }}"
                                                    title="Manage Advances">
                                                <i class="fa-solid fa-credit-card"></i>
                                            </button>
                                            @if($isFinalized && !empty($attendance->employee->email))
                                                <button class="btn btn-sm btn-outline-success send-salary-slip-btn" 
                                                        data-bs-toggle="tooltip"
                                                        data-employee-id="{{ $attendance->employee->id }}"
                                                        data-employee-name="{{ $attendance->employee->name }}"
                                                        data-employee-email="{{ $attendance->employee->email }}"
                                                        data-month="{{ $month }}"
                                                        data-year="{{ $year }}"
                                                        title="Send Salary Slip">
                                                    <i class="fa fa-envelope"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons-container">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    @if (!$isFinalized)
                        <a href="{{ route('payroll.attendance', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) }}" class="btn-modern btn-modern-light">
                            <i class="fa fa-arrow-left me-2"></i> Back to Attendance
                        </a>
                    @else
                        <button class="btn-modern btn-modern-light" disabled>
                            <i class="fa fa-lock me-2"></i> Back to Attendance
                        </button>
                    @endif
                    
                    @if(!$isFinalized)
                        <a href="{{ route('payroll.comparison', ['month' => $month, 'year' => $year, 'location_id' => request('location_id')]) }}" class="btn-modern btn-modern-success">
                            <i class="fa fa-arrow-right me-2"></i> Proceed to Comparison
                        </a>
                    @else
                        <button class="btn-modern btn-modern-success" disabled>
                            <i class="fa fa-lock me-2"></i> Payroll Finalized
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Adjust Salary Modal -->
<div class="modal fade" id="adjustSalaryModal" tabindex="-1" aria-labelledby="adjustSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-bottom: none;">
                <h5 class="modal-title" id="adjustSalaryModalLabel">
                    <i class="fa fa-edit me-2"></i>
                    Adjust Salary Components
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle me-2"></i>
                    Note: You can override non-protected salary components. Protected components (EPF, ESI, etc.) are disabled.
                </div>
                <div class="employee-summary mb-4 p-3 border rounded">
                    <div class="d-flex align-items-center">
                        <img id="modalEmployeeAvatar" src="" class="rounded-circle me-3" width="60" height="60" alt="Employee Avatar">
                        <div>
                            <h5 id="modalEmployeeName" class="mb-1"></h5>
                            <div class="d-flex">
                                <span class="badge bg-light text-dark me-2">
                                    <i class="fa fa-id-card me-1 text-primary"></i>
                                    <span id="modalEmployeeId"></span>
                                </span>
                                <span id="modalEmployeeDesignation" class="text-muted"></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fa fa-plus-circle me-2"></i> Earnings</h6>
                            </div>
                            <div class="card-body">
                                <div id="earningsContainer" class="component-container"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header bg-danger text-white">
                                <h6 class="mb-0"><i class="fa fa-minus-circle me-2"></i> Deductions</h6>
                            </div>
                            <div class="card-body">
                                <div id="deductionsContainer" class="component-container"></div>
                            </div>
                        </div>
                        
                        <!-- Advance Deduction Info (Read-only) -->
                        <div class="card mb-4" id="advanceDeductionCard" style="display: none;">
                            <div class="card-header bg-warning text-dark">
                                <h6 class="mb-0"><i class="fa fa-money me-2"></i> Advance Deduction (Read-only)</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info small mb-2">
                                    <i class="fa fa-info-circle me-1"></i>
                                    Advance deductions are managed through the "Manage Advances" button and cannot be overridden here.
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span><strong>Advance Deduction:</strong></span>
                                    <span class="badge bg-warning text-dark" id="advanceDeductionAmount">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="summary-card mb-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="card bg-success text-white mb-3">
                                <div class="card-body p-2 text-center">
                                    <h6 class="card-title mb-1">Gross Pay</h6>
                                    <h4 id="modalGrossPay" class="mb-0"><span class="currency-symbol">₹</span>0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-danger text-white mb-3">
                                <div class="card-body p-2 text-center">
                                    <h6 class="card-title mb-1">Total Deductions</h6>
                                    <h4 id="modalTotalDeductions" class="mb-0"><span class="currency-symbol">₹</span>0.00</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card text-white mb-3" style="background-color: #3fcce3;">
                                <div class="card-body p-2 text-center">
                                    <h6 class="card-title mb-1">Net Pay</h6>
                                    <h4 id="modalNetPay" class="mb-0"><span class="currency-symbol">₹</span>0.00</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveOverridesBtn">
                    <i class="fa fa-save me-2"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Advance Management Modal -->
<div class="modal fade" id="manageAdvanceModal" tabindex="-1" aria-labelledby="manageAdvanceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-bottom: none;">
                <h5 class="modal-title" id="manageAdvanceModalLabel">Manage Advances</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body" id="manageAdvanceModalBody">
                <!-- Add/Edit Advance Form -->
                <div class="card">
                    <div class="card-body">
                        <form id="add-advance-form" action="/advance/add" method="POST">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="employee_id" value="">
                            <input type="hidden" name="advance_id" id="advance_id">
                            <h5 class="mb-4">
                                <i class="fa fa-plus-circle me-2 text-primary"></i>
                                Add New Advance for <strong>Employee Name</strong>
                            </h5>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="advance_amount" class="form-label">
                                            <i class="fa fa-rupee-sign me-1 text-success"></i>Advance Amount *
                                        </label>
                                        <input type="number" class="form-control" id="advance_amount" name="advance_amount" step="0.01" min="1" placeholder="0.00" required>
                                        <div class="invalid-feedback" id="advance_amount_error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tenure_months" class="form-label">
                                            <i class="fa fa-calendar-alt me-1 text-primary"></i>Tenure (Months) *
                                        </label>
                                        <input type="number" class="form-control" id="tenure_months" name="tenure_months" min="1" max="60" placeholder="1-60" required>
                                        <div class="invalid-feedback" id="tenure_months_error"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="start_date" class="form-label">
                                            <i class="fa fa-play me-1 text-info"></i>Start Month *
                                        </label>
                                        <input type="month" class="form-control" id="start_date" name="start_date" value="{{ $year }}-{{ sprintf('%02d', $month) }}" min="{{ $year }}-{{ sprintf('%02d', $month) }}" required>
                                        <div class="invalid-feedback" id="start_date_error"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="notes" class="form-label">
                                            <i class="fa fa-sticky-note me-1 text-warning"></i>Notes (Optional)
                                        </label>
                                        <textarea class="form-control" id="notes" name="notes" rows="1" placeholder="Optional notes about the advance..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-success">
                                            <i class="fa fa-calculator me-1"></i>Monthly Deduction (Auto Calculated)
                                        </label>
                                        <input type="text" class="form-control bg-light" id="monthly_deduction_display" readonly placeholder="Will be calculated automatically...">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label text-info">
                                            <i class="fa fa-stop me-1"></i>End Month (Auto Calculated)
                                        </label>
                                        <input type="text" class="form-control bg-light" id="end_date_display" readonly placeholder="Will be calculated automatically...">
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center pt-3">
                                <div>
                                    <button type="submit" class="btn btn-primary" id="save-advance-btn">
                                        <i class="fa fa-save me-2"></i>Save Advance
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary ms-2" id="cancel-edit-btn" style="display: none;">
                                        <i class="fa fa-times me-2"></i>Cancel Edit
                                    </button>
                                </div>
                                <small class="text-muted">
                                    <i class="fa fa-info-circle me-1 text-info"></i>
                                    Fields marked with * are required
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- Existing Advances Table -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="fa fa-list me-2 text-secondary"></i>
                            Existing Advances
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        <div id="existing-advances-list" class="advances-table-wrapper">
                            <div class="table-responsive advances-table-scroll">
                                <table class="table table-hover mb-0 table-sm">
                                    <thead class="bg-light sticky-top">
                                        <tr>
                                            <th class="border-0" style="min-width: 100px;">
                                                <i class="fa fa-rupee-sign me-1 text-success"></i>Amount
                                            </th>
                                            <th class="border-0" style="min-width: 80px;">
                                                <i class="fa fa-calendar me-1 text-primary"></i>Tenure
                                            </th>
                                            <th class="border-0" style="min-width: 120px;">
                                                <i class="fa fa-minus me-1 text-danger"></i>Monthly Deduction
                                            </th>
                                            <th class="border-0" style="min-width: 100px;">
                                                <i class="fa fa-rupee-sign me-1 text-warning"></i>Remaining
                                            </th>
                                            <th class="border-0" style="min-width: 180px;">
                                                <i class="fa fa-calendar-alt me-1 text-info"></i>Period
                                            </th>
                                            <th class="border-0" style="min-width: 80px;">
                                                <i class="fa fa-info me-1 text-secondary"></i>Status
                                            </th>
                                            <th class="border-0 text-center" style="min-width: 120px;">
                                                <i class="fa fa-cog me-1 text-dark"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="advances-table-body">
                                        <!-- Dynamic content will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="no-advances-message" class="text-center p-4" style="display: none;">
                                <div class="mb-3">
                                    <i class="fa fa-inbox fa-2x text-muted"></i>
                                </div>
                                <h6 class="text-muted">No Advances Found</h6>
                                <p class="text-muted mb-0 small">This employee has no advance records yet. Add one above to get started.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Payroll context - current month and year being viewed
    const currentPayrollMonth = {{ $month }};
    const currentPayrollYear = {{ $year }};
    
    // Active component IDs (only status = '1' and have actual data)
    const activeEarningComponentIds = @json($earningComponents->pluck('id')->toArray());
    const activeDeductionComponentIds = @json($deductionComponents->pluck('id')->toArray());
    const hasAdvanceDeductions = @json($hasAdvanceDeductions ?? false);
    
    // Currency formatting function
    function formatCurrency(amount) {
        return '₹' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Handle early salary processed checkbox changes
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap tooltips for override indicators
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Add event listener for early salary checkboxes
        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('early-salary-checkbox')) {
                const checkbox = e.target;
                const employeeId = checkbox.getAttribute('data-employee-id');
                const month = checkbox.getAttribute('data-month');
                const year = checkbox.getAttribute('data-year');
                const isChecked = checkbox.checked;
                
                // Show loading state
                checkbox.disabled = true;
                
                // Send AJAX request
                fetch('{{ route("payroll.update-early-salary-processed") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        month: month,
                        year: year,
                        early_salary_processed: isChecked
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success message
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        // Revert checkbox state on error
                        checkbox.checked = !isChecked;
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Failed to update early salary status'
                        });
                    }
                })
                .catch(error => {
                    // Revert checkbox state on error
                    checkbox.checked = !isChecked;
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while updating early salary status'
                    });
                })
                .finally(() => {
                    // Re-enable checkbox
                    checkbox.disabled = false;
                });
            }
        });
    });
    
    // Global modal instance
    let adjustSalaryModal = null;

    

    // Search functionality

    function filterEmployees() {

        const searchTerm = document.getElementById('employeeSearch').value.toLowerCase();

        const rows = document.querySelectorAll('.employee-row');

        

        rows.forEach(row => {

            const employeeName = row.getAttribute('data-employee-name');

            if (employeeName.includes(searchTerm)) {

                row.style.display = '';

            } else {

                row.style.display = 'none';

            }

        });

    }

    

    // Initialize tooltips and modal

    document.addEventListener('DOMContentLoaded', function() {

        // Initialize tooltips

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));

        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {

            return new bootstrap.Tooltip(tooltipTriggerEl);

        });

        

        // Initialize modal instance

        adjustSalaryModal = new bootstrap.Modal(

            document.getElementById('adjustSalaryModal')

        );

        

        // Add close handlers for modal buttons

        document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(btn => {

            btn.addEventListener('click', function() {

                adjustSalaryModal.hide();

            });

        });

        

        // Smooth scrolling indicator

        const scrollWrapper = document.querySelector('.table-scroll-wrapper');

        let scrollTimeout;

        

        scrollWrapper.addEventListener('scroll', function() {

            this.classList.add('scrolling');

            

            clearTimeout(scrollTimeout);

            scrollTimeout = setTimeout(() => {

                this.classList.remove('scrolling');

            }, 150);

        });

    });

    

    // Finalize payroll button removed - now redirects to comparison page via link



    // Store component data globally

    let currentEmployeeData = null;

    let currentAttendanceId = null;

    

   // Function to open the adjust salary modal
    function openAdjustSalaryModal(attendanceId, employeeId) {
        // Find the row data
        const row = document.querySelector(`.employee-row[data-attendance-id="${attendanceId}"]`);
        if (!row) return;

        // Get employee data from data attributes
        const employeeData = {
            id: employeeId,
            name: row.dataset.employeeName,
            employee_id: row.dataset.employeeCode,
            designation: row.dataset.employeeDesignation,
            avatar: row.dataset.employeeAvatar,
            attendance_id: attendanceId,
            earnings: JSON.parse(row.dataset.earnings),
            deductions: JSON.parse(row.dataset.deductions),
            gross_pay: parseFloat(row.dataset.grossPay),
            total_deductions: parseFloat(row.dataset.totalDeductions),
            net_pay: parseFloat(row.dataset.netPay),
            epf_wage: parseFloat(row.dataset.epfWage)
        };

        // Set global variables
        currentEmployeeData = employeeData;
        currentAttendanceId = attendanceId;

        // Update modal header
        document.getElementById('modalEmployeeName').textContent = employeeData.name;
        document.getElementById('modalEmployeeId').textContent = employeeData.employee_id;
        document.getElementById('modalEmployeeDesignation').textContent = employeeData.designation;
        document.getElementById('modalEmployeeAvatar').src = employeeData.avatar;

        // Update summary cards with stored values
        document.getElementById('modalGrossPay').innerHTML = formatCurrency(employeeData.gross_pay);
        document.getElementById('modalTotalDeductions').innerHTML = formatCurrency(employeeData.total_deductions);
        document.getElementById('modalNetPay').innerHTML = formatCurrency(employeeData.net_pay);

        // Render earnings and deductions components
        renderComponents('earningsContainer', employeeData.earnings, 'earning');
        renderComponents('deductionsContainer', employeeData.deductions, 'deduction');

        // Handle advance deduction display
        const advanceDeductionCard = document.getElementById('advanceDeductionCard');
        const advanceDeductionAmount = document.getElementById('advanceDeductionAmount');
        
        if (employeeData.deductions.advance && employeeData.deductions.advance.applicable) {
            advanceDeductionCard.style.display = 'block';
            advanceDeductionAmount.textContent = formatCurrency(employeeData.deductions.advance.value);
        } else {
            advanceDeductionCard.style.display = 'none';
        }

        // Disable inputs if payroll is finalized
        @if($isFinalized)
            document.querySelectorAll('.override-toggle, .override-value, .reset-btn').forEach(el => {
                el.disabled = true;
            });
            document.getElementById('saveOverridesBtn').disabled = true;
        @endif

        // Show modal
        adjustSalaryModal.show();
    }

    // Function to render components in the modal
    function renderComponents(containerId, components, type) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';

        Object.entries(components).forEach(([componentId, component]) => {
            // Skip protected components and advance deductions
            const protectedIds = type === 'earning' ? [1, 2, 4] : [1, 2, 3, 4];
            const isNumericId = !isNaN(parseInt(componentId));
            
            // Skip components that are not active (check against active component IDs)
            if (isNumericId) {
                const activeComponentIds = type === 'earning' ? activeEarningComponentIds : activeDeductionComponentIds;
                if (!activeComponentIds.includes(parseInt(componentId))) {
                    return;
                }
            }
            
            // Skip advance deductions (by key, type, or name)
            if (componentId === 'advance' || 
                component.type === 'advance' || 
                (component.name && component.name.toLowerCase().includes('advance'))) {
                return;
            }
            
            // Skip protected numeric IDs
            if (isNumericId && protectedIds.includes(parseInt(componentId))) {
                return;
            }

            const isApplicable = component.applicable || false;
            const isOverridden = component.overridden || false;
            const defaultValue = component.default_value || component.value;
            const currentValue = isOverridden ? component.value : defaultValue;

            const componentDiv = document.createElement('div');
            componentDiv.className = 'mb-3 component-item';
            componentDiv.dataset.componentId = componentId;
            componentDiv.dataset.componentType = component.type;

            if (isApplicable) {
                componentDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">${component.name}</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input override-toggle" type="checkbox" 
                                ${isOverridden ? 'checked' : ''} 
                                id="toggle-${type}-${componentId}"
                                ${@json($isFinalized) ? 'disabled' : ''}>
                            <label class="form-check-label small" for="toggle-${type}-${componentId}">
                                ${isOverridden ? 'Overridden' : 'Override'}
                            </label>
                        </div>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control override-value" 
                            value="${currentValue.toFixed(2)}" 
                            min="0" step="0.01"
                            ${!isOverridden || @json($isFinalized) ? 'disabled' : ''}
                            data-default="${defaultValue.toFixed(2)}"
                            data-is-overridden="${isOverridden}">
                        <button class="btn btn-outline-secondary reset-btn" type="button" 
                            ${!isOverridden || @json($isFinalized) ? 'disabled' : ''}>
                            <i class="fa fa-undo"></i>
                        </button>
                    </div>
                    <div class="form-text small">
                        Default: ₹${defaultValue.toFixed(2)}
                        ${isOverridden ? `<span class="text-success ms-2">Overridden</span>` : ''}
                    </div>
                `;
            } else {
                componentDiv.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label mb-0">${component.name}</label>
                        <span class="text-muted small">Not Applicable</span>
                    </div>
                    <div class="input-group">
                        <span class="input-group-text">₹</span>
                        <input type="number" class="form-control override-value" 
                            value="${currentValue.toFixed(2)}" 
                            min="0" step="0.01"
                            disabled
                            data-default="${defaultValue.toFixed(2)}"
                            data-is-overridden="${isOverridden}">
                        <button class="btn btn-outline-secondary reset-btn" type="button" disabled>
                            <i class="fa fa-undo"></i>
                        </button>
                    </div>
                    <div class="form-text small text-danger">
                        This component is not enabled for this employee and cannot be overridden.
                    </div>
                `;
            }

            container.appendChild(componentDiv);
        });

        // Add event listeners only if not finalized
        @if(!$isFinalized)
            container.querySelectorAll('.override-toggle').forEach(toggle => {
                toggle.addEventListener('change', function() {
                    const input = this.closest('.component-item').querySelector('.override-value');
                    const resetBtn = this.closest('.component-item').querySelector('.reset-btn');

                    if (this.checked) {
                        input.disabled = false;
                        resetBtn.disabled = false;
                    } else {
                        input.disabled = true;
                        resetBtn.disabled = true;
                        const defaultValue = parseFloat(input.dataset.default);
                        input.value = defaultValue.toFixed(2);
                    }
                });
            });

            container.querySelectorAll('.reset-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const input = this.closest('.input-group').querySelector('.override-value');
                    const defaultValue = parseFloat(input.dataset.default);
                    input.value = defaultValue.toFixed(2);
                });
            });
        @endif
    }

    

    // Save overrides button handler

    document.getElementById('saveOverridesBtn').addEventListener('click', function() {
    const overrides = [];
    const removals = []; // Track components where override was unchecked
    
    // Collect earnings overrides and removals
    document.querySelectorAll('#earningsContainer .component-item').forEach(item => {
        const toggle = item.querySelector('.override-toggle');
        const input = item.querySelector('.override-value');
        const componentId = item.dataset.componentId;
        const componentType = item.dataset.componentType;
        const wasOverridden = input.dataset.isOverridden === 'true'; // Check if it was previously overridden
        
        if (toggle && toggle.checked) {
            // Override is enabled - save the new value
            const overrideValue = parseFloat(input.value);
            const defaultValue = parseFloat(input.dataset.default);
            
            overrides.push({
                component_id: componentId,
                component_type: componentType,
                override_value: overrideValue,
                default_value: defaultValue
            });
        } else if (wasOverridden && toggle && !toggle.checked) {
            // Override was previously enabled but now unchecked - remove it
            removals.push({
                component_id: componentId,
                component_type: componentType
            });
        }
    });
    
    // Collect deductions overrides and removals
    document.querySelectorAll('#deductionsContainer .component-item').forEach(item => {
        const toggle = item.querySelector('.override-toggle');
        const input = item.querySelector('.override-value');
        const componentId = item.dataset.componentId;
        const componentType = item.dataset.componentType;
        const wasOverridden = input.dataset.isOverridden === 'true'; // Check if it was previously overridden
        
        if (toggle && toggle.checked) {
            // Override is enabled - save the new value
            const overrideValue = parseFloat(input.value);
            const defaultValue = parseFloat(input.dataset.default);
            
            overrides.push({
                component_id: componentId,
                component_type: componentType,
                override_value: overrideValue,
                default_value: defaultValue
            });
        } else if (wasOverridden && toggle && !toggle.checked) {
            // Override was previously enabled but now unchecked - remove it
            removals.push({
                component_id: componentId,
                component_type: componentType
            });
        }
    });
    
    if (overrides.length === 0 && removals.length === 0) {
        showToast('No changes to save', 'info');
        return;
    }
    
    // Show loading state
    const saveBtn = document.getElementById('saveOverridesBtn');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Saving...';
    
    // Prepare all requests (saves and removals)
    const savePromises = [];
    
    // Add save override requests
    overrides.forEach(override => {
        savePromises.push(
            fetch('{{ route("payroll.save-component-override") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    attendance_id: currentAttendanceId,
                    employee_id: currentEmployeeData.id,
                    component_id: override.component_id,
                    component_type: override.component_type,
                    override_value: override.override_value,
                    default_value: override.default_value
                })
            }).then(response => response.json())
        );
    });
    
    // Add removal requests (set override_value to null to revert to default)
    removals.forEach(removal => {
        savePromises.push(
            fetch('{{ route("payroll.save-component-override") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    attendance_id: currentAttendanceId,
                    employee_id: currentEmployeeData.id,
                    component_id: removal.component_id,
                    component_type: removal.component_type,
                    override_value: null, // null means revert to default
                    remove_override: true
                })
            }).then(response => response.json())
        );
    });
    
    // Wait for all requests to complete
    Promise.all(savePromises)
        .then(results => {
            const allSuccess = results.every(res => res.success);
            if (allSuccess) {
                // Close modal
                adjustSalaryModal.hide();
                
                // Show success message
                const message = overrides.length > 0 && removals.length > 0 
                    ? `${overrides.length} override(s) saved and ${removals.length} reverted to default!`
                    : overrides.length > 0 
                    ? 'Overrides saved successfully!'
                    : 'Overrides reverted to default!';
                    
                showToast(message + ' Page will refresh in 2 seconds...', 'success');
                
                // Refresh page after delay to get updated data
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                const errors = results.filter(res => !res.success).map(res => res.message);
                showToast('Error saving some overrides: ' + errors.join(', '), 'error');
            }
        })
        .catch(error => {

            showToast('Error: ' + error.message, 'error');
        })
        .finally(() => {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa fa-save me-2"></i> Save Changes';
        });
});

      // Helper function to show toast notifications
    function showToast(message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            icon: type === 'success' ? 'success' : (type === 'error' || type === 'danger') ? 'error' : 'info',
            title: message
        });
    }

    

    // Add data attributes to employee rows

    document.querySelectorAll('.employee-row').forEach(row => {

        const attendanceId = row.dataset.attendanceId;

        const employeeId = row.dataset.employeeId;

        const adjustBtn = row.querySelector('.btn-outline-primary');

        

        if (adjustBtn) {

            adjustBtn.addEventListener('click', () => {

                openAdjustSalaryModal(attendanceId, employeeId);

            });

        }

    }); 



    document.querySelectorAll('.btn-outline-primary').forEach(btn => {

        btn.addEventListener('click', function(e) {

            @if($isFinalized)

                e.preventDefault();

                Swal.fire({

                    title: 'Payroll Finalized',

                    text: 'This payroll has been finalized and cannot be modified',

                    icon: 'info',

                    confirmButtonText: 'OK'

                });

            @else

                const attendanceId = this.closest('tr').dataset.attendanceId;

                const employeeId = this.closest('tr').dataset.employeeId;

                openAdjustSalaryModal(attendanceId, employeeId);

            @endif

        });

    });
    // Manage Advances modal script
document.addEventListener('DOMContentLoaded', function () {
    let manageAdvanceModal = null;
    let currentEmployeeId = null;
    let currentEmployeeName = null;

    // Initialize modal
    try {
        const modalElement = document.getElementById('manageAdvanceModal');
        if (modalElement) {
            // Try Bootstrap 5 Modal API first
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                manageAdvanceModal = new bootstrap.Modal(modalElement, {
                    backdrop: 'static',
                    keyboard: true
                });
            }
            // Fallback to jQuery if available
            else if (typeof $ !== 'undefined' && $.fn.modal) {
                manageAdvanceModal = {
                    show: function() { $('#manageAdvanceModal').modal('show'); },
                    hide: function() { $('#manageAdvanceModal').modal('hide'); }
                };
            }
            // Last resort - direct DOM manipulation
            else {
                manageAdvanceModal = {
                    show: function() { 
                        modalElement.classList.add('show');
                        modalElement.style.display = 'block';
                        document.body.classList.add('modal-open');
                    },
                    hide: function() { 
                        modalElement.classList.remove('show');
                        modalElement.style.display = 'none';
                        document.body.classList.remove('modal-open');
                    }
                };
            }
        }        } catch (error) {
            // Fallback modal object
            manageAdvanceModal = {
                show: function() { 
                    const modal = document.getElementById('manageAdvanceModal');
                    if (modal) {
                        modal.classList.add('show');
                        modal.style.display = 'block';
                        document.body.classList.add('modal-open');
                    }
                },
                hide: function() { 
                    const modal = document.getElementById('manageAdvanceModal');
                    if (modal) {
                        modal.classList.remove('show');
                        modal.style.display = 'none';
                        document.body.classList.remove('modal-open');
                    }
                }
            };
        }

    // Handle clicks on "Manage Advances" buttons
    document.addEventListener('click', function(e) {
        if (e.target.closest('.manage-advances-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.manage-advances-btn');
            
            // Check if button is disabled
            if (button.classList.contains('disabled')) {
                return;
            }
            
            currentEmployeeId = button.dataset.employeeId;
            currentEmployeeName = button.dataset.employeeName;

            if (!manageAdvanceModal) {
                return;
            }

            // Update modal title and form
            document.getElementById('manageAdvanceModalLabel').innerHTML = `Manage Advances - ${currentEmployeeName}`;
            const employeeIdInput = document.querySelector('#add-advance-form input[name="employee_id"]');
            const employeeNameElement = document.querySelector('#add-advance-form h5 strong');
            
            if (employeeIdInput) employeeIdInput.value = currentEmployeeId;
            if (employeeNameElement) employeeNameElement.textContent = currentEmployeeName;

            // Reset form
            resetAdvanceForm();

            // Load advances for this employee
            loadEmployeeAdvances(currentEmployeeId);

            // Show modal
            manageAdvanceModal.show();
        }
    });

    // Handle modal close button clicks
    document.addEventListener('click', function(e) {
        // Close button in header
        if (e.target.matches('[data-bs-dismiss="modal"]') || e.target.closest('[data-bs-dismiss="modal"]')) {
            e.preventDefault();
            if (manageAdvanceModal) {
                manageAdvanceModal.hide();
            }
        }
        
        // Direct close button handler
        if (e.target.closest('.btn-close')) {
            e.preventDefault();
            if (manageAdvanceModal) {
                manageAdvanceModal.hide();
            }
        }
    });

    // Add direct event listeners to close buttons after DOM is ready
    setTimeout(function() {
        // Header close button
        const headerCloseBtn = document.querySelector('#manageAdvanceModal .btn-close');
        if (headerCloseBtn) {
            headerCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (manageAdvanceModal) {
                    manageAdvanceModal.hide();
                }
            });
        }
        
        // Footer close button
        const footerCloseBtn = document.querySelector('#manageAdvanceModal .modal-footer .btn-secondary');
        if (footerCloseBtn) {
            footerCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (manageAdvanceModal) {
                    manageAdvanceModal.hide();
                }
            });
        }
    }, 100);

    // Handle ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && manageAdvanceModal) {
            manageAdvanceModal.hide();
        }
    });

    // Handle backdrop clicks
    document.addEventListener('click', function(e) {
        if (e.target.matches('#manageAdvanceModal.modal.show')) {
            manageAdvanceModal.hide();
        }
    });

    // Load employee advances function
    function loadEmployeeAdvances(employeeId) {
        const tableBody = document.getElementById('advances-table-body');
        const noAdvancesMessage = document.getElementById('no-advances-message');
        
        if (!tableBody) {
            return;
        }
        
        // Show loading
        tableBody.innerHTML = '<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading advances...</td></tr>';
        noAdvancesMessage.style.display = 'none';

        fetch(`/employees/${employeeId}/advances`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    populateAdvancesTable(data.advances);
                } else {
                    throw new Error(data.message || 'Failed to load advances');
                }
            })
            .catch(error => {
                tableBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Failed to load advances</td></tr>';
            });
    }

    // Populate advances table function
    function populateAdvancesTable(advances) {
        const tableBody = document.getElementById('advances-table-body');
        const noAdvancesMessage = document.getElementById('no-advances-message');

        if (!advances || advances.length === 0) {
            tableBody.innerHTML = '';
            noAdvancesMessage.style.display = 'block';
            return;
        }

        noAdvancesMessage.style.display = 'none';
        let tableHTML = '';

        advances.forEach(advance => {
            // Determine status based on tenure dates relative to current payroll month/year
            function parseYMD(dateStr) {
                // Expecting YYYY-MM-DD or YYYY-MM format
                if (!dateStr) return null;
                const parts = dateStr.split('-');
                if (parts.length >= 2) {
                    const y = parseInt(parts[0], 10);
                    const m = parseInt(parts[1], 10) - 1; // JS months are 0-indexed
                    const d = parts[2] ? parseInt(parts[2], 10) : 1;
                    return new Date(y, m, d);
                }
                const dt = new Date(dateStr);
                return isNaN(dt.getTime()) ? null : dt;
            }

            // Use the payroll month/year being viewed, not today's date
            const payrollDate = new Date(currentPayrollYear, currentPayrollMonth - 1, 1);
            payrollDate.setHours(0, 0, 0, 0);

            const startDate = parseYMD(advance.start_date);
            const endDate = parseYMD(advance.end_date);

            let status = (advance.status || 'active').toString();

            // Compare advance tenure with the payroll period being viewed
            if (startDate && endDate) {
                // Extract year-month only for comparison
                const startYM = startDate.getFullYear() * 100 + startDate.getMonth();
                const endYM = endDate.getFullYear() * 100 + endDate.getMonth();
                const payrollYM = currentPayrollYear * 100 + (currentPayrollMonth - 1);

                // If payroll month is before the advance starts
                if (payrollYM < startYM) {
                    status = 'not_started';
                }
                // If payroll month is after the advance ends
                else if (payrollYM > endYM) {
                    status = 'completed';
                }
                // Otherwise it's active for this payroll month
                else if (status !== 'cancelled') {
                    status = 'active';
                }
            }

            // Map status to bootstrap badge color
            const statusBadge = status === 'active' ? 'success' :
                                status === 'completed' ? 'info' :
                                status === 'cancelled' ? 'danger' :
                                status === 'not_started' ? 'warning' : 'primary';
            
            const totalDeducted = advance.deductions ? advance.deductions.reduce((sum, d) => sum + parseFloat(d.amount), 0) : 0;
            const remainingAmount = parseFloat(advance.advance_amount) - totalDeducted;

            tableHTML += `
                <tr>
                    <td>${formatCurrency(advance.advance_amount)}</td>
                    <td>${advance.tenure_months} months</td>
                    <td>${formatCurrency(advance.monthly_deduction)}</td>
                    <td><span class="${remainingAmount <= 0 ? 'text-success' : ''}">${formatCurrency(remainingAmount)}</span></td>
                    <td>${advance.start_date} - ${advance.end_date}</td>
                    <td><span class="badge bg-${statusBadge}">${status.charAt(0).toUpperCase() + status.slice(1)}</span></td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            ${status === 'active' ? `
                                <button class="btn btn-outline-primary edit-advance-btn" data-advance='${JSON.stringify(advance)}' title="Edit Advance">
                                    <i class="fa fa-edit"></i>
                                </button>
                                <button class="btn btn-outline-danger delete-advance-btn" data-advance-id="${advance.id}" title="Delete Advance">
                                    <i class="fa fa-trash"></i>
                                </button>
                            ` : `
                                <span class="text-muted small">No actions available</span>
                            `}
                        </div>
                    </td>
                </tr>
            `;
        });

        tableBody.innerHTML = tableHTML;
    }

    // Reset form function
    function resetAdvanceForm() {
        const form = document.getElementById('add-advance-form');
        form.reset();
        document.getElementById('advance_id').value = '';
        form.action = '/advance/add';
        document.querySelector('#add-advance-form h5').innerHTML = `<i class="fa fa-plus-circle me-2 text-primary"></i>Add New Advance for <strong>${currentEmployeeName}</strong>`;
        document.getElementById('save-advance-btn').innerHTML = '<i class="fa fa-save me-2"></i>Save Advance';
        document.getElementById('cancel-edit-btn').style.display = 'inline-block';
        
        // Clear calculations
        document.getElementById('monthly_deduction_display').value = 'Will be calculated automatically...';
        document.getElementById('end_date_display').value = 'Will be calculated automatically...';
        
        // Clear errors
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
    }

    // Edit advance handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-advance-btn')) {
            const advance = JSON.parse(e.target.closest('.edit-advance-btn').dataset.advance);
            
            // Populate form
            document.getElementById('advance_id').value = advance.id;
            document.getElementById('advance_amount').value = advance.advance_amount;
            document.getElementById('tenure_months').value = advance.tenure_months;
            
            // Format start_date correctly for month input (YYYY-MM)
            let startDateFormatted = advance.start_date;
            if (advance.start_date && advance.start_date.includes('-')) {
                const dateParts = advance.start_date.split('-');
                if (dateParts.length >= 2) {
                    startDateFormatted = `${dateParts[0]}-${dateParts[1].padStart(2, '0')}`;
                }
            }
            document.getElementById('start_date').value = startDateFormatted;
            document.getElementById('notes').value = advance.notes || '';
            
            // Update form
            document.getElementById('add-advance-form').action = `/advance/${advance.id}/update`;
            document.querySelector('#add-advance-form h5').innerHTML = `<i class="fa fa-edit me-2 text-warning"></i>Edit Advance for <strong>${currentEmployeeName}</strong>`;
            document.getElementById('save-advance-btn').innerHTML = '<i class="fa fa-save me-2"></i>Update Advance';
            document.getElementById('cancel-edit-btn').style.display = 'inline-block';
            
            // Calculate values
            calculateAdvance();
        }
    });

    // Calculate advance function (existing function should work)
    function calculateAdvance() {
        const amount = parseFloat(document.getElementById('advance_amount').value) || 0;
        const tenure = parseInt(document.getElementById('tenure_months').value) || 0;
        const startDate = document.getElementById('start_date').value;

        if (amount > 0 && tenure > 0) {
            const monthlyDeduction = (amount / tenure).toFixed(2);
            document.getElementById('monthly_deduction_display').value = formatCurrency(monthlyDeduction);

            if (startDate) {
                const start = new Date(startDate + '-01');
                const endDate = new Date(start.getFullYear(), start.getMonth() + tenure,  0);
                const endMonth = endDate.toLocaleString('default', { month: 'long', year: 'numeric' });
                document.getElementById('end_date_display').value = endMonth;
            }
        } else {
            document.getElementById('monthly_deduction_display').value = 'Will be calculated automatically...';
            document.getElementById('end_date_display').value = 'Will be calculated automatically...';
        }
    }

    // Add event listeners for calculation
    document.addEventListener('input', function(e) {
        if (e.target.matches('#advance_amount, #tenure_months, #start_date')) {
            calculateAdvance();
        }
    });

    // Cancel edit button handler
    document.addEventListener('click', function(e) {
        if (e.target.matches('#cancel-edit-btn')) {
            resetAdvanceForm();
        }
    });

    // Delete advance handler (soft delete)
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-advance-btn')) {
            const advanceId = e.target.closest('.delete-advance-btn').dataset.advanceId;
            
            Swal.fire({
                title: 'Delete Advance?',
                text: "This will soft delete the advance. It can be restored by an administrator if needed.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    const button = e.target.closest('.delete-advance-btn');
                    const originalHtml = button.innerHTML;
                    button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                    button.disabled = true;
                    
                    fetch(`/advance/${advanceId}/delete`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Deleted!',
                                text: data.message || 'Advance deleted successfully!',
                                icon: 'success',
                                timer: 2000,
                                showConfirmButton: false
                            });
                            
                            // Reset form and reload advances
                            loadEmployeeAdvances(currentEmployeeId);
                            
                            // Refresh page after delay to get updated payroll data
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            throw new Error(data.message || 'Failed to delete advance');
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to delete advance',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        button.innerHTML = originalHtml;
                        button.disabled = false;
                    });
                }
            });
        }
    });

    // Form submission handler
    document.getElementById('add-advance-form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitButton = this.querySelector('button[type="submit"]');
        const originalText = submitButton.innerHTML;
        
        // Clear previous errors
        this.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        this.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
        
        // Show loading
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Processing...';
        submitButton.disabled = true;

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset form and reload advances
                resetAdvanceForm();
                loadEmployeeAdvances(currentEmployeeId);
                
                // Show success message
                showToast(data.message || 'Advance processed successfully! Page will refresh in 2 seconds...', 'success');
                
                // Refresh page after delay to get updated payroll data
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                // Handle validation errors
                if (data.errors) {
                    for (const field in data.errors) {
                        const input = document.getElementById(field);
                        const errorDiv = document.getElementById(field + '_error');
                        if (input) input.classList.add('is-invalid');
                        if (errorDiv && data.errors[field][0]) errorDiv.textContent = data.errors[field][0];
                    }
                }
                throw new Error(data.message || 'Failed to process advance');
            }
        })
        .catch(error => {
            showToast(error.message || 'An error occurred while processing the advance', 'error');
        })
        .finally(() => {
            submitButton.innerHTML = originalText;
            submitButton.disabled = false;
        });
    });

    // Alert function
    function showAlert(type, message) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }

    // Send Salary Slip Email Handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.send-salary-slip-btn')) {
            e.preventDefault();
            e.stopPropagation();
            
            const button = e.target.closest('.send-salary-slip-btn');
            const employeeId = button.dataset.employeeId;
            const employeeName = button.dataset.employeeName;
            const employeeEmail = button.dataset.employeeEmail;
            const month = button.dataset.month;
            const year = button.dataset.year;

            // Validate email address
            if (!employeeEmail || employeeEmail.trim() === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Email Not Found',
                    text: `No email address found for ${employeeName}. Please update the employee's email address first.`,
                    confirmButtonText: 'OK'
                });
                return;
            }

            // Show confirmation dialog
            Swal.fire({
                title: 'Send Salary Slip?',
                html: `Send salary slip for <strong>${employeeName}</strong> to:<br><em>${employeeEmail}</em>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fa fa-envelope me-2"></i>Send Email',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendSalarySlipEmail(button, employeeId, employeeName, employeeEmail, month, year);
                }
            });
        }
    });

    // Function to send salary slip email
    function sendSalarySlipEmail(button, employeeId, employeeName, employeeEmail, month, year) {
        const originalContent = button.innerHTML;
        const originalTitle = button.getAttribute('title');
        
        // Show loading state
        button.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        button.setAttribute('title', 'Sending...');
        button.disabled = true;

        // Send AJAX request
        fetch('{{ route("payroll.send-salary-slip") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                employee_id: employeeId,
                employee_name: employeeName,
                employee_email: employeeEmail,
                month: month,
                year: year
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Email Sent!',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                
                // Update button to show sent status temporarily
                button.innerHTML = '<i class="fa fa-check text-success"></i>';
                button.setAttribute('title', 'Salary slip sent successfully');
                
                // Reset button after 3 seconds
                setTimeout(() => {
                    button.innerHTML = originalContent;
                    button.setAttribute('title', originalTitle);
                    button.disabled = false;
                }, 3000);
            } else {
                throw new Error(data.message || 'Failed to send salary slip');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Failed to Send',
                text: error.message || 'An error occurred while sending the salary slip email',
                confirmButtonText: 'OK'
            });
            
            // Reset button state
            button.innerHTML = originalContent;
            button.setAttribute('title', originalTitle);
            button.disabled = false;
        });
    }

    // Send All Salary Slips Handler
    document.getElementById('sendAllSalarySlipsBtn').addEventListener('click', function() {
        const employees = [];
        const employeeRows = document.querySelectorAll('.employee-row');
        
        // Collect all employees with email addresses
        employeeRows.forEach(row => {
            const employeeId = row.dataset.employeeId;
            const employeeName = row.dataset.employeeName;
            const employeeEmail = row.querySelector('.send-salary-slip-btn')?.dataset.employeeEmail;
            
            if (employeeEmail && employeeEmail.trim() !== '') {
                employees.push({
                    id: employeeId,
                    name: employeeName,
                    email: employeeEmail
                });
            }
        });

        if (employees.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Valid Email Addresses',
                text: 'No employees found with valid email addresses.',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Show confirmation dialog
        Swal.fire({
            title: 'Send All Salary Slips?',
            html: `Send salary slips to <strong>${employees.length} employees</strong>?<br><small class="text-muted">This may take a few moments to complete.</small>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#007bff',
            cancelButtonColor: '#6c757d',
            confirmButtonText: '<i class="fa fa-envelope me-2"></i>Send to All',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                sendAllSalarySlips(employees);
            }
        });
    });

    // Function to send salary slips to all employees
    function sendAllSalarySlips(employees) {
        const button = document.getElementById('sendAllSalarySlipsBtn');
        const originalContent = button.innerHTML;
        
        // Show loading state
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Sending...';
        button.disabled = true;

        let successCount = 0;
        let failedCount = 0;
        let processedCount = 0;
        const totalCount = employees.length;

        // Show progress dialog
        Swal.fire({
            title: 'Sending Salary Slips',
            html: `<div class="text-center">
                <div class="progress mb-3" style="height: 25px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                         role="progressbar" 
                         style="width: 0%" 
                         id="sendAllProgress">0%</div>
                </div>
                <p id="sendAllStatus">Preparing to send...</p>
            </div>`,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send emails one by one to avoid overwhelming the server
        async function sendNext(index) {
            if (index >= employees.length) {
                // All done - show final result
                const progress = document.getElementById('sendAllProgress');
                const status = document.getElementById('sendAllStatus');
                
                progress.style.width = '100%';
                progress.textContent = '100%';
                progress.classList.remove('progress-bar-animated');
                
                if (failedCount === 0) {
                    progress.classList.add('bg-success');
                    status.innerHTML = `<i class="fa fa-check-circle text-success me-2"></i>All ${successCount} salary slips sent successfully!`;
                } else {
                    progress.classList.add('bg-warning');
                    status.innerHTML = `<i class="fa fa-exclamation-triangle text-warning me-2"></i>${successCount} sent, ${failedCount} failed`;
                }

                // Reset button state
                button.innerHTML = originalContent;
                button.disabled = false;

                // Close progress dialog after 3 seconds
                setTimeout(() => {
                    Swal.close();
                    
                    // Show final summary
                    Swal.fire({
                        icon: failedCount === 0 ? 'success' : 'warning',
                        title: 'Bulk Email Complete',
                        html: `<strong>Successfully sent:</strong> ${successCount}<br><strong>Failed:</strong> ${failedCount}`,
                        confirmButtonText: 'OK'
                    });
                }, 3000);
                
                return;
            }

            const employee = employees[index];
            const progress = document.getElementById('sendAllProgress');
            const status = document.getElementById('sendAllStatus');
            
            // Update progress
            const progressPercent = Math.round((index / totalCount) * 100);
            progress.style.width = progressPercent + '%';
            progress.textContent = progressPercent + '%';
            status.textContent = `Sending to ${employee.name}... (${index + 1}/${totalCount})`;

            try {
                const response = await fetch('{{ route("payroll.send-salary-slip") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        employee_id: employee.id,
                        employee_name: employee.name,
                        employee_email: employee.email,
                        month: {{ $month }},
                        year: {{ $year }}
                    })
                });

                const data = await response.json();
                
                if (data.success) {
                    successCount++;
                } else {
                    failedCount++;
                    console.error(`Failed to send to ${employee.name}:`, data.message);
                }
            } catch (error) {
                failedCount++;
                console.error(`Error sending to ${employee.name}:`, error);
            }

            processedCount++;
            
            // Wait a bit before sending the next one to avoid overwhelming the server
            setTimeout(() => {
                sendNext(index + 1);
            }, 500); // 500ms delay between emails
        }

    // Start sending
        sendNext(0);
    }

    // Floating button functionality
    $('#move-to-top').click(function() {
        $('html, body').animate({
            scrollTop: 0
        }, 500);
    });

    $('#move-to-last').click(function() {
        $('html, body').animate({
            scrollTop: $(document).height()
        }, 500);
        });



    }); 


function filterTableByLocation(locationName) {
                    const rows = document.querySelectorAll('.employee-row');
                    
                    // Update dropdown active state
                    document.querySelectorAll('.dropdown-item').forEach(item => {
                        if (item.textContent.trim() === (locationName === 'all' ? 'All Locations' : locationName)) {
                            item.classList.add('active');
                            // Update button text to show selected filter
                            const btn = document.getElementById('locationFilterDropdown');
                            if(btn) btn.innerHTML = '<i class="fa fa-filter me-2"></i> ' + (locationName === 'all' ? 'Filter by Location' : locationName);
                        } else {
                            item.classList.remove('active');
                        }
                    });

            // Update download links with selected location
            const locParam = locationName === 'all' ? '' : locationName;
            // Note: In the revised requirement, we need to pass location_id. 
            // However, our dropdown uses location NAME for filtering the frontend table.
            // Ideally, we should filter the download links by ID.
            // But since the request 'location_id' is already in the URL if specific location was selected,
            // we only need to handle the case where "All" was selected initially (so request('location_id') is null)
            // and then the user filters by location on the frontend.
            
            // To properly support downloading for a specific location when "All" was processed,
            // we would need to map the location name back to an ID or change the filter to use IDs.
            // For now, let's try to find the location ID from the rows if possible or rely on the backend to handle name?
            // Actually, best approach: The download links should point to the currently filtered view IF the backend supports filtering by location on "All" processed data.
            // If the backend `downloadBankTransferExcel` respects `location_id`, we can append it.
            
            // Let's iterate options to find the ID for the name
            let selectedLocationId = '';
            document.querySelectorAll('.dropdown-item').forEach(item => {
                if (item.textContent.trim() === locationName && locationName !== 'all') {
                     // We need a way to get ID. Let's add data-id to the dropdown items in the view first.
                     selectedLocationId = item.getAttribute('data-id');
                }
            });

            const updateLink = (id, baseUrl) => {
                const el = document.getElementById(id);
                if (el) {
                    const url = new URL(baseUrl, window.location.origin);
                    if (selectedLocationId) {
                        url.searchParams.set('location_id', selectedLocationId);
                    } else if (locationName === 'all') {
                         url.searchParams.delete('location_id');
                    }
                    el.href = url.toString();
                }
            };
            
            updateLink('canaraExcelLink', "{{ route('payroll.bank-excel', ['month' => $month, 'year' => $year]) }}");
            updateLink('canaraCsvLink', "{{ route('payroll.bank-csv', ['month' => $month, 'year' => $year]) }}");
            updateLink('iciciExcelLink', "{{ route('payroll.bank-icici-xlsx', ['month' => $month, 'year' => $year]) }}");


                    let totalEmployees = 0;
                    let totalGrossPay = 0;
                    let totalDeductions = 0;
                    let netPayable = 0;
                    let totalAdvance = 0;
                    let totalEpf = 0;
                    let totalEsic = 0;
                    let totalPt = 0;

                    rows.forEach(row => {
                        const rowLocation = row.getAttribute('data-employee-location');
                        if (locationName === 'all' || rowLocation === locationName) {
                            row.style.display = '';
                            
                            // Calculate summaries
                            totalEmployees++;
                            totalGrossPay += parseFloat(row.getAttribute('data-gross-pay') || 0);
                            totalDeductions += parseFloat(row.getAttribute('data-total-deductions') || 0);
                            netPayable += parseFloat(row.getAttribute('data-net-pay') || 0);
                            
                            // Parse deductions JSON to extract specific components
                            try {
                                const deductions = JSON.parse(row.getAttribute('data-deductions') || '{}');
                                
                                // Advance
                                if (deductions.advance && deductions.advance.applicable) {
                                    totalAdvance += parseFloat(deductions.advance.value || 0);
                                }
                                
                                // EPF (ID 1)
                                if (deductions[1] && deductions[1].applicable) {
                                    totalEpf += parseFloat(deductions[1].value || 0);
                                }
                                
                                // ESIC (ID 2)
                                if (deductions[2] && deductions[2].applicable) {
                                    totalEsic += parseFloat(deductions[2].value || 0);
                                }
                                
                                // PT (ID 4)
                                if (deductions[4] && deductions[4].applicable) {
                                    totalPt += parseFloat(deductions[4].value || 0);
                                }
                            } catch (e) {
                                console.error('Error parsing deductions for summary calculation', e);
                            }

                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Update summary cards
                    updateSummaryCard('Total Employees', totalEmployees, false);
                    updateSummaryCard('Total Gross Pay', totalGrossPay, true);
                    updateSummaryCard('Total Deductions', totalDeductions, true);
                    updateSummaryCard('Net Payable', netPayable, true);
                    updateSummaryCard('Total Advance Deducted', totalAdvance, true);
                    updateSummaryCard('Total EPF Deductions', totalEpf, true);
                    updateSummaryCard('Total ESIC Deductions', totalEsic, true);
                    updateSummaryCard('Total PT Deductions', totalPt, true);
            }

            function updateSummaryCard(title, value, isCurrency) {
                    // Find all summary cards
                    const cards = document.querySelectorAll('.summary-card');
                    
                    cards.forEach(card => {
                        const cardTitle = card.querySelector('.summary-card-title').textContent.trim();
                        if (cardTitle === title) {
                            const valueElement = card.querySelector('.summary-card-value');
                            
                            if (isCurrency) {
                                // Format as Indian Currency
                                const formattedValue = new Intl.NumberFormat('en-IN', {
                                    maximumFractionDigits: 2,
                                    minimumFractionDigits: 2
                                }).format(value);
                                valueElement.innerHTML = `<span class="currency-symbol">₹</span>${formattedValue}`;
                            } else {
                                valueElement.textContent = value;
                            }
                        }
                    });
            }
    </script>
@endsection


@section('style')
<style>
/* Advance Modal Styles */
#manageAdvanceModal .card {
    margin-bottom: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

/* Advances Table Scroll Wrapper */
.advances-table-wrapper {
    position: relative;
}

.advances-table-scroll {
    max-height: 400px;
    overflow-y: auto;
    overflow-x: auto;
    border-radius: 0 0 8px 8px;
    scrollbar-width: thin;
    scrollbar-color: #c1c1c1 #f1f1f1;
}

.advances-table-scroll::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.advances-table-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.advances-table-scroll::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.advances-table-scroll::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.advances-table-scroll table {
    width: 100%;
    min-width: 780px;
}

.advances-table-scroll thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: #f8f9fa !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

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

/* Responsive Styles for Advance Management Modal */
@media (max-width: 768px) {
    #manageAdvanceModal .modal-dialog {
        margin: 0.5rem;
        max-width: calc(100% - 1rem);
    }

    #manageAdvanceModal .modal-content {
        border-radius: 0.5rem;
    }

    #manageAdvanceModal .modal-header {
        padding: 1rem;
    }

    #manageAdvanceModal .modal-header h5 {
        font-size: 1rem;
    }

    #manageAdvanceModal .modal-body {
        padding: 1rem;
        max-height: calc(100vh - 150px);
        overflow-y: auto;
    }

    #manageAdvanceModal .card-body {
        padding: 0.75rem;
    }

    #manageAdvanceModal .row {
        margin-left: 0;
        margin-right: 0;
    }

    #manageAdvanceModal .row > [class*="col-"] {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        margin-bottom: 0.75rem;
    }

    #manageAdvanceModal .form-label {
        font-size: 0.875rem;
        margin-bottom: 0.375rem;
    }

    .action-buttons-container { 
        padding: 0.875rem 1rem; 
    }
    
    .action-buttons-container .btn-modern { 
        padding: 0.625rem 1.25rem; 
        font-size: 0.875rem; 
    }

    #manageAdvanceModal .form-control {
        font-size: 0.875rem;
        padding: 0.5rem;
    }

    #manageAdvanceModal .btn {
        font-size: 0.875rem;
        padding: 0.5rem 1rem;
    }

    #manageAdvanceModal .table {
        font-size: 0.75rem;
    }

    #manageAdvanceModal .table th,
    #manageAdvanceModal .table td {
        padding: 0.5rem 0.25rem;
        white-space: nowrap;
    }

    #manageAdvanceModal .card {
        margin-bottom: 0.75rem;
    }

    #manageAdvanceModal .card-header h5 {
        font-size: 0.9rem;
    }

    .advances-table-scroll {
        max-height: 300px;
    }

    .advances-table-scroll table {
        min-width: 650px;
    }

    #manageAdvanceModal h5 {
        font-size: 0.95rem;
    }

    #manageAdvanceModal .d-flex.justify-content-between {
        flex-direction: column;
        gap: 0.75rem;
    }

    #manageAdvanceModal .d-flex.justify-content-between small {
        text-align: center;
    }
}

@media (min-width: 769px) and (max-width: 1024px) {
    #manageAdvanceModal .modal-dialog {
        max-width: 90%;
        margin: 1rem auto;
    }

    #manageAdvanceModal .modal-body {
        padding: 1.5rem;
        max-height: calc(100vh - 180px);
        overflow-y: auto;
    }

    #manageAdvanceModal .row > [class*="col-"] {
        margin-bottom: 1rem;
    }

    #manageAdvanceModal .form-control {
        font-size: 0.9rem;
    }

    #manageAdvanceModal .table {
        font-size: 0.85rem;
    }

    #manageAdvanceModal .table th,
    #manageAdvanceModal .table td {
        padding: 0.6rem 0.4rem;
    }

    .advances-table-scroll {
        max-height: 350px;
    }

    .advances-table-scroll table {
        min-width: 750px;
    }
}

@media (min-width: 1025px) and (max-width: 1550px) {
    #manageAdvanceModal .modal-dialog {
        max-width: 85%;
    }

    #manageAdvanceModal .modal-body {
        max-height: calc(100vh - 200px);
        overflow-y: auto;
    }

    #manageAdvanceModal .form-control {
        font-size: 0.9rem;
    }

    #manageAdvanceModal .table {
        font-size: 0.875rem;
    }

    .advances-table-scroll {
        max-height: 380px;
    }
}

@media (min-width: 1551px) {
    #manageAdvanceModal .modal-body {
        max-height: calc(100vh - 220px);
        overflow-y: auto;
    }
}

/* Table styles */
.table-container {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    max-height: 600px;
}

.table-scroll-wrapper {
    overflow: auto;
    max-width: 100%;
    max-height: 600px;
    position: relative;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    scrollbar-width: auto;
    -ms-overflow-style: auto;
}

.table-scroll-wrapper::-webkit-scrollbar {
    height: 12px;
    width: 12px;
}

.table-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 6px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.table-scroll-wrapper::-webkit-scrollbar-corner {
    background: #f1f1f1;
}

.table-scroll-wrapper {
    scroll-behavior: smooth;
}

.table-scroll-wrapper.scrolling {
    box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
}

/* Sticky table header */
.payroll-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

.payroll-table thead th {
    position: sticky;
    top: 0; /* Default top for first row */
    z-index: 2;
    /* background-color is now handled by specific header classes */
}

/* 2. Sub-header row needs to be sticky below the first header row */
.payroll-table tr.header-sub th {
    top: 75px; /* Adjust this value to match the height of the main header row */
}

/* 3. Body cells in the frozen column are sticky to the left */
.payroll-table tbody td.frozen-column {
    position: sticky;
    left: 0;
    z-index: 1; /* Lower z-index than header */
    background-color: white !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* 4. The top-left header cell is sticky to top AND left, with the highest z-index */
.payroll-table thead th.frozen-column {
    left: 0;
    z-index: 3;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.15);
}

/* Frozen column base styling */
.frozen-column {
    min-width: 280px !important;
    max-width: 280px !important;
    width: 280px !important;
}

/* Employee cell specific styling */
.employee-info {
    text-align: left !important;
    padding: 12px 16px !important;
}

.employee-info .employee-name {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 4px;
    display: block;
    line-height: 1.3;
}

.employee-info .employee-code {
    font-size: 11px;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.employee-info .employee-dept {
    font-size: 10px;
    color: #17a2b8;
    display: block;
}

.employee-avatar {
    position: relative;
    display: inline-block;
    margin-right: 12px;
}

.employee-avatar img {
    border: 2px solid #e9ecef;
}

.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.employee-info-text h6 {
    font-size: 14px;
    margin-bottom: 4px;
    color: #2c3e50;
}

.employee-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.employee-meta .badge {
    font-size: 10px;
    padding: 4px 8px;
}

.employee-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}

.employee-id {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 2px;
}

.employee-dept {
    font-size: 10px;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-block;
}

.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.amount-positive {
    color: #28a745;
}

.amount-negative {
    color: #dc3545;
}

.progress-cell {
    min-width: 120px;
}

.progress-info {
    font-size: 11px;
    margin-bottom: 5px;
}

.bg-info-light { background-color: #d1ecf1; }
.bg-success-light { background-color: #d4edda; }
.table > :not(:first-child) {
    border-top: none;
}

/* Currency and number formatting */
.currency-symbol {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 500;
}

/* Header gradient styles for table */
.employee-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.attendance-header {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #212529 !important;
    font-weight: 600;
}

.epf-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.earnings-group {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.basic-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.hra-header {
    background: linear-gradient(135deg, #20c997 0%, #17a085 100%) !important;
    color: white !important;
}

.conveyance-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.medical-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.special-allowance-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.deductions-group {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.statutory-deductions-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.other-deductions-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.gross-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.net-pay-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
}

.total-deductions-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.early-salary-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.actions-header {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
    color: white !important;
}

.earnings-component {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.deductions-component {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.header-sub th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    font-size: 0.85rem;
}

.header-sub .sub-header {
    background: linear-gradient(135deg, #f1f3f4 0%, #dadce0 100%) !important;
    color: #495057 !important;
    font-weight: 500;
}

.salary-table td {
    font-size: 14px;
    border-right: 1px solid #dee2e6;
}

.salary-table th {
    border-right: 1px solid rgba(255,255,255,0.2);
}

.component-header .header-contents {
    min-height: 50px;
    padding: 4px 6px;
}

.component-header .component-name {
    font-size: 12px;
    margin-bottom: 3px;
}

.component-header .component-rate {
    font-size: 10px;
}

.header-contents {
    min-height: 55px;
    padding: 6px;
}

/* --- Z-Index Stacking Context Rules --- */
/* The entire header block is sticky */
.payroll-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* 1. All header cells are sticky to the top */
.payroll-table thead th {
    position: sticky;
    top: 0; /* Default top for first row */
    z-index: 2;
    /* background-color is now handled by specific header classes */
}

/* 2. Sub-header row needs to be sticky below the first header row */
.payroll-table tr.header-sub th {
    top: 75px; /* Adjust this value to match the height of the main header row */
}

/* 3. Body cells in the frozen column are sticky to the left */
.payroll-table tbody td.frozen-column {
    position: sticky;
    left: 0;
    z-index: 1; /* Lower z-index than header */
    background-color: white !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* 4. The top-left header cell is sticky to top AND left, with the highest z-index */
.payroll-table thead th.frozen-column {
    left: 0;
    z-index: 3;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.15);
}

/* Frozen column base styling */
.frozen-column {
    min-width: 280px !important;
    max-width: 280px !important;
    width: 280px !important;
}

/* Employee cell specific styling */
.employee-info {
    text-align: left !important;
    padding: 12px 16px !important;
}

.employee-info .employee-name {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 4px;
    display: block;
    line-height: 1.3;
}

.employee-info .employee-code {
    font-size: 11px;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.employee-info .employee-dept {
    font-size: 10px;
    color: #17a2b8;
    display: block;
}

.employee-avatar {
    position: relative;
    display: inline-block;
    margin-right: 12px;
}

.employee-avatar img {
    border: 2px solid #e9ecef;
}

.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.employee-info-text h6 {
    font-size: 14px;
    margin-bottom: 4px;
    color: #2c3e50;
}

.employee-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.employee-meta .badge {
    font-size: 10px;
    padding: 4px 8px;
}

.employee-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}

.employee-id {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 2px;
}

.employee-dept {
    font-size: 10px;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-block;
}

.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.amount-positive {
    color: #28a745;
}

.amount-negative {
    color: #dc3545;
}

.progress-cell {
    min-width: 120px;
}

.progress-info {
    font-size: 11px;
    margin-bottom: 5px;
}

.bg-info-light { background-color: #d1ecf1; }
.bg-success-light { background-color: #d4edda; }
.table > :not(:first-child) {
    border-top: none;
}

/* Currency and number formatting */
.currency-symbol {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 500;
}

/* Header gradient styles for table */
.employee-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.attendance-header {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #212529 !important;
    font-weight: 600;
}

.epf-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.earnings-group {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.basic-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.hra-header {
    background: linear-gradient(135deg, #20c997 0%, #17a085 100%) !important;
    color: white !important;
}

.conveyance-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.medical-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.special-allowance-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.deductions-group {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.statutory-deductions-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.other-deductions-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.gross-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.net-pay-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
}

.total-deductions-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.early-salary-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.actions-header {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
    color: white !important;
}

.earnings-component {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.deductions-component {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.header-sub th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    font-size: 0.85rem;
}

.header-sub .sub-header {
    background: linear-gradient(135deg, #f1f3f4 0%, #dadce0 100%) !important;
    color: #495057 !important;
    font-weight: 500;
}

.salary-table td {
    font-size: 14px;
    border-right: 1px solid #dee2e6;
}

.salary-table th {
    border-right: 1px solid rgba(255,255,255,0.2);
}

.component-header .header-contents {
    min-height: 50px;
    padding: 4px 6px;
}

.component-header .component-name {
    font-size: 12px;
    margin-bottom: 3px;
}

.component-header .component-rate {
    font-size: 10px;
}

.header-contents {
    min-height: 55px;
    padding: 6px;
}

/* --- Z-Index Stacking Context Rules --- */
/* The entire header block is sticky */
.payroll-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* 1. All header cells are sticky to the top */
.payroll-table thead th {
    position: sticky;
    top: 0; /* Default top for first row */
    z-index: 2;
    /* background-color is now handled by specific header classes */
}

/* 2. Sub-header row needs to be sticky below the first header row */
.payroll-table tr.header-sub th {
    top: 75px; /* Adjust this value to match the height of the main header row */
}

/* 3. Body cells in the frozen column are sticky to the left */
.payroll-table tbody td.frozen-column {
    position: sticky;
    left: 0;
    z-index: 1; /* Lower z-index than header */
    background-color: white !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* 4. The top-left header cell is sticky to top AND left, with the highest z-index */
.payroll-table thead th.frozen-column {
    left: 0;
    z-index: 3;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.15);
}

/* Frozen column base styling */
.frozen-column {
    min-width: 280px !important;
    max-width: 280px !important;
    width: 280px !important;
}

/* Employee cell specific styling */
.employee-info {
    text-align: left !important;
    padding: 12px 16px !important;
}

.employee-info .employee-name {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 4px;
    display: block;
    line-height: 1.3;
}

.employee-info .employee-code {
    font-size: 11px;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.employee-info .employee-dept {
    font-size: 10px;
    color: #17a2b8;
    display: block;
}

.employee-avatar {
    position: relative;
    display: inline-block;
    margin-right: 12px;
}

.employee-avatar img {
    border: 2px solid #e9ecef;
}

.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.employee-info-text h6 {
    font-size: 14px;
    margin-bottom: 4px;
    color: #2c3e50;
}

.employee-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.employee-meta .badge {
    font-size: 10px;
    padding: 4px 8px;
}

.employee-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}

.employee-id {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 2px;
}

.employee-dept {
    font-size: 10px;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-block;
}

.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.amount-positive {
    color: #28a745;
}

.amount-negative {
    color: #dc3545;
}

.progress-cell {
    min-width: 120px;
}

.progress-info {
    font-size: 11px;
    margin-bottom: 5px;
}

.bg-info-light { background-color: #d1ecf1; }
.bg-success-light { background-color: #d4edda; }
.table > :not(:first-child) {
    border-top: none;
}

/* Currency and number formatting */
.currency-symbol {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 500;
}

/* Header gradient styles for table */
.employee-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.attendance-header {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #212529 !important;
    font-weight: 600;
}

.epf-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.earnings-group {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.basic-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.hra-header {
    background: linear-gradient(135deg, #20c997 0%, #17a085 100%) !important;
    color: white !important;
}

.conveyance-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.medical-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.special-allowance-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.deductions-group {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.statutory-deductions-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.other-deductions-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.gross-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.net-pay-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
}

.total-deductions-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.early-salary-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.actions-header {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
    color: white !important;
}

.earnings-component {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.deductions-component {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.header-sub th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    font-size: 0.85rem;
}

.header-sub .sub-header {
    background: linear-gradient(135deg, #f1f3f4 0%, #dadce0 100%) !important;
    color: #495057 !important;
    font-weight: 500;
}

.salary-table td {
    font-size: 14px;
    border-right: 1px solid #dee2e6;
}

.salary-table th {
    border-right: 1px solid rgba(255,255,255,0.2);
}

.component-header .header-contents {
    min-height: 50px;
    padding: 4px 6px;
}

.component-header .component-name {
    font-size: 12px;
    margin-bottom: 3px;
}

.component-header .component-rate {
    font-size: 10px;
}

.header-contents {
    min-height: 55px;
    padding: 6px;
}

/* --- Z-Index Stacking Context Rules --- */
/* The entire header block is sticky */
.payroll-table thead {
    position: sticky;
    top: 0;
    z-index: 10;
}

/* 1. All header cells are sticky to the top */
.payroll-table thead th {
    position: sticky;
    top: 0; /* Default top for first row */
    z-index: 2;
    /* background-color is now handled by specific header classes */
}

/* 2. Sub-header row needs to be sticky below the first header row */
.payroll-table tr.header-sub th {
    top: 75px; /* Adjust this value to match the height of the main header row */
}

/* 3. Body cells in the frozen column are sticky to the left */
.payroll-table tbody td.frozen-column {
    position: sticky;
    left: 0;
    z-index: 1; /* Lower z-index than header */
    background-color: white !important;
    box-shadow: 2px 0 4px rgba(0,0,0,0.1);
}

/* 4. The top-left header cell is sticky to top AND left, with the highest z-index */
.payroll-table thead th.frozen-column {
    left: 0;
    z-index: 3;
    box-shadow: 2px 2px 5px rgba(0,0,0,0.15);
}

/* Frozen column base styling */
.frozen-column {
    min-width: 280px !important;
    max-width: 280px !important;
    width: 280px !important;
}

/* Employee cell specific styling */
.employee-info {
    text-align: left !important;
    padding: 12px 16px !important;
}

.employee-info .employee-name {
    font-weight: 600;
    font-size: 14px;
    color: #2c3e50;
    margin-bottom: 4px;
    display: block;
    line-height: 1.3;
}

.employee-info .employee-code {
    font-size: 11px;
    color: #6c757d;
    display: block;
    margin-bottom: 2px;
}

.employee-info .employee-dept {
    font-size: 10px;
    color: #17a2b8;
    display: block;
}

.employee-avatar {
    position: relative;
    display: inline-block;
    margin-right: 12px;
}

.employee-avatar img {
    border: 2px solid #e9ecef;
}

.status-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.employee-info-text h6 {
    font-size: 14px;
    margin-bottom: 4px;
    color: #2c3e50;
}

.employee-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 4px;
}

.employee-meta .badge {
    font-size: 10px;
    padding: 4px 8px;
}

.employee-name {
    font-weight: 600;
    color: #495057;
    margin-bottom: 3px;
}

.employee-id {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 2px;
}

.employee-dept {
    font-size: 10px;
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-block;
}

.amount-cell {
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

.amount-positive {
    color: #28a745;
}

.amount-negative {
    color: #dc3545;
}

.progress-cell {
    min-width: 120px;
}

.progress-info {
    font-size: 11px;
    margin-bottom: 5px;
}

.bg-info-light { background-color: #d1ecf1; }
.bg-success-light { background-color: #d4edda; }
.table > :not(:first-child) {
    border-top: none;
}

/* Currency and number formatting */
.currency-symbol {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-weight: 500;
}

/* Header gradient styles for table */
.employee-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.attendance-header {
    background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%) !important;
    color: #212529 !important;
    font-weight: 600;
}

.epf-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.earnings-group {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.basic-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.hra-header {
    background: linear-gradient(135deg, #20c997 0%, #17a085 100%) !important;
    color: white !important;
}

.conveyance-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.medical-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.special-allowance-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.deductions-group {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.statutory-deductions-header {
    background: linear-gradient(135deg, #6f42c1 0%, #59359a 100%) !important;
    color: white !important;
}

.other-deductions-header {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
    color: white !important;
}

.gross-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.net-pay-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
    color: white !important;
}

.total-deductions-header {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.early-salary-header {
    background: linear-gradient(135deg, #fd7e14 0%, #e55a00 100%) !important;
    color: white !important;
}

.actions-header {
    background: linear-gradient(135deg, #6c757d 0%, #545b62 100%) !important;
    color: white !important;
}

.earnings-component {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    color: white !important;
}

.deductions-component {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
    color: white !important;
}

.header-sub th {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
    color: #495057 !important;
    font-size: 0.85rem;
}

.header-sub .sub-header {
    background: linear-gradient(135deg, #f1f3f4 0%, #dadce0 100%) !important;
    color: #495057 !important;
    font-weight: 500;
}

.salary-table td {
    font-size: 14px;
    border-right: 1px solid #dee2e6;
}

.salary-table th {
    border-right: 1px solid rgba(255,255,255,0.2);
}

.component-header .header-contents {
    min-height: 50px;
    padding: 4px 6px;
}

.component-header .component-name {
    font-size: 12px;
    margin-bottom: 3px;
}

.component-header .component-rate {
    font-size: 10px;
}

.header-contents {
    min-height: 55px;
    padding: 6px;
}
</style>
@endsection



