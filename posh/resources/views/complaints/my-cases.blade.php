@extends('layouts.posh')

@section('title', 'My Complaints')
@section('page-title', 'My Complaints')
@section('page-subtitle', '')

@php
    $statusStyles = [
        'Submitted' => 'bg-blue-100 text-blue-800 ring-blue-200/60',
        'Acknowledged' => 'bg-sky-100 text-sky-800 ring-sky-200/60',
        'Under IC/LC Review' => 'bg-indigo-100 text-indigo-800 ring-indigo-200/60',
        'Additional Info Requested' => 'bg-amber-100 text-amber-900 ring-amber-200/60',
        'Rejected (with reasons)' => 'bg-rose-100 text-rose-800 ring-rose-200/60',
        'Conciliation In Progress' => 'bg-violet-100 text-violet-800 ring-violet-200/60',
        'Inquiry Started' => 'bg-orange-100 text-orange-900 ring-orange-200/60',
        'Closed' => 'bg-emerald-100 text-emerald-800 ring-emerald-200/60',
        'Archived' => 'bg-slate-100 text-slate-600 ring-slate-200/60',
        'Management Action Pending (60 days)' => 'bg-amber-100 text-amber-900 ring-amber-200/60',
        'Recommendation Pending' => 'bg-purple-100 text-purple-800 ring-purple-200/60',
    ];
    $defaultBadge = 'bg-slate-100 text-slate-700 ring-slate-200/60';
    $openCount = $complaints->filter(fn ($c) => !$c->isClosed())->count();
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-6 left-1/3 h-28 w-28 rounded-full bg-blue-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-folder-open text-xl text-blue-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Your filings</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">My Complaints</h1>
                <p class="mt-1 text-sm text-slate-300">Track status of cases you have filed — confidential</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($complaints->count() > 0)
                <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">
                    <i class="fas fa-folder-open text-blue-200"></i>
                    {{ $complaints->count() }} total · {{ $openCount }} open
                </span>
            @endif
            <a href="{{ route('complaints.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-900 shadow-md hover:bg-blue-50 transition">
                <i class="fas fa-plus"></i> File new complaint
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    @if($complaints->isEmpty())
        <div class="px-6 py-16 text-center">
            <span class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-100 to-indigo-100 text-blue-600 mb-4">
                <i class="fas fa-file-circle-plus text-2xl"></i>
            </span>
            <h2 class="text-lg font-semibold text-blue-950">No complaints filed yet</h2>
            <p class="mt-2 max-w-md mx-auto text-sm text-slate-500 leading-relaxed">
                You can file a confidential complaint anytime. Policy acknowledgement is separate and does not block filing.
            </p>
            <a href="{{ route('complaints.create') }}"
                class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700 transition">
                <i class="fas fa-paper-plane"></i> File your first complaint
            </a>
            <p class="mt-4 text-xs text-slate-400">
                <a href="{{ route('employee.portal') }}" class="text-blue-600 hover:text-blue-800 font-medium">Back to employee portal</a>
            </p>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($complaints as $c)
                <a href="{{ route('complaints.show', $c) }}"
                    class="group flex flex-col sm:flex-row sm:items-center gap-4 px-5 py-4 no-underline transition hover:bg-blue-50/40">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600 transition group-hover:bg-blue-100 group-hover:text-blue-700">
                            <i class="fas fa-folder"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold text-blue-950">{{ $c->case_number }}</p>
                            <p class="mt-0.5 text-sm text-slate-600 truncate">
                                <span class="text-slate-400">vs</span> {{ $c->respondent_name }}
                            </p>
                            <p class="mt-1 text-xs text-slate-400">
                                <i class="fas fa-calendar-day mr-1"></i>Filed {{ $c->created_at->format('d M Y') }}
                                @if($c->incident_date)
                                    · Incident {{ $c->incident_date->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:shrink-0 pl-14 sm:pl-0">
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 {{ $statusStyles[$c->status] ?? $defaultBadge }}">
                            {{ $c->status }}
                        </span>
                        <span class="inline-flex items-center gap-1 text-sm font-semibold text-blue-600 group-hover:text-blue-800">
                            View <i class="fas fa-arrow-right text-[10px] transition group-hover:translate-x-0.5"></i>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>

@if($complaints->isNotEmpty())
<div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-xs text-slate-500">
    <span><i class="fas fa-lock text-slate-400 mr-1"></i> Only you and authorised IC/LC members can access case details.</span>
    <a href="{{ route('employee.portal') }}" class="font-semibold text-blue-600 hover:text-blue-800">Employee portal →</a>
</div>
@endif

@endsection
