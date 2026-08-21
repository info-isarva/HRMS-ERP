@extends('layouts.master')

@section('title', 'Payroll Comparison Report')

@section('content')
<div class="page-wrapper">
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
                                <i class="fa fa-chart-line fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Payroll Comparison Report</h1>
                                <p class="page-header-subtitle">Compare payroll data between different months</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">Payroll Comparison</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Comparison Form -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h5 class="card-title mb-0">
                            <i class="fa fa-chart-line me-2"></i> Compare Payroll Data
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('payroll.comparison.generate') }}" method="POST" id="comparisonForm">
                            @csrf
                            <div class="row">
                                <div class="col-12 col-sm-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">First Month</label>
                                        <select class="form-control form-select select2" name="first_month" id="first_month" required>
                                            <option value="">Select First Month</option>
                                            @foreach($availableMonths as $month) 
                                                <option value="{{ $month['value'] }}" {{ request('first_month') == $month['value'] ? 'selected' : '' }}>
                                                    {{ $month['label'] }}
                                                </option> 
                                            @endforeach 
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Second Month</label>
                                        <select class="form-control form-select select2" name="second_month" id="second_month" required>
                                            <option value="">Select Second Month</option>
                                            @foreach($availableMonths as $month) 
                                                <option value="{{ $month['value'] }}" {{ request('second_month') == $month['value'] ? 'selected' : '' }}>
                                                    {{ $month['label'] }}
                                                </option> 
                                            @endforeach 
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4 mb-3">
                                    <div class="form-group">
                                        <label class="form-label fw-bold">Employee Filter (Optional)</label>
                                        <select class="form-control form-select select2" name="employee_filter" id="employee_filter">
                                            <option value="">All Employees</option>
                                            @foreach($employees as $employee) 
                                                <option value="{{ $employee->id }}" {{ request('employee_filter') == $employee->id ? 'selected' : '' }}>
                                                    {{ $employee->name }}
                                                </option> 
                                            @endforeach 
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end mt-3 d-flex flex-column flex-sm-row justify-content-end">
                                <button type="submit" class="btn btn-primary btn-lg mb-2 mb-sm-0 me-sm-2">
                                    <i class="fa fa-chart-bar me-2"></i> Generate Comparison
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-lg" id="resetForm">
                                    <i class="fa fa-redo me-2"></i> Reset
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        @if(isset($comparisonData))
        <!-- Month-wise Analytics Cards -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h4 class="mb-0 fw-bold">
                            <i class="fa fa-calendar-alt me-2"></i>{{ $comparisonData['first_month_name'] }} Summary
                        </h4>
                    </div>
                    <div class="card-body d-flex flex-wrap justify-content-between">
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-users fa-lg me-1" style="color: #987fe5;"></i>
                                <div class="small fw-bold">Total Employees</div>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #987fe5;">{{ $comparisonData['totals']['first_month']['employee_count'] }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-money-bill fa-lg me-1 text-success"></i>
                                <div class="small fw-bold">Gross Pay</div>
                            </div>
                            <div class="h4 mb-0 text-success fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['gross_pay'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-calculator fa-lg me-1" style="color: #3fcce3;"></i>
                                <div class="small fw-bold">Net Payable</div>
                            </div>
                            <div class="h4 mb-0 fw-bold" style="color: #3fcce3;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['net_pay'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-minus-circle fa-lg me-1 text-danger"></i>
                                <div class="small fw-bold">Total Deductions</div>
                            </div>
                            <div class="h4 mb-0 text-danger fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['total_deductions'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-id-card fa-lg me-1" style="color: #20c997;"></i>
                                <div class="small fw-bold">EPF</div>
                            </div>
                            <div class="h4 mb-0 fw-bold" style="color: #20c997;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['epf'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-heartbeat fa-lg me-1 text-info"></i>
                                <div class="small fw-bold">ESIC</div>
                            </div>
                            <div class="h4 mb-0 text-info fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['esic'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <h4 class="mb-0 fw-bold">
                            <i class="fa fa-calendar-alt me-2"></i>{{ $comparisonData['second_month_name'] }} Summary
                        </h4>
                    </div>
                    <div class="card-body d-flex flex-wrap justify-content-between">
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-users fa-lg me-1" style="color: #987fe5;"></i>
                                <div class="small fw-bold">Total Employees</div>
                            </div>
                            <div class="h3 mb-0 fw-bold" style="color: #987fe5;">{{ $comparisonData['totals']['second_month']['employee_count'] }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class=" fa fa-money-bill fa-lg me-1 text-success"></i>
                                <div class="small fw-bold">Gross Pay</div>
                            </div>
                            <div class="h4 mb-0 text-success fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['gross_pay'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-calculator fa-lg me-1" style="color: #3fcce3;"></i>
                                <div class="small fw-bold">Net Payable</div>
                            </div>
                            <div class="h4 mb-0 fw-bold" style="color: #3fcce3;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['net_pay'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-minus-circle fa-lg me-1 text-danger"></i>
                                <div class="small fw-bold">Total Deductions</div>
                            </div>
                            <div class="h4 mb-0 text-danger fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['total_deductions'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-id-card fa-lg me-1" style="color: #20c997;"></i>
                                <div class="small fw-bold">EPF</div>
                            </div>
                            <div class="h4 mb-0 fw-bold" style="color: #20c997;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['epf'], 0) }}</div>
                        </div>
                        <div class="p-4 text-center flex-fill" style="min-width: 150px;">
                            <div class="text-muted mb-2">
                                <i class="fa fa-heartbeat fa-lg me-1 text-info"></i>
                                <div class="small fw-bold">ESIC</div>
                            </div>
                            <div class="h4 mb-0 text-info fw-bold">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['esic'], 0) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Analytics Cards -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-users fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">Employee Change</div>
                        <div class="h4 mb-0 fw-bold">
                            @if($comparisonData['summary']['employee_count_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ abs($comparisonData['summary']['employee_count_change']) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ abs($comparisonData['summary']['employee_count_change']) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100 {{ $comparisonData['summary']['gross_pay_change'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-money-bill fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">Gross Pay Change</div>
                        <div class="h5 mb-0 fw-bold">
                            @if($comparisonData['summary']['gross_pay_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['gross_pay_change']), 0) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['gross_pay_change']), 0) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100 {{ $comparisonData['summary']['net_pay_change'] >= 0 ? 'bg-success' : 'bg-danger' }}">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-calculator fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">Net Pay Change</div>
                        <div class="h5 mb-0 fw-bold">
                            @if($comparisonData['summary']['net_pay_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['net_pay_change']), 0) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['net_pay_change']), 0) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100 {{ $comparisonData['summary']['deduction_change'] >= 0 ? 'bg-warning' : 'bg-info' }}">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-minus-circle fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">Deduction Change</div>
                        <div class="h5 mb-0 fw-bold">
                            @if($comparisonData['summary']['deduction_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['deduction_change']), 0) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['deduction_change']), 0) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #20c997 0%, #17a085 100%);">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-id-card fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">EPF Change</div>
                        <div class="h5 mb-0 fw-bold">
                            @if($comparisonData['summary']['epf_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['epf_change']), 0) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['epf_change']), 0) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-4">
                <div class="card border-0 shadow-sm h-100 bg-info">
                    <div class="card-body text-center p-4 text-white">
                        <div class="mb-2">
                            <i class="fa fa-heartbeat fa-2x"></i>
                        </div>
                        <div class="small fw-bold mb-1">ESIC Change</div>
                        <div class="h5 mb-0 fw-bold">
                            @if($comparisonData['summary']['esic_change'] >= 0)
                                <i class="fa fa-arrow-up me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['esic_change']), 0) }}
                            @else
                                <i class="fa fa-arrow-down me-1"></i>{{ get_currency_symbol() }}{{ number_format(abs($comparisonData['summary']['esic_change']), 0) }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Comparison Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white !important;">
                        <div class="d-flex justify-content-between align-items-center">
                            <h4 class="card-title mb-0 fw-bold">
                                <i class="fa fa-table me-2"></i> Detailed Comparison: {{ $comparisonData['first_month_name'] }} vs {{ $comparisonData['second_month_name'] }}
                            </h4>
                            <div>
                                <button class="btn btn-outline-light btn-sm" onclick="exportToPDF()">
                                    <i class="fa fa-file-pdf me-1"></i> Export PDF
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0" id="comparisonTable">
                                <thead>
                                    <tr class="table-dark">
                                        <th rowspan="2" class="align-middle text-center border-end fw-bold" style="background-color: #6b6fd5;">Employee Details</th>
                                        <th colspan="5" class="text-center border-end fw-bold" style="background-color: #007bff; color: white;">{{ $comparisonData['first_month_name'] }}</th>
                                        <th colspan="5" class="text-center border-end fw-bold" style="background-color: #17a2b8; color: white;">{{ $comparisonData['second_month_name'] }}</th>
                                        <th colspan="5" class="text-center fw-bold" style="background-color: #28a745; color: white;">Changes</th>
                                    </tr>
                                    <tr class="table-secondary">
                                        <!-- First Month -->
                                        <th class="text-center fw-bold" style="background-color: #007bff; color: white;">Gross Pay</th>
                                        <th class="text-center fw-bold" style="background-color: #007bff; color: white;">EPF</th>
                                        <th class="text-center fw-bold" style="background-color: #007bff; color: white;">ESIC</th>
                                        <th class="text-center fw-bold" style="background-color: #007bff; color: white;">Total Deductions</th>
                                        <th class="text-center border-end fw-bold" style="background-color: #007bff; color: white;">Net Pay</th>
                                        <!-- Second Month -->
                                        <th class="text-center fw-bold" style="background-color: #17a2b8; color: white;">Gross Pay</th>
                                        <th class="text-center fw-bold" style="background-color: #17a2b8; color: white;">EPF</th>
                                        <th class="text-center fw-bold" style="background-color: #17a2b8; color: white;">ESIC</th>
                                        <th class="text-center fw-bold" style="background-color: #17a2b8; color: white;">Total Deductions</th>
                                        <th class="text-center border-end fw-bold" style="background-color: #17a2b8; color: white;">Net Pay</th>
                                        <!-- Changes -->
                                        <th class="text-center fw-bold" style="background-color: #28a745; color: white;">Gross Pay</th>
                                        <th class="text-center fw-bold" style="background-color: #28a745; color: white;">EPF</th>
                                        <th class="text-center fw-bold" style="background-color: #28a745; color: white;">ESIC</th>
                                        <th class="text-center fw-bold" style="background-color: #28a745; color: white;">Total Deductions</th>
                                        <th class="text-center fw-bold" style="background-color: #28a745; color: white;">Net Pay</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparisonData['employees'] as $employee)
                                    <tr class="align-middle hover-row">
                                        <td class="fw-bold border-end" style="background-color: #f8f9fa;">
                                            <div class="d-flex align-items-center">
                                                <div class="employee-avatar me-3">

                                                    <img src="{{ asset($employee['profile_image'] ?? 'assets/img/user-icon.webp') }}" 

                                                         class="rounded-circle"

                                                         width="45" 

                                                         height="45"

                                                         alt="Avatar">

                                                    <div class="status-indicator"></div>

                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $employee['name'] }}</div>
                                                    <small class="text-muted">ID: {{ $employee['employee_id'] }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <!-- First Month Data -->
                                        @if($employee['first_month']['status'] == 'not_joined')
                                            <td class="text-center text-muted bg-light" colspan="5">
                                                <div class="py-2">
                                                    <i class="fa fa-user-plus text-warning me-2"></i>
                                                    <em class="fw-bold">Not Joined</em>
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['first_month']['gross_pay'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['first_month']['epf'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['first_month']['esic'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['first_month']['total_deductions'], 0) }}</td>
                                            <td class="text-center border-end fw-bold text-success">{{ get_currency_symbol() }}{{ number_format($employee['first_month']['net_pay'], 0) }}</td>
                                        @endif
                                        <!-- Second Month Data -->
                                        @if($employee['second_month']['status'] == 'left')
                                            <td class="text-center text-danger bg-light" colspan="5">
                                                <div class="py-2">
                                                    <i class="fa fa-user-times text-danger me-2"></i>
                                                    <em class="fw-bold">Employee Left</em>
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['second_month']['gross_pay'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['second_month']['epf'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['second_month']['esic'], 0) }}</td>
                                            <td class="text-center fw-semibold">{{ get_currency_symbol() }}{{ number_format($employee['second_month']['total_deductions'], 0) }}</td>
                                            <td class="text-center border-end fw-bold text-success">{{ get_currency_symbol() }}{{ number_format($employee['second_month']['net_pay'], 0) }}</td>
                                        @endif
                                        <!-- Changes -->
                                        @if($employee['first_month']['status'] == 'not_joined')
                                            <td class="text-center text-success bg-light" colspan="5">
                                                <div class="py-2">
                                                    <i class="fa fa-user-plus text-success me-2"></i>
                                                    <em class="fw-bold">New Joinee</em>
                                                </div>
                                            </td>
                                        @elseif($employee['second_month']['status'] == 'left')
                                            <td class="text-center text-danger bg-light" colspan="5">
                                                <div class="py-2">
                                                    <i class="fa fa-user-minus text-danger me-2"></i>
                                                    <em class="fw-bold">Employee Left</em>
                                                </div>
                                            </td>
                                        @else
                                            <td class="text-center">
                                                @php $grossChange = $employee['second_month']['gross_pay'] - $employee['first_month']['gross_pay']; @endphp
                                                <span class="badge badge-lg {{ $grossChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $grossChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($grossChange, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $epfChange = $employee['second_month']['epf'] - $employee['first_month']['epf']; @endphp
                                                <span class="badge badge-lg {{ $epfChange >= 0 ? 'bg-warning' : 'bg-info' }}">
                                                    {{ $epfChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($epfChange, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $esicChange = $employee['second_month']['esic'] - $employee['first_month']['esic']; @endphp
                                                <span class="badge badge-lg {{ $esicChange >= 0 ? 'bg-warning' : 'bg-info' }}">
                                                    {{ $esicChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($esicChange, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $deductionChange = $employee['second_month']['total_deductions'] - $employee['first_month']['total_deductions']; @endphp
                                                <span class="badge badge-lg {{ $deductionChange >= 0 ? 'bg-warning' : 'bg-info' }}">
                                                    {{ $deductionChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($deductionChange, 0) }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                @php $netChange = $employee['second_month']['net_pay'] - $employee['first_month']['net_pay']; @endphp
                                                <span class="badge badge-lg {{ $netChange >= 0 ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $netChange >= 0 ? '+' : '' }}{{ get_currency_symbol() }}{{ number_format($netChange, 0) }}
                                                </span>
                                            </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                                <!-- Summary Row -->
                                <tfoot class="table-dark">
                                    <tr class="fw-bold">
                                        <td class="border-end text-center" style="background-color: #343a40; color: white;">
                                            <i class="fa fa-calculator me-2"></i>TOTALS
                                        </td>
                                        <td class="text-center" style="background-color: #007bff; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['gross_pay'], 0) }}</td>
                                        <td class="text-center" style="background-color: #007bff; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['epf'], 0) }}</td>
                                        <td class="text-center" style="background-color: #007bff; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['esic'], 0) }}</td>
                                        <td class="text-center" style="background-color: #007bff; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['total_deductions'], 0) }}</td>
                                        <td class="text-center border-end" style="background-color: #007bff; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['first_month']['net_pay'], 0) }}</td>
                                        <td class="text-center" style="background-color: #17a2b8; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['gross_pay'], 0) }}</td>
                                        <td class="text-center" style="background-color: #17a2b8; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['epf'], 0) }}</td>
                                        <td class="text-center" style="background-color: #17a2b8; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['esic'], 0) }}</td>
                                        <td class="text-center" style="background-color: #17a2b8; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['total_deductions'], 0) }}</td>
                                        <td class="text-center border-end" style="background-color: #17a2b8; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['totals']['second_month']['net_pay'], 0) }}</td>
                                        <td class="text-center" style="background-color: #28a745; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['gross_pay_change'], 0) }}</td>
                                        <td class="text-center" style="background-color: #28a745; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['epf_change'], 0) }}</td>
                                        <td class="text-center" style="background-color: #28a745; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['esic_change'], 0) }}</td>
                                        <td class="text-center" style="background-color: #28a745; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['deduction_change'], 0) }}</td>
                                        <td class="text-center" style="background-color: #28a745; color: white;">{{ get_currency_symbol() }}{{ number_format($comparisonData['summary']['net_pay_change'], 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
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

    .settings-card .card-header h4,
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
    .bg-gradient-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    }
    .bg-gradient-dark {
        background: linear-gradient(135deg, #343a40 0%, #23272b 100%);
    }

    /* Avatar and Status Indicator Styling */
    .employee-avatar {
        position: relative;
        display: inline-block;
    }

    .employee-avatar img {
        border: 3px solid #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .employee-avatar:hover img {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }

    .status-indicator {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background-color: #28a745;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .status-indicator.offline {
        background-color: #dc3545;
    }

    .status-indicator.away {
        background-color: #ffc107;
    }

    /* Avatar Styling */
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    /* Enhanced Table Styling */
    .table th {
        font-weight: 700;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid rgba(0,0,0,.125);
    }

    .table td {
        font-weight: 500;
        border: 1px solid rgba(0,0,0,.125);
        vertical-align: middle;
    }

    .hover-row:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Enhanced Badge Styling */
    .badge-lg {
        padding: 0.5rem 1rem;
        font-size: 0.875rem;
        font-weight: 600;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    /* Card Animations */
    .card {
        transition: all 0.3s ease;
        border: none;
        border-radius: 1rem;
        overflow: hidden;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    }

    .card-header {
        border: none;
        border-radius: 1rem 1rem 0 0;
    }

    /* Enhanced Typography */
    .fw-semibold {
        font-weight: 600;
    }

    .text-success {
        color: #28a745 !important;
        font-weight: 600;
    }

    .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
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
    .form-control, .select2-container--default .select2-selection--single {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        font-weight: 500;
    }

    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #667eea;
        font-weight: 600;
    }

    .text-danger {
        color: #dc3545 !important;
        font-weight: 600;
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
    .form-control, .select2-container--default .select2-selection--single {
        border-radius: 0.5rem;
        border: 2px solid #e9ecef;
        font-weight: 500;
    }

    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    /* Select2 Styling */
    .select2-container--default .select2-selection--single {
        height: calc(2.25rem + 2px);
        padding: 0.375rem 0.75rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #495057;
        line-height: 1.5;
        padding-left: 0;
        padding-right: 20px;
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
        
        .h3, .h4 {
            font-size: 1.5rem;
        }
        
        .badge-lg {
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
        }
        
        .avatar-circle {
            width: 40px;
            height: 40px;
        }
        
        /* Change analytics cards mobile layout */
        .col-12.col-sm-6.col-md-3 {
            margin-bottom: 1rem;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body {
            padding: 1rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .fa-2x {
            font-size: 1.5rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .h4,
        .col-12.col-sm-6.col-md-3 .card-body .h5 {
            font-size: 1.1rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .small {
            font-size: 0.7rem !important;
        }
        
        /* Month-wise analytics cards mobile adjustments */
        .col-12.col-md-6 .card-body .col-4 {
            padding: 0.75rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .fa-lg {
            font-size: 1rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .h3,
        .col-12.col-md-6 .card-body .col-4 .h4 {
            font-size: 1.2rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .small {
            font-size: 0.7rem !important;
        }
        
        /* Button spacing on mobile */
        .d-flex.flex-column.flex-sm-row .btn {
            width: 100%;
        }
        
        .d-flex.flex-column.flex-sm-row .btn.me-sm-2 {
            margin-right: 0.5rem;
        }
    }
    
    @media (max-width: 576px) {
        .page-header-title {
            font-size: 1.25rem !important;
        }
        
        .page-header-subtitle {
            font-size: 0.9rem !important;
        }
        
        .settings-card .card-body {
            padding: 1.5rem 1rem !important;
        }
        
        /* Make change cards full width on very small screens */
        .col-12.col-sm-6.col-md-3 {
            flex: 0 0 100%;
            max-width: 100%;
        }
        
        /* Table responsive improvements */
        .table-responsive {
            font-size: 0.8rem;
        }
        
        .table th,
        .table td {
            padding: 0.5rem 0.25rem;
        }
        
        .employee-avatar img {
            width: 35px !important;
            height: 35px !important;
        }
        
        .employee-avatar .status-indicator {
            width: 10px !important;
            height: 10px !important;
        }
    }
    
    /* Extra small screens (phones < 480px) */
    @media (max-width: 480px) {
        .container-fluid {
            padding-left: 10px !important;
            padding-right: 10px !important;
        }
        
        .page-wrapper {
            padding: 1rem 0.5rem !important;
        }
        
        .page-header-gradient {
            padding: 1.5rem 1rem !important;
        }
        
        .page-header-title {
            font-size: 1.1rem !important;
        }
        
        .page-header-subtitle {
            font-size: 0.8rem !important;
        }
        
        .page-header-icon-box {
            width: 3rem !important;
            height: 3rem !important;
        }
        
        .page-header-icon-box i {
            font-size: 1.2rem !important;
        }
        
        .settings-card {
            margin-bottom: 1rem !important;
            border-radius: 0.5rem !important;
        }
        
        .settings-card .card-header {
            padding: 1rem !important;
        }
        
        .settings-card .card-header h5 {
            font-size: 1rem !important;
        }
        
        .settings-card .card-body {
            padding: 1rem 0.75rem !important;
        }
        
        /* Form inputs on very small screens */
        .form-group {
            margin-bottom: 1rem !important;
        }
        
        .form-label {
            font-size: 0.9rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        .select2-container--default .select2-selection--single {
            height: calc(2rem + 2px) !important;
            font-size: 0.9rem !important;
        }
        
        /* Month summary cards - ensure no overflow */
        .col-12.col-md-6 {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 {
            padding: 0.5rem 0.25rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .fa-lg {
            font-size: 0.9rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .h3 {
            font-size: 1rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .h4 {
            font-size: 1.1rem !important;
        }
        
        .col-12.col-md-6 .card-body .col-4 .small {
            font-size: 0.65rem !important;
        }
        
        /* Change analytics cards - full width and compact */
        .col-12.col-sm-6.col-md-3 {
            padding-left: 5px !important;
            padding-right: 5px !important;
            margin-bottom: 0.75rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body {
            padding: 0.75rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .fa-2x {
            font-size: 1.2rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .h4,
        .col-12.col-sm-6.col-md-3 .card-body .h5 {
            font-size: 0.9rem !important;
        }
        
        .col-12.col-sm-6.col-md-3 .card-body .small {
            font-size: 0.6rem !important;
        }
        
        /* Buttons on very small screens */
        .d-flex.flex-column.flex-sm-row .btn {
            font-size: 0.9rem !important;
            padding: 0.5rem 1rem !important;
        }
        
        /* Table on very small screens */
        .table-responsive {
            font-size: 0.7rem !important;
            margin-left: -10px !important;
            margin-right: -10px !important;
            width: calc(100% + 20px) !important;
        }
        
        .table th,
        .table td {
            padding: 0.25rem 0.125rem !important;
            white-space: nowrap;
        }
        
        .employee-avatar {
            margin-right: 0.5rem !important;
        }
        
        .employee-avatar img {
            width: 30px !important;
            height: 30px !important;
        }
        
        .employee-avatar .status-indicator {
            width: 8px !important;
            height: 8px !important;
        }
        
        .badge-lg {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.7rem !important;
        }
        
        /* Card headers */
        .card-header h4 {
            font-size: 0.9rem !important;
        }
        
        .card-header h4 i {
            font-size: 0.8rem !important;
        }
    }
    
    /* Ultra small screens (phones < 400px) - Force single column layout */
    @media (max-width: 400px) {
        /* Ensure all cards are single column and no overflow */
        .col-12.col-sm-6.col-md-3 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 2px !important;
            padding-right: 2px !important;
            margin-bottom: 0.5rem !important;
        }
        
        .col-12.col-md-6 {
            flex: 0 0 100% !important;
            max-width: 100% !important;
            width: 100% !important;
            padding-left: 2px !important;
            padding-right: 2px !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* Reduce container padding even more */
        .container-fluid {
            padding-left: 5px !important;
            padding-right: 5px !important;
        }
        
        .page-wrapper {
            padding: 0.75rem 0.25rem !important;
        }
        
        /* Make cards even more compact */
        .col-12.col-sm-6.col-md-3 .card-body {
            padding: 0.5rem !important;
        }
        
        .col-12.col-md-6 .card-body {
            padding: 0.75rem 0.5rem !important;
        }
        
        /* Ensure no horizontal overflow */
        .row {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
        
        .settings-card {
            margin-left: 0 !important;
            margin-right: 0 !important;
        }
    }

    /* Print Styling */
    @media print {
        .card {
            box-shadow: none !important;
            border: 1px solid #dee2e6 !important;
        }
        
        .btn {
            display: none !important;
        }
        
        .hover-row:hover {
            transform: none !important;
            box-shadow: none !important;
        }
    }

    /* Smooth Scrolling for Table */
    .table-responsive {
        border-radius: 0 0 1rem 1rem;
    }

    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: function() {
                return $(this).data('placeholder');
            },
            allowClear: true
        });

        // Form validation
        $('#comparisonForm').submit(function(e) {
            const firstMonth = $('#first_month').val();
            const secondMonth = $('#second_month').val();
            
            if (!firstMonth || !secondMonth) {
                e.preventDefault();
                alert('Please select both months for comparison');
                return false;
            }
            
            if (firstMonth === secondMonth) {
                e.preventDefault();
                alert('Please select different months for comparison');
                return false;
            }
        });

        // Reset button functionality
        $("#resetForm").click(function() {
            $("#comparisonForm")[0].reset();
            $('.select2').val(null).trigger('change');
        });
    });

    function exportToPDF() {
        // Implementation for PDF export
        const form = document.createElement('form');
        form.method = 'GET';
        form.action = '{{ route("payroll.comparison.export") }}';
        
        // Add form parameters
        const firstMonth = document.getElementById('first_month').value;
        const secondMonth = document.getElementById('second_month').value;
        const employeeFilter = document.getElementById('employee_filter').value;
        
        if (firstMonth) {
            const input1 = document.createElement('input');
            input1.type = 'hidden';
            input1.name = 'first_month';
            input1.value = firstMonth;
            form.appendChild(input1);
        }
        
        if (secondMonth) {
            const input2 = document.createElement('input');
            input2.type = 'hidden';
            input2.name = 'second_month';
            input2.value = secondMonth;
            form.appendChild(input2);
        }
        
        if (employeeFilter) {
            const input3 = document.createElement('input');
            input3.type = 'hidden';
            input3.name = 'employee_filter';
            input3.value = employeeFilter;
            form.appendChild(input3);
        }
        
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
</script>

@endsection
