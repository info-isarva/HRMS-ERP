@extends('layouts.posh')

@section('title', 'Management')
@section('page-title', 'Management Portal')
@section('page-subtitle', '')

@php
    $isIc = auth()->user()?->hasIcAccess();
    $pendingCount = $pending->count();
    $recCount = $recentRecs->count();
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-violet-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-8 right-0 h-36 w-36 rounded-full bg-violet-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-briefcase text-xl text-violet-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-200/90">Employer action</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Management Portal</h1>
                <p class="mt-1 text-sm text-slate-300">IC recommendations &amp; 60-day management implementation</p>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <span class="inline-flex items-center gap-2 rounded-xl bg-amber-500/20 px-3 py-2 text-sm font-semibold ring-1 ring-amber-300/30">
                <i class="fas fa-hourglass-half text-amber-200"></i>
                {{ $pendingCount }} pending
            </span>
            <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-3 py-2 text-sm font-semibold ring-1 ring-white/20">
                <i class="fas fa-scale-balanced text-violet-200"></i>
                {{ $recCount }} awaiting rec.
            </span>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
    {{-- Pending management action --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-amber-50 to-orange-50/50 px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white shadow-sm">
                    <i class="fas fa-clock"></i>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-blue-950">Pending management action</h2>
                    <p class="text-xs text-slate-500">60-day SLA after IC report</p>
                </div>
            </div>
            @if($pendingCount > 0)
                <span class="rounded-full bg-amber-600 px-2.5 py-0.5 text-xs font-bold text-white">{{ $pendingCount }}</span>
            @endif
        </div>

        <div class="flex-1 p-4 space-y-3">
            @forelse($pending as $c)
                @php
                    $rec = $c->getCaseData('recommendation', 'No recommendation text yet.');
                    $due = $c->management_action_due_at;
                    $daysLeft = null;
                    if ($due) {
                        $daysLeft = $due->isPast() ? -$due->diffInDays(now()) : now()->diffInDays($due);
                    }
                    $urgent = $daysLeft !== null && $daysLeft <= 14;
                @endphp
                <article class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-amber-300 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold text-blue-950">{{ $c->case_number }}</p>
                            <p class="mt-0.5 text-sm text-slate-600">
                                <i class="fas fa-user text-slate-400 text-xs mr-1"></i>{{ $c->respondent_name }}
                            </p>
                        </div>
                        @if($due)
                            <span class="shrink-0 inline-flex flex-col items-end rounded-lg px-2.5 py-1 text-[10px] font-bold {{ $urgent ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-900' }}">
                                <span>Due {{ $due->format('d M Y') }}</span>
                                @if($daysLeft !== null)
                                    <span class="font-medium opacity-80">{{ $daysLeft >= 0 ? $daysLeft . 'd left' : abs($daysLeft) . 'd overdue' }}</span>
                                @endif
                            </span>
                        @endif
                    </div>
                    <blockquote class="mt-3 border-l-2 border-amber-300 pl-3 text-sm leading-relaxed text-slate-600 line-clamp-3">
                        {{ $rec }}
                    </blockquote>
                    @if($isIc)
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('cases.operate', ['complaint' => $c, 'step' => 7]) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700">
                                <i class="fas fa-pen text-[10px]"></i> Record action
                            </a>
                            <a href="{{ route('complaints.show', $c) }}"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                View case
                            </a>
                        </div>
                    @endif
                </article>
            @empty
                <div class="py-12 text-center">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-emerald-50 text-emerald-600 mb-3">
                        <i class="fas fa-circle-check text-xl"></i>
                    </span>
                    <p class="text-sm font-semibold text-slate-700">All clear</p>
                    <p class="mt-1 text-xs text-slate-500">No cases awaiting management action right now.</p>
                </div>
            @endforelse
        </div>
    </section>

    {{-- Recent IC recommendations --}}
    <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden flex flex-col">
        <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-violet-50/50 px-5 py-4">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                    <i class="fas fa-scale-balanced"></i>
                </span>
                <div>
                    <h2 class="text-sm font-semibold text-blue-950">Recent IC recommendations</h2>
                    <p class="text-xs text-slate-500">Awaiting employer implementation</p>
                </div>
            </div>
            @if($recCount > 0)
                <span class="rounded-full bg-indigo-600 px-2.5 py-0.5 text-xs font-bold text-white">{{ $recCount }}</span>
            @endif
        </div>

        <div class="flex-1 p-4 space-y-3 max-h-[32rem] overflow-y-auto mgmt-scroll">
            @forelse($recentRecs as $c)
                @php $rec = $c->getCaseData('recommendation', '—'); $finding = $c->getCaseData('finding'); @endphp
                <article class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 transition hover:border-indigo-200 hover:bg-white">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-mono text-sm font-bold text-blue-950">{{ $c->case_number }}</p>
                        @if($finding)
                            <span class="shrink-0 rounded-md bg-indigo-100 px-2 py-0.5 text-[10px] font-semibold capitalize text-indigo-800">{{ str_replace('_', ' ', $finding) }}</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs text-slate-500">{{ $c->displayComplainant() }} · {{ $c->respondent_name }}</p>
                    <p class="mt-2 text-sm text-slate-600 leading-relaxed line-clamp-2">{{ $rec }}</p>
                    @if($isIc)
                        <a href="{{ route('cases.operate', ['complaint' => $c, 'step' => 6]) }}"
                            class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-800">
                            Open in workflow <i class="fas fa-arrow-right text-[10px]"></i>
                        </a>
                    @endif
                </article>
            @empty
                <div class="py-12 text-center">
                    <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                        <i class="fas fa-inbox text-xl"></i>
                    </span>
                    <p class="text-sm font-semibold text-slate-700">No recommendations in queue</p>
                    <p class="mt-1 text-xs text-slate-500">Cases move here after inquiry &amp; hearing steps.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>

<div class="mt-4 rounded-xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-xs text-slate-500 leading-relaxed">
    <i class="fas fa-circle-info text-slate-400 mr-1"></i>
    Management must act on proved findings within <strong>60 days</strong> of the IC report (POSH Rules). IC members can record outcomes in the operate workflow.
    @if($isIc)
        <a href="{{ route('cases.index') }}" class="ml-1 font-semibold text-blue-600 hover:text-blue-800">View all cases →</a>
    @endif
</div>

@push('styles')
<style>
    .mgmt-scroll { scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }
    .mgmt-scroll::-webkit-scrollbar { width: 4px; }
    .mgmt-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .line-clamp-2, .line-clamp-3 {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .line-clamp-2 { -webkit-line-clamp: 2; }
    .line-clamp-3 { -webkit-line-clamp: 3; }
</style>
@endpush
@endsection
