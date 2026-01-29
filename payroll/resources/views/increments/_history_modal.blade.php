@php
    // Group increments by employee for history modals
    $employeeIncrements = [];
    foreach($increments as $inc) {
        if(!isset($employeeIncrements[$inc->employee_id])) {
            $employeeIncrements[$inc->employee_id] = [
                'employee' => $inc->employee,
                'increments' => []
            ];
        }
        $employeeIncrements[$inc->employee_id]['increments'][] = $inc;
    }
@endphp

<!-- Increment History Modals -->
@foreach($employeeIncrements as $empId => $empData)
<div class="modal custom-modal fade" id="history_{{ $empId }}" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white">
                    <i class="fa fa-history me-2"></i>
                    Increment History - {{ $empData['employee']->name ?? 'N/A' }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Employee ID:</strong> {{ $empData['employee']->unique_id ?? 'N/A' }} | 
                    <strong>Current Designation:</strong> {{ $empData['employee']->designationObj->position ?? 'N/A' }} |
                    <strong>Total Increments:</strong> {{ count($empData['increments']) }}
                </div>
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Effective Date</th>
                                <th>Type</th>
                                <th>From → To Designation</th>
                                <th>Previous CTC</th>
                                <th>New CTC</th>
                                <th>Increment</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Created At</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($empData['increments'] as $index => $history)
                            <tr class="{{ $loop->first ? 'table-success' : '' }}">
                                <td>
                                    @if($loop->first)
                                        <span class="badge bg-success">CURRENT</span>
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
                                    <small>
                                        {{ $history->previousDesignation->position ?? '-' }}
                                        @if($history->previousDesignation && $history->newDesignation && $history->previousDesignation->id != $history->newDesignation->id)
                                            <i class="fa fa-arrow-right text-success"></i>
                                            <strong>{{ $history->newDesignation->position }}</strong>
                                        @endif
                                    </small>
                                </td>
                                <td>₹{{ number_format($history->previous_ctc, 2) }}</td>
                                <td><strong class="text-success">₹{{ number_format($history->new_ctc, 2) }}</strong></td>
                                <td>
                                    <span class="badge bg-primary">
                                        +₹{{ number_format($history->increment_amount, 2) }}
                                        ({{ number_format($history->increment_percentage, 2) }}%)
                                    </span>
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
                                    <small>
                                        {{ $history->creator->name ?? 'System' }}
                                        @if($history->updated_by && $history->updated_by != $history->created_by)
                                            <br><em class="text-muted">Updated by: {{ $history->updater->name }}</em>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <small>
                                        {{ $history->created_at->format('d M Y H:i') }}
                                        @if($history->updated_at != $history->created_at)
                                            <br><em class="text-muted">Updated: {{ $history->updated_at->format('d M Y H:i') }}</em>
                                        @endif
                                    </small>
                                </td>
                                <td>
                                    <a href="#" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#view_increment_{{ $history->id }}">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endforeach
