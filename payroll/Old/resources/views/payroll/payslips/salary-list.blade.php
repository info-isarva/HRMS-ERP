@extends('layouts.master')
@section('title', 'Salary Breakdown - ' . $monthName)

@section('content')
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Modern Header Card -->
        <div class="card border-0 shadow-lg mb-4">
            <div class="card-header bg-gradient-primary text-white py-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <div class="header-icon me-3">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <div>
                            <h2 class="mb-0 font-weight-bold">Salary Breakdown</h2>
                            <p class="mb-0 opacity-75">
                                <i class="fas fa-calendar-alt me-1"></i>{{ $monthName }}
                                <span class="mx-2">•</span>
                                <i class="fas fa-users me-1"></i>{{ $attendances->count() }} Employees
                            </p>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- URL Parameters Display -->
                        <div class="url-params-info me-3">
                            <small class="text-white-50">
                                <i class="fas fa-link me-1"></i>URL Parameters: month={{ $month }}&year={{ $year }}
                            </small>
                        </div>

                        <a href="{{ route('payroll/employee-list') }}"
                           class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-2"></i>Back to Selection
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Bar -->
            <div class="card-body bg-light py-3">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-primary">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-value">{{ $attendances->count() }}</div>
                            <div class="stat-label">Total Employees</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-success">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                            <div class="stat-value">{{ get_currency_symbol() }}{{ number_format($attendances->sum('totalEarnings'), 0) }}</div>
                            <div class="stat-label">Total Earnings</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-danger">
                                <i class="fas fa-minus-circle"></i>
                            </div>
                            <div class="stat-value">{{ get_currency_symbol() }}{{ number_format($attendances->sum('totalDeductions'), 0) }}</div>
                            <div class="stat-label">Total Deductions</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-icon text-info">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-value">{{ get_currency_symbol() }}{{ number_format($attendances->sum('netPay'), 0) }}</div>
                            <div class="stat-label">Total Net Pay</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
           
            <div class="card-body p-0">
                <!-- Search and Filter Bar -->
                <div class="p-3 bg-light border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="fa fa-search text-muted"></i>
                                </span>
                                <input type="text" 
                                       class="form-control border-start-0" 
                                       id="employeeSearch" 
                                       placeholder="Search employees..."
                                       onkeyup="filterEmployees()">
                            </div>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                <i class="fa fa-info-circle me-1"></i>
                                Scroll horizontally to view all columns
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Table Container -->
                <div class="table-container">
                    <div class="table-scroll-wrapper">
                        <table class="table payroll-table mb-0">
                            <thead>
                                <!-- Main Header Row -->
                                <tr class="header-main">
                                    <th class="frozen-column employee-header" rowspan="2">
                                        <div class="header-content">
                                            <i class="fa fa-user me-2"></i>Employee Details
                                        </div>
                                    </th>
                                    <th class=" attendance-header" colspan="2">
                                        <div class="header-content text-center">
                                            <i class="fa fa-calendar-check me-2"></i>Attendance
                                        </div>
                                    </th>
                                    
                                    <!-- Earnings Group -->
                                    <!-- @if($earningComponents->count() > 0)
                                        <th colspan="{{ $earningComponents->count() }}" class="earnings-group">
                                            <div class="header-content text-center">
                                                <i class="fa fa-plus-circle me-2"></i>EARNINGS
                                            </div>
                                        </th>
                                    @endif -->
                                    <th class="gross-pay-header" rowspan="2">
                                        <div class="header-content text-center">Gross Pay</div>
                                    </th>
                                    <!-- <th class="epf-header" rowspan="2">
                                        <div class="header-content text-center">EPF Wages</div>
                                    </th> -->
                                    
                                    <!-- Deductions Group -->
                                    <!-- @if($deductionComponents->count() > 0)
                                        <th colspan="{{ $deductionComponents->count() }}" class="deductions-group">
                                            <div class="header-content text-center">
                                                <i class="fa fa-minus-circle me-2"></i>DEDUCTIONS
                                            </div>
                                        </th>
                                    @endif -->
                                    <th class="total-deductions-header" rowspan="2">
                                        <div class="header-content text-center">Total Deductions</div>
                                    </th>
                                    <th class="net-pay-header" rowspan="2">
                                        <div class="header-content text-center" >Net Pay</div>
                                    </th>
                                    <th class="actions-header" rowspan="2">
                                        <div class="header-content text-center">Actions</div>
                                    </th>
                                </tr>
                                
                                <!-- Sub Header Row -->
                                <tr class="header-sub">
                                    <th class=" sub-header">
                                        <div class="header-content text-center">
                                            <small>Worked Days</small>
                                        </div>
                                    </th>
                                    <th class=" sub-header">
                                        <div class="header-content text-center">
                                            <small>Total Days</small>
                                        </div>
                                    </th>
                                    
                                    <!-- Earning Components -->
                                    <!-- @foreach($earningComponents as $component)
                                        <th class="component-header earnings-component">
                                            <div class="header-content text-center">
                                                <div class="component-name">{{ $component->short_name }}</div>
                                                @if($component->is_percentage)
                                                    <small class="component-rate">({{ $component->percentage_value }}%)</small>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach -->
                                    
                                    
                                    
                                    <!-- Deduction Components -->
                                    <!-- @foreach($deductionComponents as $component)
                                        <th class="component-header deductions-component">
                                            <div class="header-content text-center">
                                                <div class="component-name">{{ $component->short_name }}</div>
                                                @if($component->is_percentage)
                                                    <small class="component-rate">({{ $component->percentage_value }}%)</small>
                                                @endif
                                            </div>
                                        </th>
                                    @endforeach -->
                                    
                                    <!-- <th class="sub-header total-deductions-sub"></th>
                                    <th class="sub-header net-pay-sub"></th>
                                    <th class="sub-header actions-sub"></th> -->
                                </tr>
                            </thead>
                            <tbody>
                               
                                
                                @foreach($attendances as $attendance)
                                <tr class="employee-row" 
                                    data-employee-name="{{ strtolower($attendance->employee->name) }}"
                                    data-attendance-id="{{ $attendance->id }}"
                                    data-date-of-joining  ="{{ $attendance->employee->date_of_joining  }}"
                                    data-employee-department = "{{ $departments[$attendance->employee->department] ?? 'N/A' }}"
                                    data-worked-days = "{{ $attendance->employee_worked_days }}"
                                    data-employee-id="{{ $attendance->employee->id }}"
                                    data-employee-code="{{ $attendance->employee->employee_id }}"
                                    data-employee-designation="{{ $designations[$attendance->employee->designation] ?? 'N/A' }}"
                                    data-employee-avatar="{{ asset($attendance->employee->profile_image ?? 'assets/img/user-icon.webp') }}"
                                    data-pay-peroid = "{{ $monthName  }}"
                                    data-earnings="{{ json_encode($attendance->earnings) }}"
                                    data-deductions="{{ json_encode($attendance->deductions) }}"
                                    data-combained="{{ json_encode($attendance->combainedValues) }}"
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
                                                    <div class="status-indicator"></div>
                                                </div>
                                                <div class="employee-info-text">
                                                    <h6 class="employee-name mb-1">{{ $attendance->employee->name }}</h6>
                                                    @if($isFinalized)
                                                        <span class="badge bg-success">
                                                            <i class="fa fa-lock me-1"></i> Finalized
                                                        </span>
                                                    @endif
                                                    <div class="employee-meta">
                                                        <span class="badge bg-light text-dark me-2">
                                                            <i class="fa fa-id-card me-1"></i>
                                                            {{ $attendance->employee->employee_id }}
                                                        </span>
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
                                    <td class=" attendance-cell text-center">
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
                                    <td class=" attendance-cell text-center">
                                        <span class="total-days">{{ $attendance->total_working_days }}</span>
                                    </td>
                                    
                                    <!-- Earnings Columns -->
                                    <!-- @foreach($earningComponents as $component)
                                        <td class="earnings-cell text-center">
                                            @if(isset($attendance->earnings[$component->id]['applicable']) && !$attendance->earnings[$component->id]['applicable'])
                                                <span class="not-applicable">N/A</span>
                                            @else
                                                <span class="amount">{{ get_currency_symbol() }}{{ number_format($attendance->earnings[$component->id]['value'], 2) }}</span>
                                            @endif
                                        </td>
                                    @endforeach -->
                                    
                                    <td class="gross-pay-cell text-center">
                                        <span class="amount-highlight gross">{{ get_currency_symbol() }}{{ number_format($attendance->totalEarnings, 2) }}</span>
                                    </td>
                                    
                                    <!-- <td class="epf-cell text-center">
                                        <span class="amount">{{ get_currency_symbol() }}{{ number_format($attendance->epfWage, 2) }}</span>
                                    </td> -->
                                    
                                    <!-- Deductions Columns -->
                                    
                                    <!-- @foreach($deductionComponents as $component)
                                        <td class="deductions-cell text-center">
                                            @if(isset($attendance->deductions[$component->id]['applicable']) && !$attendance->deductions[$component->id]['applicable'])
                                                <span class="not-applicable">N/A</span>
                                            @else
                                                <span class="amount">{{ get_currency_symbol() }}{{ number_format($attendance->deductions[$component->id]['value'], 2) }}</span>
                                            @endif
                                        </td>
                                    @endforeach -->
                                    
                                    <td class="total-deductions-cell text-center">
                                        <span class="amount-highlight deduction">{{ get_currency_symbol() }}{{ number_format($attendance->totalDeductions, 2) }}</span>
                                    </td>
                                    
                                    <td class="net-pay-cell text-center">
                                        <span class="amount-highlight net">{{ get_currency_symbol() }}{{ number_format($attendance->netPay, 2) }}</span>
                                    </td>
                                    
                                    <td class="actions-cell text-center">
                                        <div class="btn-group" role="group">

                                            <button class="btn btn-sm btn-outline-primary" 
                                                    data-bs-toggle="tooltip" 
                                                    title="Pay Slip">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                
                <!-- Footer Actions -->
                <div class="card-footer bg-light py-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <a href="{{ route('payroll/employee-list') }}"
                               class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-arrow-left me-2"></i>Back to Month Selection
                            </a>
                            <div class="ms-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Parameters in URL: <code>?payout_month_year={{ str_pad($month, 2, '0', STR_PAD_LEFT) }}-{{ $year }}</code>
                                </small>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <!-- Export Options -->
                            <div class="dropdown">
                                <!-- <button class="btn btn-outline-primary btn-lg dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fas fa-download me-2"></i>Export Data
                                </button> -->
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payroll.bank-excel', [$month, $year]) }}">
                                            <i class="fas fa-file-excel me-2 text-success"></i>Bank Transfer (Excel)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payroll.bank-csv', [$month, $year]) }}">
                                            <i class="fas fa-file-csv me-2 text-info"></i>Bank Transfer (CSV)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payroll.epf-excel-csv', [$month, $year]) }}?format=1">
                                            <i class="fas fa-file-excel me-2 text-warning"></i>EPF Report (Excel)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payroll.epf-excel-csv', [$month, $year]) }}?format=2">
                                            <i class="fas fa-file-csv me-2 text-warning"></i>EPF Report (CSV)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('payroll.epf-excel-csv', [$month, $year]) }}?format=3">
                                            <i class="fas fa-file-text me-2 text-secondary"></i>EPF Report (Text)
                                        </a>
                                    </li>
                                </ul>
                            </div>

                            @if(!$isFinalized)
                                <button class="btn btn-success btn-lg" id="finalizePayroll">
                                    <i class="fas fa-check-circle me-2"></i>Finalize Payroll
                                </button>
                            @else
                                <button class="btn btn-success btn-lg disabled">
                                    <i class="fas fa-lock me-2"></i>Payroll Finalized
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$isFinalized)
                    <form id="finalizeForm" action="{{ route('payroll.finalize', [$month, $year]) }}" method="POST" class="d-none">
                        @csrf
                    </form>
                @endif
            </div>
        </div>

    </div>
