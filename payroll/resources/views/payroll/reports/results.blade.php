@extends('layouts.master')

@section('title', 'Payroll Report Results')

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

    /* Summary Cards */
    .summary-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; padding: 1.5rem; margin-bottom: 1rem; position: relative; overflow: hidden; }
    .summary-card::before { content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); }
    .summary-card-icon { width: 3rem; height: 3rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem; }
    .summary-card-title { font-size: 0.875rem; font-weight: 500; color: #6b7280; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .summary-card-value { font-size: 1.5rem; font-weight: 700; color: #1f2937; margin: 0; }

    /* Filter Card */
    .filter-card { background: white; border-radius: 1rem; box-shadow: 0 4px 6px rgba(0,0,0,0.07); border: 1px solid #e5e7eb; padding: 1.5rem; margin-bottom: 2rem; }
    .filter-card-header { border-bottom: 1px solid #e5e7eb; padding-bottom: 1rem; margin-bottom: 1rem; }
    .filter-badge { display: inline-block; padding: 0.25rem 0.75rem; background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; border-radius: 0.5rem; font-size: 0.875rem; margin: 0.125rem; }

    /* Table Styling */
    .modern-table { width: 100%; border-collapse: collapse; }
    .modern-table thead th { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; font-weight: 600; padding: 1rem 0.75rem; font-size: 0.875rem; text-transform: uppercase; letter-spacing: 0.5px; border: none; position: sticky; top: 0; z-index: 10; }
    .modern-table tbody td { padding: 0.75rem; border-bottom: 1px solid #f3f4f6; vertical-align: middle; font-size: 0.875rem; }
    .modern-table tbody tr:hover { background-color: rgba(102, 126, 234, 0.05); }
    .modern-table tfoot td { background: #f8f9fa; font-weight: 600; border-top: 2px solid #dee2e6; }

    /* Table Container */
    .table-container { position: relative; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-height: 600px; }
    .table-scroll-wrapper { overflow: auto; max-width: 100%; max-height: 600px; position: relative; border: 1px solid #dee2e6; border-radius: 8px; }

    /* Tab Styling */
    .nav-tabs-modern { border: none; background: #f8f9fa; border-radius: 0.5rem 0.5rem 0 0; padding: 0.5rem; }
    .nav-tabs-modern .nav-link { border: none; color: #6b7280; font-weight: 500; padding: 0.75rem 1.5rem; border-radius: 0.5rem; margin-right: 0.25rem; }
    .nav-tabs-modern .nav-link.active { background: linear-gradient(135deg,#667eea 0%,#764ba2 100%); color: white; }
    .nav-tabs-modern .nav-link:hover { background: rgba(102, 126, 234, 0.1); color: #667eea; }

    /* Employee Avatar */
    .employee-avatar { width: 35px; height: 35px; border-radius: 50%; border: 2px solid #e9ecef; overflow: hidden; margin-right: 10px; }
    .employee-avatar img { width: 100%; height: 100%; object-fit: cover; }

    /* Amount Styling */
    .amount-positive { color: #28a745; font-weight: 600; }
    .amount-negative { color: #dc3545; font-weight: 600; }
    .currency-symbol { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-weight: 500; }

    /* Button Styles */
    .btn-modern { padding: 0.75rem 2rem; border-radius: 0.5rem; font-weight: 500; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-modern-success { background: linear-gradient(135deg,#10b981 0%,#059669 100%); color: white; }
    .btn-modern-success:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); color: white; }
    .btn-modern-info { background: linear-gradient(135deg,#3b82f6 0%,#1d4ed8 100%); color: white; }
    .btn-modern-info:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4); color: white; }

    /* Back Button */
    .back-button { background: #f8f9fa; color: #6b7280; border: 1px solid #e5e7eb; }
    .back-button:hover { background: #e9ecef; }

    /* Custom Header Styles */
    .epf-header {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        color: white !important;
    }

    .gross-pay-header {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        color: white !important;
    }

    .total-deductions-header {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
    }

    .net-pay-header {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
        color: white !important;
    }

    .earnings-group {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
        color: white !important;
    }

    .deductions-group {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        color: white !important;
    }

    .bg-success-light {
        background-color: #d4edda !important;
    }

    .bg-danger-light {
        background-color: #f8d7da !important;
    }

    /* Fix sticky header for sub-row */
    .modern-table thead tr.header-sub th {
        position: sticky;
        top: 52px; /* Height of the first header row */
        z-index: 9;
    }

    .modern-table thead tr.header-sub th.bg-success-light {
        background-color: #d4edda !important;
    }

    .modern-table thead tr.header-sub th.bg-danger-light {
        background-color: #f8d7da !important;
    }

    .modern-table thead tr.header-main th {
        position: sticky;
        top: 0;
        z-index: 10;
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
                                    <i class="fas fa-chart-pie text-white" style="font-size:1.5rem;"></i>
                                </div>
                                <div>
                                    <h1 class="page-header-title">Payroll Report Results</h1>
                                    <p class="page-header-subtitle">Comprehensive payroll analysis and detailed employee compensation breakdown</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4 text-end">
                            <a href="{{ route('payroll.reports.index') }}" class="back-button btn-modern">
                                <i class="fas fa-arrow-left me-2"></i> Back to Reports
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Applied Filters -->
        <div class="filter-card">
            <div class="filter-card-header">
                <h5 class="mb-0">
                    <i class="fa fa-filter me-2 text-primary"></i> Applied Filters
                </h5>
            </div>
            <div class="row">
                <div class="col-md-3">
                    <p class="mb-2"><strong><i class="fa fa-eye me-1"></i> View Type:</strong></p>
                    <span class="filter-badge">
                        {{ $viewType === 'monthly' ? 'Monthly View' : 'Consolidated View' }}
                    </span>
                </div>
                <div class="col-md-5">
                    <p class="mb-2"><strong><i class="fa fa-calendar me-1"></i> Selected Months:</strong></p>
                    <div>
                        @foreach($appliedFilters['months'] as $month)
                            <span class="filter-badge">{{ $month }}</span>
                        @endforeach
                    </div>
                </div>
                <div class="col-md-4">
                    @if(!empty($appliedFilters['employees']))
                        <p class="mb-2"><strong><i class="fa fa-users me-1"></i> Filtered Employees:</strong></p>
                        <div>
                            @foreach($appliedFilters['employees'] as $employeeId)
                                @php
                                    $employee = \App\Models\EmployeeBasicDetail::find($employeeId);
                                @endphp
                                <span class="filter-badge">{{ $employee->name ?? 'N/A' }}</span>
                            @endforeach
                        </div>
                    @else
                        <p class="mb-2"><strong><i class="fa fa-users me-1"></i> Employees:</strong></p>
                        <span class="filter-badge">All Employees</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
                        <i class="fa fa-users fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Total Employees</h6>
                    <h4 class="summary-card-value">{{ $groupedAttendances->first() ? $groupedAttendances->first()->count() : $consolidatedData->count() }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <i class="fa fa-rupee-sign fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Total Gross Pay</h6>
                    <h4 class="summary-card-value"><span class="currency-symbol">₹</span>{{ number_format($totals['gross_pay'], 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #dc3545, #c82333);">
                        <i class="fa fa-minus-circle fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Total Deductions</h6>
                    <h4 class="summary-card-value"><span class="currency-symbol">₹</span>{{ number_format($totals['total_deduction'], 2) }}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card">
                    <div class="summary-card-icon" style="background: linear-gradient(135deg, #3fcce3, #17a2b8);">
                        <i class="fa fa-calculator fa-lg text-white"></i>
                    </div>
                    <h6 class="summary-card-title">Net Payable</h6>
                    <h4 class="summary-card-value"><span class="currency-symbol">₹</span>{{ number_format($totals['net_pay'], 2) }}</h4>
                </div>
            </div>
        </div>



        <!-- Report Results -->
        <div class="modern-card">
            <div class="modern-card-header">
                <h4><i class="fa fa-chart-pie me-2"></i>Payroll Report Summary</h4>
                <div class="mt-2">
                    <small class="text-white-50">Detailed payroll report for selected period</small>
                </div>
            </div>
            <div class="modern-card-body p-0">

                @if($viewType === 'monthly')
                    <!-- Month Tabs -->
                    <ul class="nav nav-tabs-modern" id="monthTabs" role="tablist">
                        @foreach($groupedAttendances as $month => $attendances)
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                        id="{{ Str::slug($month) }}-tab"
                                        data-bs-toggle="tab"
                                        data-bs-target="#{{ Str::slug($month) }}"
                                        type="button"
                                        role="tab">
                                    <i class="fa fa-calendar me-1"></i>{{ $month }}
                                </button>
                            </li>
                        @endforeach
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3" id="monthTabsContent">
                        @foreach($groupedAttendances as $month => $attendances)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="{{ Str::slug($month) }}"
                                role="tabpanel">

                                <div class="table-container">
                                    <div class="table-scroll-wrapper">
                                        <table class="modern-table payroll-report-table mb-0">
                                            <thead>
                                                <tr class="header-main">
                                                    <th rowspan="2" class="align-middle">Employee Details</th>
                                                    <th colspan="2" class="text-center">Attendance</th>
                                                    <th colspan="{{ $earningComponents->count() }}" class="text-center earnings-group">Earnings</th>
                                                    <th rowspan="2" class="align-middle gross-pay-header">Gross Pay</th>
                                                    <th rowspan="2" class="align-middle epf-header">EPF Wages</th>
                                                    <th colspan="{{ $deductionComponents->count() + (($hasAdvanceDeductions ?? false) ? 1 : 0) }}" class="text-center deductions-group">Deductions</th>
                                                    <th rowspan="2" class="align-middle total-deductions-header">Total Deductions</th>
                                                    <th rowspan="2" class="align-middle net-pay-header">Net Pay</th>
                                                </tr>
                                                <tr class="header-sub">
                                                    <th class="text-center">Worked Days</th>
                                                    <th class="text-center">Total Days</th>
                                                    @foreach($earningComponents as $component)
                                                        <th class="text-center bg-success-light">{{ $component->short_name }}</th>
                                                    @endforeach
                                                    @foreach($deductionComponents as $component)
                                                        <th class="text-center bg-danger-light">{{ $component->short_name }}</th>
                                                    @endforeach
                                                    @if($hasAdvanceDeductions ?? false)
                                                        <th class="text-center bg-danger-light">Advance</th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($attendances as $attendance)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="employee-avatar">
                                                                    <img src="{{ asset($attendance->employee->profile_image ?? 'assets/img/user-icon.webp') }}"
                                                                         alt="Avatar">
                                                                </div>
                                                                <div>
                                                                    <div class="font-weight-bold">{{ $attendance->employee->name }}</div>
                                                                    <small class="text-muted">{{ $attendance->employee->employee_id }}</small>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">{{ $attendance->employee_worked_days }}</td>
                                                        <td class="text-center">{{ $attendance->total_working_days }}</td>
                                                        @foreach($earningComponents as $component)
                                                            <td class="text-center">
                                                                @php
                                                                    $value = $attendance->earnings[(string)$component->id]['value'] ?? 0;
                                                                @endphp
                                                                <span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($value, 2) }}</span>
                                                            </td>
                                                        @endforeach
                                                        <td class="text-center"><span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($attendance->gross_pay, 2) }}</span></td>
                                                        <td class="text-center"><span class="currency-symbol">₹</span>{{ number_format($attendance->epfWage ?? 0, 2) }}</span></td>
                                                        @foreach($deductionComponents as $component)
                                                            <td class="text-center">
                                                                @php
                                                                    $value = $attendance->deductions[$component->id]['value'] ?? 0;
                                                                @endphp
                                                                <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($value, 2) }}</span>
                                                            </td>
                                                        @endforeach
                                                        @if($hasAdvanceDeductions ?? false)
                                                            <td class="text-center">
                                                                @php
                                                                    $advanceValue = $attendance->deductions['advance']['value'] ?? 0;
                                                                @endphp
                                                                <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($advanceValue, 2) }}</span>
                                                            </td>
                                                        @endif
                                                        <td class="text-center"><span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($attendance->total_deduction, 2) }}</span></td>
                                                        <td class="text-center"><span class="font-weight-bold text-primary"><span class="currency-symbol">₹</span>{{ number_format($attendance->total_payable, 2) }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr class="font-weight-bold">
                                                    <td class="font-weight-bold">Totals</td>
                                                    <td class="text-center">{{ $attendances->sum('employee_worked_days') }}</td>
                                                    <td class="text-center">{{ $attendances->sum('total_working_days') }}</td>
                                                    @foreach($earningComponents as $component)
                                                        <td class="text-center">
                                                            @php
                                                                $total = 0;
                                                                foreach($attendances as $attendance) {
                                                                    $total += $attendance->earnings[$component->id]['value'] ?? 0;
                                                                }
                                                            @endphp
                                                            <span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($total, 2) }}</span>
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center"><span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('gross_pay'), 2) }}</span></td>
                                                    <td class="text-center"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('epfWage'), 2) }}</span></td>
                                                    @foreach($deductionComponents as $component)
                                                        <td class="text-center">
                                                            @php
                                                                $total = 0;
                                                                foreach($attendances as $attendance) {
                                                                    $total += $attendance->deductions[$component->id]['value'] ?? 0;
                                                                }
                                                            @endphp
                                                            <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($total, 2) }}</span>
                                                        </td>
                                                    @endforeach
                                                    <td class="text-center"><span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('total_deduction'), 2) }}</span></td>
                                                    <td class="text-center"><span class="font-weight-bold text-primary"><span class="currency-symbol">₹</span>{{ number_format($attendances->sum('total_payable'), 2) }}</span></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Consolidated Table -->
                    <div class="p-3">
                        <div class="table-container">
                            <div class="table-scroll-wrapper">
                                <table class="modern-table payroll-report-table mb-0">
                                    <thead>
                                        <tr class="header-main">
                                            <th rowspan="2" class="align-middle">Employee Details</th>
                                            <th colspan="2" class="text-center">Attendance</th>
                                            <th colspan="{{ $earningComponents->count() }}" class="text-center earnings-group">Earnings</th>
                                            <th rowspan="2" class="align-middle gross-pay-header">Gross Pay</th>
                                            <th rowspan="2" class="align-middle epf-header">EPF Wages</th>
                                            <th colspan="{{ $deductionComponents->count() + (($hasAdvanceDeductions ?? false) ? 1 : 0) }}" class="text-center deductions-group">Deductions</th>
                                            <th rowspan="2" class="align-middle total-deductions-header">Total Deductions</th>
                                            <th rowspan="2" class="align-middle net-pay-header">Net Pay</th>
                                        </tr>
                                        <tr class="header-sub">
                                            <th class="text-center">Worked Days</th>
                                            <th class="text-center">Total Days</th>
                                            @foreach($earningComponents as $component)
                                                <th class="text-center bg-success-light">{{ $component->short_name }}</th>
                                            @endforeach
                                            @foreach($deductionComponents as $component)
                                                <th class="text-center bg-danger-light">{{ $component->short_name }}</th>
                                            @endforeach
                                            @if($hasAdvanceDeductions ?? false)
                                                <th class="text-center bg-danger-light">Advance</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($consolidatedData as $empId => $data)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="employee-avatar">
                                                            <img src="{{ asset($data['employee']->profile_image ?? 'assets/img/user-icon.webp') }}"
                                                                 alt="Avatar">
                                                        </div>
                                                        <div>
                                                            <div class="font-weight-bold">{{ $data['employee']->name }}</div>
                                                            <small class="text-muted">{{ $data['employee']->employee_id }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">{{ $data['employee_worked_days'] }}</td>
                                                <td class="text-center">{{ $data['total_working_days'] }}</td>
                                                @foreach($earningComponents as $component)
                                                    <td class="text-center">
                                                        <span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($data['earnings'][(string)$component->id] ?? 0, 2) }}</span>
                                                    </td>
                                                @endforeach
                                                <td class="text-center"><span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($data['gross_pay'], 2) }}</span></td>
                                                <td class="text-center"><span class="currency-symbol">₹</span>{{ number_format($data['epfWage'] ?? 0, 2) }}</span></td>
                                                @foreach($deductionComponents as $component)
                                                    <td class="text-center">
                                                        <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($data['deductions'][$component->id] ?? 0, 2) }}</span>
                                                    </td>
                                                @endforeach
                                                @if($hasAdvanceDeductions ?? false)
                                                    <td class="text-center">
                                                        <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($data['deductions']['advance'] ?? 0, 2) }}</span>
                                                    </td>
                                                @endif
                                                <td class="text-center"><span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($data['total_deduction'], 2) }}</span></td>
                                                <td class="text-center"><span class="font-weight-bold text-primary"><span class="currency-symbol">₹</span>{{ number_format($data['total_payable'], 2) }}</span></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="font-weight-bold">
                                            <td class="font-weight-bold">Totals</td>
                                            <td class="text-center">{{ $consolidatedData->sum('employee_worked_days') }}</td>
                                            <td class="text-center">{{ $consolidatedData->sum('total_working_days') }}</td>
                                            @foreach($earningComponents as $component)
                                                <td class="text-center">
                                                    @php
                                                        $total = 0;
                                                        foreach($consolidatedData as $data) {
                                                            $total += $data['earnings'][$component->id] ?? 0;
                                                        }
                                                    @endphp
                                                    <span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($total, 2) }}</span>
                                                </td>
                                            @endforeach
                                            <td class="text-center"><span class="amount-positive"><span class="currency-symbol">₹</span>{{ number_format($consolidatedData->sum('gross_pay'), 2) }}</span></td>
                                            <td class="text-center"><span class="currency-symbol">₹</span>{{ number_format($consolidatedData->sum('epfWage'), 2) }}</span></td>
                                            @foreach($deductionComponents as $component)
                                                <td class="text-center">
                                                    @php
                                                        $total = 0;
                                                        foreach($consolidatedData as $data) {
                                                            $total += $data['deductions'][$component->id] ?? 0;
                                                        }
                                                    @endphp
                                                    <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($total, 2) }}</span>
                                                </td>
                                            @endforeach
                                            @if($hasAdvanceDeductions ?? false)
                                                <td class="text-center">
                                                    @php
                                                        $advanceTotal = 0;
                                                        foreach($consolidatedData as $data) {
                                                            $advanceTotal += $data['deductions']['advance'] ?? 0;
                                                        }
                                                    @endphp
                                                    <span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($advanceTotal, 2) }}</span>
                                                </td>
                                            @endif
                                            <td class="text-center"><span class="amount-negative"><span class="currency-symbol">₹</span>{{ number_format($consolidatedData->sum('total_deduction'), 2) }}</span></td>
                                            <td class="text-center"><span class="font-weight-bold text-primary"><span class="currency-symbol">₹</span>{{ number_format($consolidatedData->sum('total_payable'), 2) }}</span></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Action Buttons -->
                <div class="modern-card-body border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('payroll.reports.index') }}" class="back-button btn-modern">
                            <i class="fas fa-arrow-left me-2"></i> Back to Reports
                        </a>
                        <div class="d-flex">                            
                            <a href="{{ route('payroll.reports.export-excel', request()->query()) }}" class="btn-modern btn-modern-success me-3">
                                <i class="fas fa-file-excel me-2"></i> Export to Excel
                            </a>
                            <a href="{{ route('payroll.reports.export', request()->query()) }}" class="btn-modern btn-modern-info">
                                <i class="fas fa-file-pdf me-2"></i> Export to PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>    </div>

</div>



@endsection

@section('scripts')



@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Initialize Bootstrap tabs properly
        $('#monthTabs button').click(function() {
            $(this).tab('show');
        });

        // Activate first tab
        $('#monthTabs button:first').tab('show');
    });
</script>
@endsection