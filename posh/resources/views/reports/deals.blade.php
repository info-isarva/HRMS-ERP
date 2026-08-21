@extends('layouts.app')

@section('content')
<style>
/* Responsive table styles: show header label left and value right on small screens */
@media (max-width: 767.98px) {
    .table-responsive table thead { display: none; }
    .table-responsive table, .table-responsive tbody, .table-responsive tr, .table-responsive td { display: block; width: 100%; }
    .table-responsive tbody tr { padding: 8px 0; border-bottom: 1px solid rgba(0,0,0,0.05); margin-bottom: 8px; }
    .table-responsive td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 6px 0;
        white-space: normal !important;
    }
    .table-responsive td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6b7280;
        margin-right: 12px;
        flex: 0 0 auto;
    }

    .row > [class*='col-'] {
        margin-bottom: 20px; /* Add gap between columns on mobile */
    }
}
</style>
<div class="container-fluid p-4">
    <div>
        <div class="my-3">
             <h4 class="mb-0">Analytics Reports</h4>
        </div>
        <form method="GET" action="{{ route('reports.deals') }}" id="dealsReportForm" class="mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="filter" class="form-label">Report Period</label>
                    <select name="filter" id="filter" class="form-select">
                        <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                        <option value="last_week" {{ request('filter') == 'last_week' ? 'selected' : '' }}>Last 1 Week</option>
                        <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>This Month</option>
                        <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                        <option value="yearly" {{ request('filter') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                        <option value="between" {{ request('filter') == 'between' ? 'selected' : '' }}>Between Dates</option>
                    </select>
                </div>
                <div class="col-md-4" id="dateRangeSection" style="display: none;">
                    <label class="form-label">Select Date Range</label>
                    <div class="d-flex gap-2">
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ old('start_date', $start ?? request('start_date')) }}">
                        <span class="align-self-center">to</span>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ old('end_date', $end ?? request('end_date')) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="stage" class="form-label">Deal Stage</label>
                    <select name="stage" id="stage" class="form-select">
                        <option value="" {{ request('stage') == '' ? 'selected' : '' }}>All</option>
                        <option value="Open" {{ request('stage') == 'Open' ? 'selected' : '' }}>Open</option>
                        <option value="Closed Won" {{ request('stage') == 'Closed Won' ? 'selected' : '' }}>Closed Won</option>
                        <option value="Closed Lost" {{ request('stage') == 'Closed Lost' ? 'selected' : '' }}>Closed Lost</option>
                    </select>
                </div>
            </div>
        </form>
        @if(isset($historicalSelected) && $historicalSelected)
            <div class="alert alert-warning">Unable to process the selected report for a historical financial year.</div>
        @endif
        @if(!$deals->isEmpty())
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4" style="height: 100%;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Deal Source Distribution</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="dealSourcePieChart" style="max-width: 100%; height: 500px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm mb-4" style="height: 100%;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Deal Stage Analysis</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center flex-column">
                        <canvas id="dealStageBarChart" style="max-width: 100%; height: 500px;"></canvas>
                        <div id="stageColorLegend" class="mt-3 w-100 text-center"></div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">Deals Report</h5>
            </div>
            <div class="card-body">
                
                @if(!$deals->isEmpty())
                   
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th>Title</th>
                                    <th>Company</th>
                                    <th>Contact Person</th>
                                    <th>Deal Owner</th>
                                    <th>Stage</th>
                                    <th>Lead Source</th>
                                    <th>Amount</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deals as $deal)
                                <tr>
                                    <td data-label="Title"><a class="text-decoration-none" href="{{ route('deals.show', $deal->id) }}">{{ $deal->title }}</a></td>
                                    <td data-label="Company">{{ $deal->organization->name ?? '-' }}</td>
                                    <td data-label="Contact Person">{{ $deal->person ? ($deal->person->first_name . ' ' . $deal->person->last_name) : '-' }}</td>
                                    <td data-label="Deal Owner">{{ $deal->owner->name ?? '-' }}</td>
                                    <td data-label="Stage">
                                        @php
                                            $stage = $deal->stage ?? '-';
                                            $stageColors = [
                                                'Open' => 'bg-primary',
                                                'Closed Won' => 'bg-success-subtle text-success',
                                                'Closed Lost' => 'bg-danger-subtle text-danger',
                                            ];
                                            $color = $stageColors[$stage] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $color }}">{{ $stage }}</span>
                                    </td>
                                    <td data-label="Lead Source">{{ $deal->dealSource->name ?? '-' }}</td>
                                    <td data-label="Amount">{{ $deal->amount ?? '-' }}</td>
                                    <td data-label="Created At">{{ $deal->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">No deals found for this period.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleDateRange() {
        var filter = document.getElementById('filter').value;
        var dateRangeSection = document.getElementById('dateRangeSection');
        if (filter === 'between') {
            dateRangeSection.style.display = '';
        } else {
            dateRangeSection.style.display = 'none';
        }
    }
    document.getElementById('filter').addEventListener('change', function() {
        toggleDateRange();
        document.getElementById('dealsReportForm').submit();
    });
    document.getElementById('stage').addEventListener('change', function() {
        document.getElementById('dealsReportForm').submit();
    });
    document.getElementById('start_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('dealsReportForm').submit();
        }
    });
    document.getElementById('end_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('dealsReportForm').submit();
        }
    });
    window.addEventListener('DOMContentLoaded', toggleDateRange);

    // Pie chart data
    var dealSourceLabels = @json($dealSourceLabels ?? []);
    var dealSourceCounts = @json($dealSourceCounts ?? []);
    if (dealSourceLabels.length > 0 && dealSourceCounts.length > 0) {
        var ctxPie = document.getElementById('dealSourcePieChart').getContext('2d');
        // Use a different color palette for pie chart
        var pieColors = [
            '#F44336', '#9C27B0', '#3F51B5', '#009688', '#CDDC39', '#FF9800', '#607D8B', '#00BFAE', '#FFB300', '#8D6E63', '#C51162', '#00C853'
        ];
        var pieBgColors = [];
        for (var i = 0; i < dealSourceLabels.length; i++) {
            pieBgColors.push(pieColors[i % pieColors.length]);
        }
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: dealSourceLabels,
                datasets: [{
                    data: dealSourceCounts,
                    backgroundColor: pieBgColors,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Deal Source Analytics' }
                }
            }
        });
    }

    // Bar chart data
    var dealStageLabels = @json($dealStageLabels ?? []);
    var dealStageCounts = @json($dealStageCounts ?? []);
    if (dealStageLabels.length > 0 && dealStageCounts.length > 0) {
        var ctxBar = document.getElementById('dealStageBarChart').getContext('2d');
        // Assign a color for each stage
        var stageColors = [
            '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#8BC34A', '#E91E63', '#00BCD4', '#FFC107', '#795548'
        ];
        // If more stages than colors, repeat colors
        var barColors = [];
        for (var i = 0; i < dealStageLabels.length; i++) {
            barColors.push(stageColors[i % stageColors.length]);
        }
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: dealStageLabels,
                datasets: [{
                    label: 'Deals by Stage',
                    data: dealStageCounts,
                    backgroundColor: barColors,
                    barPercentage: 0.5, // reduce bar width
                    categoryPercentage: 0.5 // reduce bar width
                }]
            },
            options: {
                indexAxis: 'y', // horizontal bar chart
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Deal Stage Analytics' }
                },
                scales: {
                    x: { beginAtZero: true }
                }
            }
        });
        // Render color legend below chart
        var legendHtml = '<div class="d-flex flex-wrap gap-3">';
        for (var i = 0; i < dealStageLabels.length; i++) {
            legendHtml += '<span style="display:inline-flex;align-items:center;gap:6px;"><span style="width:18px;height:18px;display:inline-block;border-radius:3px;background:' + barColors[i] + ';"></span> ' + dealStageLabels[i] + '</span>';
        }
        legendHtml += '</div>';
        document.getElementById('stageColorLegend').innerHTML = legendHtml;
    }

    // Explicitly set the height and width of the graphs to prevent auto-resizing
    const graphHeight = '500px';
    const graphWidth = '500px';

    document.getElementById('dealSourcePieChart').style.height = graphHeight;
    document.getElementById('dealSourcePieChart').style.width = graphWidth;
    document.getElementById('dealSourcePieChart').style.maxHeight = graphHeight;
    document.getElementById('dealSourcePieChart').style.maxWidth = graphWidth;

    document.getElementById('dealStageBarChart').style.height = graphHeight;
    document.getElementById('dealStageBarChart').style.width = graphWidth;
    document.getElementById('dealStageBarChart').style.maxHeight = graphHeight;
    document.getElementById('dealStageBarChart').style.maxWidth = graphWidth;

    
</script>
@endpush
@endsection
