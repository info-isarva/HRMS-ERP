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
    <div>
        <div class="my-3">
            <h4 class="mb-0">Lost Deals Report</h4>
        </div>
        <div>
             @if($deals->isEmpty())
                <div class="alert alert-info">No lost deals found.</div>
            @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Company Name</th>
                            <th>Title</th>
                            <th>Deal Owner</th>
                            <th>Lead Source</th>
                            <th>Stage</th>
                            <th>Created Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deals as $deal)
                        <tr>
                            <td data-label="#">{{ $loop->iteration }}</td>
                            <td data-label="Company Name"><a href="{{ route('organizations.show', $deal->organization->id) }}">{{ $deal->organization->name ?? '-' }}</a></td>
                            <td data-label="Title"><a class="text-decoration-none" href="{{ route('deals.show', $deal->id) }}">{{ $deal->title }}</a></td>
                            <td data-label="Deal Owner">{{ $deal->owner->name ?? '-' }}</td>
                            <td data-label="Lead Source">{{ $deal->dealSource->name ?? '-' }}</td>
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
                            <td data-label="Created Time">{{ $deal->created_at ? \Carbon\Carbon::parse($deal->created_at)->format('d-m-Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection

