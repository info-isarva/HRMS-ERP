@extends('layouts.app')
@section('content')
<div class="container-fluid p-4">
    <h2 class="mb-4">Monthly User Performance Report</h2>
    <form method="GET" class="mb-3 d-flex flex-wrap align-items-end">
        @php
            // Prepare fiscal year months if start/end provided
            $fyMonths = [];
            if (!empty($start) && !empty($end)) {
                try {
                    $p = \Carbon\Carbon::parse($start)->startOfMonth();
                    $endP = \Carbon\Carbon::parse($end)->startOfMonth();
                    while ($p->lte($endP)) {
                        $fyMonths[] = $p->format('Y-n');
                        $p->addMonth();
                    }
                } catch (Exception $e) {
                    $fyMonths = [];
                }
            }
        @endphp

        <div class="me-2 mb-2">
            <label for="year" class="form-label">Year</label>
            <select name="year" id="year" class="form-select">
                
                @for($y = date('Y')-2; $y <= date('Y')+1; $y++)
                    @php
                        $yearHasFyMonths = false;
                        if (!empty($fyMonths)) {
                            foreach ($fyMonths as $fm) {
                                list($fyY, $fyM) = explode('-', $fm);
                                if ((int)$fyY === (int)$y) { $yearHasFyMonths = true; break; }
                            }
                        }
                    @endphp
                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }} @if(!empty($fyMonths) && !$yearHasFyMonths) disabled class="text-muted" @endif>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="me-2 mb-2">
            <label for="month" class="form-label">Month</label>
            <select name="month" id="month" class="form-select">
                <!-- <option value="">All</option> -->
                @for($m = 1; $m <= 12; $m++)
                    @php
                        $isInFy = true;
                        if (!empty($fyMonths)) {
                            if (!empty($year)) {
                                $isInFy = in_array($year . '-' . $m, $fyMonths);
                            } else {
                                // if no specific year selected, enable the month only if it appears in any FY month
                                $found = false;
                                foreach ($fyMonths as $fm) {
                                    list($fyY, $fyM) = explode('-', $fm);
                                    if ((int)$fyM === (int)$m) { $found = true; break; }
                                }
                                $isInFy = $found;
                            }
                        }
                    @endphp
                    <option value="{{ $m }}" {{ ($month ?? date('n')) == $m ? 'selected' : '' }} @if(!empty($fyMonths) && !$isInFy) disabled class="text-muted" @endif>{{ date('F', mktime(0,0,0,$m,1)) }}</option>
                @endfor
            </select>
        </div>
        <div class="me-2 mb-2">
            <label for="user_id" class="form-label">User</label>
            <select name="user_id" id="user_id" class="form-select">
                <option value="">All</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ ($user_id ?? '') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        
        <button type="submit" class="btn btn-custom mb-2" style="padding: 6px 20px !important;">Search</button>
        <!-- <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a> -->
    </form>
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
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle mb-0">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Year</th>
                    <th>Month</th>
                    <th>Sales Target</th>
                    <th>Achieved Sales</th>
                    <th>Progress</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report as $row)
                    <tr>
                        <td data-label="User">{{ $row['user_name'] }}</td>
                        <td data-label="Year">{{ $row['year'] }}</td>
                        <td data-label="Month">{{ \Carbon\Carbon::create()->month($row['month'])->format('F') }}</td>
                        <td data-label="Sales Target">{{ $currency_symbol }} {{ number_format($row['sales_target'], 2) }}</td>
                        <td data-label="Achieved Sales">{{ $currency_symbol }} {{ number_format($row['achieved_sales'], 2) }}</td>
                        <td data-label="Progress">
                            <div style="min-width:120px;">
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar" style="width: {{ $row['progress'] }}%;" aria-valuenow="{{ $row['progress'] }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $row['progress'] }}%
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">Data Not Available this Financial  Year</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
