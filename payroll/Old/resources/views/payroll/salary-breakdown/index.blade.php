@extends('layouts.master')
@section('title', 'Salary Breakdown')

@section('content')
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Salary Breakdown</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('payroll.index') }}">Payroll</a></li>
                        <li class="breadcrumb-item active">Salary Breakdown</li>
                    </ul>
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
                                <span class="font-weight-medium">Viewing Financial Year: 
                                    <strong>{{ $fyContext['selectedFinancialYear']->year_name }}</strong>
                                </span>
                                @if(!$fyContext['isFinancialYearEditable'])
                                    <span class="badge bg-warning text-dark ms-2">Read-only (Historical Data)</span>
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

        <!-- Finalized Months Card -->
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Select Finalized Payout Month for Salary Breakdown
                </h4>
            </div>
            <div class="card-body">
                @if($finalizedMonths->count() > 0)
                    <div class="row">
                        @foreach($finalizedMonths as $month)
                            @php
                                $monthName = \Carbon\Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->format('F Y');
                                $isCurrentMonth = $month->payout_month == now()->month && $month->payout_year == now()->year;
                            @endphp
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="card border-left-primary h-100">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="card-title mb-0">{{ $monthName }}</h6>
                                            @if($isCurrentMonth)
                                                <span class="badge bg-success badge-sm">Current</span>
                                            @endif
                                        </div>
                                        <p class="card-text text-muted small mb-3">
                                            {{ \Carbon\Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->format('M d') }} - 
                                            {{ \Carbon\Carbon::createFromDate($month->payout_year, $month->payout_month, 1)->endOfMonth()->format('M d, Y') }}
                                        </p>
                                        <button type="button" 
                                                class="btn btn-primary btn-sm btn-block view-breakdown-btn" 
                                                data-month="{{ $month->payout_month }}" 
                                                data-year="{{ $month->payout_year }}">
                                            <i class="fas fa-eye me-1"></i>View Salary Breakdown
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No Finalized Payouts Found</h5>
                        <p class="text-muted">No finalized salary payouts available for the selected financial year.<br>
                        Please finalize salary payouts first to view breakdown reports.</p>
                        @if($fyContext['isFinancialYearEditable'])
                            <a href="{{ route('payroll') }}" class="btn btn-primary mt-2">
                                <i class="fas fa-plus me-1"></i>Go to Payroll Management
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!-- /Page Content -->
</div>

