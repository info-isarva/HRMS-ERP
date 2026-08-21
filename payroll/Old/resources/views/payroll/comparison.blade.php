@extends('layouts.master')

@section('title', 'Payroll Comparison')

@section('content')

<!-- SweetAlert2 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

<style>
    /* Page Header Card */
    .page-header-card { background: white; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.1); overflow: hidden; margin-bottom: 1.5rem; }
    .page-header-gradient { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 1.5rem 1.5rem; position: relative; }
    .page-header-pattern { position: absolute; inset: 0; background: rgba(0,0,0,0.05); }
    .page-header-icon-box { width:3rem; height:3rem; background: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.3); border-radius:0.75rem; display:flex; align-items:center; justify-content:center; }
    .page-header-title { font-size:1.5rem; font-weight:700; color:white; margin-bottom:0.25rem; }
    .page-header-subtitle { font-size:0.875rem; color: rgba(255,255,255,0.9); margin:0; }

    /* Modern Card Styles */
    .modern-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; overflow: hidden; margin-bottom: 1.5rem; }
    .modern-card-header { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); padding: 0.875rem 1.25rem; border-bottom: none; }
    .modern-card-header h4 { color: white; font-weight: 600; margin: 0; font-size: 1rem; }
    .modern-card-body { padding: 1.25rem; }

    /* Button Styles */
    .btn-modern { padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-primary { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .btn-modern-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4); color: white; }
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); }
    .btn-modern-light { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .btn-modern-light:hover { background: #e9ecef; }

    /* Comparison Table Styles */
    .comparison-table { width: 100%; border-collapse: collapse; }
    .comparison-table thead th { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; font-weight: 600; padding: 0.75rem; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; text-align: center; }
    .comparison-table tbody td { padding: 0.75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; font-size: 0.8125rem; text-align: center; }
    .comparison-table tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .comparison-table tbody td:first-child { font-weight: 600; color: #1f2937; text-align: left; }
    
    /* Highlighted row for Net Payable */
    .highlight-row { background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%); }
    .highlight-row td { font-weight: 700; font-size: 0.9rem; border-top: 2px solid #667eea; border-bottom: 2px solid #667eea; }
    
    /* Difference indicators */
    .diff-positive { color: #10b981; font-weight: 600; }
    .diff-negative { color: #ef4444; font-weight: 600; }
    .diff-neutral { color: #6b7280; font-weight: 600; }
    
    /* Alert Box */
    .alert-info { background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(29, 78, 216, 0.1) 100%); border-left: 4px solid #3b82f6; padding: 0.875rem 1.25rem; border-radius: 0.5rem; margin-bottom: 1.25rem; }
    .alert-info i { color: #3b82f6; margin-right: 0.5rem; }

    /* Summary Cards */
    .summary-card { background: white; border-radius: 0.875rem; padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; text-align: center; transition: transform 0.2s; height: 100%; position: relative; overflow: hidden; }
    .summary-card:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
    /* .summary-card.current-month { background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%); border: 1px solid rgba(102, 126, 234, 0.2); } */
    .summary-card.highlight-net-payable { border: 2px solid #3b82f6; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15); background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(37, 99, 235, 0.05) 100%); }
    .summary-card.highlight-net-payable:hover { box-shadow: 0 6px 16px rgba(59, 130, 246, 0.25); transform: translateY(-3px); }
    .summary-card.highlight-net-payable.current-month { background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(37, 99, 235, 0.08) 100%); border: 2px solid #3b82f6; }
    .summary-card-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.625rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.125rem; }
    .summary-card-label { font-size: 0.75rem; font-weight: 500; color: #6b7280; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 0.375rem; }
    .summary-card-value { font-size: 1.125rem; font-weight: 700; color: #1f2937; margin-bottom: 0; }
    .highlight-net-payable .summary-card-label { color: #3b82f6; font-weight: 600; }
    .highlight-net-payable .summary-card-value { color: #1e40af; font-size: 1.25rem; }

    /* Card glow effect */
    .card-glow {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: radial-gradient(circle at top right, rgba(102, 126, 234, 0.08), transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
        z-index: 0;
    }
    .summary-card:hover .card-glow {
        opacity: 1;
    }
    .summary-card > * {
        position: relative;
        z-index: 1;
    }

    /* Month containers */
    .current-month-container,
    .previous-month-container {
        padding: 1rem;
        border-radius: 0.75rem;
    }
    
    .current-month-container {
        background: #f3f4f6;
        border: 1px solid #e5e7eb;
    }

    .previous-month-container {
        background: white;
        border: 1px solid #e5e7eb;
    }

    /* Summary card change indicator */
    .summary-card-change {
        font-size: 0.6875rem;
        font-weight: 600;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        background: rgba(0,0,0,0.03);
        display: inline-block;
        margin-left: 0.5rem;
    }
    
    /* Value row container */
    .summary-card-value-row {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    /* Compact card variant */
    .compact-card {
        padding: 0.75rem;
    }
    .compact-card .summary-card-icon {
        margin-bottom: 0.5rem;
    }

    /* Deduction breakdown card */
    .deduction-breakdown-card {
        background: white;
        border-radius: 0.875rem;
        padding: 1rem;
        box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        border: 1px solid rgba(102, 126, 234, 0.2);
        height: 100%;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03) 0%, rgba(118, 75, 162, 0.03) 100%);
    }

    /* Mini stat cards */
    .mini-stat-card {
        background: white;
        border-radius: 0.625rem;
        padding: 0.75rem;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        border: 1px solid #f3f4f6;
        transition: all 0.2s ease;
        height: 100%;
    }
    .mini-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.12);
    }
    .mini-stat-icon {
        width: 2.25rem;
        height: 2.25rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-size: 1rem;
        color: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .mini-stat-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        margin-bottom: 0.375rem;
    }
    .mini-stat-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    /* Month Header Card */
    .month-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 0.75rem 1rem; border-radius: 0.625rem; text-align: center; margin-bottom: 1rem; }
    .month-header.current-month { background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); }
    .month-header h5 { margin: 0; font-size: 0.9375rem; font-weight: 600; }
    .month-header .fa-calendar { margin-right: 0.375rem; font-size: 0.875rem; }

    /* Change Cards */
    .change-card { background: white; border-radius: 0.875rem; padding: 1rem; box-shadow: 0 2px 6px rgba(0,0,0,0.06); border: 1px solid #e5e7eb; text-align: center; transition: all 0.2s; height: 100%; }
    .change-card:hover { transform: translateY(-3px); box-shadow: 0 6px 14px rgba(0,0,0,0.12); }
    .change-card-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.75rem; font-size: 1.375rem; color: white; }
    .change-card-label { font-size: 0.8125rem; font-weight: 600; color: white; margin-bottom: 0.625rem; }
    .change-card-value { font-size: 1.25rem; font-weight: 700; color: white; margin: 0; }
    .change-card-arrow { font-size: 0.875rem; margin-right: 0.25rem; }

    /* Color schemes for change cards */
    .change-card.employees { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
    .change-card.gross-pay { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .change-card.net-pay { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
    .change-card.deductions { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .change-card.epf { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .change-card.esic { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }

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

    /* Responsive adjustments */
    @media (max-width: 1200px) {
        .change-card-value { font-size: 1.25rem; }
        .change-card-label { font-size: 0.875rem; }
        .change-card-icon { width: 3.5rem; height: 3.5rem; font-size: 1.5rem; }
    }

    @media (max-width: 992px) {
        .summary-card { padding: 1.25rem; }
        .summary-card-icon { width: 3rem; height: 3rem; font-size: 1.25rem; }
        .summary-card-value { font-size: 1.25rem; }
    }

    @media (max-width: 768px) {
        .page-header-gradient { padding: 1.5rem 1rem; }
        .page-header-title { font-size: 1.375rem; }
        .page-header-subtitle { font-size: 0.875rem; }
        .page-header-icon-box { width: 3rem; height: 3rem; }
        
        .summary-card { padding: 1rem; }
        .summary-card-icon { width: 2.5rem; height: 2.5rem; font-size: 1.125rem; margin-bottom: 0.75rem; }
        .summary-card-label { font-size: 0.75rem; margin-bottom: 0.375rem; }
        .summary-card-value { font-size: 1.125rem; }
        
        .month-header { padding: 0.75rem; margin-bottom: 1rem; }
        .month-header h5 { font-size: 1rem; }
        
        .change-card { padding: 1rem; }
        .change-card-icon { width: 3rem; height: 3rem; font-size: 1.375rem; margin-bottom: 0.75rem; }
        .change-card-label { font-size: 0.8125rem; margin-bottom: 0.5rem; }
        .change-card-value { font-size: 1.125rem; }
        
        .modern-card-body { padding: 1.25rem; }
        .modern-card { margin-bottom: 1.5rem; }
        .btn-modern { padding: 0.625rem 1.25rem; font-size: 0.875rem; }
        
        .action-buttons-container { padding: 0.875rem 1rem; }
    }

    @media (max-width: 576px) {
        .page-header-gradient { padding: 1.25rem 0.875rem; }
        .page-header-title { font-size: 1.125rem; }
        .page-header-icon-box { width: 2.5rem; height: 2.5rem; }
        .page-header-icon-box i { font-size: 1.125rem; }
        
        .summary-card { padding: 0.875rem; }
        .summary-card-icon { width: 2.25rem; height: 2.25rem; font-size: 1rem; margin-bottom: 0.625rem; }
        .summary-card-label { font-size: 0.6875rem; }
        .summary-card-value { font-size: 1rem; }
        
        .month-header { padding: 0.625rem; margin-bottom: 0.875rem; }
        .month-header h5 { font-size: 0.875rem; }
        
        .change-card { padding: 0.875rem; }
        .change-card-icon { width: 2.5rem; height: 2.5rem; font-size: 1.125rem; margin-bottom: 0.625rem; }
        .change-card-label { font-size: 0.75rem; margin-bottom: 0.5rem; }
        .change-card-value { font-size: 1rem; }
        
        .modern-card-body { padding: 1rem; }
        .comparison-table thead th { padding: 0.75rem 0.5rem; font-size: 0.75rem; }
        .comparison-table tbody td { padding: 0.75rem 0.5rem; font-size: 0.8125rem; }
        .highlight-row td { font-size: 0.875rem; }
        
        .btn-modern { padding: 0.5rem 1rem; font-size: 0.8125rem; }
        .action-buttons-container { 
            padding: 0.75rem; 
            flex-direction: column; 
            gap: 0.75rem;
        }
        .action-buttons-container .d-flex { 
            flex-direction: column !important; 
            gap: 0.75rem;
        }
        .action-buttons-container .btn-modern { 
            width: 100%;
        }
    }

</style>

<div class="page-wrapper">
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="d-flex align-items-center position-relative">
                    <div class="page-header-icon-box">
                        <i class="fa fa-chart-line fa-2x" style="color: white;"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Payroll Comparison</h1>
                        <p class="page-header-subtitle">Compare payroll metrics between {{ $monthName }} and {{ $previousMonthName ?? 'Previous Month' }}</p>
                    </div>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="bg-white">
                @include('payroll.partials.progress-steps', [
                    'currentStep' => $currentStep,
                    'month' => $month,
                    'year' => $year,
                    'attendanceSaved' => $attendanceSaved ?? false,
                    'isFinalized' => $isFinalized ?? false
                ])
            </div>
        </div>

        <!-- Info Alert -->
        @if(!$previousTotals)
        <div class="alert-info">
            <i class="fa fa-info-circle fa-lg"></i>
            <strong>Note:</strong> No previous month data available for comparison. This might be the first payroll month.
        </div>
        @endif

        <!-- Month Summary Cards -->
        <div class="row mb-4">            
            <!-- Previous Month Summary -->
            <div class="col-md-6">
                <div class="month-header" style="margin-bottom: 1.5rem;">
                    <h5><i class="fa fa-calendar"></i>{{ $previousMonthName ?? 'Previous Month' }} Summary</h5>
                </div>
                <div class="previous-month-container">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="summary-card">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="summary-card-label">Total Employees</div>
                                <div class="summary-card-value">{{ $previousTotals['employees'] ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                    <i class="fa fa-money-bill-wave"></i>
                                </div>
                                <div class="summary-card-label">Gross Pay</div>
                                <div class="summary-card-value">{{ get_currency_symbol() }}{{ $previousTotals ? number_format($previousTotals['total_gross_pay'], 0) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card highlight-net-payable">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                                    <i class="fa fa-hand-holding-usd"></i>
                                </div>
                                <div class="summary-card-label">Net Payable</div>
                                <div class="summary-card-value">{{ get_currency_symbol() }}{{ $previousTotals ? number_format($previousTotals['net_payable'], 0) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white;">
                                    <i class="fa fa-minus-circle"></i>
                                </div>
                                <div class="summary-card-label">Total Deductions</div>
                                <div class="summary-card-value">{{ get_currency_symbol() }}{{ $previousTotals ? number_format($previousTotals['total_deductions'], 0) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white;">
                                    <i class="fa fa-piggy-bank"></i>
                                </div>
                                <div class="summary-card-label">EPF</div>
                                <div class="summary-card-value">{{ get_currency_symbol() }}{{ $previousTotals ? number_format($previousTotals['total_epf'], 0) : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card">
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white;">
                                    <i class="fa fa-hospital"></i>
                                </div>
                                <div class="summary-card-label">ESIC</div>
                                <div class="summary-card-value">{{ get_currency_symbol() }}{{ $previousTotals ? number_format($previousTotals['total_esic'], 0) : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Month Summary -->
            <div class="col-md-6">
                <div class="month-header current-month">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0"><i class="fa fa-calendar-check me-2"></i>{{ $monthName }} Summary</h5>
                        <span class="badge" style="background: rgba(255,255,255,0.25); padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                            <i class="fa fa-check-circle me-1"></i>Current
                        </span>
                    </div>
                </div>
                <div class="current-month-container">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="summary-card current-month">
                                <div class="card-glow"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);">
                                    <i class="fa fa-users"></i>
                                </div>
                                <div class="summary-card-label">Total Employees</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value">{{ $currentTotals['employees'] }}</div>
                                    @if($previousTotals && $differences['employees'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['employees'] > 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="fa fa-{{ $differences['employees'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ abs($differences['employees']) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="summary-card current-month">
                                <div class="card-glow"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                                    <i class="fa fa-money-bill-wave"></i>
                                </div>
                                <div class="summary-card-label">Gross Pay</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value">{{ format_currency($currentTotals['total_gross_pay']) }}</div>
                                    @if($previousTotals && $differences['total_gross_pay'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['total_gross_pay'] > 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="fa fa-{{ $differences['total_gross_pay'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ format_currency(abs($differences['total_gross_pay'])) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card highlight-net-payable current-month" style="box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25);">
                                <div class="card-glow" style="background: radial-gradient(circle at top right, rgba(59, 130, 246, 0.15), transparent);"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; width: 3rem; height: 3rem; font-size: 1.375rem; box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);">
                                    <i class="fa fa-hand-holding-usd"></i>
                                </div>
                                <div class="summary-card-label">Net Payable Amount</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value" style="font-size: 1.5rem;">{{ format_currency($currentTotals['net_payable']) }}</div>
                                    @if($previousTotals && $differences['net_payable'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['net_payable'] > 0 ? 'text-success' : 'text-danger' }}">
                                                <i class="fa fa-{{ $differences['net_payable'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ format_currency(abs($differences['net_payable'])) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card current-month">
                                <div class="card-glow"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);">
                                    <i class="fa fa-minus-circle"></i>
                                </div>
                                <div class="summary-card-label">Total Deductions</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value">{{ format_currency($currentTotals['total_deductions']) }}</div>
                                    @if($previousTotals && $differences['total_deductions'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['total_deductions'] > 0 ? 'text-danger' : 'text-success' }}">
                                                <i class="fa fa-{{ $differences['total_deductions'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ format_currency(abs($differences['total_deductions'])) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card current-month">
                                <div class="card-glow"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);">
                                    <i class="fa fa-piggy-bank"></i>
                                </div>
                                <div class="summary-card-label">EPF</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value">{{ format_currency($currentTotals['total_epf']) }}</div>
                                    @if($previousTotals && $differences['total_epf'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['total_epf'] > 0 ? 'text-danger' : 'text-success' }}">
                                                <i class="fa fa-{{ $differences['total_epf'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ format_currency(abs($differences['total_epf'])) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="summary-card current-month">
                                <div class="card-glow"></div>
                                <div class="summary-card-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);">
                                    <i class="fa fa-hospital"></i>
                                </div>
                                <div class="summary-card-label">ESIC</div>
                                <div class="summary-card-value-row">
                                    <div class="summary-card-value">{{ format_currency($currentTotals['total_esic']) }}</div>
                                    @if($previousTotals && $differences['total_esic'] != 0)
                                        <div class="summary-card-change">
                                            <span class="{{ $differences['total_esic'] > 0 ? 'text-danger' : 'text-success' }}">
                                                <i class="fa fa-{{ $differences['total_esic'] > 0 ? 'arrow-up' : 'arrow-down' }} me-1" style="font-size: 0.7rem;"></i>
                                                {{ format_currency(abs($differences['total_esic'])) }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

      <!--  @if($differences)
        
        <div class="modern-card mb-4">
            <div class="modern-card-header">
                <h4><i class="fa fa-chart-bar me-2"></i>Month-over-Month Changes</h4>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                   
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card employees">
                            <div class="change-card-icon">
                                <i class="fa fa-users"></i>
                            </div>
                            <div class="change-card-label">Employee Change</div>
                            <div class="change-card-value">
                                @if($differences['employees'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['employees'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ abs($differences['employees']) }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card gross-pay">
                            <div class="change-card-icon">
                                <i class="fa fa-money-bill-wave"></i>
                            </div>
                            <div class="change-card-label">Gross Pay Change</div>
                            <div class="change-card-value">
                                @if($differences['total_gross_pay'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['total_gross_pay'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ format_currency(abs($differences['total_gross_pay'])) }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card net-pay">
                            <div class="change-card-icon">
                                <i class="fa fa-calculator"></i>
                            </div>
                            <div class="change-card-label">Net Pay Change</div>
                            <div class="change-card-value">
                                @if($differences['net_payable'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['net_payable'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ format_currency(abs($differences['net_payable'])) }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card deductions">
                            <div class="change-card-icon">
                                <i class="fa fa-minus-circle"></i>
                            </div>
                            <div class="change-card-label">Deduction Change</div>
                            <div class="change-card-value">
                                @if($differences['total_deductions'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['total_deductions'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ format_currency(abs($differences['total_deductions'])) }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card epf">
                            <div class="change-card-icon">
                                <i class="fa fa-piggy-bank"></i>
                            </div>
                            <div class="change-card-label">EPF Change</div>
                            <div class="change-card-value">
                                @if($differences['total_epf'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['total_epf'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ format_currency(abs($differences['total_epf'])) }}
                            </div>
                        </div>
                    </div>

                    
                    <div class="col-md-4 col-lg-2">
                        <div class="change-card esic">
                            <div class="change-card-icon">
                                <i class="fa fa-hospital"></i>
                            </div>
                            <div class="change-card-label">ESIC Change</div>
                            <div class="change-card-value">
                                @if($differences['total_esic'] > 0)
                                    <i class="fa fa-arrow-up change-card-arrow"></i>
                                @elseif($differences['total_esic'] < 0)
                                    <i class="fa fa-arrow-down change-card-arrow"></i>
                                @endif
                                {{ format_currency(abs($differences['total_esic'])) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> 
        @endif -->

        <!-- Action Buttons -->
        <div class="action-buttons-container">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('payroll.salary-breakdown', ['month' => $month, 'year' => $year, 'location_id' => $locationId ?? request('location_id')]) }}" class="btn-modern btn-modern-light">
                        <i class="fa fa-arrow-left me-2"></i> Back to Salary Review
                    </a>

                    @if(!$isFinalized)
                    <form action="{{ route('payroll.finalize', [$month, $year]) }}" method="POST" id="finalizeForm">
                        @csrf
                        <input type="hidden" name="location_id" value="{{ $locationId ?? request('location_id') }}">
                        <button type="submit" class="btn-modern btn-modern-success">
                            <i class="fa fa-check-circle me-2"></i> Finalize Payroll
                        </button>
                    </form>
                    @else
                    <button type="button" class="btn-modern btn-modern-success" disabled>
                        <i class="fa fa-lock me-2"></i> Payroll Already Finalized
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const finalizeForm = document.getElementById('finalizeForm');
        
        if (finalizeForm) {
            finalizeForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                Swal.fire({
                    title: 'Finalize Payroll?',
                    html: `
                        <p class="mb-2">Are you sure you want to finalize the payroll for <strong>{{ $monthName }}</strong>?</p>
                        <div class="alert alert-warning mt-3" style="font-size: 0.9rem;">
                            <i class="fa fa-exclamation-triangle me-2"></i>
                            <strong>Warning:</strong> This action cannot be undone. Once finalized:
                            <ul class="text-start mt-2 mb-0" style="font-size: 0.85rem;">
                                <li>Attendance records will be locked</li>
                                <li>Salary calculations will be saved</li>
                                <li>No further modifications will be allowed</li>
                            </ul>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fa fa-check-circle me-2"></i> Yes, Finalize',
                    cancelButtonText: '<i class="fa fa-times me-2"></i> Cancel',
                    customClass: {
                        popup: 'swal2-popup-custom',
                        confirmButton: 'btn-modern btn-modern-success',
                        cancelButton: 'btn-modern btn-modern-secondary'
                    },
                    buttonsStyling: false,
                    reverseButtons: true,
                    focusCancel: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        Swal.fire({
                            title: 'Processing...',
                            html: 'Finalizing payroll data. Please wait...',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Submit the form
                        finalizeForm.submit();
                    }
                });
            });
        }
    });
</script>
@endsection
@section('style')
<style>
    .swal2-popup-custom {
        border-radius: 1rem;
    }
    .swal2-html-container {
        margin: 1.5em 1em !important;
    }
    .swal2-html-container .alert-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(217, 119, 6, 0.1) 100%);
        border-left: 4px solid #f59e0b;
        border-radius: 0.5rem;
        padding: 1rem;
        color: #78350f;
    }
    .swal2-html-container .alert-warning i {
        color: #f59e0b;
    }
    .btn-modern {
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.2s;
        border: none;
        cursor: pointer;
    }
    .btn-modern-success {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }
    .btn-modern-success:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-modern-secondary {
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        color: white;
    }
    .btn-modern-secondary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(107, 114, 128, 0.3);
    }
</style>
@endsection
