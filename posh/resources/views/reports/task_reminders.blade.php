@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <h1 class="mb-4">Reminder Reports</h1>

    <!-- Date Filter Form -->
    <form method="GET" action="{{ route('task-reports.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-4">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            <div class="col-md-4 d-flex align-items-end mt-3">
                <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Filter</button>
            </div>
        </div>
    </form>

    <!-- Task Reminder Table -->
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Company</th>
                    <th>Contact Person</th>
                    <th>Owner</th>
                    <th>Due Date</th>
                    <th>Reminder Time</th>
                    <th>Email Sent</th>
                    <th>Notification Sent</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tasks as $task)
                    @foreach($task->reminders as $reminder)
                        <tr>
                            <td data-label="Task">{{ $task->name }}</td>
                            @php
                                $contact = null;
                                if ($task->related_type === 'lead') {
                                    $lead = \App\Models\Lead::find($task->related_id);
                                    $organization = $lead ? $lead->organization->name : null;
                                    $contact = $lead && $lead->person ? $lead->person->first_name . ' ' . $lead->person->last_name : '';
                                } elseif ($task->related_type === 'deal') {
                                    $deal = \App\Models\Deal::find($task->related_id);
                                    $organization = $deal ? $deal->organization->name : null;
                                    $contact = $deal && $deal->person ? $deal->person->first_name . ' ' . $deal->person->last_name : '';
                                }
                            @endphp
                            <td data-label="Company">{{  $organization ?? 'N/A' }}</td>   
                            <td data-label="Contact Person"> {{ $contact ?? '' }}</td>
                            <td data-label="Owner">{{ $task->owner->name ?? 'N/A' }}</td>
                            <td data-label="Due Date">{{ $task->due_at ? \Carbon\Carbon::parse($task->due_at)->format('d-m-Y H:i') : 'N/A' }}</td>
                            <td data-label="Reminder Time">{{ $reminder->remind_at ? \Carbon\Carbon::parse($reminder->remind_at)->format('d-m-Y H:i') : 'N/A' }}</td>
                            <td data-label="Email Sent">
                                @if($reminder->email_sent)
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                            </td>
                            <td data-label="Notification Sent">
                                @if($reminder->notification_sent)
                                    <i class="fas fa-check-circle text-success"></i>
                                @else
                                    <i class="fas fa-times-circle text-danger"></i>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No reminders found for the selected date range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination Links -->

    <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pagination-info">
        <div class="small text-muted">Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} reminders</div>
        <div class="pagination-custom text-center my-3">
            <nav aria-label="Calls pagination">
                <ul class="pagination justify-content-center gap-3 mb-0">
                    <li class="page-item {{ $tasks->onFirstPage() ? 'disabled' : '' }}">
                        <a class="page-link" href="{{ $tasks->previousPageUrl() ?: '#' }}" tabindex="-1" aria-disabled="{{ $tasks->onFirstPage() ? 'true' : 'false' }}">&laquo; Previous</a>
                    </li>
                    <li class="page-item {{ $tasks->hasMorePages() ? '' : 'disabled' }}">
                        <a class="page-link" href="{{ $tasks->nextPageUrl() ?: '#' }}" aria-disabled="{{ $tasks->hasMorePages() ? 'false' : 'true' }}">Next &raquo;</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
   
</div>
@endsection