@extends('layouts.posh')

@section('title', 'All Cases')
@section('page-title', 'All Cases')
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-6 right-1/4 h-32 w-32 rounded-full bg-indigo-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-folder-open text-xl text-blue-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">IC / HR workspace</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">All Cases</h1>
                <p class="mt-1 text-sm text-slate-300">Confidential case register — operate and monitor workflow</p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold ring-1 ring-white/20">
                <i class="fas fa-layer-group text-blue-200"></i>
                {{ $complaints->total() }} {{ Str::plural('case', $complaints->total()) }}
            </span>
            <a href="{{ route('complaints.create') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2 text-sm font-semibold text-blue-900 shadow-md hover:bg-blue-50 transition">
                <i class="fas fa-plus"></i> New complaint
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

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
    ];
    $defaultBadge = 'bg-slate-100 text-slate-700 ring-slate-200/60';
    $hasFilters = request()->filled('q') || request()->filled('status');
@endphp

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    {{-- Single-line filters --}}
    <div class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 lg:px-5">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <div class="relative flex-1 min-w-0">
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Search case ID, complainant, respondent…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm text-slate-700 placeholder:text-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
            </div>
            <div class="relative w-full sm:w-52 shrink-0">
                <select name="status"
                    class="w-full appearance-none rounded-xl border border-slate-200 bg-white py-2.5 pl-4 pr-10 text-sm text-slate-700 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition">
                    <option value="">All statuses</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $s }}</option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down pointer-events-none absolute right-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400"></i>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="inline-flex flex-1 sm:flex-none items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                @if($hasFilters)
                    <a href="{{ route('cases.index') }}"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50"
                        title="Clear filters">
                        <i class="fas fa-xmark"></i>
                        <span class="hidden sm:inline">Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[720px] border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/80">
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 lg:px-5">Case ID</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Complainant</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Respondent</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Filed</th>
                    <th class="px-4 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 lg:px-5">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($complaints as $c)
                    <tr class="group transition hover:bg-blue-50/40">
                        <td class="px-4 py-3.5 lg:px-5">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-[10px] font-bold text-slate-600 group-hover:bg-blue-100 group-hover:text-blue-700">
                                    <i class="fas fa-folder"></i>
                                </span>
                                <div>
                                    <p class="font-mono text-sm font-semibold text-blue-950">{{ $c->case_number }}</p>
                                    @if($c->routed_to)
                                        <p class="text-[10px] font-medium text-slate-400">{{ $c->routed_to === 'LC' ? 'Local Committee' : 'Internal Committee' }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 max-w-[10rem]">
                            <span class="block truncate" title="{{ $c->displayComplainant() }}">{{ $c->displayComplainant() }}</span>
                            @if($c->is_anonymous)
                                <span class="text-[10px] text-indigo-600"><i class="fas fa-user-secret"></i> Anonymous</span>
                            @endif
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-700 max-w-[10rem]">
                            <span class="block truncate" title="{{ $c->respondent_name }}">{{ $c->respondent_name }}</span>
                        </td>
                        <td class="px-4 py-3.5">
                            <span class="inline-flex max-w-[11rem] items-center rounded-full px-2.5 py-1 text-[11px] font-semibold leading-tight ring-1 {{ $statusStyles[$c->status] ?? $defaultBadge }}">
                                {{ $c->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 text-sm text-slate-600 whitespace-nowrap">
                            {{ $c->created_at->format('d M Y') }}
                        </td>
                        <td class="px-4 py-3.5 lg:px-5">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('cases.operate', $c) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition hover:from-blue-700 hover:to-indigo-700">
                                    <i class="fas fa-play text-[10px]"></i> Operate
                                </a>
                                <a href="{{ route('complaints.show', $c) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                                    View
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                                <i class="fas fa-inbox text-xl"></i>
                            </span>
                            <p class="text-sm font-semibold text-slate-700">No cases found</p>
                            <p class="mt-1 text-xs text-slate-500">
                                @if($hasFilters)
                                    Try adjusting your search or filters.
                                @else
                                    Complaints filed via the portal will appear here.
                                @endif
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($complaints->hasPages())
        <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 lg:px-5">
            {{ $complaints->links() }}
        </div>
    @endif
</div>

@endsection
