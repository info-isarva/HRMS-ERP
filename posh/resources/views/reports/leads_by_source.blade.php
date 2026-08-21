@extends('layouts.app')

@section('content')
<style>
    /* Pagination styling: center, rounded buttons, subtle borders like screenshot */
.pagination-custom { display: flex; justify-content: right; }
.pagination-custom .pagination { margin: 0; }
.pagination-custom .page-item .page-link {
    border-radius: 8px;
    border: 1px solid #e6e9ef;
    color: #232323;
    padding: 8px 14px;
    background: #fff;
}
.pagination-custom .page-item.disabled .page-link {
    color: #bfc6d1;
    background: #fff;
    border-color: #f0f2f5;
}
.pagination-custom .page-item.active .page-link {
    background: #f8fafc;
    border-color: #dfe7f2;
    color: #111827;
}
.pagination-custom .page-link:focus, .pagination-custom .page-link:hover { box-shadow: none; }
@media (max-width: 991.98px) {
    .pagination-custom { justify-content: center; }
}
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
}
</style>
<div class="container-fluid p-4">
    <h2>Leads by Source</h2>

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
            <a href="{{ route('reports.leads_by_source_custom') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mb-3">
        <a href="{{ route('reports.leads_by_source_excel', request()->only('start_date','end_date')) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Lead Source</th>
                    
                    <!-- <th>Email</th> -->
                     <th>Category</th>
                     <th>Lead Name</th>
                    <th>Mobile</th>
                    <th>Company</th>
                    <th>Title</th>
                    <th>Created Time</th>
                    <th>Lead Owner</th>
                </tr>
            </thead>
            <tbody>
                @forelse($grouped as $source => $leads)
                    @php $count = $leads->count(); $i = 0; @endphp
                    @foreach($leads as $lead)
                        <tr>
                            @if($i == 0)
                                <td class="d-none d-md-table-cell" rowspan="{{ $count }}">{{ $source }} <span class="text-muted">({{ $count }})</span></td>
                            @endif
                            <td data-label="Category"> @if(!empty($lead->category_names))
                                        @foreach($lead->category_names as $categoryName)
                                            <div>{{ $categoryName }}</div>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                            </td>
                            {{-- mobile-only source cell so each mobile row shows Lead Source as data-label --}}
                            <!-- <td class="d-md-none" data-label="Lead Source">{{ $source }} <span class="text-muted">({{ $count }})</span></td> -->
                            @php $fullName = $lead->person ? trim(($lead->person->first_name ?? '') . ' ' . ($lead->person->last_name ?? '')) : ''; @endphp
                            <td data-label="Lead Name"><a href="{{ url('/leads/'.$lead->id) }}" target="_blank">{{ $fullName !== '' ? $fullName : '-' }}</a></td>
                            <!-- <td>{{ $lead->person && ($lead->person->email ?? '') ? $lead->person->email : ($lead->email ?? '-') }}</td> -->
                            <td data-label="Mobile">{{ $lead->person && ($lead->person->mobile ?? '') ? $lead->person->mobile : ($lead->mobile ?? '-') }}</td>
                            <td data-label="Company">{{ $lead->organization ? $lead->organization->name : '-' }}</td>
                            <td data-label="Title">{{ $lead->title }}</td>
                            <td data-label="Created Time">{{ $lead->created_at ? $lead->created_at->format('d-m-Y h:i A') : '-' }}</td>
                            <td data-label="Lead Owner">{{ $lead->owner ? $lead->owner->name : '-' }}</td>
                            
                        </tr>
                        @php $i++; @endphp
                    @endforeach
                @empty
                    <tr><td colspan="9" class="text-center">No leads found for selected filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
        @if(isset($leadsPaginator))
            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pagination-info">
                <div class="small text-muted">Showing {{ $leadsPaginator->firstItem() ?? 0 }} to {{ $leadsPaginator->lastItem() ?? 0 }} of {{ $leadsPaginator->total() }} leads</div>
                <div class="pagination-custom text-center my-3">
                    <nav aria-label="Leads pagination">
                        <ul class="pagination justify-content-center gap-3 mb-0">
                            <li class="page-item {{ $leadsPaginator->onFirstPage() ? 'disabled' : '' }}">
                                <a class="page-link" href="{{ $leadsPaginator->previousPageUrl() ?: '#' }}" tabindex="-1" aria-disabled="{{ $leadsPaginator->onFirstPage() ? 'true' : 'false' }}">&laquo; Previous</a>
                            </li>
                            <li class="page-item {{ $leadsPaginator->hasMorePages() ? '' : 'disabled' }}">
                                <a class="page-link" href="{{ $leadsPaginator->nextPageUrl() ?: '#' }}" aria-disabled="{{ $leadsPaginator->hasMorePages() ? 'false' : 'true' }}">Next &raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        @endif
   
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
        if (s && e && s > e) {
            end.value = s;
        }
    }
    start.addEventListener('change', normalize);
    end.addEventListener('change', normalize);
    normalize();
});
</script>
@endpush
