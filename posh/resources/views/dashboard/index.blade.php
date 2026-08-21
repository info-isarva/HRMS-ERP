@extends('layouts.posh')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-12 -right-12 h-48 w-48 rounded-full bg-blue-400 blur-3xl"></div>
        <div class="absolute bottom-0 left-1/4 h-32 w-64 rounded-full bg-indigo-500 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-7 lg:px-8 lg:py-8">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20 backdrop-blur">
                    <i class="fas fa-shield-halved text-2xl text-blue-200"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">POSH Act, 2013</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight lg:text-3xl">Compliance Command Center</h1>
                    <p class="mt-2 max-w-xl text-sm text-slate-300 leading-relaxed">
                        Confidential workplace safety overview for {{ auth()->user()->organization?->display_name ?? 'your organization' }}.
                    </p>
                    @if(auth()->user()->organization)
                        <span class="mt-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-indigo-100 ring-1 ring-white/20">
                            <i class="fas fa-sliders"></i> {{ auth()->user()->organization->deploymentLabel() }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2 lg:justify-end">
                @if(auth()->user()->hasIcAccess())
                    <a href="{{ route('cases.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-medium ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20">
                        <i class="fas fa-folder-open"></i> All cases
                    </a>
                    <a href="{{ route('compliance.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-900 shadow-lg transition hover:bg-blue-50">
                        <i class="fas fa-clipboard-check"></i> Compliance
                    </a>
                @else
                    <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-blue-900 shadow-lg transition hover:bg-blue-50">
                        <i class="fas fa-file-circle-plus"></i> File complaint
                    </a>
                    <a href="{{ route('employee.portal') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2.5 text-sm font-medium ring-1 ring-white/20 backdrop-blur transition hover:bg-white/20">
                        <i class="fas fa-user-shield"></i> Employee portal
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .posh-metric {
        position: relative;
        overflow: hidden;
        border-radius: 1rem;
        border: 1px solid rgba(226, 232, 240, 0.9);
        background: #fff;
        padding: 1.25rem 1.35rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04), 0 8px 24px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .posh-metric:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(37, 99, 235, 0.08); }
    .posh-metric::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        border-radius: 1rem 1rem 0 0;
    }
    .posh-metric--blue::before { background: linear-gradient(90deg, #2563eb, #6366f1); }
    .posh-metric--amber::before { background: linear-gradient(90deg, #d97706, #f59e0b); }
    .posh-metric--emerald::before { background: linear-gradient(90deg, #059669, #10b981); }
    .posh-metric--violet::before { background: linear-gradient(90deg, #4f46e5, #7c3aed); }
    .posh-metric--slate::before { background: linear-gradient(90deg, #334155, #64748b); }
    .posh-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 0.2rem 0.65rem;
        font-size: 0.68rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        border-radius: 9999px;
        background: #f1f5f9;
        color: #334155;
        border: 1px solid #e2e8f0;
    }
    .posh-case-row {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 0.75rem;
        border: 1px solid #f1f5f9;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .posh-case-row:hover {
        border-color: #dbeafe;
        box-shadow: 0 4px 16px rgba(59, 130, 246, 0.06);
    }
    .posh-alert-item {
        display: flex;
        align-items: flex-start;
        gap: 0.85rem;
        padding: 0.9rem 1rem;
        border-radius: 0.75rem;
        background: #fff;
        border: 1px solid #fecaca;
    }
    .posh-alert-item--warn { border-color: #fde68a; background: linear-gradient(135deg, #fffbeb 0%, #fff 100%); }
    .posh-alert-item--danger { border-color: #fecaca; background: linear-gradient(135deg, #fef2f2 0%, #fff 100%); }
</style>
@endpush

@section('content')

{{-- KPI metrics --}}
<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 {{ auth()->user()->hasIcAccess() ? 'xl:grid-cols-5' : 'xl:grid-cols-4' }}">
    <a href="{{ auth()->user()->hasIcAccess() ? route('cases.index') : route('complaints.my') }}" class="posh-metric posh-metric--blue block no-underline text-inherit">
        <div class="flex items-start justify-between">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                <i class="fas fa-layer-group text-lg"></i>
            </div>
            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400">All time</span>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-blue-950">{{ $stats['total_cases'] }}</p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">Total cases</p>
    </a>

    <div class="posh-metric posh-metric--amber">
        <div class="flex items-start justify-between">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                <i class="fas fa-hourglass-half text-lg"></i>
            </div>
            @if($stats['total_cases'] > 0)
                <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold text-amber-800">
                    {{ round(($stats['open_cases'] / max($stats['total_cases'], 1)) * 100) }}%
                </span>
            @endif
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-blue-950">{{ $stats['open_cases'] }}</p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">Open inquiries</p>
    </div>

    <div class="posh-metric posh-metric--emerald">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
            <i class="fas fa-circle-check text-lg"></i>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-blue-950">{{ $stats['closed_cases'] }}</p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">Closed / resolved</p>
    </div>

    <div class="posh-metric posh-metric--slate">
        <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
            <i class="fas fa-people-group text-lg"></i>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-blue-950">{{ $stats['ic_members'] }}</p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">IC members active</p>
    </div>

    @if(auth()->user()->hasIcAccess())
    <div class="posh-metric posh-metric--violet">
        <div class="flex items-start justify-between">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                <i class="fas fa-clipboard-check text-lg"></i>
            </div>
            <div class="h-8 w-8 rounded-full border-2 border-violet-200 flex items-center justify-center text-[10px] font-bold text-violet-700">
                {{ min(100, $compliancePercent) }}%
            </div>
        </div>
        <p class="mt-4 text-3xl font-bold tracking-tight text-blue-950">{{ $compliancePercent }}%</p>
        <p class="mt-0.5 text-sm font-medium text-slate-500">S.19 employer duties</p>
    </div>
    @endif
</div>

{{-- Policy acknowledgement --}}
@if($activePolicy && !$userAcked)
<div class="flex items-start gap-4 rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 via-white to-orange-50 p-5 shadow-sm">
    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
        <i class="fas fa-file-contract text-xl"></i>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-amber-950">Policy acknowledgement pending</p>
        <p class="mt-1 text-sm text-amber-800/80">Review the active POSH policy ({{ $activePolicy->version }}) to complete your compliance record.</p>
    </div>
    <a href="{{ route('employee.policy') }}" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition hover:bg-amber-700">
        Review policy
    </a>
</div>
@endif

{{-- SLA alerts --}}
@if(isset($slaAlerts) && $slaAlerts->isNotEmpty())
<div class="rounded-2xl border border-red-200/60 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center gap-3 border-b border-red-100 bg-gradient-to-r from-red-50 to-white px-5 py-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-red-100 text-red-600">
            <i class="fas fa-triangle-exclamation"></i>
        </div>
        <div>
            <h2 class="text-base font-semibold text-red-900">Statutory SLA attention</h2>
            <p class="text-xs text-red-700/70">{{ $slaAlerts->count() }} case(s) need immediate IC review</p>
        </div>
    </div>
    <div class="space-y-3 p-5">
        @foreach($slaAlerts as $alert)
            <div class="posh-alert-item {{ $alert['type'] === 'danger' ? 'posh-alert-item--danger' : 'posh-alert-item--warn' }}">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $alert['type'] === 'danger' ? 'bg-red-100 text-red-600' : 'bg-amber-100 text-amber-600' }}">
                    <i class="fas {{ $alert['type'] === 'danger' ? 'fa-clock' : 'fa-bell' }} text-sm"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-blue-950">{{ $alert['case'] }}</p>
                    <p class="text-sm text-slate-600 mt-0.5">{{ $alert['msg'] }}</p>
                </div>
                <span class="shrink-0 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide {{ $alert['type'] === 'danger' ? 'bg-red-600 text-white' : 'bg-amber-500 text-white' }}">
                    {{ $alert['type'] === 'danger' ? 'Overdue' : 'Due soon' }}
                </span>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- Recent cases --}}
<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                <i class="fas fa-folder-tree"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-blue-950">Recent case activity</h2>
                <p class="text-xs text-slate-500">Latest filings — confidential to IC / complainant</p>
            </div>
        </div>
        @if(auth()->user()->hasIcAccess())
            <a href="{{ route('cases.index') }}" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-700">
                View all <i class="fas fa-arrow-right text-xs"></i>
            </a>
        @else
            <a href="{{ route('complaints.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-plus text-xs"></i> New complaint
            </a>
        @endif
    </div>

    <div class="p-5 space-y-3">
        @forelse($recentCases as $c)
            <div class="posh-case-row">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 font-mono text-xs font-bold text-slate-600">
                    {{ substr($c->case_number, -4) }}
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-blue-950">{{ $c->case_number }}</span>
                        <span class="posh-status-pill">{{ $c->status }}</span>
                        <span class="text-[10px] font-medium uppercase tracking-wider text-slate-400">{{ $c->routed_to }}</span>
                    </div>
                    <p class="mt-1 text-sm text-slate-500 truncate">
                        {{ $c->displayComplainant() }} <span class="text-slate-300">vs</span> {{ $c->respondent_name }}
                    </p>
                    <p class="mt-0.5 text-xs text-slate-400">Filed {{ $c->created_at->format('d M Y') }}</p>
                </div>
                @if(auth()->user()->hasIcAccess())
                    <a href="{{ route('cases.operate', $c) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:from-blue-700 hover:to-indigo-700">
                        Operate <i class="fas fa-chevron-right text-[10px] opacity-80"></i>
                    </a>
                @else
                    <a href="{{ route('complaints.show', $c) }}" class="shrink-0 inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50">
                        View
                    </a>
                @endif
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 py-12 text-center">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                    <i class="fas fa-inbox text-2xl"></i>
                </div>
                <p class="mt-4 font-medium text-slate-600">No cases recorded yet</p>
                <p class="mt-1 text-sm text-slate-400">Complaints filed through the portal will appear here.</p>
                <a href="{{ route('complaints.create') }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-blue-600 hover:text-blue-800">
                    <i class="fas fa-file-circle-plus"></i> File first complaint
                </a>
            </div>
        @endforelse
    </div>
</div>

{{-- Footer strip --}}
<div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200/60 bg-white/60 px-4 py-3 text-xs text-slate-500 backdrop-blur">
    <span><i class="fas fa-lock mr-1.5 text-slate-400"></i> All case data is confidential under Section 16, POSH Act</span>
    @if($stats['active_policy'] ?? false)
        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 font-medium text-emerald-700 ring-1 ring-emerald-200/60">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Active policy published
        </span>
    @endif
</div>

@endsection
