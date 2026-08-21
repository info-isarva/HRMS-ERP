@extends('layouts.master')

@section('content')
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

    /* Stats Cards */
    .stats-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        text-align: center;
    }

    .stats-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .stats-card .stats-icon {
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin: 0 auto 1rem;
        font-size: 1.25rem;
    }

    .stats-card .stats-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }

    .stats-card .stats-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 500;
    }

    /* Modern Filter Card */
    .filter-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        overflow: visible;
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
    }

    .filter-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .filter-card .card-header h4 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
    }

    .filter-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .filter-card .card-body {
        padding: 2rem;
    }

    /* Form Styling */
    .filter-card .form-group {
        margin-bottom: 1.5rem;
    }

    .filter-card .form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .filter-card .form-control {
        border: 2px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.875rem;
        transition: all 0.2s ease;
        background: white;
    }

    .filter-card .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .filter-card .form-control option {
        background: white;
        color: #374151;
        padding: 0.5rem;
    }

    .filter-card .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 500;
        color: white;
        transition: all 0.2s ease;
        font-size: 0.875rem;
    }

    .filter-card .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%);
    }

    /* Chart Cards */
    .chart-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .chart-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .chart-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .chart-card .card-header h4 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        color: white !important;
    }

    .chart-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    .chart-card .card-body {
        padding: 2rem;
    }

    /* DataTable Styling */
    .table-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        margin-bottom: 2rem;
        overflow: hidden;
    }

    .table-card .card-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 1rem 1rem 0 0 !important;
        padding: 1.5rem;
    }

    .table-card .card-header h4 {
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        color: white !important;
    }

    .table-card .card-header i {
        margin-right: 0.5rem;
        opacity: 0.9;
    }

    /* Empty State */
    .empty-state-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        border: 1px solid #e5e7eb;
        padding: 3rem;
        text-align: center;
    }

    .empty-state-card .empty-icon {
        width: 4rem;
        height: 4rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
    }

    .empty-state-card h4 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1rem;
    }

    .empty-state-card p {
        font-size: 1rem;
        color: #6b7280;
        margin-bottom: 1rem;
    }

    .empty-state-card small {
        color: #9ca3af;
    }

    /* DataTables Custom Styling */
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter,
    .dataTables_wrapper .dataTables_info,
    .dataTables_wrapper .dataTables_paginate {
        color: #495057;
        font-weight: 500;
    }

    .dataTables_wrapper .dataTables_filter input {
        border: 2px solid #e9ecef;
        border-radius: 8px;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
    }

    .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border: 2px solid #e9ecef;
        border-radius: 6px;
        margin: 0 2px;
        color: #495057 !important;
        background: white !important;
        transition: all 0.3s ease;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #667eea !important;
        border-color: #667eea;
        color: white !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: #667eea !important;
        border-color: #667eea;
        color: white !important;
        font-weight: 600;
    }

    .table thead th {
        border-bottom: 3px solid #667eea;
        font-weight: 600;
        color: #495057;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: rgba(102, 126, 234, 0.05);
    }

    .badge-lg {
        font-size: 0.8rem;
        padding: 0.5rem 0.75rem;
    }
