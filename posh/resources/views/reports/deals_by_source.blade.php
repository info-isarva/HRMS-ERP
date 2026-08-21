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
        overflow: visible !important;
        text-overflow: inherit !important;
    }
    .table-responsive td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #6b7280;
        margin-right: 12px;
        flex: 0 0 auto;
    }
    .table-responsive td a { display: inline-block; max-width: 65%; overflow: hidden; text-overflow: ellipsis; }
    .table-responsive td[rowspan] { display: none; }
}
</style>
<div class="container-fluid p-4">
    <h2>Deals by Source</h2>

    <form method="GET" class="mb-3 row g-3 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" value="{{ request('start_date', $start ?? '') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" value="{{ request('end_date', $end ?? '') }}" class="form-control">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Filter</button>
            <a href="{{ route('reports.deals_by_source_custom') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>LEAD SOURCE</th>
                    <th>ACCOUNT NAME</th>
                    <th>DEAL NAME</th>
                    <th>CLOSING DATE</th>
                    <th>STAGE</th>
                    <th class="text-end">AMOUNT</th>
                </tr>
            </thead>
            <tbody>
                @php $grandTotal = 0; @endphp
                @forelse($grouped as $source => $deals)
                    @php $count = $deals->count(); $i = 0; $groupSum = 0; @endphp
                    @foreach($deals as $deal)
                        @php $groupSum += is_numeric($deal->amount) ? (float)$deal->amount : 0; $grandTotal += is_numeric($deal->amount) ? (float)$deal->amount : 0; @endphp
                        <tr @if($loop->iteration % 2 == 0) style="background:#fff6f6;" @endif>
                            @if($i == 0)
                                <td rowspan="{{ $count }}"><strong>{{ $source }}</strong> <small>({{ $count }})</small></td>
                            @endif
                            {{-- Mobile-only source cell so each row shows the source label on small screens --}}
                            <!-- <td class="d-md-none" data-label="Lead Source"><strong>{{ $source }}</strong> <small>({{ $count }})</small></td> -->
                            <td data-label="Account Name">{{ $deal->organization ? $deal->organization->name : '-' }}</td>
                            <td data-label="Deal Name"><a href="{{ url('/deals/'.$deal->id) }}" target="_blank">{{ $deal->title ?? '-' }}</a></td>
                            <td data-label="Closing Date">{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('d/m/Y') : '-' }}</td>
                            <td data-label="Stage">{{ $deal->stage ?? '-' }}</td>
                            <td data-label="Amount" class="text-end">@if(is_numeric($deal->amount)) @money($deal->amount) @else - @endif</td>
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                    <tr style="background:#fff6f6;">
                        <td colspan="5" class="text-end"><strong style="color:#6b21a8;">Sum @money($groupSum)</strong></td>
                        <td class="text-end"><a href="#" style="color:#6b21a8; font-weight:600;">Sum @money($groupSum)</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">No deals found for selected filters.</td></tr>
                @endforelse
                <tr>
                    <td colspan="5"><strong>Grand Total</strong></td>
                    <td class="text-end"><strong style="color:#6b21a8;">Sum @money($grandTotal)</strong></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function(){
    var start = document.querySelector('input[name="start_date"]');
    var end = document.querySelector('input[name="end_date"]');
    if (!start || !end) return;
    function normalize() {
        var s = start.value;
        var e = end.value;
        if (s) end.min = s; else end.min = '';
        if (e) start.max = e; else start.max = '';
        // if both set and start > end, adjust end to start
        if (s && e && s > e) {
            end.value = s;
        }
    }
    start.addEventListener('change', normalize);
    end.addEventListener('change', normalize);
    // initialize
    normalize();
});
</script>
@endpush