<!-- Salary Breakdown Modal -->
<div class="modal fade" id="salaryBreakdownModal" tabindex="-1" aria-labelledby="salaryBreakdownModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="salaryBreakdownModalLabel">
                    <i class="fas fa-chart-bar me-2"></i>Salary Breakdown - <span id="breakdown-month-year"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="breakdown-loading" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2">Loading salary breakdown...</p>
                </div>
                <div id="breakdown-content" style="display: none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="export-breakdown-btn" style="display: none;">
                    <i class="fas fa-download me-1"></i>Export to CSV
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    // Handle view breakdown button click
    $('.view-breakdown-btn').on('click', function() {
        const month = $(this).data('month');
        const year = $(this).data('year');
        const monthName = new Date(year, month - 1).toLocaleString('default', { month: 'long', year: 'numeric' });
        
        // Reset modal
        $('#breakdown-month-year').text(monthName);
        $('#breakdown-loading').show();
        $('#breakdown-content').hide();
        $('#export-breakdown-btn').hide();
        
        // Show modal
        $('#salaryBreakdownModal').modal('show');
        
        // Load breakdown data
        $.ajax({
            url: '{{ route("payroll.salary-breakdown.show") }}',
            method: 'POST',
            data: {
                payout_month: month,
                payout_year: year,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    displayBreakdownData(response.data);
                    
                    // Store data for export
                    $('#export-breakdown-btn').data('month', month).data('year', year).show();
                } else {
                    showError(response.message || 'Failed to load salary breakdown');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'An error occurred while loading salary breakdown';
                showError(message);
            },
            complete: function() {
                $('#breakdown-loading').hide();
            }
        });
    });
    
    // Handle export button click
    $('#export-breakdown-btn').on('click', function() {
        const month = $(this).data('month');
        const year = $(this).data('year');
        
        const form = $('<form>', {
            method: 'POST',
            action: '{{ route("payroll.salary-breakdown.export") }}'
        }).append($('<input>', {
            type: 'hidden',
            name: '_token',
            value: '{{ csrf_token() }}'
        })).append($('<input>', {
            type: 'hidden',
            name: 'payout_month',
            value: month
        })).append($('<input>', {
            type: 'hidden',
            name: 'payout_year',
            value: year
        }));
        
        $('body').append(form);
        form.submit();
        form.remove();
    });
    
    function displayBreakdownData(data) {
        let html = `
            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <h4>${data.summary.total_employees}</h4>
                            <small>Total Employees</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-wallet fa-2x mb-2"></i>
                            <h4>${window.globalCurrencySymbol}${numberFormat(data.summary.total_gross_salary)}</h4>
                            <small>Total Gross Salary</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-minus-circle fa-2x mb-2"></i>
                            <h4>${window.globalCurrencySymbol}${numberFormat(data.summary.total_deductions)}</h4>
                            <small>Total Deductions</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body text-center">
                            <i class="fas fa-hand-holding-usd fa-2x mb-2"></i>
                            <h4>${window.globalCurrencySymbol}${numberFormat(data.summary.total_net_salary)}</h4>
                            <small>Total Net Salary</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employee Details Table -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered" id="breakdown-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Basic Salary</th>
                            <th>Gross Salary</th>
                            <th>Deductions</th>
                            <th>Net Salary</th>
                            <th>Overtime</th>
                            <th>Incentives</th>
                            <th>Present Days</th>
                        </tr>
                    </thead>
                    <tbody>
        `;
        
        data.employees.forEach(function(employee) {
            html += `
                <tr>
                    <td>
                        <div>
                            <strong>${employee.employee_code}</strong><br>
                            <small>${employee.first_name} ${employee.last_name}</small>
                        </div>
                    </td>
                    <td>
                        <div>
                            <strong>${employee.department_name || 'N/A'}</strong><br>
                            <small>${employee.designation_name || 'N/A'}</small>
                        </div>
                    </td>
                    <td>${window.globalCurrencySymbol}${numberFormat(employee.basic_salary)}</td>
                    <td>${window.globalCurrencySymbol}${numberFormat(employee.gross_salary)}</td>
                    <td>${window.globalCurrencySymbol}${numberFormat(employee.total_deductions)}</td>
                    <td><strong>${window.globalCurrencySymbol}${numberFormat(employee.net_salary)}</strong></td>
                    <td>
                        ${employee.overtime_hours || 0} hrs<br>
                        <small>${window.globalCurrencySymbol}${numberFormat(employee.overtime_amount || 0)}</small>
                    </td>
                    <td>${window.globalCurrencySymbol}${numberFormat(employee.incentive_amount || 0)}</td>
                    <td>${employee.present_days || 0} days</td>
                </tr>
            `;
        });
        
        html += `
                    </tbody>
                </table>
            </div>
        `;
        
        $('#breakdown-content').html(html).show();
        
        // Initialize DataTable for better UX
        $('#breakdown-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[0, 'asc']],
            dom: 'Bfrtip',
            buttons: []
        });
    }
    
    function numberFormat(num) {
        return parseFloat(num || 0).toLocaleString(window.globalCurrencyLocale, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function showError(message) {
        $('#breakdown-content').html(`
            <div class="alert alert-danger text-center">
                <i class="fas fa-exclamation-triangle fa-2x mb-2"></i>
                <h5>Error Loading Breakdown</h5>
                <p>${message}</p>
            </div>
        `).show();
    }
});
</script>
@endsection
