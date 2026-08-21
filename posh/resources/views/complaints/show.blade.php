@extends('layouts.posh')

@section('title', $complaint->case_number)
@section('page-title', $complaint->case_number)
@section('page-subtitle', '')

@php
    $statusStyles = [
        'Submitted' => 'bg-blue-100 text-blue-800 ring-blue-200/60',
        'Acknowledged' => 'bg-sky-100 text-sky-800 ring-sky-200/60',
        'Under IC/LC Review' => 'bg-indigo-100 text-indigo-800 ring-indigo-200/60',
        'Additional Info Requested' => 'bg-amber-100 text-amber-900 ring-amber-200/60',
        'Rejected (with reasons)' => 'bg-rose-100 text-rose-800 ring-rose-200/60',
        'Conciliation In Progress' => 'bg-violet-100 text-violet-800 ring-violet-200/60',
        'Interim Relief Applied' => 'bg-teal-100 text-teal-800 ring-teal-200/60',
        'Inquiry Started' => 'bg-orange-100 text-orange-900 ring-orange-200/60',
        'Notice Issued to Respondent' => 'bg-cyan-100 text-cyan-800 ring-cyan-200/60',
        'Hearing Completed' => 'bg-purple-100 text-purple-800 ring-purple-200/60',
        'Recommendation Pending' => 'bg-purple-100 text-purple-800 ring-purple-200/60',
        'Management Action Pending (60 days)' => 'bg-amber-100 text-amber-900 ring-amber-200/60',
        'Closed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/60',
        'Archived' => 'bg-slate-100 text-slate-600 ring-slate-200/60',
    ];
    $badgeClass = $statusStyles[$complaint->status] ?? 'bg-slate-100 text-slate-700 ring-slate-200/60';
    $isIc = auth()->user()->hasIcAccess();
    $respondentLabel = config('posh.respondent_types.'.$complaint->respondent_type, $complaint->respondent_type);

    $initials = function (?string $name): string {
        $name = trim((string) $name);
        if ($name === '' || str_contains(strtolower($name), 'anonymous')) return '?';
        $parts = preg_split('/\s+/', $name);
        $letters = '';
        foreach (array_slice($parts, 0, 2) as $p) {
            $letters .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $letters ?: '?';
    };

    $logMeta = [
        'complaint_filed' => ['icon' => 'fa-file-circle-plus', 'color' => 'bg-blue-100 text-blue-600', 'title' => 'Complaint filed'],
        'status_change' => ['icon' => 'fa-arrows-rotate', 'color' => 'bg-indigo-100 text-indigo-600', 'title' => 'Status updated'],
        'step_save' => ['icon' => 'fa-pen', 'color' => 'bg-violet-100 text-violet-600', 'title' => 'Workflow update'],
    ];
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-6 right-0 h-32 w-32 rounded-full bg-indigo-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Case details</p>
            <h1 class="text-xl font-bold tracking-tight font-mono lg:text-2xl">{{ $complaint->case_number }}</h1>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center rounded-full bg-white/15 px-2.5 py-1 text-xs font-semibold text-white ring-1 ring-white/25">
                    {{ $complaint->status }}
                </span>
                <span class="text-sm text-slate-300">·</span>
                <span class="text-sm text-slate-300">{{ $complaint->routed_to === 'LC' ? 'Local Committee' : 'Internal Committee' }}</span>
                @if($complaint->is_anonymous)
                    <span class="inline-flex items-center gap-1 rounded-full bg-indigo-500/30 px-2 py-0.5 text-xs"><i class="fas fa-user-secret"></i> Anonymous</span>
                @endif
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($isIc)
                <a href="{{ route('cases.operate', $complaint) }}"
                    class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-900 shadow-md hover:bg-blue-50 transition">
                    <i class="fas fa-play"></i> Operate case
                </a>
            @endif
            <a href="{{ $isIc ? route('cases.index') : route('complaints.my') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2 space-y-6">
        {{-- Parties & key facts --}}
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-3">
                <h2 class="text-sm font-semibold text-blue-950">Complaint details</h2>
            </div>
            <div class="p-5 space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="rounded-xl border border-blue-100 bg-gradient-to-br from-blue-50 to-white p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-blue-600/80">Complainant</p>
                        <div class="mt-2 flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-600 text-xs font-bold text-white">{{ $initials($complaint->displayComplainant()) }}</span>
                            <p class="font-semibold text-blue-950">{{ $complaint->displayComplainant() }}</p>
                        </div>
                        @if(!$complaint->is_anonymous && $complaint->department)
                            <p class="mt-2 text-xs text-slate-500"><i class="fas fa-building mr-1"></i>{{ $complaint->department }}</p>
                        @endif
                    </div>
                    <div class="rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 to-white p-4">
                        <p class="text-[10px] font-semibold uppercase tracking-wide text-rose-600/80">Respondent</p>
                        <div class="mt-2 flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-600 text-xs font-bold text-white">{{ $initials($complaint->respondent_name) }}</span>
                            <p class="font-semibold text-blue-950">{{ $complaint->respondent_name }}</p>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ $respondentLabel }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase text-slate-400">Incident</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-700">{{ $complaint->incident_date->format('d M Y') }}</p>
                    </div>
                    <div class="rounded-lg border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase text-slate-400">Filed</p>
                        <p class="mt-0.5 text-sm font-semibold text-slate-700">{{ $complaint->created_at->format('d M Y') }}</p>
                        <p class="text-[10px] text-slate-500">{{ $complaint->created_at->format('H:i') }}</p>
                    </div>
                    @if($complaint->report_due_at)
                    <div class="rounded-lg border border-amber-100 bg-amber-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase text-amber-700/80">Inquiry due</p>
                        <p class="mt-0.5 text-sm font-semibold text-amber-900">{{ $complaint->report_due_at->format('d M Y') }}</p>
                    </div>
                    @endif
                    @if($complaint->management_action_due_at)
                    <div class="rounded-lg border border-orange-100 bg-orange-50/80 px-3 py-2.5">
                        <p class="text-[10px] font-semibold uppercase text-orange-700/80">Mgmt due</p>
                        <p class="mt-0.5 text-sm font-semibold text-orange-900">{{ $complaint->management_action_due_at->format('d M Y') }}</p>
                    </div>
                    @endif
                </div>

                @if(!$complaint->filing_within_deadline)
                    <div class="flex gap-2 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <i class="fas fa-triangle-exclamation mt-0.5 shrink-0"></i>
                        <span>Filing was <strong>outside</strong> the 3+3 month window — extension reason on record.</span>
                    </div>
                @endif

                <div class="rounded-xl border border-slate-100 bg-slate-50/50 p-4">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-400 mb-2">Description</p>
                    <p class="text-sm leading-relaxed text-slate-700 whitespace-pre-wrap">{{ $complaint->description }}</p>
                    @if($complaint->incident_location)
                        <p class="mt-3 text-xs text-slate-500 border-t border-slate-200/80 pt-3">
                            <i class="fas fa-location-dot text-slate-400 mr-1"></i>{{ $complaint->incident_location }}
                        </p>
                    @endif
                </div>
            </div>
        </section>

        @if($complaint->evidence->count())
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-blue-950">Evidence</h2>
                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $complaint->evidence->count() }}</span>
            </div>
            <ul class="divide-y divide-slate-100">
                @foreach($complaint->evidence as $ev)
                    <li class="flex items-center justify-between gap-3 px-5 py-3.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-slate-500">
                                <i class="fas fa-paperclip text-sm"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-medium text-slate-700">{{ $ev->original_filename }}</p>
                                <p class="text-xs text-slate-500">{{ number_format($ev->file_size / 1024, 1) }} KB</p>
                            </div>
                        </div>
                        @if($isIc)
                            <a href="{{ route('complaints.evidence.download', [$complaint, $ev]) }}"
                                class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                <i class="fas fa-download text-[10px]"></i> Download
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
        @endif
    </div>

    {{-- Timeline sidebar --}}
    <div class="xl:col-span-1">
        <section class="xl:sticky xl:top-24 rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-4 py-3 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-blue-950">Timeline</h2>
                <span class="text-xs text-slate-400">{{ $complaint->logs->count() }} {{ Str::plural('event', $complaint->logs->count()) }}</span>
            </div>

            @if($complaint->logs->isNotEmpty())
                <ul class="max-h-[28rem] overflow-y-auto py-3 px-2 case-timeline-scroll">
                    @foreach($complaint->logs as $log)
                        @php
                            $meta = $logMeta[$log->action_type] ?? ['icon' => 'fa-circle', 'color' => 'bg-slate-100 text-slate-600', 'title' => ucfirst(str_replace('_', ' ', $log->action_type))];
                        @endphp
                        <li class="relative flex gap-3 pb-4 pl-2 last:pb-2">
                            @if(!$loop->last)
                                <span class="absolute left-[1.15rem] top-8 bottom-0 w-px bg-slate-200" aria-hidden="true"></span>
                            @endif
                            <span class="relative z-10 flex h-7 w-7 shrink-0 items-center justify-center rounded-full {{ $meta['color'] }} ring-4 ring-white">
                                <i class="fas {{ $meta['icon'] }} text-[10px]"></i>
                            </span>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-sm font-semibold text-slate-700">{{ $meta['title'] }}</p>
                                <time class="text-[10px] font-medium text-slate-400">{{ $log->created_at->format('d M Y · H:i') }}</time>
                                @if($log->new_status)
                                    <span class="mt-1 inline-block max-w-full truncate rounded-md px-1.5 py-0.5 text-[10px] font-semibold ring-1 {{ $statusStyles[$log->new_status] ?? 'bg-slate-100 text-slate-600 ring-slate-200/60' }}">
                                        {{ $log->new_status }}
                                    </span>
                                @endif
                                @if($log->notes)
                                    <p class="mt-1.5 text-xs text-slate-500 leading-snug">{{ $log->notes }}</p>
                                @endif
                                @if($log->user)
                                    <p class="mt-1 text-[10px] text-slate-400"><i class="fas fa-user-circle"></i> {{ $log->user->name }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @else
                <div class="px-4 py-12 text-center">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400 mb-2">
                        <i class="fas fa-clock-rotate-left"></i>
                    </span>
                    <p class="text-sm text-slate-500">No activity logged yet</p>
                </div>
            @endif
        </section>
    </div>
</div>

@push('styles')
<style>
    .case-timeline-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .case-timeline-scroll::-webkit-scrollbar { width: 4px; }
    .case-timeline-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
</style>
@endpush
@endsection
