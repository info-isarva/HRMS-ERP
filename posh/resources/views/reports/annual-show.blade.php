@extends('layouts.posh')

@section('title', 'Report ' . $report->report_year)
@section('page-title', 'Annual Report ' . $report->report_year)
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-900 to-violet-900 text-white shadow-xl">
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20 text-lg font-bold">
                {{ substr((string) $report->report_year, -2) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-200/90">Annual report</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">{{ \App\Models\Organization::sanitizeDisplayName($data['organization'] ?? '') }}</h1>
                <p class="mt-1 text-sm text-slate-300">Year {{ $report->report_year }}
                    @if($report->submitted_at)
                        · Submitted {{ $report->submitted_at->format('d M Y') }}
                    @else
                        · <span class="text-amber-200">Not yet submitted to District Officer</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('reports.annual.index') }}"
                class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition">
                <i class="fas fa-arrow-left"></i> All reports
            </a>
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-3 flex items-center justify-between gap-3">
        <h2 class="text-sm font-semibold text-blue-950">Report particulars (Section 22)</h2>
        @if($report->submitted_at)
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
                <i class="fas fa-check"></i> Submitted
            </span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">
                <i class="fas fa-clock"></i> Draft
            </span>
        @endif
    </div>
    <div class="p-5 lg:p-6">
        @include('reports._annual-body', ['report' => $report, 'data' => $data])
    </div>
</div>

<div class="mt-4 flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-3">
    <a href="{{ route('reports.annual.export', $report) }}" target="_blank"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-indigo-700 hover:to-violet-700 transition">
        <i class="fas fa-print"></i> Print / export PDF
    </a>
    @if(!$report->submitted_at)
        <form method="POST" action="{{ route('reports.annual.submitted', $report) }}" class="sm:ml-auto">
            @csrf
            <button type="submit"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100">
                <i class="fas fa-paper-plane"></i> Mark submitted to District Officer
            </button>
        </form>
    @endif
</div>

@endsection