</style>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <!-- Page Content -->
    <div class="content container-fluid">
        <!-- Modern Page Header -->
        <div class="page-header-card">
            <div class="page-header-gradient">
                <div class="page-header-pattern"></div>
                <div class="page-header-circle-1"></div>
                <div class="page-header-circle-2"></div>
                <div class="d-flex align-items-center">
                    <div class="page-header-icon-box">
                        <i class="fa fa-chart-bar fa-lg"></i>
                    </div>
                    <div class="ms-3">
                        <h1 class="page-header-title">Payroll Analytics</h1>
                        <p class="page-header-subtitle">Comprehensive payroll insights and visualizations</p>
                    </div>
                </div>
            </div>
            <div class="p-3 d-flex justify-content-between align-items-center">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('home') }}">
                                <i class="fa fa-home me-1"></i>Dashboard
                            </a>
                        </li>
                        <li class="breadcrumb-item">
                            <i class="fa fa-file-alt me-1"></i>Reports
                        </li>
                        <li class="breadcrumb-item active">
                            <i class="fa fa-chart-bar me-1"></i>Payroll Analytics
                        </li>
                    </ol>
                </nav>
                <div class="header-stats text-end">
                    <small class="text-muted">Analytics Dashboard</small>
                    <div class="mt-1">
                        <i class="fa fa-calendar-alt me-2 text-primary"></i>
                        <span class="font-weight-bold">{{ date('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Modern Page Header -->

        <!-- Modern Filter Section -->
        <div class="filter-card">
            <div class="card-header">
                <h4><i class="fa fa-filter me-2"></i>Select Payroll Period</h4>
            </div>
            <div class="card-body">
                <form method="GET" class="row align-items-end justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fa fa-calendar me-1"></i>Payroll Month & Year
                            </label>
                            <select name="payout_month_year" id="payout_month_year" class="form-control form-select" required>
                                <option value="">Choose Month & Year</option>
                                @foreach($months as $m)
                                    <option value="{{$m['key']}}" {{ $selected==$m['key'] ? 'selected' : '' }}>
                                        {{$m['label']}}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-search me-2"></i>Load Analytics
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <!-- /Modern Filter Section -->

        @if($analytics)
        <!-- Enhanced Summary Cards -->
        <div class="row">
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-users"></i>
                    </div>
                    <div class="stats-value">{{ $analytics['employee_count'] }}</div>
                    <div class="stats-label">Total Employees</div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">{{ get_currency_symbol() }}{{ number_format($analytics['gross_total']/100000,1) }}L</div>
                    <div class="stats-label">Gross Total</div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-minus-circle"></i>
                    </div>
                    <div class="stats-value">{{ get_currency_symbol() }}{{ number_format($analytics['deduction_total']/100000,1) }}L</div>
                    <div class="stats-label">Total Deductions</div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-hand-holding-usd"></i>
                    </div>
                    <div class="stats-value">{{ get_currency_symbol() }}{{ number_format($analytics['net_total']/100000,1) }}L</div>
                    <div class="stats-label">Net Payable</div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-calculator"></i>
                    </div>
                    <div class="stats-value">{{ get_currency_symbol() }}{{ number_format($analytics['avg_gross']) }}</div>
                    <div class="stats-label">Avg Gross</div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fa fa-chart-line"></i>
                    </div>
                    <div class="stats-value">{{ get_currency_symbol() }}{{ number_format($analytics['avg_net']) }}</div>
                    <div class="stats-label">Avg Net</div>
                </div>
            </div>
        </div>
        <!-- /Enhanced Summary Cards -->

        <!-- Enhanced Charts Section -->
        <div class="row">
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-chart-bar me-2"></i>Earnings Distribution
                        </h4>
                    </div>
                    <div class="card-body">
                        <canvas id="earningsBarChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="chart-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-chart-bar me-2"></i>Deductions Distribution
                        </h4>
                    </div>
                    <div class="card-body">
                        <canvas id="deductionsBarChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Charts Row -->
        <div class="row">
            <div class="col-lg-4">
                <div class="chart-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-chart-pie me-2"></i>Earnings vs Deductions
                        </h4>
                    </div>
                    <div class="card-body">
                        <canvas id="earningsDeductionsPie" height="150"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="table-card">
                    <div class="card-header">
                        <h4 class="card-title">
                            <i class="fa fa-table me-2"></i>Component Breakdown
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="componentTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th><i class="fa fa-tag me-1"></i>Component</th>
                                        <th><i class="fa fa-cogs me-1"></i>Type</th>
                                        <th class="text-end"><i class="fa fa-money-bill-wave me-1"></i>Amount</th>
                                        <th class="text-end"><i class="fa fa-percentage me-1"></i>% of Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalEarn = array_sum(array_column($analytics['earnings_components'],'earnings'));
                                        $totalDed = array_sum(array_column($analytics['deductions_components'],'deductions'));
                                    @endphp

                                    <!-- Earnings Components -->
                                    @foreach($analytics['earnings_components'] as $id => $row)
                                        @php
                                            $amount = $row['earnings'] ?? 0;
                                            $pct = $totalEarn > 0 ? ($amount/$totalEarn*100) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong class="text-success">
                                                    <i class="fa fa-plus-circle me-1"></i>{{ $row['name'] ?? 'C'.$id }}
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-success badge-lg">
                                                    <i class="fa fa-arrow-up me-1"></i>Earning
                                                </span>
                                            </td>
                                            <td class="text-end text-success font-weight-bold">
                                                {{ get_currency_symbol() }}{{ number_format($amount,2) }}
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-light text-dark">{{ number_format($pct,2) }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- Deductions Components -->
                                    @foreach($analytics['deductions_components'] as $id => $row)
                                        @php
                                            $amount = $row['deductions'] ?? 0;
                                            $pct = $totalDed > 0 ? ($amount/$totalDed*100) : 0;
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong class="text-danger">
                                                    <i class="fa fa-minus-circle me-1"></i>{{ $row['name'] ?? 'C'.$id }}
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger badge-lg">
                                                    <i class="fa fa-arrow-down me-1"></i>Deduction
                                                </span>
                                            </td>
                                            <td class="text-end text-danger font-weight-bold">
                                                {{ get_currency_symbol() }}{{ number_format($amount,2) }}
                                            </td>
                                            <td class="text-end">
                                                <span class="badge bg-light text-dark">{{ number_format($pct,2) }}%</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Enhanced Charts Section -->
        @else
        <!-- Modern Empty State -->
        <div class="empty-state-card">
            <div class="empty-icon">
                <i class="fa fa-chart-bar"></i>
            </div>
            <h4>No Analytics Data Available</h4>
            <p>Select a finalized payroll month above to view comprehensive analytics, charts, and insights for your organization.</p>
            <div class="mt-3">
                <small>Data will be available once payroll processing is completed for the selected period.</small>
            </div>
        </div>
        <!-- /Modern Empty State -->
        @endif

    </div>
</div>
<!-- /Page Wrapper -->
@endsection

@section('script')
@if($analytics)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize DataTable for component breakdown
    if ($.fn.DataTable) {
        $('#componentTable').DataTable({
            "pageLength": 10,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
            "ordering": true,
            "searching": true,
            "paging": true,
            "info": true,
            "responsive": true,
            "language": {
                "search": "<i class='fa fa-search'></i> Search components:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ components",
                "infoEmpty": "No components found",
                "infoFiltered": "(filtered from _MAX_ total components)",
                "zeroRecords": "No matching components found",
                "paginate": {
                    "first": "<i class='fa fa-angle-double-left'></i>",
                    "last": "<i class='fa fa-angle-double-right'></i>",
                    "next": "<i class='fa fa-angle-right'></i>",
                    "previous": "<i class='fa fa-angle-left'></i>"
                }
            },
            "columnDefs": [
                { "orderable": false, "targets": [1] },
                { "className": "text-end", "targets": [2, 3] }
            ],
            "initComplete": function() {
                // Add custom styling to DataTable elements
                $('.dataTables_length select').addClass('form-control form-control-sm');
                $('.dataTables_filter input').addClass('form-control form-control-sm');
            }
        });
    }

    // Earnings and Deductions data preparation
    const earningsData = @json($analytics['earnings_components']);
    const deductionsData = @json($analytics['deductions_components']);

    // Earnings Bar Chart
    const earningsNames = Object.values(earningsData).map(item => item.name || 'Unknown');
    const earningsAmounts = Object.values(earningsData).map(item => item.earnings || 0);
    
    const earningsBarCtx = document.getElementById('earningsBarChart').getContext('2d');
    new Chart(earningsBarCtx, {
        type: 'bar',
        data: {
            labels: earningsNames,
            datasets: [{
                label: 'Earnings',
                data: earningsAmounts,
                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '{{ get_currency_symbol() }}' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Deductions Bar Chart
    const deductionsNames = Object.values(deductionsData).map(item => item.name || 'Unknown');
    const deductionsAmounts = Object.values(deductionsData).map(item => item.deductions || 0);
    
    const deductionsBarCtx = document.getElementById('deductionsBarChart').getContext('2d');
    new Chart(deductionsBarCtx, {
        type: 'bar',
        data: {
            labels: deductionsNames,
            datasets: [{
                label: 'Deductions',
                data: deductionsAmounts,
                backgroundColor: 'rgba(255, 99, 132, 0.8)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '{{ get_currency_symbol() }}' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Pie Chart - Earnings vs Deductions
    const pieCtx = document.getElementById('earningsDeductionsPie').getContext('2d');
    new Chart(pieCtx, {
        type: 'doughnut',
        data: {
            labels: ['Total Earnings', 'Total Deductions'],
            datasets: [{
                data: [{{ $analytics['gross_total'] }}, {{ $analytics['deduction_total'] }}],
                backgroundColor: [
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 99, 132, 0.8)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const value = context.parsed;
                            const total = {{ $analytics['gross_total'] + $analytics['deduction_total'] }};
                            const percentage = ((value / total) * 100).toFixed(1);
                            return context.label + ': {{ get_currency_symbol() }}' + value.toLocaleString() + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endif
@endsection