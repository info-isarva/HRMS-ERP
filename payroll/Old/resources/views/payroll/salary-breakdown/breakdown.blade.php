@extends('layouts.master')
@section('title', 'Salary Breakdown - ' . \Carbon\Carbon::createFromDate($payoutYear, $payoutMonth, 1)->format('F Y'))

@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">
                        Salary Breakdown - {{ \Carbon\Carbon::createFromDate($payoutYear, $payoutMonth, 1)->format('F Y') }}
                    </h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll') }}">Payroll</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll.salary-breakdown') }}">Salary Breakdown</a></li>
                        <li class="breadcrumb-item active">{{ \Carbon\Carbon::createFromDate($payoutYear, $payoutMonth, 1)->format('F Y') }}</li>
                    </ul>
                </div>
                <div class="col-auto">
                    <a href="{{ route('payroll.salary-breakdown') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Overview
                    </a>
                    <a href="{{ route('payroll.salary-breakdown.export') }}?payout_month={{ $payoutMonth }}&payout_year={{ $payoutYear }}" 
                       class="btn btn-success ms-2">
                        <i class="fas fa-download me-1"></i>Export CSV
                    </a>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <!-- Financial Year Info -->
        @if($fyContext['selectedFinancialYear'])
            <div class="card mb-4">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-alt text-primary me-2"></i>
                                <span class="font-weight-medium">Financial Year: 
                                    <strong>{{ $fyContext['selectedFinancialYear']->year_name }}</strong>
                                </span>
                                @if(!$fyContext['isFinancialYearEditable'])
                                    <span class="badge bg-warning text-dark ms-2">Historical Data</span>
                                @else
                                    <span class="badge bg-success ms-2">Current Year</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 text-end">
                            <small class="text-muted">
                                {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->start_date)->format('M d, Y') }} - 
                                {{ \Carbon\Carbon::parse($fyContext['selectedFinancialYear']->end_date)->format('M d, Y') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-users fa-2x mb-2"></i>
                        <h4>{{ $salaryBreakdown['summary']['total_employees'] }}</h4>
                        <small>Total Employees</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                        <h4>{{ get_currency_symbol() }}{{ number_format($salaryBreakdown['summary']['total_gross_salary'], 2) }}</h4>
                        <small>Total Gross Salary</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-minus-circle fa-2x mb-2"></i>
                        <h4>{{ get_currency_symbol() }}{{ number_format($salaryBreakdown['summary']['total_deductions'], 2) }}</h4>
                        <small>Total Deductions</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                        <h4>{{ get_currency_symbol() }}{{ number_format($salaryBreakdown['summary']['total_net_salary'], 2) }}</h4>
                        <small>Total Net Salary</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Summary Row -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-left-primary">
                    <div class="card-body text-center">
                        <i class="fas fa-clock text-primary fa-lg mb-2"></i>
                        <h5>{{ number_format($salaryBreakdown['summary']['total_overtime'], 2) }} hrs</h5>
                        <small class="text-muted">Total Overtime</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-success">
                    <div class="card-body text-center">
                        <i class="fas fa-award text-success fa-lg mb-2"></i>
                        <h5>{{ get_currency_symbol() }}{{ number_format($salaryBreakdown['summary']['total_incentives'], 2) }}</h5>
                        <small class="text-muted">Total Incentives</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-info">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-check text-info fa-lg mb-2"></i>
                        <h5>{{ number_format($salaryBreakdown['summary']['total_present_days'], 0) }}</h5>
                        <small class="text-muted">Total Present Days</small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-left-warning">
                    <div class="card-body text-center">
                        <i class="fas fa-calendar-times text-warning fa-lg mb-2"></i>
                        <h5>{{ number_format($salaryBreakdown['summary']['total_leaves'], 0) }}</h5>
                        <small class="text-muted">Total Leave Days</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Details Table -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Employee Salary Details
                </h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered" id="salary-breakdown-table">
                        <thead class="thead-dark">
                            <tr>
                                <th>Employee</th>
                                <th>Department</th>
                                <th>Basic Salary</th>
                                <th>Allowances</th>
                                <th>Gross Salary</th>
                                <th>Deductions</th>
                                <th>Net Salary</th>
                                <th>Overtime</th>
                                <th>Incentives</th>
                                <th>Attendance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salaryBreakdown['employees'] as $employee)
                                <tr>
                                    <td>
                                        <div>
                                            <strong>{{ $employee->employee_code }}</strong><br>
                                            <small>{{ $employee->first_name }} {{ $employee->last_name }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $employee->department_name ?? 'N/A' }}</strong><br>
                                            <small>{{ $employee->designation_name ?? 'N/A' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ get_currency_symbol() }}{{ number_format($employee->basic_salary, 2) }}</td>
                                    <td>
                                        <small>
                                            HRA: {{ get_currency_symbol() }}{{ number_format($employee->hra_amount ?? 0, 2) }}<br>
                                            Transport: {{ get_currency_symbol() }}{{ number_format($employee->transport_allowance ?? 0, 2) }}<br>
                                            Other: {{ get_currency_symbol() }}{{ number_format($employee->other_allowance ?? 0, 2) }}
                                        </small>
                                    </td>
                                    <td><strong>{{ get_currency_symbol() }}{{ number_format($employee->gross_salary, 2) }}</strong></td>
                                    <td>
                                        <small>
                                            PF: {{ get_currency_symbol() }}{{ number_format($employee->pf_deduction ?? 0, 2) }}<br>
                                            ESI: {{ get_currency_symbol() }}{{ number_format($employee->esi_deduction ?? 0, 2) }}<br>
                                            Tax: {{ get_currency_symbol() }}{{ number_format($employee->tax_deduction ?? 0, 2) }}<br>
                                            Other: {{ get_currency_symbol() }}{{ number_format($employee->other_deductions ?? 0, 2) }}
                                        </small>
                                    </td>
                                    <td><strong class="text-success">{{ get_currency_symbol() }}{{ number_format($employee->net_salary, 2) }}</strong></td>
                                    <td>
                                        {{ $employee->overtime_hours ?? 0 }} hrs<br>
                                        <small>{{ get_currency_symbol() }}{{ number_format($employee->overtime_amount ?? 0, 2) }}</small>
                                    </td>
                                    <td>{{ get_currency_symbol() }}{{ number_format($employee->incentive_amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $employee->present_days ?? 0 }} present</span><br>
                                        <span class="badge bg-warning text-dark">{{ $employee->total_leave_days ?? 0 }} leave</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#salary-breakdown-table').DataTable({
        responsive: true,
        pageLength: 25,
        order: [[0, 'asc']],
        dom: 'Bfrtip',
        buttons: [
            'copy', 'csv', 'excel', 'pdf', 'print'
        ],
        columnDefs: [
            {
                targets: [2, 4, 5, 6, 7, 8], // Salary columns
                className: 'text-end'
            }
        ]
    });
});
</script>
@endsection
