@extends('layouts.posh')

@section('title', 'Policy')
@section('page-title', 'POSH Policy')
@section('page-subtitle', '')

@php
    $activePolicy = $policies->firstWhere('is_active', true);
    $draftCount = $policies->where('is_active', false)->count();
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-violet-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-6 right-0 h-32 w-32 rounded-full bg-violet-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                <i class="fas fa-file-contract text-xl text-indigo-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200/90">Section 19</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Workplace policy</h1>
                <p class="mt-1 text-sm text-indigo-100/90">Publish versions for employee acknowledgement</p>
            </div>
        </div>
        <a href="{{ route('policies.create') }}"
            class="inline-flex items-center gap-2 rounded-xl bg-white px-5 py-2.5 text-sm font-semibold text-indigo-900 shadow-md hover:bg-indigo-50 transition shrink-0">
            <i class="fas fa-plus"></i> New policy version
        </a>
    </div>
</div>
@endsection

@section('content')

@if($activePolicy)
<div class="mb-6 rounded-2xl border-2 border-emerald-200/80 bg-gradient-to-r from-emerald-50 to-teal-50/50 p-5 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div class="flex items-start gap-4 min-w-0">
            <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                <i class="fas fa-circle-check text-lg"></i>
            </span>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-800">Live for employees</p>
                <h2 class="text-lg font-bold text-blue-950 truncate">{{ $activePolicy->title }}</h2>
                <p class="mt-1 text-sm text-emerald-900/80">
                    Version <span class="font-mono font-semibold">{{ $activePolicy->version }}</span>
                    @if($activePolicy->published_at)
                        · Published {{ $activePolicy->published_at->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <a href="{{ route('employee.policy') }}" target="_blank"
                class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 transition">
                <i class="fas fa-eye"></i> Preview as employee
            </a>
            <a href="{{ route('policies.edit', $activePolicy) }}"
                class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                <i class="fas fa-pen"></i> Edit
            </a>
        </div>
    </div>
</div>
@endif

<div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold text-blue-950">All policy versions</h2>
            <p class="text-xs text-slate-500 mt-0.5">{{ $policies->count() }} total · {{ $draftCount }} draft{{ $draftCount === 1 ? '' : 's' }}</p>
        </div>
    </div>

    @if($policies->isEmpty())
        <div class="px-6 py-16 text-center">
            <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-500 mb-3">
                <i class="fas fa-file-contract text-2xl"></i>
            </span>
            <p class="text-sm font-semibold text-blue-950">No policies created yet</p>
            <p class="mt-2 text-xs text-slate-500 max-w-md mx-auto">Create your first workplace POSH policy so employees can read and acknowledge it.</p>
            <a href="{{ route('policies.create') }}"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700">
                <i class="fas fa-plus"></i> Create first policy
            </a>
        </div>
    @else
        <div class="divide-y divide-slate-100">
            @foreach($policies as $policy)
                <article class="flex flex-col lg:flex-row lg:items-center gap-4 px-5 py-4 transition hover:bg-indigo-50/30 {{ $policy->is_active ? 'bg-emerald-50/20' : '' }}">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl font-mono text-xs font-bold {{ $policy->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ Str::limit($policy->version, 6, '') }}
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold text-blue-950 truncate">{{ $policy->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5">
                                Version <span class="font-mono">{{ $policy->version }}</span>
                                · {{ $policy->published_at ? 'Published ' . $policy->published_at->format('d M Y') : 'Not published' }}
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 lg:shrink-0">
                        @if($policy->is_active)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200/60">
                                <i class="fas fa-broadcast-tower text-[10px]"></i> Active
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-slate-200/60">
                                Draft
                            </span>
                        @endif

                        <a href="{{ route('policies.edit', $policy) }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-blue-950 hover:border-indigo-300 hover:bg-indigo-50 transition">
                            <i class="fas fa-pen text-[10px]"></i> Edit
                        </a>

                        @if(!$policy->is_active)
                            <form method="POST" action="{{ route('policies.activate', $policy) }}" class="inline"
                                onsubmit="return confirm('Publish {{ $policy->version }}? This will replace the current active policy for all employees.');">
                                @csrf
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:from-blue-700 hover:to-indigo-700 transition">
                                    <i class="fas fa-upload text-[10px]"></i> Publish live
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>

<div class="mt-4 flex gap-3 rounded-xl border border-indigo-200/80 bg-indigo-50/50 px-4 py-3 text-xs text-indigo-950 leading-relaxed">
    <i class="fas fa-circle-info text-indigo-500 mt-0.5 shrink-0"></i>
    <p>Only <strong>one policy</strong> can be active at a time. Publishing a version makes it visible on the employee portal for acknowledgement. Older versions stay in the list for records.</p>
</div>

@endsection
