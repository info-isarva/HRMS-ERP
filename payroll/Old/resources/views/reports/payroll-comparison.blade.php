@extends('layouts.master')

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
                                <i class="fa fa-balance-scale fa-lg"></i>
                            </div>
                            <div class="ms-3">
                                <h1 class="page-header-title">Payroll Comparison</h1>
                                <p class="page-header-subtitle">Compare payroll data between different months</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 d-flex justify-content-between align-items-center">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                                <li class="breadcrumb-item"><a href="#">Reports</a></li>
                                <li class="breadcrumb-item active">Payroll Comparison</li>
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

            /* Metric Cards Styling */
            .metric-card {
                background: white;
                border-radius: 1rem;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
                border: 1px solid #e5e7eb;
                margin-bottom: 1.5rem;
                transition: all 0.3s ease;
            }

            .metric-card:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
            }

            .metric-card .card-body {
                padding: 2rem;
                text-align: center;
            }

            .dash-widget-icon {
                width: 3rem;
                height: 3rem;
                background: rgba(102, 126, 234, 0.1);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 1rem;
                font-size: 1.25rem;
            }

            .dash-widget-icon.text-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; }
            .dash-widget-icon.text-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
            .dash-widget-icon.text-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; }
            .dash-widget-icon.text-info { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }

            .dash-count h3 {
                font-size: 1.75rem;
                font-weight: 700;
                margin-bottom: 0.5rem;
            }

            .dash-title {
                color: #6b7280;
                font-size: 0.875rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            /* Table Styling */
            .settings-card .table {
                background: white;
                margin-bottom: 0;
            }

            .settings-card .table thead th {
                background: #f8f9fa;
                border-bottom: 2px solid #e5e7eb;
                color: #374151;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                font-size: 0.75rem;
                padding: 1rem 0.75rem;
            }

            .settings-card .table tbody td {
                padding: 0.75rem;
                border-bottom: 1px solid #e5e7eb;
                color: #374151;
            }

            .settings-card .table tbody tr:hover {
                background: #f8f9fa;
            }
        </style>

        <!-- Search Filter -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fa fa-filter"></i>Filter Options</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Base Month</label>
                                    <select name="base" class="form-control form-select" required>
                                        <option value="">Select Base Month</option>
                                        @foreach($months as $m)
                                            <option value="{{$m['key']}}" {{ $base==$m['key'] ? 'selected' : '' }}>{{$m['label']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Compare With</label>
                                    <select name="next" class="form-control form-select" required>
                                        <option value="">Select Comparison Month</option>
                                        @foreach($months as $m)
                                            <option value="{{$m['key']}}" {{ $next==$m['key'] ? 'selected' : '' }}>{{$m['label']}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fa fa-balance-scale"></i> Compare
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Search Filter -->

        @if($comparison)
        <!-- Summary Changes -->
        <div class="row">
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon {{ $comparison['gross_diff']>=0 ? 'text-success':'text-danger' }}">
                                <i class="fa {{ $comparison['gross_diff']>=0 ? 'fa-arrow-up':'fa-arrow-down' }}"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="{{ $comparison['gross_diff']>=0 ? 'text-success':'text-danger' }}">
                                    {{ get_currency_symbol() }}{{ number_format(abs($comparison['gross_diff'])) }}
                                </h3>
                            </div>
                            <div class="dash-title">Gross Pay Change</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon {{ $comparison['net_diff']>=0 ? 'text-success':'text-danger' }}">
                                <i class="fa {{ $comparison['net_diff']>=0 ? 'fa-arrow-up':'fa-arrow-down' }}"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="{{ $comparison['net_diff']>=0 ? 'text-success':'text-danger' }}">
                                    {{ get_currency_symbol() }}{{ number_format(abs($comparison['net_diff'])) }}
                                </h3>
                            </div>
                            <div class="dash-title">Net Pay Change</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon {{ $comparison['deduction_diff']<=0 ? 'text-success':'text-warning' }}">
                                <i class="fa {{ $comparison['deduction_diff']<=0 ? 'fa-arrow-down':'fa-arrow-up' }}"></i>
                            </span>
                            <div class="dash-count">
                                <h3 class="{{ $comparison['deduction_diff']<=0 ? 'text-success':'text-warning' }}">
                                    {{ get_currency_symbol() }}{{ number_format(abs($comparison['deduction_diff'])) }}
                                </h3>
                            </div>
                            <div class="dash-title">Deductions Change</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="metric-card">
                    <div class="card-body">
                        <div class="dash-widget-header">
                            <span class="dash-widget-icon text-info">
                                <i class="fa fa-percentage"></i>
                            </span>
                            <div class="dash-count">
                                @php
                                    $grossPercent = $comparison['base']['gross_total'] > 0 ? 
                                        ($comparison['gross_diff'] / $comparison['base']['gross_total'] * 100) : 0;
                                @endphp
                                <h3 class="{{ $grossPercent>=0 ? 'text-success':'text-danger' }}">
                                    {{ number_format(abs($grossPercent), 1) }}%
                                </h3>
                            </div>
                            <div class="dash-title">Growth Rate</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Summary Changes -->

        <!-- Comparison Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="settings-card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-chart-bar"></i>Gross vs Net Comparison</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="grossNetChart" height="120"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="settings-card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-chart-pie"></i>Month-wise Totals</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="monthTotalsChart" height="120"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Comparison Charts -->

        <!-- Component Changes Chart -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-chart-line"></i>Component Changes Overview</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="componentChangesChart" height="80"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Component Changes Chart -->

        <!-- Detailed Comparison Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h4 class="card-title"><i class="fa fa-table"></i>Detailed Component Comparison</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Component</th>
                                        <th class="text-end">{{ $comparison['base']['label'] }} (Earnings)</th>
                                        <th class="text-end">{{ $comparison['next']['label'] }} (Earnings)</th>
                                        <th class="text-end">Δ Earnings</th>
                                        <th class="text-end">{{ $comparison['base']['label'] }} (Deductions)</th>
                                        <th class="text-end">{{ $comparison['next']['label'] }} (Deductions)</th>
                                        <th class="text-end">Δ Deductions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($comparison['components'] as $id => $row)
                                        <tr>
                                            <td><strong>{{ $row['name'] ?? 'C'.$id }}</strong></td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($row['base_earnings'],2) }}</td>
                                            <td class="text-end">{{ get_currency_symbol() }}{{ number_format($row['next_earnings'],2) }}</td>
                                            <td class="text-end {{ $row['earnings_diff']>=0 ? 'text-success':'text-danger' }}">
                                                <strong>{{ $row['earnings_diff']>=0 ? '+':'' }}{{ get_currency_symbol() }}{{ number_format($row['earnings_diff'],2) }}</strong>
                                            </td>
                                            <td class="text-end text-warning">{{ get_currency_symbol() }}{{ number_format($row['base_deductions'],2) }}</td>
                                            <td class="text-end text-warning">{{ get_currency_symbol() }}{{ number_format($row['next_deductions'],2) }}</td>
                                            <td class="text-end {{ $row['deductions_diff']<=0 ? 'text-success':'text-danger' }}">
                                                <strong>{{ $row['deductions_diff']>=0 ? '+':'' }}{{ get_currency_symbol() }}{{ number_format($row['deductions_diff'],2) }}</strong>
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
        <!-- /Detailed Comparison Table -->
        @else
        <div class="row">
            <div class="col-md-12">
                <div class="settings-card">
                    <div class="card-header">
                        <h5><i class="fa fa-info-circle"></i>Ready to Compare</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="empty-state">
                            <i class="fa fa-balance-scale fa-5x text-muted mb-4"></i>
                            <h4>Ready to Compare</h4>
                            <p class="text-muted">Select two finalized payroll months to compare performance, identify trends, and analyze component-level changes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
<!-- /Page Wrapper -->
@endsection

@section('script')
@if($comparison)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseData = @json($comparison['base']);
    const nextData = @json($comparison['next']);
    const components = @json($comparison['components']);
    
    // Gross vs Net Comparison Chart
    const grossNetCtx = document.getElementById('grossNetChart').getContext('2d');
    new Chart(grossNetCtx, {
        type: 'bar',
        data: {
            labels: [baseData.label, nextData.label],
            datasets: [{
                label: 'Gross Total',
                data: [baseData.gross_total, nextData.gross_total],
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'Net Total',
                data: [baseData.net_total, nextData.net_total],
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
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
                            return '{{ get_currency_symbol() }}' + (value/100000).toFixed(1) + 'L';
                        }
                    }
                }
            }
        }
    });

    // Month Totals Doughnut Chart
    const monthTotalsCtx = document.getElementById('monthTotalsChart').getContext('2d');
    new Chart(monthTotalsCtx, {
        type: 'doughnut',
        data: {
            labels: ['Base Month', 'Comparison Month'],
            datasets: [{
                data: [baseData.gross_total, nextData.gross_total],
                backgroundColor: [
                    'rgba(255, 206, 86, 0.8)',
                    'rgba(54, 162, 235, 0.8)'
                ],
                borderColor: [
                    'rgba(255, 206, 86, 1)',
                    'rgba(54, 162, 235, 1)'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Component Changes Chart
    const componentNames = Object.values(components).map(comp => comp.name);
    const earningsDiffs = Object.values(components).map(comp => comp.earnings_diff);
    const deductionsDiffs = Object.values(components).map(comp => comp.deductions_diff);
    
    const componentChangesCtx = document.getElementById('componentChangesChart').getContext('2d');
    new Chart(componentChangesCtx, {
        type: 'bar',
        data: {
            labels: componentNames,
            datasets: [{
                label: 'Earnings Change',
                data: earningsDiffs,
                backgroundColor: earningsDiffs.map(val => val >= 0 ? 'rgba(75, 192, 192, 0.6)' : 'rgba(255, 99, 132, 0.6)'),
                borderColor: earningsDiffs.map(val => val >= 0 ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 99, 132, 1)'),
                borderWidth: 1
            }, {
                label: 'Deductions Change',
                data: deductionsDiffs,
                backgroundColor: deductionsDiffs.map(val => val <= 0 ? 'rgba(75, 192, 192, 0.6)' : 'rgba(255, 99, 132, 0.6)'),
                borderColor: deductionsDiffs.map(val => val <= 0 ? 'rgba(75, 192, 192, 1)' : 'rgba(255, 99, 132, 1)'),
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
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': {{ get_currency_symbol() }}' + context.parsed.y.toLocaleString();
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