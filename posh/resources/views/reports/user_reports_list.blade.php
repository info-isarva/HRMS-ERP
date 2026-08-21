@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <h2>User Reports</h2>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white fw-bold" style="font-size:1.3rem; color: #fff !important;">Search Filters</div>
        <div class="card-body">
            <form method="GET" class="mb-0">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="from_date" class="form-label fw-bold">Start Date:</label>
                        <input type="date" id="from_date" name="from_date" class="form-control" value="{{ request('from_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="to_date" class="form-label fw-bold">End Date:</label>
                        <input type="date" id="to_date" name="to_date" class="form-control" value="{{ request('to_date', date('Y-m-d')) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="user_id" class="form-label fw-bold">Users:</label>
                        <select id="user_id" name="user_id" class="form-select">
                            <option value="">None</option>
                            @foreach(\App\Models\User::where('crm_role_type', '!=', '0')->get() as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-12 mt-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Search</button>
                        <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Only show results if the user has clicked Search --}}
    @if(request()->has('from_date') || request()->has('to_date') || request()->has('user_id'))
        @if(isset($results) && count($results) > 0 && collect($results)->sum(fn($row) => count($row['leads']) + count($row['deals'])) > 0)
            <a href="{{ route('reports.user_reports_pdf', request()->all()) }}" class="btn btn-danger mb-3" target="_blank">
                Export PDF
            </a>
            @foreach($results as $row)
                <div class="card mb-4">
                    <div class="card-header fw-bold">
                        {{ $row['user']->name }}
                    </div>
                    <div class="card-body">
                        <h5>Leads <span class="badge bg-info">{{ count($row['leads']) }}</span></h5>
                        <table class="table table-bordered table-sm mb-3">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Company Name</th>
                                    <th>Converted to Deal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($row['leads'] as $lead)
                                    <tr>
                                        <td>{{ $lead->created_at ? \Carbon\Carbon::parse($lead->created_at)->format('d-m-Y') : '-' }}</td>
                                        <td><a href="{{ url('/leads/' . $lead->id) }}" target="_blank">{{ $lead->title }}</a></td>
                                        <td>{{ $lead->organization ? $lead->organization->name : '-' }}</td>
                                        <td>{{ $lead->converted_at ? 'Yes' : 'No' }}</td>
                                        <td>{{ $lead->status }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted">No leads found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        <h5>Deals <span class="badge bg-info">{{ count($row['deals']) }}</span></h5>
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Company Name</th>
                                    <th>Expected Close Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($row['deals'] as $deal)
                                    <tr>
                                        <td>{{ $deal->created_at ? \Carbon\Carbon::parse($deal->created_at)->format('d-m-Y') : '-' }}</td>
                                        <td><a href="{{ url('/deals/' . $deal->id) }}" target="_blank">{{ $deal->title }}</a></td>
                                        <td>{{ $deal->organization ? $deal->organization->name : '-' }}</td>
                                        <td>{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('d-m-Y') : '-' }}</td>
                                        <td>{{ $deal->stage }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-muted">No deals found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @else
            <div class="alert alert-info">No data found for the selected filters.</div>
        @endif
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var fromDateInput = document.getElementById('from_date');
    var toDateInput = document.getElementById('to_date');
    if (fromDateInput && toDateInput) {
        fromDateInput.addEventListener('change', function() {
            toDateInput.min = fromDateInput.value;
            if (toDateInput.value < fromDateInput.value) {
                toDateInput.value = fromDateInput.value;
            }
        });
        // Set initial min value on page load
        toDateInput.min = fromDateInput.value;
    }
});
</script>
@endpush
