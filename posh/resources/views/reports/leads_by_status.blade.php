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
</style>
<div class="container-fluid p-4">
     <div>
        <div class="my-3">
             <h4 class="mb-0">Leads by Status Report</h4>
        </div>
        <div>
            <form method="GET" class="mb-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">-- All Statuses --</option>
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @if(request('status') == $status) selected @endif>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- <div class="col-md-2">
                        <button type="submit" class="btn btn-primary">Filter</button>

                    </div> --}}
                </div>
            </form>
            @if($leads->isEmpty())
                <div class="alert alert-info">No leads found for the selected status.</div>
            @else
            <div class="mb-3">
                <a href="{{ route('reports.leads_by_status_excel', request()->only('status')) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <!-- <th>Company Owner</th> -->
                            <th>Company</th>
                            <th>Contact Person</th>
                            <th>Lead Owner</th>
                            <th>Category</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($leads as $lead)
                        <tr>
                            <td data-label="#">{{ $loop->iteration }}</td>
                            <td data-label="Title"><a class="text-decoration-none text-primary" href="{{ route('leads.show', $lead->id) }}">{{ $lead->title }}</a></td>
                            <!-- <td data-label="Company Owner">{{ $lead->customer->name ?? '-' }}</td> -->
                            <td data-label="Company">{{ $lead->organization->name ?? '-' }}</td>
                            <td data-label="Contact Person">{{ $lead->person ? ($lead->person->first_name . ' ' . $lead->person->last_name) : '-' }}</td>
                            <td data-label="Lead Owner">{{ $lead->owner->name ?? '-' }}</td>
                            <td data-label="Category"> @if(!empty($lead->category_names))
                                        @foreach($lead->category_names as $categoryName)
                                            <div>{{ $categoryName }}</div>
                                        @endforeach
                                    @else
                                        N/A
                                    @endif
                            </td>
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
                            <td data-label="Created At">{{ $lead->created_at->format('d-m-Y H:i') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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

            <!-- <div class="pagination-custom text-center my-3">
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
            </div> -->
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var statusDropdown = document.getElementById('status');
        if (statusDropdown) {
            statusDropdown.addEventListener('change', function() {
                this.form.submit();
            });
        }
    });
</script>
@endsection