</div>
<!-- Model section -->
<div class="modal fade" id="adjustSalaryModal" tabindex="-1" aria-labelledby="adjustSalaryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-primary text-white">
                <h5 class="modal-title" id="adjustSalaryModalLabel">
                    <i class="fa fa-edit me-2"></i>
                    Payslip
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                
                @include('payroll.payslips.payslip-form')
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button  type="button" class="btn btn-primary" id="pdfgenerate">
                    <i class="fa fa-save me-2"></i> Download PDF
                </button>
                
            </div>
        </div>
    </div>
</div>

<style>
    /* Table Container Styles */
.table-container {
    position: relative;
    overflow: hidden;
    border-radius: 0 0 0.5rem 0.5rem;
}

.table-scroll-wrapper {
    overflow-x: auto;
    overflow-y: auto;
    max-height: 70vh;
    position: relative;
}

/* Custom Scrollbar */
.table-scroll-wrapper::-webkit-scrollbar {
    height: 8px;
    width: 8px;
}

.table-scroll-wrapper::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.table-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Table Styles */
.payroll-table {
    min-width: 1200px;
    margin: 0;
    background: white;
}

/* Frozen Column Styles */
.frozen-column {
    position: sticky;
    left: 0;
    z-index: 10;
    background: white !important; 
    
    box-shadow: 2px 0 5px -2px rgba(0,0,0,0.12);
}

