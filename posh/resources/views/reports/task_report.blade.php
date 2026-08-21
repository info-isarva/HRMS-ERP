@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="{{asset('css/leads.css') }}">
<div class="container-fluid p-4">
    <div class="card mt-0">
        <div class="card-header">
            <h4>Task Report</h4>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('reports.task') }}" class="mb-4">
                <div class="row g-2 search-filters">
                    <div class="col-md-2">
                        <label for="filter" class="form-label">Filter</label>
                        <select name="filter" id="filter" class="form-select">
                            <option value="overdue" <?= (request('filter') == 'overdue') ? 'selected' : '' ?>>Overdue</option>
                            <option value="today" <?= (request('filter') == 'today') ? 'selected' : '' ?>>Today</option>
                            <option value="upcoming" <?= (request('filter') == 'upcoming') ? 'selected' : '' ?>>Upcoming</option>
                            <option value="completed" <?= (request('filter') == 'completed') ? 'selected' : '' ?>>Completed</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label for="assigned_to" class="form-label">Assigned To</label>
                        <select name="assigned_to" id="assigned_to" class="form-select">
                            <option value="">All Users</option>
                            @foreach(\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" {{ request('assigned_to') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-custom" style="padding: 6px 20px !important;">Generate Report</button>
                    </div>
                </div>
            </form>

            @if(isset($tasks) && !$tasks->isEmpty())
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Task Name</th>
                            <th>Organization</th>
                            <th>Type</th>
                            
                            <th>Created By</th>
                            <th>Assigned To</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($tasks as $task)
                            <tr>
                                <td>{{ $task->name }}</td>
                                <td>{{ $task->organization_name }}</td>
                                <td>{{ ($task->related_type === 'deal') ? 'Deals' : (($task->related_type === 'lead') ? 'Leads' : 'Other') }}</td>
                                <td>{{ \App\Models\User::find($task->user_owner_id)->name ?? 'Unknown' }}</td>
                                <td>{{ \App\Models\User::find($task->user_assigned_id)->name ?? 'Unknown' }}</td>
                                <td>{{ \Carbon\Carbon::parse($task->due_at)->format('d-m-Y') }}</td>
                                <td>
                                 @php
                                    $status = $task->status ?? '-';
                                    $stageColors = [
                                        'In Progress' => 'bg-primary',
                                        'Completed' => 'bg-success-subtle text-success',
                                        'Not Started' => 'bg-danger-subtle text-danger',
                                        'Waiting for input' => 'bg-warning-subtle text-warning',
                                    ];
                                    $color = $stageColors[$status] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $color }}">{{ ucfirst($status) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pagination-info">
                    <div class="small text-muted">Showing {{ $tasks->firstItem() ?? 0 }} to {{ $tasks->lastItem() ?? 0 }} of {{ $tasks->total() }} tasks</div>
                    <div class="pagination-custom text-center my-3">
                        <nav aria-label="Tasks pagination">
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
            @else
                <p>No tasks found for the selected criteria.</p>
            @endif
        </div>
    </div>
</div>

<style>
    @media (max-width: 1320px) {
        .table {
            font-size: 0.9rem;
        }

        .table thead {
            display: none;
        }

        .table tbody tr {
            display: flex;
            flex-direction: column;
            margin-bottom: 1rem;
            border: 1px solid #ddd;
            padding: 0.5rem;
        }

        .table tbody tr td {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
        }

        .table tbody tr td::before {
            content: attr(data-label);
            font-weight: bold;
            margin-right: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .btn {
            width: 100%;
        }
    }

    .search-filters .form-control, .search-filters .form-select, .search-filters .btn {
        height: calc(2.75rem + 2px); /* Matches default Bootstrap input height */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');

        startDateInput.addEventListener('change', function() {
            const startDate = new Date(this.value);
            if (endDateInput) {
                endDateInput.min = startDate.toISOString().split('T')[0];
            }
        });

        const tableRows = document.querySelectorAll('.table tbody tr');
        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            cells.forEach((cell, index) => {
                const header = document.querySelector(`.table thead th:nth-child(${index + 1})`);
                if (header) {
                    cell.setAttribute('data-label', header.textContent.trim());
                }
            });
        });
    });
</script>
@endsection