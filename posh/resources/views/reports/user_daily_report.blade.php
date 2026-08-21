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
    .table-responsive td[rowspan] { display: none; }
}
</style>
<div class="container-fluid p-4">
    <h2>User Daily Report</h2>
    <form method="GET" class="mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label fw-bold">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date', date('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label for="end_date" class="form-label fw-bold">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date', date('Y-m-d')) }}">
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
            <div class="col-md-3 mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Search</button>
                <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                <a href="{{ route('reports.user_daily_report_excel', array_filter([
                    'start_date' => request('start_date'),
                    'end_date' => request('end_date'),
                    'user_id' => request('user_id')
                ])) }}" class="btn btn-success">Export Excel</a>
            </div>
        </div>
    </form>
    @if(isset($results))
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
           <thead>
                <tr>
                    <th colspan="3"></th>
                    <th colspan="2" class="text-center">Lead Section</th>
                    <th colspan="6" class="text-center">Deals Section</th>
                </tr>
                <tr>
                    <th>User Name</th>
                    <th>Date</th>
                    <th>Call Count</th>
                    <th>Lead Name</th>
                    <th>Lead Source</th>
                    <th>Deal Title</th>
                    <th>Stage</th>
                    <th>Status</th>
                    <th>Closed Date</th>
                    <th>Deal Amount</th>
                    <th>Loss Reason</th>
                </tr>
            </thead>
            <tbody>
                @php
                $grouped = collect($results)->groupBy(function($row) {
                    return $row['user']->id . '|' . $row['date'];
                });
                @endphp
                @forelse($grouped as $key => $rows)
                    @php $first = $rows->first(); $rowCount = $rows->count(); $i = 0; @endphp
                    @foreach($rows as $row)
                        <tr>
                            @if($i == 0)
                                <td rowspan="{{ $rowCount }}" style="vertical-align: middle;" class="d-none d-md-table-cell">{{ $first['user']->name }}</td>
                                <td rowspan="{{ $rowCount }}" style="vertical-align: middle;" class="d-none d-md-table-cell">{{ $first['date'] }}</td>
                                <td rowspan="{{ $rowCount }}" style="vertical-align: middle;" class="d-none d-md-table-cell">{{ $row['call_count'] }}</td>
                            @endif

                            {{-- Mobile-only group cells so each mobile row shows the user/date/callcount --}}
                            <td class="d-md-none" data-label="User Name">{{ $first['user']->name }}</td>
                            <td class="d-md-none" data-label="Date">{{ $first['date'] }}</td>
                            <td class="d-md-none" data-label="Call Count">{{ $first['call_count'] ?? $row['call_count'] }}</td>

                            <td data-label="Lead Name">{{ $row['lead_name'] !== '' ? $row['lead_name'] : '-' }}</td>
                            <td data-label="Lead Source">{{ $row['lead_source'] !== '' ? $row['lead_source'] : '-' }}</td>
                            <td data-label="Deal Title">{{ !empty($row['deal_title']) ? $row['deal_title'] : '-' }}</td>
                            <td data-label="Stage">{{ $row['stage'] !== '' ? $row['stage'] : '-' }}</td>
                            <td data-label="Status">{{ $row['status'] !== '' ? $row['status'] : '-' }}</td>
                            <td data-label="Closed Date">{{ $row['closed_date'] !== '' ? $row['closed_date'] : '-' }}</td>
                            <td data-label="Deal Amount">{{ $row['deal_amount'] !== '' ? $row['deal_amount'] : '-' }}</td>
                            <td data-label="Loss Reason">{{ $row['loss_reason'] !== '' ? $row['loss_reason'] : '-' }}</td>
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                @empty
                <tr><td colspan="11" class="text-center">No data found for selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
