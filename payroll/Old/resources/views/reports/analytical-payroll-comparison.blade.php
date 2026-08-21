@extends('layouts.master')

@section('title', 'Payroll Report Results')
@section('content')
<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="row">
            <div class="col-lg-12">
                <!-- Modern Page Header -->
                <div class="page-header-card">
                    <div class="page-header-gradient">
                        <div class="page-header-pattern"></div>
                        <div class="page-header-circle-1"></div>
                        <div class="page-header-circle-2"></div>
                        <div class="d-flex align-items-center">
                            <div class="page-header-icon-box">
                                <i class="fas fa-chart-line fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Analytical Payroll Comparison Report</h1>
                                <p class="page-header-subtitle">Compare payroll data across financial years and months</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Reports</a></li>
                                <li class="breadcrumb-item active">Analytical Comparison</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

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

            /* Modern Settings Card */
            .settings-card {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                overflow: visible;
                border: 1px solid #e5e7eb;
                margin-bottom: 1.5rem;
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
            }

            .settings-card .card-header i {
                margin-right: 0.5rem;
                opacity: 0.9;
            }

            .settings-card .card-body {
                padding: 2rem;
            }
            .metric-card {
                background: #fff;
                border: 1px solid #e9ecef;
                border-radius: 8px;
                padding: 20px;
                text-align: center;
                margin-bottom: 15px;
                transition: all 0.3s ease;
            }
            
            .metric-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            
            .metric-value {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 5px;
            }
            
            .metric-label {
                font-size: 0.9rem;
                color: #6c757d;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }
            
            .change-badge {
                display: inline-block;
                padding: 4px 8px;
                border-radius: 12px;
                font-size: 0.8rem;
                font-weight: bold;
                margin-top: 5px;
            }
            
            /* Mobile Responsive Styles */
            @media (max-width: 768px) {
                .metric-card {
                    padding: 15px;
                    margin-bottom: 20px;
                }
                
                .metric-value {
                    font-size: 1.5rem;
                }
                
                .metric-label {
                    font-size: 0.85rem;
                }
                
                .value-display {
                    font-size: 0.7rem;
                    line-height: 1.4;
                    margin-top: 8px;
                    word-break: break-word;
                }
                
                .change-badge {
                    font-size: 0.75rem;
                    padding: 3px 6px;
                    margin-top: 8px;
                }
                
                .comparison-card {
                    margin-bottom: 25px;
                }
                
                .comparison-header {
                    font-size: 0.9rem;
                    padding: 0.8rem 1rem;
                }
                
                .comparison-body {
                    padding: 15px;
                }
                
                .component-grid {
                    grid-template-columns: 1fr;
                    gap: 8px;
                }
                
                .component-item {
                    flex-direction: column;
                    align-items: flex-start;
                    padding: 10px;
                }
                
                .component-name {
                    margin-bottom: 5px;
                }
                
                .comparison-chart {
                    height: 150px;
                    margin: 10px 0;
                }
            }
            
            @media (max-width: 576px) {
                .metric-card {
                    padding: 12px;
                }
                
                .metric-value {
                    font-size: 1.3rem;
                }
                
                .metric-label {
                    font-size: 0.8rem;
                }
                
                .value-display {
                    font-size: 0.65rem;
                }
                
                .change-badge {
                    font-size: 0.7rem;
                }
                
                .page-header-title {
                    font-size: 1.4rem;
                }
                
                .page-header-subtitle {
                    font-size: 0.85rem;
                }
            }
            
            .change-positive {
                background-color: #d4edda;
                color: #155724;
            }
            
            .change-negative {
                background-color: #f8d7da;
                color: #721c24;
            }
            
            .chart-container {
                position: relative;
                height: 400px;
                margin: 20px 0;
            }
            
            .comparison-card {
                background: white;
                border: 1px solid #e5e7eb;
                border-radius: 1rem;
                margin-bottom: 20px;
                overflow: hidden;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            }
            
            .comparison-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 1rem 1.5rem;
                border-bottom: none;
                font-weight: 600;
                text-align: center;
                border-radius: 1rem 1rem 0 0 !important;
            }
            
            .comparison-body {
                padding: 20px;
            }
            
            .component-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 10px;
                margin-top: 15px;
            }
            
            .component-item {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 12px;
                background: #f8f9fa;
                border-radius: 5px;
                border-left: 3px solid #007bff;
            }
            
            .component-name {
                font-size: 0.9rem;
                color: #495057;
                font-weight: 500;
            }
            
            .component-change {
                font-size: 0.85rem;
                font-weight: bold;
            }
            
            .value-display {
                font-size: 0.75rem;
                color: #6c757d;
                margin-top: 3px;
            }
            
            .comparison-chart {
                position: relative;
                height: 200px;
                margin: 15px 0;
            }
            
            /* Color coding rules */
            .employee-increase { color: #28a745; }
            .employee-decrease { color: #dc3545; }
            .gross-increase { color: #dc3545; }
            .gross-decrease { color: #28a745; }
            .deduction-increase { color: #28a745; }
            .deduction-decrease { color: #dc3545; }
            .net-increase { color: #dc3545; }
            .net-decrease { color: #28a745; }
            
            /* Print Styles - Hide sidebar and header, optimize content */
            @media print {
                /* Hide sidebar and header */
                .sidebar,
                .header,
                .main-wrapper .header,
                #sidebar,
                .mobile_btn {
                    display: none !important;
                }
                
                /* Adjust main content to full width */
                .page-wrapper {
                    margin-left: 0 !important;
                    padding-left: 0 !important;
                    width: 100% !important;
                }
                
                .content {
                    margin-left: 0 !important;
                    padding-left: 15px !important;
                    padding-right: 15px !important;
                }
                
                /* Optimize for print */
                body {
                    font-size: 12px !important;
                    line-height: 1.4 !important;
                    color: #000 !important;
                    background: white !important;
                }
                
                /* Hide export buttons in print */
                .btn {
                    display: none !important;
                }
                
                /* Show only submit button for filters */
                .btn-primary {
                    display: inline-block !important;
                }
                
                /* Hide print button during actual printing */
                .btn-warning {
                    display: none !important;
                }
                
                /* Optimize cards for print */
                .card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                    margin-bottom: 15px !important;
                }
                
                .metric-card {
                    border: 1px solid #ddd !important;
                    box-shadow: none !important;
                    margin-bottom: 10px !important;
                    page-break-inside: avoid;
                }
                
                /* Optimize tables for print */
                .table {
                    font-size: 10px !important;
                }
                
                .table th,
                .table td {
                    padding: 4px !important;
                }
                
                /* Page breaks */
                .chart-container {
                    page-break-inside: avoid;
                }
                
                .comparison-card {
                    page-break-inside: avoid;
                    margin-bottom: 15px !important;
                }
                
                /* Ensure charts are visible in print */
                canvas {
                    max-width: 100% !important;
                    height: auto !important;
                }
                
                /* Hide unnecessary elements */
                .breadcrumb,
                .col-auto i {
                    display: none !important;
                }
                
                /* Adjust page title */
                .page-title {
                    font-size: 18px !important;
                    margin-bottom: 10px !important;
                }
                
                /* Print-friendly spacing */
                .row {
                    margin-bottom: 10px !important;
                }
                
                .col-md-3, .col-md-6, .col-md-12 {
                    padding-left: 5px !important;
                    padding-right: 5px !important;
                }
            }
        </style>
        <style>
            /* Scoped responsive tweaks for filter section */
            @media (max-width: 575.98px) {
                .settings-card .card-body form .btn + .btn { margin-left: 0 !important; }
            }
            @media (min-width: 768px) {
                .settings-card .card-body form .d-flex.align-items-stretch.align-items-md-end { align-items: flex-end !important; }
                .settings-card .card-body form .d-flex.align-items-stretch.align-items-md-end .btn { min-width: 140px; }
            }
        </style>

        <!-- Financial Year Selector -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fas fa-filter"></i>Filter Options</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.payroll.comparison') }}">
                            <div class="row align-items-end">
                                <div class="col-12 col-md-8">
                                    <div class="form-group">
                                        <label class="form-label font-weight-bold">
                                            <i class="fas fa-calendar-alt me-2"></i>Select Financial Year
                                        </label>
                                        <select name="financial_year_id" id="financial_year_id" class="form-control form-select">
                                            <option value="">-- All Financial Years --</option>
                                            @foreach($financialYears as $fy)
                                                <option value="{{ $fy->id }}" {{ $selectedFinancialYearId == $fy->id ? 'selected' : '' }}>
                                                    {{ $fy->name }} ({{ $fy->start_date->format('M Y') }} - {{ $fy->end_date->format('M Y') }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 mt-2 mt-md-0">
                                    <div class="d-flex flex-column align-items-stretch align-items-md-end">
                                        <button type="submit" class="btn btn-primary w-100 mb-2 mb-md-2" aria-label="Apply Filter">
                                            <i class="fas fa-filter me-2" aria-hidden="true"></i>Apply Filter
                                        </button>
                                        @if(count($monthlyData) > 0)
                                            <a href="{{ route('reports.payroll.comparison.pdf', ['financial_year_id' => $selectedFinancialYearId]) }}"
                                               class="btn btn-success w-100 mb-2" target="_blank" aria-label="Export PDF">
                                                <i class="fas fa-file-pdf me-2" aria-hidden="true"></i>Export PDF
                                            </a>
                                            <button type="button" class="btn btn-warning w-100" onclick="window.print()" aria-label="Print Page">
                                                <i class="fas fa-print me-2" aria-hidden="true"></i>Print Page
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(count($monthlyData) > 0)
            <!-- Charts Section -->
            <div class="row">
                <div class="col-md-6">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i>Employee Count & Gross Pay Trends</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="employeeGrossChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-money-bill-wave"></i>Deductions & Net Pay Trends</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="deductionNetChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Component-wise Trends Chart -->
            <div class="row">
                <div class="col-md-12">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-area"></i>Component-wise Deduction Trends</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="componentChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Statistics -->
            <div class="row">
                <div class="col-md-12">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i>Latest Month Summary</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $latestMonth = array_values($monthlyData)[0];
                            @endphp
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-value text-primary">{{ $latestMonth['employee_count'] }}</div>
                                        <div class="metric-label">Employees</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-value text-success">{{ get_currency_symbol() }}{{ number_format($latestMonth['gross_pay']) }}</div>
                                        <div class="metric-label">Gross Pay</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-value text-warning">{{ get_currency_symbol() }}{{ number_format($latestMonth['total_deductions']) }}</div>
                                        <div class="metric-label">Total Deductions</div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="metric-card">
                                        <div class="metric-value text-info">{{ get_currency_symbol() }}{{ number_format($latestMonth['net_pay']) }}</div>
                                        <div class="metric-label">Net Pay</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Data Overview Table -->
            <div class="row">
                <div class="col-md-12">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-table"></i>Monthly Data Overview</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Month</th>
                                            <th>Employees</th>
                                            <th>Gross Pay</th>
                                            <th>EPF</th>
                                            <th>ESI</th>
                                            <th>Professional Tax</th>
                                            <th>TDS</th>
                                            <th>Advance</th>
                                            <th>Total Deductions</th>
                                            <th>Net Pay</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($monthlyData as $monthKey => $month)
                                            <tr>
                                                <td><strong>{{ $month['label'] }}</strong></td>
                                                <td>{{ $month['employee_count'] }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['gross_pay']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['epf']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['esi']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['pt']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['tds']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['advance']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['total_deductions']) }}</td>
                                                <td>{{ get_currency_symbol() }}{{ number_format($month['net_pay']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Month-over-Month Comparisons -->
            @if(count($comparisons) > 0)
                <div class="row">
                    <div class="col-md-12">
                        <div class="settings-card">
                            <div class="card-header">
                                <h5><i class="fas fa-exchange-alt"></i>Month-over-Month Comparisons</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    @foreach($comparisons as $index => $comparison)
                                        <div class="col-xl-6 col-lg-12 col-md-12 mb-4">
                                            <div class="comparison-card">
                                                <div class="comparison-header">
                                                    {{ $comparison['current_month'] }} vs {{ $comparison['previous_month'] }}
                                                </div>
                                                <div class="comparison-body">
                                                    <!-- Individual Comparison Chart -->
                                                    <div class="comparison-chart">
                                                        <canvas id="comparisonChart{{ $index }}"></canvas>
                                                    </div>
                                                    
                                                    <!-- Main metrics with all values -->
                                                    <div class="row mb-3">
                                                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                                                            <div class="metric-card">
                                                                <div class="metric-value {{ $comparison['employee_count']['is_increase'] ? 'employee-increase' : 'employee-decrease' }}">
                                                                    {{ $comparison['employee_count']['current'] }}
                                                                </div>
                                                                <div class="metric-label">Employees</div>
                                                                <div class="value-display">
                                                                    Previous: {{ $comparison['employee_count']['previous'] }}
                                                                    | Diff: {{ $comparison['employee_count']['difference'] > 0 ? '+' : '' }}{{ $comparison['employee_count']['difference'] }}
                                                                </div>
                                                                <div class="change-badge {{ $comparison['employee_count']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                                                                    <i class="fas fa-{{ $comparison['employee_count']['is_increase'] ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                                    {{ $comparison['employee_count']['percentage'] }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <div class="metric-card">
                                                                <div class="metric-value {{ $comparison['gross_pay']['is_increase'] ? 'gross-increase' : 'gross-decrease' }}">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['gross_pay']['current']) }}
                                                                </div>
                                                                <div class="metric-label">Gross Pay</div>
                                                                <div class="value-display">
                                                                    Previous: {{ get_currency_symbol() }}{{ number_format($comparison['gross_pay']['previous']) }}
                                                                    | Diff: {{ get_currency_symbol() }}{{ $comparison['gross_pay']['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['gross_pay']['difference']) }}
                                                                </div>
                                                                <div class="change-badge {{ $comparison['gross_pay']['is_increase'] ? 'change-negative' : 'change-positive' }}">
                                                                    <i class="fas fa-{{ $comparison['gross_pay']['is_increase'] ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                                    {{ $comparison['gross_pay']['percentage'] }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row mb-3">
                                                        <div class="col-md-6 col-12 mb-3 mb-md-0">
                                                            <div class="metric-card">
                                                                <div class="metric-value {{ $comparison['total_deductions']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['total_deductions']['current']) }}
                                                                </div>
                                                                <div class="metric-label">Total Deductions</div>
                                                                <div class="value-display">
                                                                    Previous: {{ get_currency_symbol() }}{{ number_format($comparison['total_deductions']['previous']) }}
                                                                    | Diff: {{ get_currency_symbol() }}{{ $comparison['total_deductions']['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['total_deductions']['difference']) }}
                                                                </div>
                                                                <div class="change-badge {{ $comparison['total_deductions']['is_increase'] ? 'change-positive' : 'change-negative' }}">
                                                                    <i class="fas fa-{{ $comparison['total_deductions']['is_increase'] ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                                    {{ $comparison['total_deductions']['percentage'] }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 col-12">
                                                            <div class="metric-card">
                                                                <div class="metric-value {{ $comparison['net_pay']['is_increase'] ? 'net-increase' : 'net-decrease' }}">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['net_pay']['current']) }}
                                                                </div>
                                                                <div class="metric-label">Net Pay</div>
                                                                <div class="value-display">
                                                                    Previous: {{ get_currency_symbol() }}{{ number_format($comparison['net_pay']['previous']) }}
                                                                    | Diff: {{ get_currency_symbol() }}{{ $comparison['net_pay']['difference'] > 0 ? '+' : '' }}{{ number_format($comparison['net_pay']['difference']) }}
                                                                </div>
                                                                <div class="change-badge {{ $comparison['net_pay']['is_increase'] ? 'change-negative' : 'change-positive' }}">
                                                                    <i class="fas fa-{{ $comparison['net_pay']['is_increase'] ? 'arrow-up' : 'arrow-down' }} me-1"></i>
                                                                    {{ $comparison['net_pay']['percentage'] }}%
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Component breakdown with values -->
                                                    <div class="component-grid">
                                                        <div class="component-item">
                                                            <span class="component-name">EPF</span>
                                                            <div>
                                                                <span class="component-change {{ $comparison['epf']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ $comparison['epf']['percentage'] > 0 ? '+' : '' }}{{ $comparison['epf']['percentage'] }}%
                                                                </span>
                                                                <div class="value-display">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['epf']['current']) }} vs {{ get_currency_symbol() }}{{ number_format($comparison['epf']['previous']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="component-item">
                                                            <span class="component-name">ESI</span>
                                                            <div>
                                                                <span class="component-change {{ $comparison['esi']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ $comparison['esi']['percentage'] > 0 ? '+' : '' }}{{ $comparison['esi']['percentage'] }}%
                                                                </span>
                                                                <div class="value-display">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['esi']['current']) }} vs {{ get_currency_symbol() }}{{ number_format($comparison['esi']['previous']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="component-item">
                                                            <span class="component-name">Professional Tax</span>
                                                            <div>
                                                                <span class="component-change {{ $comparison['pt']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ $comparison['pt']['percentage'] > 0 ? '+' : '' }}{{ $comparison['pt']['percentage'] }}%
                                                                </span>
                                                                <div class="value-display">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['pt']['current']) }} vs {{ get_currency_symbol() }}{{ number_format($comparison['pt']['previous']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="component-item">
                                                            <span class="component-name">TDS</span>
                                                            <div>
                                                                <span class="component-change {{ $comparison['tds']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ $comparison['tds']['percentage'] > 0 ? '+' : '' }}{{ $comparison['tds']['percentage'] }}%
                                                                </span>
                                                                <div class="value-display">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['tds']['current']) }} vs {{ get_currency_symbol() }}{{ number_format($comparison['tds']['previous']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="component-item">
                                                            <span class="component-name">Advance</span>
                                                            <div>
                                                                <span class="component-change {{ $comparison['advance']['is_increase'] ? 'deduction-increase' : 'deduction-decrease' }}">
                                                                    {{ $comparison['advance']['percentage'] > 0 ? '+' : '' }}{{ $comparison['advance']['percentage'] }}%
                                                                </span>
                                                                <div class="value-display">
                                                                    {{ get_currency_symbol() }}{{ number_format($comparison['advance']['current']) }} vs {{ get_currency_symbol() }}{{ number_format($comparison['advance']['previous']) }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <!-- No Data Available -->
            <div class="row">
                <div class="col-md-12">
                    <div class="settings-card">
                        <div class="card-header">
                            <h5><i class="fas fa-info-circle"></i>No Data Available</h5>
                        </div>
                        <div class="card-body text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">No Payroll Data Available</h4>
                            <p class="text-muted">
                                @if($selectedFY)
                                    No completed payroll records found for the selected financial year: {{ $selectedFY->name }}
                                @else
                                    No completed payroll records found. Please ensure payroll processing has been completed for at least one month.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <!-- /Page Content -->
</div>
<!-- /Page Wrapper -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($monthlyData) > 0)
        // Prepare chart data
        const chartData = @json($chartData);
        const monthlyData = @json($monthlyData);
        
        console.log('Chart Data:', chartData); // Debug log
        console.log('Monthly Data:', monthlyData); // Debug log
        
        // Prepare component data
        const componentData = {
            months: chartData.months.slice().reverse(),
            epf: [],
            esi: [],
            pt: [],
            tds: [],
            advance: []
        };
        
        // Reverse the monthly data order to show chronological progression
        const reversedMonthlyData = Object.values(monthlyData).reverse();
        
        reversedMonthlyData.forEach(month => {
            componentData.epf.push(month.epf);
            componentData.esi.push(month.esi);
            componentData.pt.push(month.pt);
            componentData.tds.push(month.tds);
            componentData.advance.push(month.advance);
        });
        
        // Reverse the main chart data arrays for chronological order
        const reversedChartData = {
            months: chartData.months.slice().reverse(),
            employeeCount: chartData.employeeCount.slice().reverse(),
            grossPay: chartData.grossPay.slice().reverse(),
            totalDeductions: chartData.totalDeductions.slice().reverse(),
            netPay: chartData.netPay.slice().reverse()
        };
        
        // Common chart options
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.dataset.label.includes('{{ get_currency_symbol() }}')) {
                                label += '{{ get_currency_symbol() }}' + context.parsed.y.toLocaleString();
                            } else {
                                label += context.parsed.y.toLocaleString();
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: 'Month'
                    }
                }
            }
        };
        
        // Employee Count & Gross Pay Chart
        const employeeGrossCtx = document.getElementById('employeeGrossChart');
        if (employeeGrossCtx) {
            new Chart(employeeGrossCtx, {
                type: 'line',
                data: {
                    labels: reversedChartData.months,
                    datasets: [
                        {
                            label: 'Employee Count',
                            data: reversedChartData.employeeCount,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            yAxisID: 'y',
                            tension: 0.4
                        },
                        {
                            label: 'Gross Pay ({{ get_currency_symbol() }})',
                            data: reversedChartData.grossPay,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            yAxisID: 'y1',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Employee Count'
                            },
                            beginAtZero: true
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Gross Pay ({{ get_currency_symbol() }})'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Deductions & Net Pay Chart
        const deductionNetCtx = document.getElementById('deductionNetChart');
        if (deductionNetCtx) {
            new Chart(deductionNetCtx, {
                type: 'line',
                data: {
                    labels: reversedChartData.months,
                    datasets: [
                        {
                            label: 'Total Deductions ({{ get_currency_symbol() }})',
                            data: reversedChartData.totalDeductions,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Net Pay ({{ get_currency_symbol() }})',
                            data: reversedChartData.netPay,
                            borderColor: '#6f42c1',
                            backgroundColor: 'rgba(111, 66, 193, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Amount ({{ get_currency_symbol() }})'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Component-wise Trends Chart
        const componentCtx = document.getElementById('componentChart');
        if (componentCtx) {
            new Chart(componentCtx, {
                type: 'line',
                data: {
                    labels: componentData.months,
                    datasets: [
                        {
                            label: 'EPF ({{ get_currency_symbol() }})',
                            data: componentData.epf,
                            borderColor: '#007bff',
                            backgroundColor: 'rgba(0, 123, 255, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'ESI ({{ get_currency_symbol() }})',
                            data: componentData.esi,
                            borderColor: '#28a745',
                            backgroundColor: 'rgba(40, 167, 69, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Professional Tax ({{ get_currency_symbol() }})',
                            data: componentData.pt,
                            borderColor: '#ffc107',
                            backgroundColor: 'rgba(255, 193, 7, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'TDS ({{ get_currency_symbol() }})',
                            data: componentData.tds,
                            borderColor: '#dc3545',
                            backgroundColor: 'rgba(220, 53, 69, 0.1)',
                            tension: 0.4
                        },
                        {
                            label: 'Advance ({{ get_currency_symbol() }})',
                            data: componentData.advance,
                            borderColor: '#6f42c1',
                            backgroundColor: 'rgba(111, 66, 193, 0.1)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    ...commonOptions,
                    scales: {
                        ...commonOptions.scales,
                        y: {
                            display: true,
                            title: {
                                display: true,
                                text: 'Amount ({{ get_currency_symbol() }})'
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        // Individual Comparison Charts
        const comparisons = @json($comparisons);
        comparisons.forEach((comparison, index) => {
            const comparisonCtx = document.getElementById(`comparisonChart${index}`);
            if (comparisonCtx) {
                new Chart(comparisonCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Gross Pay ({{ get_currency_symbol() }})', 'Total Deductions ({{ get_currency_symbol() }})', 'Net Pay ({{ get_currency_symbol() }})'],
                        datasets: [
                            {
                                label: comparison.current_month,
                                data: [
                                    comparison.gross_pay.current,
                                    comparison.total_deductions.current,
                                    comparison.net_pay.current
                                ],
                                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            },
                            {
                                label: comparison.previous_month,
                                data: [
                                    comparison.gross_pay.previous,
                                    comparison.total_deductions.previous,
                                    comparison.net_pay.previous
                                ],
                                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                                borderColor: 'rgba(255, 99, 132, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    boxWidth: 12,
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) {
                                            label += ': ';
                                        }
                                        label += '{{ get_currency_symbol() }}' + context.parsed.y.toLocaleString();
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: {
                                        size: 10
                                    }
                                }
                            },
                            x: {
                                ticks: {
                                    font: {
                                        size: 9
                                    },
                                    maxRotation: 45
                                }
                            }
                        }
                    }
                });
            }
        });
    @endif
});
</script>
@endsection