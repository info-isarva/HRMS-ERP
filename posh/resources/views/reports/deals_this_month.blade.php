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
    <div class="d-flex align-items-center justify-content-between  pt-4 mb-3 " style="border-bottom:none;">
        <div class="d-flex align-items-center gap-2">
            {{-- <a href="javascript:history.back()" class="me-2" style="color:#222;"><i class="bi bi-arrow-left fs-4"></i></a> --}}
            <h5 class="mb-0 fw-semibold">Deals Closing this Month <i class="bi bi-info-circle ms-1" style="color:#bdbdbd;" title="Deals with closing date in this month"></i></h5>
        </div>
        {{-- <div class="d-flex align-items-center gap-2">
            <span class="text-muted small"><i class="bi bi-arrow-clockwise me-1"></i> Updated less than a minute ago</span>
            <button class="btn btn-outline-secondary btn-sm">Edit <i class="bi bi-chevron-down ms-1"></i></button>
        </div> --}}
    </div>
    {{-- <div class="d-flex align-items-center px-4 py-3 border-bottom" style="border-top:1px solid #E63946;">
        <button class="btn btn-light border d-flex align-items-center gap-2 me-3" style="min-width:90px;"><i class="bi bi-funnel"></i> Filter</button>
        <span class="text-muted">Total Records : <b>{{ $deals->count() }}</b></span>
        <div class="ms-auto"><span class="text-end text-secondary" style="font-size:15px;cursor:pointer;">Show Details</span></div>
    </div> --}}
    <div class="table-responsive" >
        <table class="table table-bordered table-striped">
            <thead>
                <tr style="background:#f6f8fc;">
                    <th style="width:220px;">DEAL NAME</th>
                    <th style="width:180px;">ACCOUNT NAME</th>
                    <th style="width:160px;">DEAL OWNER</th>
                    <th style="width:180px;">STAGE</th>
                    <th style="width:140px;">PROBABILITY (%)</th>
                    <th style="width:160px;">CLOSING DATE</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($historicalSelected) && $historicalSelected)
                    <tr><td colspan="6" class="text-center text-muted">Unable to show deals for a historical financial year selection.</td></tr>
                @else
                    @forelse($deals as $deal)
                    <tr>
                        <td data-label="Deal Name"><a href="{{ route('deals.show', $deal->id) }}" class="text-primary text-decoration-none">{{ $deal->title }}</a></td>
                        <td data-label="Account Name">{{ $deal->organization->name ?? '-' }}</td>
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
                        <td data-label="Probability">{{ $deal->probability ?? '-' }}</td>
                        <td data-label="Closing Date">{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('d/m/Y') : '-' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">No deals found.</td></tr>
                    @endforelse
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection
