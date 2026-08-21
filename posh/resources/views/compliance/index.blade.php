@extends('layouts.posh')

@section('title', 'Compliance')
@section('page-title', 'Employer Compliance')
@section('page-subtitle', '')

@php
    $doneCount = $duties->where('is_done', true)->count();
    $totalDuties = $duties->count();
    $inputClass = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition';

    $dutyIcons = [
        'safe_environment' => 'fa-shield-halved',
        'display_penal' => 'fa-clipboard-list',
        'display_ic_order' => 'fa-clipboard-check',
        'workshops' => 'fa-chalkboard-user',
        'ic_orientation' => 'fa-users-gear',
        'facilitate_ic' => 'fa-handshake',
        'secure_attendance' => 'fa-user-check',
        'provide_info' => 'fa-file-lines',
        'assist_complaint' => 'fa-hands-helping',
        'misconduct_rules' => 'fa-gavel',
        'criminal_action' => 'fa-scale-balanced',
        'medical_assistance' => 'fa-heart-pulse',
        'annual_report' => 'fa-file-export',
        'service_rules' => 'fa-book',
    ];

    $eventLabels = [
        'workshop' => 'Employee workshop',
        'ic_orientation' => 'IC orientation',
        'display' => 'Display / posters',
        'other' => 'Other',
    ];
    $eventIcons = [
        'workshop' => ['icon' => 'fa-chalkboard-user', 'class' => 'bg-blue-50 text-blue-600'],
        'ic_orientation' => ['icon' => 'fa-users-gear', 'class' => 'bg-indigo-50 text-indigo-600'],
        'display' => ['icon' => 'fa-image', 'class' => 'bg-violet-50 text-violet-600'],
        'other' => ['icon' => 'fa-calendar', 'class' => 'bg-slate-100 text-slate-600'],
    ];
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-emerald-700 via-teal-800 to-teal-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-8 right-1/4 h-36 w-36 rounded-full bg-emerald-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-clipboard-check text-xl text-emerald-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-emerald-200/90">Section 19</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Employer Compliance</h1>
                <p class="mt-1 text-sm text-slate-300">Statutory duties &amp; prevention activities</p>
            </div>
        </div>
        <div class="flex items-center gap-4 shrink-0">
            <div class="flex items-center gap-3 rounded-xl bg-white/10 px-4 py-2 ring-1 ring-white/20">
                <div class="relative h-12 w-12">
                    <svg class="h-12 w-12 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                        <circle cx="18" cy="18" r="15.5" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="3"/>
                        <circle cx="18" cy="18" r="15.5" fill="none" stroke="#6ee7b7" stroke-width="3" stroke-linecap="round"
                            pathLength="100" stroke-dasharray="{{ $percent }} 100"/>
                    </svg>
                    <span class="absolute inset-0 flex items-center justify-center text-xs font-bold">{{ $percent }}%</span>
                </div>
                <div class="text-sm">
                    <p class="font-semibold">Checklist</p>
                    <p class="text-emerald-100/90 text-xs">{{ $doneCount }}/{{ $totalDuties }} complete</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- S.19 Duties --}}
    <div class="xl:col-span-2">
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-blue-950">Employer duties (Section 19)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Tick when complete — add notes for audit evidence</p>
                </div>
                <div class="h-2 flex-1 sm:max-w-[140px] rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-emerald-500 to-teal-500 transition-all" style="width: {{ $percent }}%"></div>
                </div>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($duties as $duty)
                    @php $icon = $dutyIcons[$duty->duty_key] ?? 'fa-circle-check'; @endphp
                    <form method="POST" action="{{ route('compliance.duty.update', $duty) }}"
                        class="group px-4 py-3.5 lg:px-5 transition {{ $duty->is_done ? 'bg-emerald-50/40' : 'hover:bg-slate-50/80' }}">
                        @csrf
                        <div class="flex flex-col lg:flex-row lg:items-center gap-3">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                <label class="flex shrink-0 cursor-pointer items-center pt-1">
                                    <input type="checkbox" name="is_done" value="1" @checked($duty->is_done)
                                        class="h-5 w-5 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                        onchange="this.form.querySelector('[data-auto-save]')?.click()">
                                </label>
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $duty->is_done ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500 group-hover:bg-blue-50 group-hover:text-blue-600' }} transition">
                                    <i class="fas {{ $icon }} text-sm"></i>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-semibold text-blue-950 {{ $duty->is_done ? 'line-through decoration-emerald-600/40 text-slate-600' : '' }}">
                                        {{ $duty->duty_text }}
                                    </p>
                                    @if($duty->done_on)
                                        <p class="mt-0.5 text-[11px] font-medium text-emerald-700">
                                            <i class="fas fa-check mr-0.5"></i>Completed {{ $duty->done_on->format('d M Y') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 pl-14 lg:pl-0 lg:w-[min(100%,22rem)] shrink-0">
                                <input type="text" name="notes" value="{{ $duty->notes }}" placeholder="Notes (optional)"
                                    class="{{ $inputClass }} flex-1 py-2 text-xs">
                                <button type="submit" data-auto-save
                                    class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-emerald-300 hover:bg-emerald-50 hover:text-emerald-800">
                                    <i class="fas fa-save lg:mr-1"></i><span class="hidden lg:inline">Save</span>
                                </button>
                            </div>
                        </div>
                    </form>
                @endforeach
            </div>
        </section>
    </div>

    {{-- Prevention events --}}
    <div class="xl:col-span-1 space-y-4">
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-gradient-to-r from-blue-50 to-indigo-50/50 px-4 py-3">
                <h2 class="text-sm font-semibold text-blue-950">Record prevention event</h2>
                <p class="text-xs text-slate-500 mt-0.5">Workshops, IC orientation, displays</p>
            </div>
            <form method="POST" action="{{ route('compliance.events.store') }}" class="p-4 space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-2">
                    <div class="col-span-2">
                        <label class="mb-1 block text-[10px] font-semibold uppercase text-slate-500">Type</label>
                        <div class="relative">
                            <select name="event_type" class="{{ $inputClass }} appearance-none pr-8 text-xs">
                                @foreach($eventLabels as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-slate-400"></i>
                        </div>
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-[10px] font-semibold uppercase text-slate-500">Title</label>
                        <input type="text" name="title" required class="{{ $inputClass }} text-xs" placeholder="e.g. Q1 awareness workshop">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase text-slate-500">Date</label>
                        <input type="date" name="held_on" required class="{{ $inputClass }} text-xs" max="{{ date('Y-m-d') }}">
                    </div>
                    <div>
                        <label class="mb-1 block text-[10px] font-semibold uppercase text-slate-500">Attendees</label>
                        <input type="number" name="attendee_count" min="0" class="{{ $inputClass }} text-xs" placeholder="0">
                    </div>
                    <div class="col-span-2">
                        <label class="mb-1 block text-[10px] font-semibold uppercase text-slate-500">Notes</label>
                        <textarea name="notes" rows="2" class="{{ $inputClass }} text-xs resize-none" placeholder="Optional details…"></textarea>
                    </div>
                </div>
                <button type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700">
                    <i class="fas fa-plus-circle"></i> Record event
                </button>
            </form>
        </section>

        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 bg-slate-50/80 px-4 py-3">
                <h2 class="text-sm font-semibold text-blue-950">Recent events</h2>
                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-[10px] font-bold text-slate-600">{{ $events->count() }}</span>
            </div>
            @if($events->isEmpty())
                <div class="px-4 py-10 text-center">
                    <span class="inline-flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-slate-400 mb-2">
                        <i class="fas fa-calendar-plus"></i>
                    </span>
                    <p class="text-xs text-slate-500">No prevention events yet</p>
                </div>
            @else
                <ul class="max-h-80 overflow-y-auto divide-y divide-slate-100 compliance-events-scroll">
                    @foreach($events as $e)
                        @php $eIcon = $eventIcons[$e->event_type] ?? $eventIcons['other']; @endphp
                        <li class="flex gap-3 px-4 py-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $eIcon['class'] }}">
                                <i class="fas {{ $eIcon['icon'] }} text-sm"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-semibold text-slate-700 truncate">{{ $e->title }}</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $eventLabels[$e->event_type] ?? $e->event_type }}
                                    · {{ $e->held_on->format('d M Y') }}
                                    @if($e->attendee_count !== null)
                                        · {{ $e->attendee_count }} attendees
                                    @endif
                                </p>
                                @if($e->notes)
                                    <p class="mt-1 text-xs text-slate-500 line-clamp-2">{{ $e->notes }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <div class="rounded-xl border border-emerald-200/80 bg-emerald-50/50 px-4 py-3 text-xs text-emerald-900 leading-relaxed">
            <i class="fas fa-circle-info text-emerald-600 mr-1"></i>
            Completion feeds your <strong>annual report</strong> and audit evidence. Keep workshop and display records up to date.
        </div>
    </div>
</div>

@push('styles')
<style>
    .compliance-events-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .compliance-events-scroll::-webkit-scrollbar { width: 4px; }
    .compliance-events-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
</style>
@endpush
@endsection
