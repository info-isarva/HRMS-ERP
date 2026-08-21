@extends('layouts.posh')

@section('title', 'Annual Reports')
@section('page-title', 'Annual Compliance Report')
@section('page-subtitle', '')

@php
    $currentYear = (int) now()->year;
    $submittedCount = $reports->whereNotNull('submitted_at')->count();
    $pendingCount = $reports->count() - $submittedCount;
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-900 to-violet-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -bottom-8 left-1/3 h-32 w-32 rounded-full bg-violet-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-file-lines text-xl text-violet-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-violet-200/90">Section 22</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Annual Compliance Report</h1>
                <p class="mt-1 text-sm text-slate-300">District Officer submission — Rule 14</p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            @if($reports->isNotEmpty())
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-xs font-semibold ring-1 ring-white/20">
                    <i class="fas fa-check text-emerald-300"></i> {{ $submittedCount }} submitted
                </span>
                <span class="inline-flex items-center gap-1.5 rounded-xl bg-white/10 px-3 py-2 text-xs font-semibold ring-1 ring-white/20">
                    <i class="fas fa-clock text-amber-200"></i> {{ $pendingCount }} pending
                </span>
            @endif
        </div>
    </div>
</div>
@endsection

@section('content')

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    {{-- Generate — single line --}}
    <div class="border-b border-slate-100 bg-slate-50/60 px-4 py-3 lg:px-5">
        <form method="POST" action="{{ route('reports.annual.generate') }}"
            class="flex flex-col sm:flex-row sm:items-center gap-3">
            @csrf
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-100 text-indigo-600">
                    <i class="fas fa-wand-magic-sparkles text-sm"></i>
                </span>
                <div>
                    <p class="text-sm font-semibold text-blue-950">Generate report</p>
                    <p class="text-xs text-slate-500 hidden sm:block">Compiles cases, prevention events &amp; S.19 duty details for the year</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0 sm:ml-auto">
                <label for="report-year" class="sr-only">Report year</label>
                <input type="number" name="year" id="report-year" value="{{ $currentYear }}" min="2020" max="2099"
                    class="w-24 rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm font-semibold text-slate-700 text-center focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-violet-700 transition">
                    <i class="fas fa-file-circle-plus"></i> Generate
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] border-collapse">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50/80">
                    <th class="px-5 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Year</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Generated</th>
                    <th class="px-4 py-3 text-left text-[10px] font-bold uppercase tracking-wider text-slate-500">Submission</th>
                    <th class="px-5 py-3 text-right text-[10px] font-bold uppercase tracking-wider text-slate-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($reports as $r)
                    <tr class="transition hover:bg-indigo-50/30">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2.5">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-100 font-bold text-indigo-700 text-sm">
                                    {{ substr((string) $r->report_year, -2) }}
                                </span>
                                <span class="text-lg font-bold text-blue-950">{{ $r->report_year }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-4 text-sm text-slate-600">
                            {{ $r->generated_at?->format('d M Y') ?? '—' }}
                            @if($r->generated_at)
                                <span class="block text-[10px] text-slate-400">{{ $r->generated_at->format('H:i') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-4">
                            @if($r->submitted_at)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/60">
                                    <i class="fas fa-check text-[10px]"></i> {{ $r->submitted_at->format('d M Y') }}
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900 ring-1 ring-amber-200/60">
                                    <i class="fas fa-clock text-[10px]"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('reports.annual.show', $r) }}"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-indigo-600 to-violet-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:from-indigo-700 hover:to-violet-700">
                                    <i class="fas fa-eye text-[10px]"></i> View
                                </a>
                                <a href="{{ route('reports.annual.export', $r) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                    <i class="fas fa-file-pdf text-[10px] text-rose-500"></i> Export
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-16 text-center">
                            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 mb-3">
                                <i class="fas fa-file-lines text-xl"></i>
                            </span>
                            <p class="text-sm font-semibold text-slate-700">No annual reports yet</p>
                            <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                                Generate a report for {{ $currentYear }} to compile case statistics and compliance data for the District Officer.
                            </p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4 flex gap-3 rounded-xl border border-indigo-200/80 bg-indigo-50/50 px-4 py-3 text-xs text-indigo-950 leading-relaxed">
    <i class="fas fa-circle-info text-indigo-500 mt-0.5 shrink-0"></i>
    <p>
        Under <strong>Section 22</strong>, the employer must submit an annual report with prescribed particulars to the District Officer.
        Regenerate anytime to refresh figures before marking as submitted.
    </p>
</div>

@endsection
