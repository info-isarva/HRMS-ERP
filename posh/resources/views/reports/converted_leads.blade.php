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
    .table-responsive td a { display: inline-block; max-width: 65%; overflow: hidden; text-overflow: ellipsis; }
}
</style>
<div class="container-fluid p-4">
     <div>
        <div class="my-3">
              <h4 class="mb-0">Converted Leads Report</h4>
        </div>
        <div>
            @if($leads->isEmpty())
                <div class="alert alert-info">No converted leads found.</div>
            @else
            <div class="mb-3">
                <a href="{{ route('reports.converted_leads_excel', request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <!-- <th>Company Owner</th> -->
                             <th>Category</th>
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Lead Owner</th>
                            <th>Status</th>
                            <th>Converted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                        <tr>
                            <td data-label="#">{{ $loop->iteration }}</td>
                            <td data-label="Title"><a class="text-decoration-none" href="{{ route('leads.show', $lead->id) }}">{{ $lead->title }}</a></td>
                            <!-- <td data-label="Company Owner">{{ $lead->customer->name ?? '-' }}</td> -->
                             <td data-label="Category"> @if(!empty($lead->category_names))
                                        @foreach($lead->category_names as $categoryName)
                                            <div>{{ $categoryName }}</div>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                            </td>
                            <td data-label="Company">{{ $lead->organization->name ?? '-' }}</td>
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
                            <td data-label="Converted At">{{ $lead->converted_at ? $lead->converted_at->format('d-m-Y H:i') : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
                @if(method_exists($leads, 'links'))
                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pagination-info">
                        <div class="small text-muted">Showing {{ $leads->firstItem() ?? 0 }} to {{ $leads->lastItem() ?? 0 }} of {{ $leads->total() }} leads</div>
                        <div class="pagination-custom text-center my-3">
                            <nav aria-label="Leads pagination">
                                <ul class="pagination justify-content-center gap-3 mb-0">
                                    <li class="page-item {{ $leads->onFirstPage() ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ $leads->previousPageUrl() ?: '#' }}" tabindex="-1" aria-disabled="{{ $leads->onFirstPage() ? 'true' : 'false' }}">&laquo; Previous</a>
                                    </li>
                                    <li class="page-item {{ $leads->hasMorePages() ? '' : 'disabled' }}">
                                        <a class="page-link" href="{{ $leads->nextPageUrl() ?: '#' }}" aria-disabled="{{ $leads->hasMorePages() ? 'false' : 'true' }}">Next &raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                @endif
            
            @endif
        </div>
    </div>
</div>
@endsection
