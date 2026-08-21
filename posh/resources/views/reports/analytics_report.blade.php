@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <h1 class="mb-4 d-none d-md-block">User Performance Analytics Reports</h1>
    <h3 class="mb-4 text-start d-block d-md-none">User Performance Analytics Reports</h3>

    <form method="GET" action="{{ route('analytics.reports') }}" class="mb-4 bg-white p-4 rounded shadow-sm">
        <div class="row g-3">
            <div class="col-md-3 col-sm-6">
                <label for="year" class="form-label">Year</label>
                <select name="year" id="year" class="form-select">
                    <option value="">Select Year</option>
                    @foreach(range(date('Y'), date('Y') - 10) as $y)
                        <option value="{{ $y }}" {{ request('year', date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="month" class="form-label">Month</label>
                <select name="month" id="month" class="form-select">
                    <option value="">Select Month</option>
                    <option value="all" {{ request('month') == 'all' ? 'selected' : '' }}>All Months</option>
                    @foreach(range(1, 12) as $m)
                        <option value="{{ $m }}" {{ request('month', date('n')) == $m ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="user_id" class="form-label">User</label>
                <select name="user_id" id="user_id" class="form-select">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-sm-6 align-self-end">
                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Filter</button>
            </div>
        </div>
    </form>

    <div class="card shadow-sm mb-5">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Analytics Overview</h5>
        </div>
        <div class="card-body">
            <div class="chart-container" style="position: relative; height: 50vh; width: 100%;">
                <canvas id="analyticsChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Analytics Report Table</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive" style="overflow-x: auto;">
                <table class="table table-bordered table-striped">
                    <thead class="table-success">
                        <tr>
                            <th>User</th>
                            <th>Year</th>
                            <th>Month</th>
                            <th>Sales Target</th>
                            <th>Achieved Sales</th>
                            <th>Progress (%)</th>
                            <th>Generated Deals</th>
                            <th>Contacted Leads</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report as $row)
                            <tr>
                                <td data-label="User">{{ $row['user_name'] }}</td>
                                <td data-label="Year">{{ $row['year'] }}</td>
                                <td data-label="Month">{{ date('F', mktime(0, 0, 0, $row['month'], 1)) }}</td>
                                <td data-label="Sales Target">{{ $currency_symbol }}{{ number_format($row['sales_target'], 2) }}</td>
                                <td data-label="Achieved Sales">{{ $currency_symbol }}{{ number_format($row['achieved_sales'], 2) }}</td>
                                <td data-label="Progress (%)">{{ $row['progress'] }}%</td>
                                <td data-label="Generated Deals">{{ $row['generated_deals'] }}</td>
                                <td data-label="Contacted Leads">{{ $row['contacted_leads'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No data available for the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('analyticsChart').getContext('2d');
        const chartData = @json($report);
        const monthNames = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];
        const labels = chartData.map(row => {
            const monthName = monthNames[row.month - 1]; // month = 1–12
            return `${row.user_name} (${monthName} ${row.year})`;
        });
        const achievedSales = chartData.map(row => row.achieved_sales);
        const salesTarget = chartData.map(row => row.sales_target);
        const generatedDeals = chartData.map(row => row.generated_deals || 0);
        const contactedLeads = chartData.map(row => row.contacted_leads || 0);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Achieved Sales',
                        data: achievedSales,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Sales Target',
                        data: salesTarget,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Generated Deals',
                        data: generatedDeals,
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                    },
                    {
                        label: 'Contacted Leads',
                        data: contactedLeads,
                        borderColor: 'rgba(153, 102, 255, 1)',
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Users and Time Period',
                            color: '#000', // Dark color for x-axis label
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            color: '#000' // Dark color for x-axis ticks
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Target Values (e.g., Deals or Sales)',
                            color: '#000', // Dark color for y-axis label
                            font: {
                                size: 14,
                                weight: 'bold'
                            }
                        },
                        ticks: {
                            color: '#000' // Dark color for y-axis ticks
                        },
                        beginAtZero: true,
                        max: Math.max(...achievedSales, ...salesTarget, ...generatedDeals, ...contactedLeads),
                    }
                }
            }
        });
    });
</script>
@endpush