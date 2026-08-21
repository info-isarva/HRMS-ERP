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
}
</style>
<div class="container-fluid p-4">
    <h2>Monthly User Report1</h2>
    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="month" class="form-label fw-bold">Month:</label>
                <input type="month" id="month" name="month" class="form-control" value="{{ request('month', date('Y-m')) }}">
            </div>
            <div class="col-md-3">
                <label for="user_id" class="form-label fw-bold">Employee:</label>
                <select id="user_id" name="user_id" class="form-select">
                    <option value="">All</option>
                    @foreach(\App\Models\User::where('crm_role_type', '!=', '0')->get() as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Search</button>
                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
            </div>
        </div>
    </form>
    @if(isset($results))
    @if(isset($results) && empty($selectedOutsideFy) && count($results) > 0)
    <div class="mb-3">
        <a href="{{ route('reports.monthly_user_report_excel', array_merge(request()->all())) }}" class="btn btn-success" target="_blank">
            Export Excel
        </a>
    </div>
    @endif
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Employee Name</th>
                    <th>Total Calls Made</th>
                    <th>Total Leads Generated</th>
                    <th>Total Deals Generated</th>
                    <th>Deals Closed Won</th>
                    <th>Deals Closed Lost</th>
                    <th>Deal Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $row)
                <tr>
                    <td data-label="Month">{{ $row['month'] }}</td>
                    <td data-label="Employee Name">{{ $row['user']->name }}</td>
                    <td data-label="Total Calls Made">
                        @if($row['calls_count'] > 0)
                            <a href="#" class="open-list-modal" data-type="calls" data-user="{{ $row['user']->id }}" data-month="{{ request('month', date('Y-m')) }}">
                                {{ $row['calls_count'] }}
                            </a>
                        @else
                            {{ $row['calls_count'] }}
                        @endif
                    </td>
                    <td data-label="Total Leads Generated">
                        @if(isset($row['leads_count']) && $row['leads_count'] > 0)
                            <a href="#" class="open-list-modal" data-type="leads" data-user="{{ $row['user']->id }}" data-month="{{ request('month', date('Y-m')) }}">
                                {{ $row['leads_count'] }}
                            </a>
                        @else
                            {{ $row['leads_count'] }}
                        @endif
                    </td>
                    <td data-label="Total Deals Generated">
                        @if(isset($row['deals_count']) && $row['deals_count'] > 0)
                            <a href="#" class="open-list-modal" data-type="deals" data-user="{{ $row['user']->id }}" data-month="{{ request('month', date('Y-m')) }}">
                                {{ $row['deals_count'] }}
                            </a>
                        @else
                            {{ $row['deals_count'] }}
                        @endif
                    </td>
                    <td data-label="Deals Closed Won">
                        @if(isset($row['deals_won_count']) && $row['deals_won_count'] > 0)
                        <a href="#" class="open-list-modal" data-type="deals_won" data-user="{{ $row['user']->id }}" data-month="{{ request('month', date('Y-m')) }}">
                            {{ $row['deals_won_count'] }}
                        </a>
                        @else
                            {{ $row['deals_won_count'] }}   
                        @endif
                    </td>
                    <td data-label="Deals Closed Lost">
                        @if(isset($row['deals_lost_count']) && $row['deals_lost_count'] > 0)
                        <a href="#" class="open-list-modal" data-type="deals_lost" data-user="{{ $row['user']->id }}" data-month="{{ request('month', date('Y-m')) }}">
                            {{ $row['deals_lost_count'] }}
                        </a>
                        @else
                            {{ $row['deals_lost_count'] }}
                        @endif
                    </td>
                    <td data-label="Deal Value">{{ \App\Helpers\MoneyFormatter::format($row['deal_amount']) }}</td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center">Data Not Available this Financial  Year</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
<!-- Modal -->
<div class="modal fade" id="listModal" tabindex="-1" aria-labelledby="listModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="listModalLabel">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="listModalBody">
                Loading...
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.open-list-modal').forEach(function(el) {
                el.addEventListener('click', function(e) {
                        e.preventDefault();
                        var type = this.getAttribute('data-type');
                        var userId = this.getAttribute('data-user');
                        var month = this.getAttribute('data-month');
                        
                        var url = '';
                        if(type === 'leads') {
                            url = '/reports/user-leads/' + userId + '?month=' + month;
                        } else if(type === 'deals') {
                            url = '/reports/user-deals/' + userId + '?month=' + month;
                        } else if(type === 'deals_won') {
                            url = '/reports/user-deals/' + userId + '?month=' + month + '&stage=Closed Won';
                        } else if(type === 'deals_lost') {
                            url = '/reports/user-deals/' + userId + '?month=' + month + '&stage=Closed Lost';
                        } else if(type === 'calls') {
                            url = '/reports/user-calls/' + userId + '?month=' + month;
                        }
                        var modal = new bootstrap.Modal(document.getElementById('listModal'));
                        document.getElementById('listModalBody').innerHTML = 'Loading...';
                        console.log(url);
                        fetch(url)
                                .then(response => response.text())
                                .then(html => {
                                        document.getElementById('listModalBody').innerHTML = html;
                                });
                        modal.show();
                });
        });
});
</script>
@endpush

@endsection
