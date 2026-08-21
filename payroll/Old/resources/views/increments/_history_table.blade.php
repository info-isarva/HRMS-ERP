@if($increments->count() > 0)
    <div class="alert alert-info">
        <strong>Employee:</strong> {{ $employee->name }} ({{ $employee->unique_id }}) | 
        <strong>Current Designation:</strong> {{ $employee->designationObj->position ?? 'N/A' }}
    </div>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Effective Date</th>
                    <th>Type</th>
                    <th>Compensation Change</th>
                    <th>Designation Change</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($increments as $history)
                <tr class="{{ $loop->first ? 'table-success' : '' }}">
                    <td>
                        @if($loop->first)
                            <span class="badge bg-success">LATEST</span>
                        @else
                            {{ $loop->iteration }}
                        @endif
                    </td>
                    <td>{{ $history->effective_date->format('d M Y') }}</td>
                    <td>
                        @if($history->type == 'increment')
                            <span class="badge bg-info">Increment</span>
                        @elseif($history->type == 'promotion')
                            <span class="badge bg-warning">Promotion</span>
                        @else
                            <span class="badge bg-success">Both</span>
                        @endif
                    </td>
                    <td>
                        <div>Prev: {{ get_currency_symbol() }}{{ number_format($history->previous_ctc, 0) }}</div>
                        <div class="fw-bold text-success">New: {{ get_currency_symbol() }}{{ number_format($history->new_ctc, 0) }}</div>
                        <small class="text-primary">
                            +{{ number_format($history->increment_percentage, 1) }}%
                        </small>
                    </td>
                    <td>
                        @if($history->previousDesignation && $history->newDesignation && $history->previousDesignation->id != $history->newDesignation->id)
                            <small>{{ $history->previousDesignation->position }}</small>
                            <i class="fa fa-arrow-right text-muted mx-1"></i>
                            <div class="fw-bold">{{ $history->newDesignation->position }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>
                        @if($history->status == 'approved')
                            <span class="badge bg-primary">Approved</span>
                        @elseif($history->status == 'processed')
                            <span class="badge bg-success">Processed</span>
                        @else
                            <span class="badge bg-warning">{{ ucfirst($history->status) }}</span>
                        @endif
                    </td>
                    <td>
                        <small class="text-muted">
                            By: {{ $history->creator->name ?? 'System' }}<br>
                            {{ $history->created_at->format('d M Y') }}
                        </small>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="alert alert-warning text-center">
        No increment history found for this employee.
    </div>
@endif
