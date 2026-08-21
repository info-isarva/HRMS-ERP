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

    .graph-card {
        margin-bottom: 20px; /* Add gap between graph cards on mobile */
    }

    .row > [class*='col-'] {
        margin-bottom: 20px; /* Add gap between columns on mobile */
    }
}
</style>
<div class="container-fluid p-4">
    <div>
        <div class="my-3">
            <h5 class="mb-0">Analytics Reports</h5> 
        </div>
        <form method="GET" action="{{ route('reports.leads') }}" id="leadsReportForm" class="mb-4">
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
            </div>
        </form>
        @if(isset($historicalSelected) && $historicalSelected)
            <div class="alert alert-warning">Unable to process the selected report for a historical financial year.</div>
        @endif
        @if(!$leads->isEmpty())
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm mb-4 graph-card" style="height: 100%;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Lead Source Distribution</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <canvas id="leadSourcePieChart" style="max-width: 100%; height: 500px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm mb-4 graph-card" style="height: 100%;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Lead Status Analysis</h5>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center flex-column">
                        <canvas id="leadStatusBarChart" style="max-width: 100%; height: 500px;"></canvas>
                        <div id="statusColorLegend" class="mt-3 w-100 text-center"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- <h4 class="mb-3 mt-4">Leads Report</h4> -->
        @endif
        <div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Leads Report</h5>
                </div>
                <div class="card-body">
                    @if($leads->isEmpty())
                        <div class="alert alert-info">No leads found for this period.</div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-success">
                                <tr>
                                    <th>Title</th>
                                    <th>Company Name</th>
                                    <th>Contact Person</th>
                                    <th>Lead Owner</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $lead)
                                <tr>
                                    <td data-label="Title"><a class="text-decoration-none" href="{{ route('leads.show', $lead->id) }}">{{ $lead->title }}</a></td>
                                    <td data-label="Company Name">{{ $lead->organization->name ?? '-' }}</td>
                                    <td data-label="Contact Person">{{ $lead->person ? ($lead->person->first_name . ' ' . $lead->person->last_name) : '-' }}</td>
                                    <td data-label="Lead Owner">{{ $lead->owner->name ?? '-' }}</td>
                                    <td data-label="Status">
                                        @php
                                            $status = $lead->status ?? '-';
                                            $statusColors = [
                                                'New' => 'bg-primary',
                                                'Contacted' => 'bg-info text-dark',
                                                'Qualified' => 'bg-success-subtle text-success',
                                                'Lost' => 'bg-danger-subtle text-danger',
                                                'Converted' => 'bg-warning text-dark',
                                            ];
                                            $color = $statusColors[$status] ?? 'bg-secondary';
                                        @endphp
                                        <span class="badge {{ $color }}">{{ $status }}</span>
                                    </td>
                                    <td data-label="Created At">{{ $lead->created_at->format('d-m-Y H:i') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $leads->withQueryString()->links() }}
                    </div>
                    @endif
                </div>
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
        document.getElementById('leadsReportForm').submit();
    });
    window.addEventListener('DOMContentLoaded', toggleDateRange);
    document.getElementById('start_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('leadsReportForm').submit();
        }
    });
    document.getElementById('end_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('leadsReportForm').submit();
        }
    });

    // Pie chart data
    var leadSourceLabels = @json($leadSourceLabels ?? []);
    var leadSourceCounts = @json($leadSourceCounts ?? []);
    if (leadSourceLabels.length > 0 && leadSourceCounts.length > 0) {
        var ctxPie = document.getElementById('leadSourcePieChart').getContext('2d');
        new Chart(ctxPie, {
            type: 'pie',
            data: {
                labels: leadSourceLabels,
                datasets: [{
                    data: leadSourceCounts,
                    backgroundColor: [
                        '#36A2EB', '#FF6384', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF'
                    ],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                    title: { display: true, text: 'Lead Source Analytics' }
                }
            }
        });
    }

    // Bar chart data
    var leadStatusLabels = @json($leadStatusLabels ?? []);
    var leadStatusCounts = @json($leadStatusCounts ?? []);
    if (leadStatusLabels.length > 0 && leadStatusCounts.length > 0) {
        var ctxBar = document.getElementById('leadStatusBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: leadStatusLabels,
                datasets: [{
                    label: 'Leads by Status',
                    data: leadStatusCounts,
                    backgroundColor: '#36A2EB',
                    barPercentage: 0.5, // reduce bar width
                    categoryPercentage: 0.5 // reduce bar width
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Lead Status Analytics' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    // Explicitly set the height and width of the graphs to prevent auto-resizing
    const graphHeight = '500px';
    const graphWidth = '500px';

    document.getElementById('leadSourcePieChart').style.height = graphHeight;
    document.getElementById('leadSourcePieChart').style.width = graphWidth;
    document.getElementById('leadSourcePieChart').style.maxHeight = graphHeight;
    document.getElementById('leadSourcePieChart').style.maxWidth = graphWidth;

    document.getElementById('leadStatusBarChart').style.height = graphHeight;
    document.getElementById('leadStatusBarChart').style.width = graphWidth;
    document.getElementById('leadStatusBarChart').style.maxHeight = graphHeight;
    document.getElementById('leadStatusBarChart').style.maxWidth = graphWidth;
</script>
@endpush
@endsection