.frozen-column.employee-header {
    min-width: 280px;
    max-width: 280px;
    left: 0;
    z-index: 12;
    background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    align-content: center;
}

.frozen-column.attendance-header {
    min-width: 90px;
    max-width: 90px;
    left: 280px;
    z-index: 11;
    background:  linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.frozen-column.employee-info {
    min-width: 280px;
    max-width: 280px;
    left: 0;
    z-index: 10;
    border-right: 2px solid #e9ecef;
}

.frozen-column.attendance-cell {
    min-width: 90px;
    max-width: 90px;
    left: 280px;
    z-index: 9;
    border-right: 1px solid #e9ecef;
}

/* Header Styles */
/* .header-main th {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    padding: 15px 12px;
    border: none;
    position: sticky;
    top: 0;
    z-index: 8;
}

.header-sub th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 500;
    font-size: 0.8rem;
    padding: 12px 10px;
    border-bottom: 2px solid #dee2e6;
    position: sticky;
    top: 60px;
    z-index: 8;
} */

/* Frozen header z-index adjustments */
/* .header-main .frozen-column {
    z-index: 15;
}

.header-sub .frozen-column {
    z-index: 14;
} */

/* Header Content */
/* .header-content {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    white-space: nowrap;
} */

/* Group Headers */
.earnings-group {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
}

.deductions-group {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
}

.gross-pay-header {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
    align-content: center;
}
.total-deductions-header, .net-pay-header, .actions-header {
    align-content: center;
}

.epf-header{
    background: linear-gradient(135deg, #5adbc3 0%, #1e8187 100%) !important;
    align-content: center;
}

.net-pay-header {
    background: linear-gradient(135deg, #3fcce3 0%, #0da2bb 100%) !important;
}

/* Component Headers */
.component-header {
    min-width: 120px;
    background: #f8f9fa !important;
}

.component-name {
    font-weight: 600;
    margin-bottom: 2px;
}

.component-rate {
    color: #6c757d;
    font-size: 0.7rem;
}

/* Employee Row Styles */
.employee-row {
    transition: all 0.2s ease;
}

.employee-row:hover {
    background-color: #f8f9fa !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

/* Employee Details */
.employee-details {
    padding: 8px;
}

.employee-avatar {
    position: relative;
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

.employee-name {
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.employee-meta {
    margin-top: 4px;
}

/* Attendance Styles */
.attendance-info {
    padding: 8px 4px;
}

.attendance-days {
    font-weight: 600;
    color: #495057;
    font-size: 1.1rem;
}

.attendance-progress {
    margin-top: 4px;
}

.total-days {
    font-weight: 500;
    color: #6c757d;
}

/* Amount Styles */
.amount {
    font-weight: 500;
    color: #495057;
    font-family: 'Courier New', monospace;
}

.amount-highlight {
    font-weight: 600;
    font-size: 1.05rem;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
}

.amount-highlight.gross {
    color: #155724;
    background: rgba(40, 167, 69, 0.1);
}

.amount-highlight.deduction {
    color: #721c24;
    background: rgba(220, 53, 69, 0.1);
}

.amount-highlight.net {
    color: #1b1e21;
    background: rgba(52, 58, 64, 0.1);
}

.not-applicable {
    color: #6c757d;
    font-style: italic;
    font-size: 0.9rem;
}

/* Cell Styles */
.payroll-table td {
    padding: 12px 10px;
    vertical-align: middle;
    border-top: 1px solid #e9ecef;
    white-space: nowrap;
    min-width: 100px;
}

.earnings-cell, .deductions-cell {
    background: rgba(255, 255, 255, 0.95);
}

.gross-pay-cell {
    background: rgba(40, 167, 69, 0.05);
}

.total-deductions-cell {
    background: rgba(220, 53, 69, 0.05);
}

.net-pay-cell {
    background: rgba(52, 58, 64, 0.05);
}

/* Actions */
.actions-cell {
    min-width: 120px;
}

/* Background Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .frozen-column.employee-header,
    .frozen-column.employee-info {
        min-width: 220px;
        max-width: 220px;
    }
    
    .frozen-column.attendance-header,
    .frozen-column.attendance-cell {
        left: 220px;
    }
    
    .card-header h4 {
        font-size: 1.1rem;
    }
    
    .employee-name {
        font-size: 0.9rem;
    }
}

/* Loading State */
.table-loading {
    position: relative;
}

.table-loading::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Print Styles */
@media print {
    .frozen-column {
        position: static !important;
        box-shadow: none !important;
    }
    
    .table-scroll-wrapper {
        overflow: visible !important;
        max-height: none !important;
    }
    
    .card-header .btn-group,
    .actions-cell {
        display: none !important;
    }
}
    .modern-table {
        max-height: 70vh;
        overflow: auto;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }
    
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .sticky-column {
        position: sticky;
        left: 0;
        z-index: 50;
        background: white;
        box-shadow: 2px 0 4px -2px rgba(0,0,0,0.1);
    }
    
    .bg-info-light { background-color: #d1ecf1; }
    .bg-success-light { background-color: #d4edda; }
    .bg-warning-light { background-color: #fff3cd; }
    .bg-danger-light { background-color: #f8d7da; }
    .bg-dark-light { background-color: #d6d8d9; }
    
    thead tr:first-child th {
        border-bottom: 2px solid #dee2e6 !important;
    }
    
    tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    .table > :not(:first-child) {
        border-top: none;
    }
</style>
<script>
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

    document.querySelectorAll('.btn-outline-primary').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const attendanceId = this.closest('tr').dataset.attendanceId;
                const employeeId = this.closest('tr').dataset.employeeId;
                openAdjustSalaryModal(attendanceId, employeeId);
        });
    });

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
            date_of_joining: row.dataset.dateOfJoining,
            designation: row.dataset.employeeDesignation,
            department: row.dataset.employeeDepartment,
            worked_days: row.dataset.workedDays,
            avatar: row.dataset.employeeAvatar,
            attendance_id: attendanceId,
            pay_peroid: row.dataset.payPeroid,
            earnings : JSON.parse(row.dataset.earnings), 
            deductions : JSON.parse(row.dataset.deductions),
            combained: row.dataset.combained,
            // deductions: JSON.parse(row.dataset.deductions),
            gross_pay: parseFloat(row.dataset.grossPay),
            total_deductions: parseFloat(row.dataset.totalDeductions),
            net_pay: parseFloat(row.dataset.netPay),
            epf_wage: parseFloat(row.dataset.epfWage)
        };
        console.log(employeeData);
        // Set global variables
        currentEmployeeData = employeeData;
        currentAttendanceId = attendanceId;
        
        // Update modal header

        document.getElementById('empName').textContent = employeeData.name.toUpperCase();
        document.getElementById('doj').textContent = employeeData.date_of_joining;
        document.getElementById('designation').textContent = employeeData.designation;
        document.getElementById('department').textContent = employeeData.department;
        document.getElementById('workedDays').textContent = employeeData.worked_days;
        document.getElementById('payPeroid').textContent = employeeData.pay_peroid;
        
        // // Update summary cards
        // document.getElementById('modalGrossPay').textContent = '{{ get_currency_symbol() }}' + employeeData.gross_pay.toFixed(2);
        // document.getElementById('modalTotalDeductions').textContent = '{{ get_currency_symbol() }}' + employeeData.total_deductions.toFixed(2);
        document.getElementById('modalNetPay').textContent = window.globalCurrencySymbol + '  ' + numberFormat(employeeData.net_pay, 2);
        document.getElementById('wordNetPay').textContent =  numberToWords(employeeData.net_pay);
        
        
        // // Render earnings components
        renderComponents('earningsContainer', employeeData.earnings,employeeData.deductions, numberFormat(employeeData.gross_pay, 2), numberFormat(employeeData.total_deductions, 2) ,'Earnings');
        
        // // Render deductions components
        // renderComponents('deductionsContainer', employeeData.deductions, numberFormat(employeeData.total_deductions, 2), 'Deduction');
        
        // // Show modal


        document.getElementById('pdfgenerate').addEventListener('click', function () {
            if (!currentEmployeeData) return;

            let payslipId = currentEmployeeData.id; // Replace this with your dynamic JS variable
            let month = {{ $month }}
            let year = {{ $year }}
            let url = `/payroll/payslip-pdf/${payslipId}/${month}/${year}`;
            window.open(url, '_blank'); // ← Open in new tab  
        });
        adjustSalaryModal.show();
        
    }


    function renderComponents(containerId, components,deductions, totalEarning, totalDeduction, type) {
        const container = document.getElementById(containerId);
        container.innerHTML = '';
        
        // Collect all applicable earnings and deductions separately
        const applicableEarnings = [];
        const applicableDeductions = [];
        
        Object.entries(components).forEach(([id, component]) => {
            if(component.applicable && component.name && component.name.trim() !== '') {
                applicableEarnings.push({
                    name: component.name,
                    value: numberFormat(component.value, 0)
                });
            }
        });
        
        Object.entries(deductions).forEach(([id, deduction]) => {
            if(deduction.applicable && deduction.name && deduction.name.trim() !== '') {
                applicableDeductions.push({
                    name: deduction.name,
                    value: deduction.value
                });
            }
        });
        
        // Determine the maximum number of rows needed
        const maxRows = Math.max(applicableEarnings.length, applicableDeductions.length);
        
        // Create rows only if there are components to show
        for(let i = 0; i < maxRows; i++) {
            const componentDiv = document.createElement('tr');
            
            const earning = applicableEarnings[i] || { name: '', value: '' };
            const deduction = applicableDeductions[i] || { name: '', value: '' };
            
            componentDiv.innerHTML = `
                <td>${earning.name}</td>
                <td>${earning.value}</td>
                <td>${deduction.name}</td>
                <td>${deduction.value}</td>
            `;
            
            container.appendChild(componentDiv);
        }
        
        // Add total row only if there are any components
        if(maxRows > 0) {
            const componentDiv1 = document.createElement('tr');
            componentDiv1.innerHTML = `
                <td><strong>Total Earnings</strong></td>
                <td><strong>${'{{ get_currency_symbol() }} ' + totalEarning}</strong></td>
                <td><strong>Total Deductions</strong></td>
                <td><strong>${'{{ get_currency_symbol() }} ' + totalDeduction}</strong></td>
            `;
            container.appendChild(componentDiv1);
        }
    }

    function numberFormat(number, decimals = 0, decPoint = '.', thousandsSep = ',') {
    const fixed = number.toFixed(decimals);
    const parts = fixed.split('.');
    
    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandsSep);
    
    return parts.join(decPoint);
    }


    function numberToWords(num) {
  const a = [
    '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six',
    'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve',
    'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
    'Seventeen', 'Eighteen', 'Nineteen'
  ];
  const b = [
    '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
    'Sixty', 'Seventy', 'Eighty', 'Ninety'
  ];

  function inWords(n) {
    if (n < 20) return a[n];
    if (n < 100) return b[Math.floor(n / 10)] + (n % 10 ? ' ' + a[n % 10] : '');
    if (n < 1000) return a[Math.floor(n / 100)] + ' Hundred' + (n % 100 ? ' ' + inWords(n % 100) : '');
    if (n < 1000000) return inWords(Math.floor(n / 1000)) + ' Thousand' + (n % 1000 ? ' ' + inWords(n % 1000) : '');
    return 'Number too large';
  }

  num = Number(num.toString().replace(/,/g, '')); // Remove commas
  const wholePart = Math.floor(num);
  const decimalPart = Math.round((num - wholePart) * 100);

  let result = inWords(wholePart);
  if (decimalPart > 0) {
    result += ' and ' + inWords(decimalPart) + ' ' + (window.globalCurrencySubunit || 'Paise');
  }
  return result.trim() + ' ' + (window.globalCurrencyName || 'Rupees') + ' Only';
}

// Save overrides button handler
// document.getElementById('saveOverridesBtn').addEventListener('click', function() {
//      // Show loading state
//      const saveBtn = document.getElementById('saveOverridesBtn');
//         saveBtn.disabled = true;
//         saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i> Saving...';
        
//         // Send each override to the server
//         const savePromises = overrides.map(override => {
//             return fetch('{{ route("payroll.save-component-override") }}', {
//                 method: 'POST',
//                 headers: {
//                     'Content-Type': 'application/json',
//                     'X-CSRF-TOKEN': '{{ csrf_token() }}'
//                 },
//                 body: JSON.stringify({
//                     attendance_id: currentAttendanceId,
//                     employee_id: currentEmployeeData.id,
//                     component_id: override.component_id,
//                     component_type: override.component_type,
//                     override_value: override.override_value,
//                     default_value: override.default_value
//                 })
//             }).then(response => response.json());
//         });
// });

document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('adjustSalaryModal');

        modal.addEventListener('hidden.bs.modal', function () {
            // Clear all text content
            modal.querySelectorAll('[id]').forEach(el => {
                if (['empName', 'doj', 'designation', 'department', 'workedDays', 'payPeroid', 'modalNetPay', 'wordNetPay'].includes(el.id)) {
                    el.textContent = '';
                }
            });

            // Clear dynamic containers
            const earningsContainer = document.getElementById('earningsContainer');
            if (earningsContainer) earningsContainer.innerHTML = '';

            // Optionally clear deductionsContainer if used
            // const deductionsContainer = document.getElementById('deductionsContainer');
            // if (deductionsContainer) deductionsContainer.innerHTML = '';

            // Remove old click listeners (avoid duplicates)
            const newBtn = document.getElementById('pdfgenerate').cloneNode(true);
            document.getElementById('pdfgenerate').replaceWith(newBtn);
        });
    });

</script>

<style>
/* Stat Cards */
.stat-card {
    background: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-icon {
    font-size: 1.5rem;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 1.2rem;
    font-weight: bold;
    color: #2c3e50;
}

.stat-label {
    font-size: 0.85rem;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* URL Parameters Info */
.url-params-info {
    background: rgba(255,255,255,0.1);
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid rgba(255,255,255,0.2);
}

.url-params-info code {
    background: rgba(255,255,255,0.2);
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 0.8rem;
}

/* Enhanced Button Styles */
.btn-lg {
    padding: 12px 24px;
    font-size: 1rem;
    border-radius: 8px;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    border-radius: 8px;
}

.dropdown-item {
    padding: 10px 20px;
    transition: background-color 0.2s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Header Icon */
.header-icon {
    background: rgba(255,255,255,0.2);
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

@endsection