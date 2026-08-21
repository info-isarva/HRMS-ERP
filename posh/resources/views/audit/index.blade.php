@extends('layouts.posh')

@section('title', 'Audit Log')
@section('page-title', 'Audit Log')
@section('page-subtitle', '')

@php
    $hasFilters = request()->filled('q');
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-blue-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-6 right-1/4 h-28 w-28 rounded-full bg-slate-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-clock-rotate-left text-xl text-slate-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-300/90">Compliance trail</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Audit Log</h1>
                <p class="mt-1 text-sm text-slate-400">Immutable record of system activity</p>
            </div>
        </div>
        <span class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-semibold ring-1 ring-white/20 shrink-0">
            <i class="fas fa-list text-slate-300"></i>
            {{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }}
        </span>
    </div>
</div>
@endsection

@section('content')

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 lg:px-5">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
            <div class="relative flex-1 min-w-0">
                <i class="fas fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-slate-400"></i>
                <input type="text" name="q" value="{{ request('q') }}"
                    placeholder="Search action, case ID, or details…"
                    class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition">
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="submit"
                    class="inline-flex flex-1 sm:flex-none items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700 transition">
                    <i class="fas fa-filter text-xs"></i> Filter
                </button>
                @if($hasFilters)
                    <a href="{{ route('audit.index') }}"
                        class="inline-flex items-center justify-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50"
                        title="Clear search">
                        <i class="fas fa-xmark"></i><span class="hidden sm:inline">Clear</span>
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($logs->isEmpty())
        <div class="px-6 py-16 text-center">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-3">
                <i class="fas fa-clipboard-list text-xl"></i>
            </span>
            <p class="text-sm font-semibold text-slate-700">No audit entries</p>
            <p class="mt-1 text-xs text-slate-500">
                @if($hasFilters)
                    Try a different search term.
                @else
                    Activity from complaints, case operations, and admin actions appears here.
                @endif
            </p>
        </div>
    @else
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] border-collapse">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50/80">
                        <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500 w-[11rem]">When</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Activity</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Case</th>
                        <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Details</th>
                        <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500 hidden lg:table-cell">Source</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($logs as $log)
                        @php
                            $action = strtolower($log->action ?? '');
                            $icon = 'fa-circle-dot';
                            $iconBg = 'bg-slate-100 text-slate-600';
                            if (str_contains($action, 'complaint') || str_contains($action, 'filed') || str_contains($action, 'intake')) {
                                $icon = 'fa-file-circle-plus';
                                $iconBg = 'bg-blue-100 text-blue-600';
                            } elseif (str_contains($action, 'status')) {
                                $icon = 'fa-arrows-rotate';
                                $iconBg = 'bg-indigo-100 text-indigo-600';
                            } elseif (str_contains($action, 'evidence') || str_contains($action, 'download')) {
                                $icon = 'fa-download';
                                $iconBg = 'bg-violet-100 text-violet-600';
                            } elseif (str_contains($action, 'seed') || str_contains($action, 'test')) {
                                $icon = 'fa-flask';
                                $iconBg = 'bg-amber-100 text-amber-700';
                            } elseif (str_contains($action, 'operate') || str_contains($action, 'step')) {
                                $icon = 'fa-pen';
                                $iconBg = 'bg-teal-100 text-teal-700';
                            }
                        @endphp
                        <tr class="group transition hover:bg-slate-50/80">
                            <td class="px-5 py-3.5 whitespace-nowrap">
                                <p class="text-sm font-medium text-blue-950">{{ $log->created_at->format('d M Y') }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $log->created_at->format('H:i') }}</p>
                            </td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
                                        <i class="fas {{ $icon }} text-xs"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-blue-950">{{ $log->action }}</p>
                                        @if($log->user)
                                            <p class="text-[11px] text-slate-500 mt-0.5">
                                                <i class="fas fa-user text-[9px] mr-0.5"></i>{{ $log->user->name }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                @if($log->case_number)
                                    <span class="inline-flex font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-1 rounded-md">
                                        {{ $log->case_number }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3.5 max-w-xs">
                                <p class="text-sm text-slate-600 leading-snug" title="{{ $log->details }}">
                                    {{ $log->details ? Str::limit($log->details, 100) : '—' }}
                                </p>
                            </td>
                            <td class="px-5 py-3.5 text-right hidden lg:table-cell">
                                @if($log->ip_address)
                                    <span class="inline-flex items-center gap-1 rounded-md bg-slate-50 px-2 py-1 text-[10px] font-mono text-slate-500 ring-1 ring-slate-200/80">
                                        <i class="fas fa-network-wired text-[8px]"></i>{{ $log->ip_address }}
                                    </span>
                                @else
                                    <span class="text-slate-300 text-xs">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="border-t border-slate-100 bg-slate-50/50 px-4 py-3 lg:px-5">
                {{ $logs->links() }}
            </div>
        @endif
    @endif
</div>

<div class="mt-4 flex gap-3 rounded-xl border border-slate-200/80 bg-slate-50/80 px-4 py-3 text-xs text-slate-600 leading-relaxed">
    <i class="fas fa-lock text-slate-400 mt-0.5 shrink-0"></i>
    <p>Audit entries are append-only and support compliance reviews. They cannot be edited or deleted from this screen.</p>
</div>

@endsection
