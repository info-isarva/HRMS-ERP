@extends('layouts.posh')

@section('title', 'Operate ' . $complaint->case_number)
@section('page-title', 'Operate Case')
@section('page-subtitle', '')

@php
    $cd = $complaint->case_data ?? [];
    $savedStep = (int) ($complaint->operate_step ?? 0);
    $totalSteps = count($steps);
    $progressPct = $totalSteps > 0 ? round((($stepIndex + 1) / $totalSteps) * 100) : 0;

    $stepMeta = [
        'review' => ['short' => 'Review', 'icon' => 'fa-clipboard-check'],
        'conciliation' => ['short' => 'Conciliation', 'icon' => 'fa-handshake'],
        'interim' => ['short' => 'Interim', 'icon' => 'fa-shield-halved'],
        'notice' => ['short' => 'Notice', 'icon' => 'fa-envelope-open-text'],
        'inquiry' => ['short' => 'Inquiry', 'icon' => 'fa-magnifying-glass'],
        'hearing' => ['short' => 'Hearing', 'icon' => 'fa-comments'],
        'recommendation' => ['short' => 'Finding', 'icon' => 'fa-scale-balanced'],
        'action' => ['short' => 'Action', 'icon' => 'fa-briefcase'],
        'appeal' => ['short' => 'Close', 'icon' => 'fa-flag-checkered'],
    ];

    $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition';
    $labelClass = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';

    $initials = function (?string $name): string {
        $name = trim((string) $name);
        if ($name === '' || str_contains(strtolower($name), 'anonymous')) {
            return '?';
        }
        $parts = preg_split('/\s+/', $name);
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $letters .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $letters ?: '?';
    };

    $activityItems = collect();
    foreach ($complaint->logs ?? [] as $log) {
        $title = match ($log->action_type) {
            'complaint_filed' => 'Complaint filed',
            'status_change' => 'Status changed',
            'step_save' => 'Workflow step saved',
            default => ucfirst(str_replace('_', ' ', $log->action_type)),
        };
        $activityItems->push([
            'at' => $log->created_at,
            'icon' => match ($log->action_type) {
                'complaint_filed' => 'fa-file-circle-plus',
                'status_change' => 'fa-arrows-rotate',
                default => 'fa-pen',
            },
            'color' => match ($log->action_type) {
                'complaint_filed' => 'bg-blue-100 text-blue-600',
                'status_change' => 'bg-indigo-100 text-indigo-600',
                default => 'bg-slate-100 text-slate-600',
            },
            'title' => $title,
            'note' => $log->notes,
            'by' => $log->user?->name,
            'badge' => $log->new_status,
        ]);
    }
    foreach ($cd['timeline'] ?? [] as $entry) {
        $stepKey = $entry['step'] ?? '';
        $meta = $stepMeta[$stepKey] ?? ['short' => $stepKey, 'icon' => 'fa-circle'];
        try {
            $at = ! empty($entry['at']) ? \Carbon\Carbon::parse($entry['at']) : null;
        } catch (\Throwable) {
            $at = null;
        }
        $activityItems->push([
            'at' => $at,
            'icon' => $meta['icon'],
            'color' => 'bg-violet-100 text-violet-600',
            'title' => 'Step: ' . $meta['short'],
            'note' => $entry['note'] ?? null,
            'by' => $entry['by'] ?? null,
            'badge' => $entry['status'] ?? null,
        ]);
    }
    $recentActivity = $activityItems
        ->filter(fn ($i) => $i['at'] !== null)
        ->sortByDesc('at')
        ->unique(fn ($i) => ($i['at']?->timestamp ?? 0) . '|' . ($i['title'] ?? '') . '|' . ($i['note'] ?? ''))
        ->take(8)
        ->values();
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-6 right-1/4 h-32 w-32 rounded-full bg-indigo-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-4 min-w-0">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                    <i class="fas fa-gavel text-xl text-blue-200"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Case workflow</p>
                    <h1 class="text-xl font-bold tracking-tight truncate lg:text-2xl">{{ $complaint->case_number }}</h1>
                    <div class="mt-2 flex flex-wrap items-center gap-2 text-sm">
                        <span class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-0.5 text-xs font-medium ring-1 ring-white/20">
                            {{ $complaint->status }}
                        </span>
                        <span class="text-slate-300">·</span>
                        <span class="text-slate-300">{{ $complaint->routed_to === 'LC' ? 'Local Committee' : 'Internal Committee' }}</span>
                        @if($complaint->is_anonymous)
                            <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/30 px-2 py-0.5 text-xs"><i class="fas fa-user-secret"></i> Anonymous</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <a href="{{ route('complaints.show', $complaint) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition">
                    <i class="fas fa-eye"></i> View case
                </a>
                <a href="{{ route('cases.index') }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition">
                    <i class="fas fa-list"></i> All cases
                </a>
            </div>
        </div>
        <div class="mt-5">
            <div class="flex items-center justify-between text-xs text-blue-100/90 mb-1.5">
                <span>Step {{ $stepIndex + 1 }} of {{ $totalSteps }}</span>
                <span>{{ $progressPct }}% through workflow</span>
            </div>
            <div class="h-1.5 rounded-full bg-white/20 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-blue-300 to-indigo-300 transition-all duration-300" style="width: {{ $progressPct }}%"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')

{{-- Step navigation --}}
<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden mb-6">
    <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 flex items-center justify-between gap-3">
        <span class="text-xs font-semibold uppercase tracking-wide text-slate-500">Workflow steps</span>
        <span class="text-xs text-slate-400 hidden sm:inline">Click a step to jump — save to record progress</span>
    </div>
    <div class="operate-stepper-scroll overflow-x-auto px-3 py-4 sm:px-4">
        <div class="flex items-start min-w-max gap-0">
            @foreach($steps as $i => $s)
                @php
                    $meta = $stepMeta[$s['key']] ?? ['short' => $s['label'], 'icon' => 'fa-circle'];
                    $isActive = $i === $stepIndex;
                    $isDone = $i < $savedStep;
                @endphp
                <div class="flex items-center">
                    <a href="{{ route('cases.operate', ['complaint' => $complaint, 'step' => $i]) }}"
                        class="operate-step group flex flex-col items-center w-[4.5rem] sm:w-[5.25rem] no-underline focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 rounded-lg"
                        title="{{ $s['label'] }} — {{ $s['status'] }}">
                        <span class="relative flex h-9 w-9 sm:h-10 sm:w-10 items-center justify-center rounded-full text-xs font-bold transition-all
                            {{ $isActive ? 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-md shadow-blue-500/30 ring-2 ring-blue-200 ring-offset-2' : '' }}
                            {{ $isDone && !$isActive ? 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200' : '' }}
                            {{ !$isActive && !$isDone ? 'bg-slate-100 text-slate-500 ring-1 ring-slate-200 group-hover:bg-blue-50 group-hover:text-blue-700 group-hover:ring-blue-200' : '' }}">
                            @if($isDone && !$isActive)
                                <i class="fas fa-check text-sm"></i>
                            @else
                                <i class="fas {{ $meta['icon'] }} text-[11px] sm:text-xs"></i>
                            @endif
                        </span>
                        <span class="mt-2 text-center text-[10px] sm:text-[11px] font-semibold leading-tight max-w-[5rem]
                            {{ $isActive ? 'text-blue-700' : ($isDone ? 'text-emerald-700' : 'text-slate-500 group-hover:text-blue-600') }}">
                            {{ $meta['short'] }}
                        </span>
                        <span class="mt-0.5 text-[9px] text-slate-400 hidden sm:block">{{ $i + 1 }}</span>
                    </a>
                    @if(!$loop->last)
                        <div class="flex h-9 sm:h-10 w-4 sm:w-6 shrink-0 items-center justify-center self-start mt-0">
                            <div class="h-0.5 w-full rounded-full {{ $i < $savedStep ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Main form --}}
    <div class="xl:col-span-2 space-y-6">
        <form method="POST" action="{{ route('cases.operate.save', $complaint) }}" class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            @csrf
            <input type="hidden" name="operate_step" value="{{ $stepIndex }}">

            @php $step = $steps[$stepIndex]; @endphp

            <div class="border-b border-slate-100 bg-gradient-to-r from-slate-50 to-blue-50/50 px-5 py-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm">
                        <i class="fas {{ ($stepMeta[$step['key']] ?? [])['icon'] ?? 'fa-pen' }}"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-blue-950">{{ preg_replace('/^\d+\.\s*/', '', $step['label']) }}</h2>
                        <p class="mt-0.5 text-sm text-slate-500">
                            Sets status to <span class="font-medium text-slate-700">{{ $step['status'] }}</span> on save
                        </p>
                    </div>
                </div>
            </div>

            <div class="p-5 space-y-5">
                @if($step['key'] === 'review')
                    <div>
                        <label class="{{ $labelClass }}">Review outcome</label>
                        <div class="relative">
                            <select name="review_outcome" class="{{ $inputClass }} appearance-none pr-10">
                                <option value="accept" @selected(($cd['review_outcome'] ?? 'accept') === 'accept')>Accept — proceed</option>
                                <option value="reject" @selected(($cd['review_outcome'] ?? '') === 'reject')>Reject (with written reasons)</option>
                                <option value="more_info" @selected(($cd['review_outcome'] ?? '') === 'more_info')>Request more information</option>
                            </select>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Notes</label>
                        <textarea name="review_notes" rows="4" class="{{ $inputClass }} resize-y" placeholder="IC review observations…">{{ $cd['review_notes'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'conciliation')
                    <label class="flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-blue-300 has-[:checked]:border-blue-400 has-[:checked]:ring-2 has-[:checked]:ring-blue-100">
                        <input type="checkbox" name="conciliation_requested" value="1" class="mt-1 h-4 w-4 rounded text-blue-600 focus:ring-blue-500"
                            @checked($cd['conciliation_requested'] ?? false)>
                        <div class="text-sm">
                            <span class="font-semibold text-blue-950">Conciliation requested</span>
                            <p class="mt-0.5 text-slate-500">Complainant has opted for conciliation at this stage.</p>
                        </div>
                    </label>
                    <div>
                        <label class="{{ $labelClass }}">Outcome / notes</label>
                        <textarea name="conciliation_outcome" rows="4" class="{{ $inputClass }} resize-y" placeholder="Conciliation summary…">{{ $cd['conciliation_outcome'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'interim')
                    <div>
                        <label class="{{ $labelClass }}">Interim relief</label>
                        <p class="text-xs text-slate-400 mb-2">Transfer, leave, no-contact order, or other immediate measures</p>
                        <textarea name="interim_relief" rows="4" class="{{ $inputClass }} resize-y" placeholder="Describe interim relief granted…">{{ $cd['interim_relief'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'notice')
                    <div>
                        <label class="{{ $labelClass }}">Notice date to respondent</label>
                        <input type="date" name="notice_date" value="{{ $cd['notice_date'] ?? '' }}" class="{{ $inputClass }}">
                    </div>
                    <a href="{{ route('cases.notice', $complaint) }}" target="_blank"
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 hover:border-blue-300">
                        <i class="fas fa-print text-blue-600"></i> Print notice (7 working days)
                    </a>
                @endif

                @if($step['key'] === 'inquiry')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="{{ $labelClass }}">Hearing date</label>
                            <input type="date" name="hearing_date" value="{{ $cd['hearing_date'] ?? '' }}" class="{{ $inputClass }}">
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Witnesses</label>
                        <textarea name="witnesses" rows="3" class="{{ $inputClass }} resize-y" placeholder="Names and roles…">{{ $cd['witnesses'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'hearing')
                    <div>
                        <label class="{{ $labelClass }}">Hearing notes / minutes</label>
                        <textarea name="hearing_notes" rows="6" class="{{ $inputClass }} resize-y" placeholder="Record of proceedings…">{{ $cd['hearing_notes'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'recommendation')
                    <div>
                        <label class="{{ $labelClass }}">Finding</label>
                        <div class="relative">
                            <select name="finding" class="{{ $inputClass }} appearance-none pr-10">
                                <option value="proved" @selected(($cd['finding'] ?? 'proved') === 'proved')>Proved</option>
                                <option value="not_proved" @selected(($cd['finding'] ?? '') === 'not_proved')>Not proved</option>
                                <option value="partially" @selected(($cd['finding'] ?? '') === 'partially')>Partially proved</option>
                            </select>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">IC recommendation</label>
                        <textarea name="recommendation" rows="4" class="{{ $inputClass }} resize-y">{{ $cd['recommendation'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'action')
                    <div>
                        <label class="{{ $labelClass }}">Management action taken</label>
                        <p class="text-xs text-slate-400 mb-2">Employer action within 60 days of recommendation</p>
                        <textarea name="action_taken" rows="4" class="{{ $inputClass }} resize-y">{{ $cd['action_taken'] ?? '' }}</textarea>
                    </div>
                @endif

                @if($step['key'] === 'appeal')
                    <label class="flex cursor-pointer items-start gap-4 rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-amber-300 has-[:checked]:border-amber-400 has-[:checked]:ring-2 has-[:checked]:ring-amber-100">
                        <input type="checkbox" name="appeal_filed" value="1" class="mt-1 h-4 w-4 rounded text-amber-600 focus:ring-amber-500"
                            @checked($cd['appeal_filed'] ?? false)>
                        <div class="text-sm">
                            <span class="font-semibold text-blue-950">Appeal filed</span>
                            <p class="mt-0.5 text-slate-500">Within the 90-day appeal window.</p>
                        </div>
                    </label>
                    <div>
                        <label class="{{ $labelClass }}">Closure notes</label>
                        <textarea name="closure_notes" rows="4" class="{{ $inputClass }} resize-y">{{ $cd['closure_notes'] ?? '' }}</textarea>
                    </div>
                @endif

                <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 p-4">
                    <label class="{{ $labelClass }}">Step notes (audit trail)</label>
                    <textarea name="step_notes" rows="2" class="{{ $inputClass }} bg-white" placeholder="Optional note logged with this save…"></textarea>
                </div>
            </div>

            <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4">
                <p class="text-xs text-slate-500">
                    <i class="fas fa-floppy-disk text-slate-400 mr-1"></i>
                    Saves step data and updates case status when changed
                </p>
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('cases.index') }}"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        All cases
                    </a>
                    <button type="submit" name="stay" value="1"
                        class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <i class="fas fa-save"></i> Save here
                    </button>
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition hover:from-blue-700 hover:to-indigo-700">
                        <i class="fas fa-arrow-right"></i> Save &amp; next
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Sidebar --}}
    <div class="xl:col-span-1 space-y-4 xl:sticky xl:top-24 xl:self-start">
        {{-- Case summary --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 px-4 py-4 text-white">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Case file</span>
                    <span class="rounded-full bg-white/15 px-2 py-0.5 text-[10px] font-semibold ring-1 ring-white/20">{{ $complaint->routed_to }}</span>
                </div>
                <p class="mt-1 font-mono text-sm font-bold tracking-tight">{{ $complaint->case_number }}</p>
                @if($complaint->submitted_at)
                    <p class="mt-1 text-xs text-slate-300">Filed {{ $complaint->submitted_at->format('d M Y') }}</p>
                @endif
            </div>

            <div class="p-4 space-y-4">
                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white shadow-sm">
                                {{ $initials($complaint->displayComplainant()) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-600/80">Complainant</p>
                                <p class="truncate text-sm font-semibold text-blue-950" title="{{ $complaint->displayComplainant() }}">{{ $complaint->displayComplainant() }}</p>
                            </div>
                        </div>
                        @if(!$complaint->is_anonymous && $complaint->department)
                            <p class="mt-2 text-[11px] text-slate-500 truncate"><i class="fas fa-building text-slate-400 mr-1"></i>{{ $complaint->department }}</p>
                        @endif
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-3">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-rose-600 text-xs font-bold text-white shadow-sm">
                                {{ $initials($complaint->respondent_name) }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-600/80">Respondent</p>
                                <p class="truncate text-sm font-semibold text-blue-950" title="{{ $complaint->respondent_name }}">{{ $complaint->respondent_name }}</p>
                            </div>
                        </div>
                        @if($complaint->respondent_type)
                            <p class="mt-2 text-[11px] text-slate-500 capitalize truncate">
                                <i class="fas fa-user-tag text-slate-400 mr-1"></i>{{ config('posh.respondent_types')[$complaint->respondent_type] ?? $complaint->respondent_type }}
                            </p>
                        @endif
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-xl border border-slate-100 bg-slate-50/80 p-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-amber-600 shadow-sm ring-1 ring-slate-200">
                        <i class="fas fa-calendar-day text-sm"></i>
                    </span>
                    <div class="min-w-0">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400">Incident</p>
                        <p class="text-sm font-semibold text-blue-950">{{ $complaint->incident_date?->format('d M Y') ?? '—' }}</p>
                        @if($complaint->incident_location)
                            <p class="mt-0.5 text-xs text-slate-500 leading-snug">{{ $complaint->incident_location }}</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-2">
                        <i class="fas fa-align-left mr-1"></i> Allegation summary
                    </p>
                    <blockquote class="relative border-l-2 border-indigo-300 pl-3 text-sm leading-relaxed text-slate-600">
                        {{ Str::limit($complaint->description, 280) }}
                    </blockquote>
                    @if(strlen($complaint->description) > 280)
                        <a href="{{ route('complaints.show', $complaint) }}" class="mt-2 inline-flex text-xs font-semibold text-blue-600 hover:text-blue-800">Read full case →</a>
                    @endif
                </div>

                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1 rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-medium text-slate-700">
                        <i class="fas fa-circle-dot text-[6px] text-blue-500"></i> {{ $complaint->status }}
                    </span>
                    @if($complaint->vs_employer)
                        <span class="inline-flex items-center gap-1 rounded-lg bg-amber-100 px-2 py-1 text-[11px] font-medium text-amber-800">vs Employer</span>
                    @endif
                </div>
            </div>
        </div>

        @if($complaint->inquiry_started_at || $complaint->report_due_at || $complaint->management_action_due_at)
        <div class="rounded-2xl border border-amber-200/80 bg-gradient-to-br from-amber-50 to-orange-50/50 p-4 shadow-sm">
            <p class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-900 mb-3">
                <i class="fas fa-bell text-amber-600"></i> SLA reminders
            </p>
            <ul class="space-y-2.5">
                @if($complaint->inquiry_started_at)
                <li class="flex items-start gap-2.5 text-sm text-amber-950">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/80 text-amber-600"><i class="fas fa-clock text-xs"></i></span>
                    <span><strong class="block text-xs text-amber-800/70">90-day inquiry</strong>Started {{ $complaint->inquiry_started_at->format('d M Y') }}</span>
                </li>
                @endif
                @if($complaint->report_due_at)
                <li class="flex items-start gap-2.5 text-sm text-amber-950">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/80 text-amber-600"><i class="fas fa-file-lines text-xs"></i></span>
                    <span><strong class="block text-xs text-amber-800/70">Report due</strong>{{ $complaint->report_due_at->format('d M Y') }}</span>
                </li>
                @endif
                @if($complaint->management_action_due_at)
                <li class="flex items-start gap-2.5 text-sm text-amber-950">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/80 text-amber-600"><i class="fas fa-briefcase text-xs"></i></span>
                    <span><strong class="block text-xs text-amber-800/70">Management action</strong>Due {{ $complaint->management_action_due_at->format('d M Y') }}</span>
                </li>
                @endif
            </ul>
        </div>
        @endif

        {{-- Recent activity --}}
        <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                <h3 class="text-sm font-semibold text-blue-950">Recent activity</h3>
                @if($recentActivity->isNotEmpty())
                    <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $recentActivity->count() }}</span>
                @endif
            </div>

            @if($recentActivity->isEmpty())
                <div class="px-4 py-8 text-center">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-2">
                        <i class="fas fa-clock-rotate-left"></i>
                    </span>
                    <p class="text-sm text-slate-500">No activity logged yet</p>
                    <p class="text-xs text-slate-400 mt-1">Saves on this page appear here</p>
                </div>
            @else
                <ul class="max-h-72 overflow-y-auto py-3 px-2 operate-activity-scroll">
                    @foreach($recentActivity as $item)
                        <li class="relative flex gap-3 pb-4 pl-3 last:pb-1">
                            @if(!$loop->last)
                                <span class="absolute left-[1.35rem] top-9 bottom-0 w-px bg-slate-200" aria-hidden="true"></span>
                            @endif
                            <span class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $item['color'] }} ring-4 ring-white">
                                <i class="fas {{ $item['icon'] }} text-[10px]"></i>
                            </span>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <div class="flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0.5">
                                    <p class="text-sm font-semibold text-slate-700">{{ $item['title'] }}</p>
                                    <time class="shrink-0 text-[10px] font-medium text-slate-400" datetime="{{ $item['at']->toIso8601String() }}">
                                        {{ $item['at']->diffForHumans(null, true) }}
                                    </time>
                                </div>
                                @if(!empty($item['badge']))
                                    <span class="mt-1 inline-block max-w-full truncate rounded-md bg-slate-100 px-1.5 py-0.5 text-[10px] font-medium text-slate-600">{{ $item['badge'] }}</span>
                                @endif
                                @if(!empty($item['note']))
                                    <p class="mt-1 text-xs text-slate-500 leading-snug line-clamp-2">{{ $item['note'] }}</p>
                                @endif
                                @if(!empty($item['by']))
                                    <p class="mt-1 text-[10px] text-slate-400"><i class="fas fa-user-circle mr-0.5"></i>{{ $item['by'] }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</div>

@push('styles')
<style>
    .operate-stepper-scroll {
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 transparent;
    }
    .operate-stepper-scroll::-webkit-scrollbar { height: 6px; }
    .operate-stepper-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .operate-activity-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .operate-activity-scroll::-webkit-scrollbar { width: 4px; }
    .operate-activity-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endsection
