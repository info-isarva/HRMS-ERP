@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <div class="my-3">
        <h4 class="mb-0">Revenue Bar Chart</h4>
    </div>
    <form method="GET" action="{{ route('reports.revenue_by_month') }}" id="revenueMonthForm" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label for="filter" class="form-label">Period</label>
                <select name="filter" id="filter" class="form-select">
                    <option value="all" {{ request('filter') == 'all' ? 'selected' : '' }}>All</option>
                    <option value="today" {{ request('filter') == 'today' ? 'selected' : '' }}>Today</option>
                    <option value="last_7_days" {{ request('filter') == 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                    <option value="this_month" {{ request('filter') == 'this_month' ? 'selected' : '' }}>Current Month</option>
                    <option value="last_month" {{ request('filter') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                    <option value="this_year" {{ request('filter') == 'this_year' ? 'selected' : '' }}>Current Year</option>
                    <!-- <option value="last_year" {{ request('filter') == 'last_year' ? 'selected' : '' }}>Last Year</option> -->
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
    <div class="d-flex align-items-center mb-3">
        @if(isset($selectedFyId) && $selectedFyId)
            <div class="me-3 small text-muted">Showing financial year range: <strong>{{ \Carbon\Carbon::parse($start)->format('d-m-Y') ?? '-' }} to {{ \Carbon\Carbon::parse($end)->format('d-m-Y')  ?? '-' }}</strong></div>
        @else
            <div class="me-3">
                <button id="prevYearBtn" class="btn btn-outline-secondary btn-sm me-2">&laquo; Previous Year</button>
                <button id="nextYearBtn" class="btn btn-outline-secondary btn-sm">Next Year &raquo;</button>
            </div>
            <div class="small text-muted ms-3">Use the buttons to navigate calendar years.</div>
        @endif
    </div>
    @if(!$monthlyRevenue->isEmpty())
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Revenue by Month</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueBarChart" style="max-height: 650px;"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0">Revenue by Month Report</h5>
        </div>
        <div class="card-body">
            @if($monthlyRevenue->isEmpty())
                <div class="alert alert-info">No revenue data found for this period.</div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-success">
                            <tr>
                                <th>Month</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($monthlyRevenue as $i => $row)
                                @php
                                    // Determine if this month falls inside the selected financial year (if any)
                                    $isInFy = true;
                                    if (isset($selectedFyId) && $selectedFyId && isset($start) && isset($end)) {
                                        try {
                                            $monthDate = \Carbon\Carbon::parse('01 ' . $row['month']);
                                            $fyStart = \Carbon\Carbon::parse($start)->startOfDay();
                                            $fyEnd = \Carbon\Carbon::parse($end)->endOfDay();
                                            $isInFy = $monthDate->between($fyStart, $fyEnd);
                                        } catch (\Exception $e) {
                                            $isInFy = true;
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td data-label="Month">
                                        @if($isInFy)
                                            <a href="#" class="month-link text-decoration-none" data-month="{{ $row['month'] }}">{{ $row['month'] }}</a>
                                        @else
                                            <span class="text-muted" title="Month outside selected financial year">{{ $row['month'] }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Amount">{{ \App\Helpers\MoneyFormatter::format($row['amount']) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight:bold;background:#ffffff;">
                                <td class="text-end" data-label="Total">Total</td>
                                <td data-label="Total Amount">
                                    {{ \App\Helpers\MoneyFormatter::format($monthlyRevenue->sum('amount')) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal for Closed Won Deals by Month -->
<div class="modal fade" id="monthDealsModal" tabindex="-1" aria-labelledby="monthDealsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="monthDealsModalLabel">Closed Won Deals - <span id="modalMonthLabel"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="monthDealsTableContainer">
                    <div class="text-center py-4" id="monthDealsLoading" style="display:none;">Loading...</div>
                    <table class="table table-bordered table-striped" id="monthDealsTable" style="display:none;">
                        <thead class="table-success">
                            <tr>
                                <th>Title</th>
                                <th>Company</th>
                                <th>Contact Person</th>
                                <th>Stage</th>
                                <th>Amount</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    <div class="alert alert-info" id="monthDealsEmpty" style="display:none;">No closed won deals found for this month.</div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Modal logic for month click
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.month-link').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var month = this.getAttribute('data-month');
                document.getElementById('modalMonthLabel').textContent = month;
                // Show loading
                document.getElementById('monthDealsLoading').style.display = '';
                document.getElementById('monthDealsTable').style.display = 'none';
                document.getElementById('monthDealsEmpty').style.display = 'none';
                // Fetch deals for this month via AJAX
                fetch(`{{ route('reports.revenue_by_month') }}?ajax=1&month=${encodeURIComponent(month)}&filter={{ request('filter') }}{{ request('start_date') ? '&start_date=' . request('start_date') : '' }}{{ request('end_date') ? '&end_date=' . request('end_date') : '' }}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('monthDealsLoading').style.display = 'none';
                        var tbody = document.querySelector('#monthDealsTable tbody');
                        tbody.innerHTML = '';
                        if (data.length > 0) {
                            let totalAmount = 0;
                            data.forEach(function(deal) {
                                totalAmount += parseFloat(deal.amount);
                                tbody.innerHTML += `<tr>
                                    <td data-label="Title">${deal.title}</td>
                                    <td data-label="Company">${deal.company}</td>
                                    <td data-label="Contact Person">${deal.contact_person}</td>
                                    <td data-label="Stage">${deal.stage === 'Closed Won' ? '<span class="badge bg-success-subtle text-success">Closed Won</span>' : deal.stage}</td>
                                    <td data-label="Amount">&#8377;${parseFloat(deal.amount).toLocaleString('en-IN', {minimumFractionDigits:2})}</td>
                                </tr>`;
                            });
                            // Add total row
                            tbody.innerHTML += `<tr style='font-weight:bold;background:#f6fff6;'><td colspan='4' class='text-end' data-label="Total">Total</td><td data-label="Total Amount">&#8377;${totalAmount.toLocaleString('en-IN', {minimumFractionDigits:2})}</td></tr>`;
                            document.getElementById('monthDealsTable').style.display = '';
                            document.getElementById('monthDealsEmpty').style.display = 'none';
                        } else {
                            document.getElementById('monthDealsTable').style.display = 'none';
                            document.getElementById('monthDealsEmpty').style.display = '';
                        }
                        var modal = new bootstrap.Modal(document.getElementById('monthDealsModal'));
                        modal.show();
                    })
                    .catch(() => {
                        document.getElementById('monthDealsLoading').style.display = 'none';
                        document.getElementById('monthDealsTable').style.display = 'none';
                        document.getElementById('monthDealsEmpty').style.display = '';
                        var modal = new bootstrap.Modal(document.getElementById('monthDealsModal'));
                        modal.show();
                    });
            });
        });
    });

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
        document.getElementById('revenueMonthForm').submit();
    });
    document.getElementById('start_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('revenueMonthForm').submit();
        }
    });
    document.getElementById('end_date').addEventListener('change', function() {
        if (document.getElementById('filter').value === 'between') {
            document.getElementById('revenueMonthForm').submit();
        }
    });
    window.addEventListener('DOMContentLoaded', toggleDateRange);

    // Bar chart data
    var revenueLabels = @json($monthlyRevenue->pluck('month'));
    var revenueAmounts = @json($monthlyRevenue->pluck('amount'));
    if (revenueLabels.length > 0 && revenueAmounts.length > 0) {
        var ctxBar = document.getElementById('revenueBarChart').getContext('2d');
        new Chart(ctxBar, {
            type: 'bar',
            data: {
                labels: revenueLabels,
                datasets: [{
                    label: 'Revenue',
                    data: revenueAmounts,
                    backgroundColor: '#4BC0C0',
                        barPercentage: 0.5, // reduce bar width
                        categoryPercentage: 0.5 // reduce bar width
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: true, text: 'Revenue by Month' }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }
</script>
<script>
    // Prev/Next year navigation (only active when no financial year is selected)
    document.addEventListener('DOMContentLoaded', function() {
        var prevBtn = document.getElementById('prevYearBtn');
        var nextBtn = document.getElementById('nextYearBtn');
        if (prevBtn && nextBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // navigate to last year (calendar) by setting filter to last_year then submit
                document.getElementById('filter').value = 'last_year';
                document.getElementById('revenueMonthForm').submit();
            });
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                // navigate to this year (calendar) by setting filter to this_year then submit
                document.getElementById('filter').value = 'this_year';
                document.getElementById('revenueMonthForm').submit();
            });
        }
    });
</script>
@endpush
@endsection
