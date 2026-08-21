@extends('layouts.app')

@section('content')
<style>
    /* reuse pagination look from leads_by_status */
.pagination-custom { display: flex; justify-content: right; width: 100%; }
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
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0">Today's Sales</h2>
        <div>
            <a href="{{ route('reports.today_closed_won_deals_excel', request()->except('page')) }}" class="btn btn-success btn-sm"><i class="bi bi-file-earmark-excel"></i> Export Excel</a>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th>Deal Name</th>
                    <th>Account Name</th>
                    <th>Deal Owner</th>
                    <th>Lead Source</th>
                    <th class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($historicalSelected) && $historicalSelected)
                    <tr><td colspan="5" class="text-center">Today's closed won sales are not available for a historical financial year selection.</td></tr>
                @else
                    @forelse($deals as $deal)
                <tr>
                    <td data-label="Deal Name"><a href="{{ url('/deals/'.$deal->id) }}">{{ $deal->title ?? '-' }}</a></td>
                    <td data-label="Account Name">{{ $deal->organization ? $deal->organization->name : '-' }}</td>
                    <td data-label="Deal Owner">{{ $deal->owner ? $deal->owner->name : '-' }}</td>
                    <td data-label="Lead Source">{{ $deal->dealSource ? $deal->dealSource->name : '-' }}</td>
                    <td data-label="Amount" class="text-end">{{ is_numeric($deal->amount) ? \App\Helpers\MoneyFormatter::format($deal->amount) : ($deal->amount ?: '-') }}</td>
                </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">No closed won sales for today.</td></tr>
                    @endforelse
                @endif
                <tr>
                    <td colspan="4"><strong>Grand Total</strong></td>
                    <td class="text-end"><a href="#" class="text-primary">Sum {{ is_numeric($total) ? \App\Helpers\MoneyFormatter::format($total) : \App\Helpers\MoneyFormatter::format(0) }}</a></td>
                </tr>
            </tbody>
        </table>
    </div>

    @if(method_exists($deals, 'onFirstPage'))
    <div class="pagination-custom text-center my-3">
        <nav aria-label="Deals pagination">
            <ul class="pagination justify-content-center gap-3 mb-0">
                <li class="page-item {{ $deals->onFirstPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $deals->previousPageUrl() ?: '#' }}" tabindex="-1" aria-disabled="{{ $deals->onFirstPage() ? 'true' : 'false' }}">&laquo; Previous</a>
                </li>
                <li class="page-item {{ $deals->hasMorePages() ? '' : 'disabled' }}">
                    <a class="page-link" href="{{ $deals->nextPageUrl() ?: '#' }}" aria-disabled="{{ $deals->hasMorePages() ? 'false' : 'true' }}">Next &raquo;</a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>
@endsection
