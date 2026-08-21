@extends('layouts.app')


@section('content')
    <!-- Dashboard-2 custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/dashboard-2.css') }}">
    <div class="container-fluid p-4">
        <div class="row g-4 mb-2">
            @php
                $cards = [
                    [
                        'title' => 'Leads',
                        'value' => $totalLeads,
                        'subtitle' => 'Total Leads',
                        'border' => '#0aa8b1',
                        'icon' => 'bi-people-fill',
                        'link' => route('leads.index'),
                    ],
                    [
                        'title' => 'Deals',
                        'value' => $totalDeals,
                        'subtitle' => 'Total Deals',
                        'border' => '#f79d24',
                        'icon' => 'bi-briefcase-fill',
                        'link' => route('deals.index'),
                    ],
                    [
                        'title' => 'Deals Created This Month',
                        'value' => $dealsCreatedThisMonth,
                        'subtitle' => isset($dealsCreatedChangePercent)
                            ? ($dealsCreatedChangePercent > 0
                                ? "+{$dealsCreatedChangePercent}% vs last month"
                                : ($dealsCreatedChangePercent < 0
                                    ? "{$dealsCreatedChangePercent}% vs last month"
                                    : 'No change vs last month'))
                            : 'No data',
                        'border' => '#44b451',
                        'icon' => 'bi-calendar-plus',
                        'link' => route('deals.index', ['created_this_month' => 1]),
                    ],
                    [
                        'title' => 'Deals Closing This Month',
                        'value' => $dealsClosingThisMonth,
                        'subtitle' => isset($dealsClosingChangePercent)
                            ? ($dealsClosingChangePercent > 0
                                ? "+{$dealsClosingChangePercent}% vs last month"
                                : ($dealsClosingChangePercent < 0
                                    ? "{$dealsClosingChangePercent}% vs last month"
                                    : 'No change vs last month'))
                            : 'No data',
                        'border' => '#b84651',
                        'icon' => 'bi-calendar-event',
                        'link' => route('deals.index', ['closing_this_month' => 1]),
                    ],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="col-12 col-sm-6 col-md-6 col-lg-6 col-xl-3">
                    <a href="{{ $card['link'] }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm rounded-4 h-100 position-relative hover-scale stat-card"
                            style="border-left:6px solid {{ $card['border'] }} !important;">
                            <div class="stat-content p-3">
                                <h6 class="fw-semibold mb-1">{{ $card['title'] }}</h6>
                                <div class="stat-value">{{ $card['value'] }}</div>
                                <div class="stat-subtitle" style="color:  {{ $card['border'] }};"><i
                                        class="bi {{ $card['icon'] }}" style="color:  {{ $card['border'] }};"></i>
                                    {{ $card['subtitle'] }}</div>
                            </div>
                            <div class="stat-icon" style="background: {{ $card['border'] }};">
                                <i class="bi {{ $card['icon'] }}"></i>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
        @include('dashboard.user_sales_widget')

        <div class="row mb-4">
            <div class="col-md-12">
                @php
                    $pinnedNotes = \App\Models\Note::where('pinned', 1)
                        ->where('created_by', auth()->id())
                        ->whereDate('noted_at', '>=', now()->toDateString())
                        ->orderByDesc('noted_at')
                        ->limit(5)
                        ->get();
                @endphp
                @if (!$pinnedNotes->isEmpty())
                    <div class="alert alert-warning d-flex align-items-center mb-4"
                        style="background:#fffbe6; border:1px solid #ffe58f;">

                        <div class="flex-grow-1">
                            <strong><i class="bi bi-pin-angle-fill me-2 text-warning" style="font-size:1.3rem;"></i> Pinned
                                Notes:</strong>
                            <ul class="mb-0 ps-3" style="list-style: disc;">
                                @foreach ($pinnedNotes as $note)
                                    <li>
                                        <span class="fw-semibold">{{ $note->content }}</span>
                                        @php
                                            $relatedName = null;
                                            $companyName = null;
                                            if ($note->related_type === 'lead') {
                                                $lead = \App\Models\Lead::find($note->related_id);
                                                if ($lead) {
                                                    $relatedName = $lead->title;
                                                    $companyName = optional($lead->organization)->name;
                                                }
                                            } elseif ($note->related_type === 'deal') {
                                                $deal = \App\Models\Deal::find($note->related_id);
                                                if ($deal) {
                                                    $relatedName = $deal->title;
                                                    $companyName = optional($deal->organization)->name;
                                                }
                                            }
                                        @endphp
                                        <span class="text-muted small">
                                            @if ($relatedName)
                                                | <span class="fw-bold">
                                                    @if ($note->related_type === 'lead')
                                                        <a href="{{ route('leads.show', $note->related_id) }}"
                                                            class="text-decoration-underline text-dark">{{ $relatedName }}</a>
                                                    @elseif ($note->related_type === 'deal')
                                                        <a href="{{ route('deals.show', $note->related_id) }}"
                                                            class="text-decoration-underline text-dark">{{ $relatedName }}</a>
                                                    @else
                                                        {{ $relatedName }}
                                                    @endif
                                                </span>
                                            @endif
                                            @if ($companyName)
                                                ({{ $companyName }})
                                            @endif
                                            {{-- ({{ $note->noted_at ? \Carbon\Carbon::parse($note->noted_at)->format('d M Y H:i') :
                                            $note->created_at->format('d M Y H:i') }})
                                            @if ($note->created_by)
                                            | {{ optional(\App\Models\User::find($note->created_by))->name }}
                                            @endif --}}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Analytics Graphs Section -->
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm p-3 dashboard-chart-card ">
                    <h6 class="fw-bold mb-3"><i class="bi bi-funnel-fill card-heading-icon"></i> Leads by Source
                        (Current Month)</h6>
                    <div class="card-content w-100">
                        <canvas id="leadsBySourceChart" data-height="320" height="320"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm p-3 dashboard-chart-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-graph-up card-heading-icon"></i> Leads & Deals (Last 6
                        Months)</h6>
                    <div class="card-content w-100">
                        <canvas id="combinedChart" data-height="320" height="320"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Targets Dashboard (Admin Only) -->
        @if (($user->crm_role_type === 0 || $user->crm_role_type === 1) && $employeeTargets)
            <div class="row g-4 mb-4">
                <div class="col-12 col-lg-6">
                    <div class="card p-4 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="fw-bold"><i class="bi bi-people-fill card-heading-icon"></i> &nbsp; Employee Targets
                            </div>
                            <div>{{ $monthName = \Carbon\Carbon::now()->month((int) $month)->format('F Y') }}</div>
                        </div>

                        @if (count($employeeTargets) > 0)
                            <!-- Desktop View (Table) -->
                            <div class="table-responsive d-none d-md-block">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr class="text-muted small">
                                            <th
                                                style="width: 30%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Employee Name</th>
                                            <th
                                                style="width: 20%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Target Amount</th>
                                            <th
                                                style="width: 20%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Achieved Sales</th>
                                            <th
                                                style="width: 15%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Progress %</th>
                                            <th
                                                style="width: 15%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Progress Bar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employeeTargets as $emp)
                                            <tr>
                                                <td class="fw-500"
                                                    style="width: 30%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $emp['name'] }}</td>
                                                <td class="fw-500"
                                                    style="width: 20%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $currency_symbol }} {{ number_format($emp['target_amount'], 2) }}
                                                </td>
                                                <td class="fw-500"
                                                    style="width: 20%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    {{ $currency_symbol }} {{ number_format($emp['achieved_sales'], 2) }}
                                                </td>
                                                <td class="fw-bold"
                                                    style="width: 15%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    @if ($emp['target_amount'] > 0)
                                                        <span
                                                            class="@if ($emp['progress'] >= 100) text-success @elseif($emp['progress'] >= 75) text-info @elseif($emp['progress'] >= 50) text-warning @else text-danger @endif">
                                                            {{ $emp['progress'] }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td
                                                    style="width: 15%; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    <div class="progress" style="height: 20px; background: #f0f0f0;">
                                                        <div class="progress-bar @if ($emp['progress'] >= 100) bg-success @elseif($emp['progress'] >= 75) bg-info @elseif($emp['progress'] >= 50) bg-warning @else bg-danger @endif"
                                                            role="progressbar"
                                                            style="width: {{ min($emp['progress'], 100) }}%;"
                                                            aria-valuenow="{{ $emp['progress'] }}" aria-valuemin="0"
                                                            aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Mobile View (Card Layout) -->
                            <div class="d-md-none">
                                @foreach ($employeeTargets as $emp)
                                    <div class="border rounded p-3 mb-3" style="background: #f9f9f9;">
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="text-muted small mb-1"><strong>Employee</strong></p>
                                                <p class="mb-0 fw-500 small">{{ $emp['name'] }}</p>
                                            </div>
                                            <div class="col-6 text-end">
                                                <p class="text-muted small mb-1"><strong>Progress</strong></p>
                                                <p class="mb-0 fw-bold small">
                                                    @if ($emp['target_amount'] > 0)
                                                        <span
                                                            class="@if ($emp['progress'] >= 100) text-success @elseif($emp['progress'] >= 75) text-info @elseif($emp['progress'] >= 50) text-warning @else text-danger @endif">
                                                            {{ $emp['progress'] }}%
                                                        </span>
                                                    @else
                                                        <span class="text-muted">N/A</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <p class="text-muted small mb-1"><strong>Target</strong></p>
                                                <p class="mb-0 fw-500 small">{{ $currency_symbol }}
                                                    {{ number_format($emp['target_amount'], 2) }}
                                                </p>
                                            </div>
                                            <div class="col-6 text-end">
                                                <p class="text-muted small mb-1"><strong>Achieved</strong></p>
                                                <p class="mb-0 fw-500 small">{{ $currency_symbol }}
                                                    {{ number_format($emp['achieved_sales'], 2) }}
                                                </p>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 18px; background: #e9ecef;">
                                            <div class="progress-bar @if ($emp['progress'] >= 100) bg-success @elseif($emp['progress'] >= 75) bg-info @elseif($emp['progress'] >= 50) bg-warning @else bg-danger @endif"
                                                role="progressbar" style="width: {{ min($emp['progress'], 100) }}%;"
                                                aria-valuenow="{{ $emp['progress'] }}" aria-valuemin="0"
                                                aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <p class="text-muted">No employee targets data available.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="card p-4 border-0 shadow-sm h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold"><i class="bi bi-envelope card-heading-icon"></i>&nbsp;Today's Task
                                Reminder
                                Emails</span>
                        </div>

                        <div>
                            @if ($todaysTaskReminders->isEmpty())
                                <div class="d-flex flex-column align-items-center justify-content-start">
                                    <img src="https://cdn-icons-png.flaticon.com/512/747/747310.png" alt="No Reminders"
                                        style="width:90px;opacity:0.15;">
                                    <div class="mt-2 text-muted">No Task Reminders for today.</div>
                                </div>
                            @else
                                <div class="table-responsive d-none d-md-block">
                                    <table class="table table-border align-middle mb-0"
                                        style="table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Task</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Company</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Contact Person</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Due Date</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Reminder Time</th>
                                                <!-- <th style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">Owner</th> -->

                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($todaysTaskReminders as $task)
                                                @foreach ($task->reminders as $reminder)
                                                    <tr>
                                                        <td><a href="{{ route('leads.show', $task->related_id) }}"
                                                                class="text-decoration-underline">{{ $task->name }}</a>
                                                        </td>
                                                        @php
                                                            $contact = null;
                                                            if ($task->related_type === 'lead') {
                                                                $lead = \App\Models\Lead::find($task->related_id);
                                                                $organization = $lead
                                                                    ? $lead->organization->name
                                                                    : null;
                                                                $contact =
                                                                    $lead && $lead->person
                                                                        ? $lead->person->first_name .
                                                                            ' ' .
                                                                            $lead->person->last_name
                                                                        : '';
                                                            } elseif ($task->related_type === 'deal') {
                                                                $deal = \App\Models\Deal::find($task->related_id);
                                                                $organization = $deal
                                                                    ? $deal->organization->name
                                                                    : null;
                                                                $contact =
                                                                    $deal && $deal->person
                                                                        ? $deal->person->first_name .
                                                                            ' ' .
                                                                            $deal->person->last_name
                                                                        : '';
                                                            }
                                                        @endphp
                                                        <td>{{ $organization ?? 'N/A' }}</td>
                                                        <td> {{ $contact ?? '' }}</td>
                                                        <td>{{ $task->due_at ? \Carbon\Carbon::parse($task->due_at)->format('d-m-Y H:i') : 'N/A' }}
                                                        </td>
                                                        <td>{{ $reminder->remind_at ? \Carbon\Carbon::parse($reminder->remind_at)->format('d-m-Y H:i') : 'N/A' }}
                                                        </td>
                                                        <!-- <td>{{ $task->owner->name ?? 'N/A' }}</td> -->


                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Mobile View (Card Layout) -->
                                <div class="d-md-none">
                                    @foreach ($todaysTaskReminders as $task)
                                        @foreach ($task->reminders as $reminder)
                                            <div class="border rounded p-3 mb-3"
                                                style="background: #f9f9f9; width: 100%;">
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1"><strong>Task</strong></p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="mb-0 fw-500 small"><a
                                                                href="{{ route('leads.show', $task->related_id) }}"
                                                                class="text-decoration-underline">{{ $task->name }}</a>
                                                        </p>
                                                    </div>
                                                </div>
                                                @php
                                                    $contact = null;
                                                    if ($task->related_type === 'lead') {
                                                        $lead = \App\Models\Lead::find($task->related_id);
                                                        $organization = $lead ? $lead->organization->name : null;
                                                        $contact =
                                                            $lead && $lead->person
                                                                ? $lead->person->first_name .
                                                                    ' ' .
                                                                    $lead->person->last_name
                                                                : '';
                                                    } elseif ($task->related_type === 'deal') {
                                                        $deal = \App\Models\Deal::find($task->related_id);
                                                        $organization = $deal ? $deal->organization->name : null;
                                                        $contact =
                                                            $deal && $deal->person
                                                                ? $deal->person->first_name .
                                                                    ' ' .
                                                                    $deal->person->last_name
                                                                : '';
                                                    }
                                                @endphp


                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1"><strong>Company</strong></p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="mb-0 fw-500 small">{{ $organization ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1"><strong>Contact Person</strong>
                                                        </p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="mb-0 fw-500 small">{{ $contact ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1"><strong>Due Date</strong></p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="mb-0 fw-bold small">
                                                            {{ $task->due_at ? \Carbon\Carbon::parse($task->due_at)->format('d-m-Y H:i') : 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="row mb-2">
                                                    <div class="col-6">
                                                        <p class="text-muted small mb-1"><strong>Reminder Time</strong></p>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <p class="mb-0 fw-500 small">
                                                            {{ $reminder->remind_at ? \Carbon\Carbon::parse($reminder->remind_at)->format('d-m-Y H:i') : 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                                <!-- <div class="row mb-2">
                                                                        <div class="col-6">
                                                                            <p class="text-muted small mb-1"><strong>Owner</strong></p>
                                                                        </div>
                                                                        <div class="col-6 text-end">
                                                                            <p class="mb-0 fw-500 small">{{ $task->owner->name ?? 'N/A' }}</p>
                                                                        </div>
                                                                    </div> -->


                                            </div>
                                        @endforeach
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row g-4 mb-0">
            <div class="col-md-6">
                <div class="card p-4 border-0 shadow-sm h-100 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold"><i class="bi bi-list-task card-heading-icon"></i>&nbsp;
                                {{ $user->crm_role_type === 1 || $user->crm_role_type === 2 ? 'All Open Tasks' : 'My Open Tasks' }}
                            </span>
                            {{-- <span class="ms-auto"><i class="bi bi-arrow-clockwise" style="cursor:pointer;"></i></span>
                                <span class="dropdown ms-2">
                                    <i class="bi bi-three-dots-vertical" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#">Refresh</a></li>
                                        <li><a class="dropdown-item" href="#">Settings</a></li>
                                    </ul>
                                </span> --}}
                        </div>
                        <div class="d-flex flex-column align-items-center justify-content-start">
                            @if ($tasks->isEmpty())
                                <img src="https://cdn-icons-png.flaticon.com/512/747/747310.png" alt="No Tasks"
                                    style="width:90px;opacity:0.15;">
                                <div class="mt-2 text-muted">No Tasks found.</div>
                            @else
                                <div class="table-responsive w-100">
                                    <table class="table table-border align-middle mb-0"
                                        style="table-layout: fixed; width: 100%;">
                                        <thead>
                                            <tr class="text-muted small">
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Subject</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Due Date</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Status</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Priority</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Related To</th>
                                                <th
                                                    style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                    Contact Name</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tasks as $task)
                                                @if (!$task->completed_at)
                                                    <tr>
                                                        {{-- <td
                                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                                {{ $task->title ?? $task->name }}</td> --}}
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Subject">
                                                            <a href="{{ route('tasks.show', $task->id) }}"
                                                                class="text-decoration-none text-primary">{{ $task->title ?? $task->name }}</a>
                                                        </td>
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Due Date">
                                                            {{ $task->due_at ? \Carbon\Carbon::parse($task->due_at)->format('d M Y') : '' }}
                                                        </td>
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Status">{{ $task->status }}</td>
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Priority">{{ ucfirst($task->priority) }}</td>
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Related To">
                                                            @if ($task->related_type === 'lead')
                                                                <a href="{{ route('leads.show', $task->related_id) }}"
                                                                    class="text-decoration-underline">Lead</a>
                                                            @elseif($task->related_type === 'deal')
                                                                <a href="{{ route('deals.show', $task->related_id) }}"
                                                                    class="text-decoration-underline">Deal</a>
                                                            @else
                                                                {{ ucfirst($task->related_type) }}
                                                            @endif
                                                        </td>
                                                        <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                            data-label="Contact Name">
                                                            @php
                                                                $contact = null;
                                                                if ($task->related_type === 'lead') {
                                                                    $lead = \App\Models\Lead::find($task->related_id);
                                                                    $contact =
                                                                        $lead && $lead->person
                                                                            ? $lead->person->first_name .
                                                                                ' ' .
                                                                                $lead->person->last_name
                                                                            : '';
                                                                } elseif ($task->related_type === 'deal') {
                                                                    $deal = \App\Models\Deal::find($task->related_id);
                                                                    $contact =
                                                                        $deal && $deal->person
                                                                            ? $deal->person->first_name .
                                                                                ' ' .
                                                                                $deal->person->last_name
                                                                            : '';
                                                                }
                                                            @endphp
                                                            {{ $contact ?? '' }}
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                    @if (!$tasks->isEmpty())
                        @if ($tasks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="small text-muted">Showing {{ $tasks->firstItem() ?? 0 }} to
                                    {{ $tasks->lastItem() ?? 0 }} of
                                    {{ $tasks->total() }} tasks
                                </div>
                                <div class="pagination-custom text-center my-3">
                                    <nav aria-label="Leads pagination">
                                        <ul class="pagination justify-content-center gap-3 mb-0">
                                            <li class="page-item {{ $tasks->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $tasks->previousPageUrl() ?: '#' }}"
                                                    tabindex="-1"
                                                    aria-disabled="{{ $tasks->onFirstPage() ? 'true' : 'false' }}">&laquo;</a>
                                            </li>
                                            <li class="page-item {{ $tasks->hasMorePages() ? '' : 'disabled' }}">
                                                <a class="page-link" href="{{ $tasks->nextPageUrl() ?: '#' }}"
                                                    aria-disabled="{{ $tasks->hasMorePages() ? 'false' : 'true' }}">&raquo;</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold"><i class="bi bi-calendar-event card-heading-icon"></i>
                            &nbsp;{{ $user->crm_role_type === 1 || $user->crm_role_type === 2 ? 'All Meetings' : 'My Meetings' }}</span>
                        {{-- <span class="ms-auto"><i class="bi bi-arrow-clockwise" style="cursor:pointer;"></i></span>
                            <span class="dropdown ms-2">
                                <i class="bi bi-three-dots-vertical" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Refresh</a></li>
                                    <li><a class="dropdown-item" href="#">Settings</a></li>
                                </ul>
                            </span> --}}
                    </div>
                    <div class="d-flex flex-column align-items-center justify-content-start">
                        @if ($meetings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Title</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                From</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                To</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Related To</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Contact Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($meetings as $meeting)
                                            <tr>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Title"><a class="text-decoration-none text-primary"
                                                        href="{{ route('meetings.show', $meeting->id) }}">{{ $meeting->name }}</a>
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="From">
                                                    {{ $meeting->start_at ? \Carbon\Carbon::parse($meeting->start_at)->format('d M Y H:i') : '' }}
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="To">
                                                    {{ $meeting->finish_at ? \Carbon\Carbon::parse($meeting->finish_at)->format('d M Y H:i') : '' }}
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Related To">{{ $meeting->related_type ?? '' }}</td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Contact Name">@php
                                                        $contact = null;
                                                        if ($meeting->related_type === 'lead') {
                                                            $lead = \App\Models\Lead::find($meeting->related_id);
                                                            $contact =
                                                                $lead && $lead->person
                                                                    ? $lead->person->first_name .
                                                                        ' ' .
                                                                        $lead->person->last_name
                                                                    : '';
                                                        } elseif ($meeting->related_type === 'deal') {
                                                            $deal = \App\Models\Deal::find($meeting->related_id);
                                                            $contact =
                                                                $deal && $deal->person
                                                                    ? $deal->person->first_name .
                                                                        ' ' .
                                                                        $deal->person->last_name
                                                                    : '';
                                                        }
                                                    @endphp
                                                    {{ $contact ?? '' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No meetings found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Title</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                From</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                To</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Related To</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Contact Name</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($meetings as $meeting)
                                            <tr>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Title">{{ $meeting->name }}</td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="From">
                                                    {{ $meeting->start_at ? \Carbon\Carbon::parse($meeting->start_at)->format('d M Y H:i') : '' }}
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="To">
                                                    {{ $meeting->finish_at ? \Carbon\Carbon::parse($meeting->finish_at)->format('d M Y H:i') : '' }}
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Related To">{{ $meeting->related_type ?? '' }}</td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Contact Name">@php
                                                        $contact = null;
                                                        if ($meeting->related_type === 'lead') {
                                                            $lead = \App\Models\Lead::find($meeting->related_id);
                                                            $contact =
                                                                $lead && $lead->person
                                                                    ? $lead->person->first_name .
                                                                        ' ' .
                                                                        $lead->person->last_name
                                                                    : '';
                                                        } elseif ($meeting->related_type === 'deal') {
                                                            $deal = \App\Models\Deal::find($meeting->related_id);
                                                            $contact =
                                                                $deal && $deal->person
                                                                    ? $deal->person->first_name .
                                                                        ' ' .
                                                                        $deal->person->last_name
                                                                    : '';
                                                        }
                                                    @endphp
                                                    {{ $contact ?? '' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No meetings found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    @if (!$meetings->isEmpty())
                        @if ($meetings instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="small text-muted">Showing {{ $meetings->firstItem() ?? 0 }} to
                                    {{ $meetings->lastItem() ?? 0 }} of {{ $meetings->total() }} meetings
                                </div>
                                <div class="pagination-custom text-center my-3">
                                    <nav aria-label="Leads pagination">
                                        <ul class="pagination justify-content-center gap-3 mb-0">
                                            <li class="page-item {{ $meetings->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $meetings->previousPageUrl() ?: '#' }}"
                                                    tabindex="-1"
                                                    aria-disabled="{{ $meetings->onFirstPage() ? 'true' : 'false' }}">&laquo;</a>
                                            </li>
                                            <li class="page-item {{ $meetings->hasMorePages() ? '' : 'disabled' }}">
                                                <a class="page-link" href="{{ $meetings->nextPageUrl() ?: '#' }}"
                                                    aria-disabled="{{ $meetings->hasMorePages() ? 'false' : 'true' }}">&raquo;</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 border-0 shadow-sm h-100 d-flex flex-column justify-content-between">

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold"><i class="bi bi-briefcase-fill card-heading-icon"></i>
                            &nbsp;{{ $user->crm_role_type === 1 || $user->crm_role_type === 2 ? 'Deals Closing This Month' : 'My Deals Closing This Month' }}</span>
                        {{-- <span class="ms-auto"><i class="bi bi-arrow-clockwise" style="cursor:pointer;"></i></span>
                            <span class="dropdown ms-2">
                                <i class="bi bi-three-dots-vertical" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item @if (request('deals_closing_filter') === 'today') active @endif"
                                            href="{{ request()->fullUrlWithQuery(['deals_closing_filter' => 'today']) }}">Today
                                            Closing</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item @if (request('deals_closing_filter') === 'week') active @endif"
                                            href="{{ request()->fullUrlWithQuery(['deals_closing_filter' => 'week']) }}">This
                                            Week</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item @if (!request()->has('deals_closing_filter') || request('deals_closing_filter') === 'month') active @endif"
                                            href="{{ request()->fullUrlWithQuery(['deals_closing_filter' => 'month']) }}">This
                                            Month</a>
                                    </li>
                                </ul>
                            </span> --}}
                    </div>
                    <div class="d-flex flex-column align-items-center justify-content-start">
                        @if ($dealsClosingThisMonthList instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Deal Name</th>
                                            <th
                                                style="width: 90px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Amount</th>
                                            <th
                                                style="width: 110px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Stage</th>
                                            <th
                                                style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Closing Date</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Company</th>
                                            <!-- <th style="width: 130px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">Contact Name</th> -->
                                            <th
                                                style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Deal Owner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($dealsClosingThisMonthList as $deal)
                                            <tr>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Deal Name"><a href="{{ route('deals.show', $deal->id) }}"
                                                        class="text-decoration-none">{{ $deal->person->first_name ?? '' }}
                                                        {{ $deal->person->last_name ?? '' }}</a></td>
                                                <td style="width: 90px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Amount">
                                                    {{ \App\Helpers\MoneyFormatter::format($deal->amount ?? 0) }}
                                                </td>
                                                <td style="width: 110px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Stage">{{ $deal->stage ?? '' }}</td>
                                                <td style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Closing Date">
                                                    {{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('d M Y') : '' }}
                                                </td>
                                                <td style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Company">{{ $deal->organization->name ?? '' }}</td>
                                                <!-- <td style="width: 130px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;" data-label="Contact Name">{{ $deal->person->first_name ?? '' }} {{ $deal->person->last_name ?? '' }}</td> -->
                                                <td style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;"
                                                    data-label="Deal Owner">{{ $deal->owner->name ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No deals closing this
                                                    month found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Deal Name</th>
                                            <th
                                                style="width: 90px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Amount</th>
                                            <th
                                                style="width: 110px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Stage</th>
                                            <th
                                                style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Closing Date</th>
                                            <th
                                                style="width: 140px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Company</th>
                                            <th
                                                style="width: 130px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Contact Name</th>
                                            <th
                                                style="width: 120px; white-space:nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                Deal Owner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {{-- @forelse($dealsClosingThisMonthList as $deal)
                                            <tr>
                                                <td>{{ $deal->title ?? $deal->name }}</td>
                                                <td>{{ $deal->amount }}</td>
                                                <td>{{ $deal->stage ?? '' }}</td>
                                                <td>{{ $deal->close_date ? \Carbon\Carbon::parse($deal->close_date)->format('d M Y')
                                                    :
                                                    '' }}</td>
                                                <td>{{ $deal->organization->name ?? '' }}</td>
                                                <td>{{ $deal->person->first_name ?? '' }} {{ $deal->person->last_name ?? '' }}</td>
                                                <td>{{ $deal->owner->name ?? '' }}</td>
                                            </tr>
                                            @empty --}}
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No deals closing this month
                                                found.
                                            </td>
                                        </tr>
                                        {{-- @endforelse --}}
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                    @if ($dealsClosingThisMonthList instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <div class="d-flex justify-content-between align-items-center mt-1">
                            <div class="small text-muted">Showing {{ $dealsClosingThisMonthList->firstItem() ?? 0 }} to
                                {{ $dealsClosingThisMonthList->lastItem() ?? 0 }} of
                                {{ $dealsClosingThisMonthList->total() }}
                                deals
                            </div>
                            <div class="pagination-custom text-center my-3">
                                <nav aria-label="Leads pagination">
                                    <ul class="pagination justify-content-center gap-3 mb-0">
                                        <li
                                            class="page-item {{ $dealsClosingThisMonthList->onFirstPage() ? 'disabled' : '' }}">
                                            <a class="page-link"
                                                href="{{ $dealsClosingThisMonthList->previousPageUrl() ?: '#' }}"
                                                tabindex="-1"
                                                aria-disabled="{{ $dealsClosingThisMonthList->onFirstPage() ? 'true' : 'false' }}">&laquo;</a>
                                        </li>
                                        <li
                                            class="page-item {{ $dealsClosingThisMonthList->hasMorePages() ? '' : 'disabled' }}">
                                            <a class="page-link"
                                                href="{{ $dealsClosingThisMonthList->nextPageUrl() ?: '#' }}"
                                                aria-disabled="{{ $dealsClosingThisMonthList->hasMorePages() ? 'false' : 'true' }}">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4 border-0 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-bold"><i class="bi bi-person-plus card-heading-icon"></i> &nbsp;
                            {{ $user->crm_role_type === 1 || $user->crm_role_type === 2 ? 'Today`s Leads' : 'My Today`s Leads' }}</span>
                        {{-- <span class="ms-auto"><i class="bi bi-arrow-clockwise" style="cursor:pointer;"></i></span>
                            <span class="dropdown ms-2">
                                <i class="bi bi-three-dots-vertical" style="cursor:pointer;" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#">Refresh</a></li>
                                    <li><a class="dropdown-item" href="#">Settings</a></li>
                                </ul>
                            </span> --}}
                    </div>
                    <div class="d-flex flex-column align-items-center justify-content-start">
                        @if ($leadsToday instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                            <!-- <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"> Title</th> -->
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Lead
                                                Name
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                Company
                                            </th>

                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Email
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Phone
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Lead
                                                Source
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Lead
                                                Owner
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leadsToday as $lead)
                                            <tr>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Lead Name"><a
                                                        href="{{ route('leads.show', $lead->id) }}"
                                                        class="text-decoration-none text-primary">{{ $lead->person->first_name ?? '' }}</a>
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Company">{{ $lead->organization->name ?? '' }}</td>
                                                <!-- <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                                    data-label="Contact">{{ $lead->person->first_name ?? '' }}</td> -->
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Email">{{ $lead->person->email ?? '' }}</td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Phone">{{ $lead->person->mobile ?? '' }}</td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Lead Source">
                                                    {{ $lead->leadSource->name ?? ($lead->lead_source ?? '') }}
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"
                                                    data-label="Lead Owner">{{ $lead->owner->name ?? '' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No leads found for
                                                    today.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <!-- <div class="d-flex justify-content-between align-items-center" style="gap:15rem;">
                                            <div class="small text-muted mb-1">
                                                Showing {{ $leadsToday->firstItem() ?? 0 }}
                                                to {{ $leadsToday->lastItem() ?? 0 }}
                                                of {{ $leadsToday->total() }} leads
                                            </div>
                                            <div class="mt-2">{{ $leadsToday->links('vendor.pagination.arrows-only') }}</div>
                                        </div> -->
                        @else
                            <div class="table-responsive w-100">
                                <table class="table table-border align-middle mb-0">
                                    <thead>
                                        <tr class="text-muted small">
                                        <tr class="text-muted small">
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;"> Lead
                                                Name
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                Company
                                            </th>
                                            <!-- <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Contact</th> -->
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Email
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Phone
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Lead
                                                Source
                                            </th>
                                            <th style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">Lead
                                                Owner
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($leadsToday as $lead)
                                            <tr>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    <a href="{{ route('leads.show', $lead->id) }}"
                                                        class="text-decoration-underline text-primary">
                                                        {{ $lead->person->first_name ?? '' }}</a>
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    {{ $lead->organization->name ?? '' }}
                                                </td>
                                                <!-- <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                                    {{ $lead->person->first_name ?? '' }}</td> -->
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    {{ $lead->person->email ?? '' }}
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    {{ $lead->person->mobile ?? '' }}
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    {{ $lead->leadSource->name ?? ($lead->lead_source ?? '') }}
                                                </td>
                                                <td style="white-space:nowrap;  overflow:hidden; text-overflow:ellipsis;">
                                                    {{ $lead->owner->name ?? '' }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No leads found for
                                                    today.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    @if (!$leadsToday->isEmpty())
                        @if ($leadsToday instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <div class="small text-muted">Showing {{ $leadsToday->firstItem() ?? 0 }} to
                                    {{ $leadsToday->lastItem() ?? 0 }} of {{ $leadsToday->total() }} leads
                                </div>
                                <div class="pagination-custom text-center my-3">
                                    <nav aria-label="Leads pagination">
                                        <ul class="pagination justify-content-center gap-3 mb-0">
                                            <li class="page-item {{ $leadsToday->onFirstPage() ? 'disabled' : '' }}">
                                                <a class="page-link" href="{{ $leadsToday->previousPageUrl() ?: '#' }}"
                                                    tabindex="-1"
                                                    aria-disabled="{{ $leadsToday->onFirstPage() ? 'true' : 'false' }}">&laquo;</a>
                                            </li>
                                            <li class="page-item {{ $leadsToday->hasMorePages() ? '' : 'disabled' }}">
                                                <a class="page-link" href="{{ $leadsToday->nextPageUrl() ?: '#' }}"
                                                    aria-disabled="{{ $leadsToday->hasMorePages() ? 'false' : 'true' }}">&raquo;</a>
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
    </div>

    <!-- Chart.js CDN -->
    <script src="{{ asset('js/chart.min.js') }}"></script>
    <script>
        window.leadsAnalytics = @json($leadsAnalytics);
        window.dealsAnalytics = @json($dealsAnalytics);
        window.leadsBySource = @json($leadsBySource ?? []);
        window.leadSourceColors = @json($leadSourceColors ?? []);
    </script>
    <script src="{{ asset('js/dashboard-2.js') }}"></script>
@endsection
