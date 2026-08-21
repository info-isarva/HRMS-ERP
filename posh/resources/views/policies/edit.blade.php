@extends('layouts.posh')

@section('title', $policy->exists ? 'Edit Policy' : 'New Policy')
@section('page-title', $policy->exists ? 'Edit Policy' : 'New Policy')
@section('page-subtitle', '')

@php
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-blue-950 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-violet-900 text-white shadow-xl">
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4 min-w-0">
            <a href="{{ route('policies.index') }}" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 hover:bg-white/25 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="min-w-0">
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200/90">
                    {{ $policy->exists ? 'Edit version' : 'New version' }}
                </p>
                <h1 class="text-xl font-bold tracking-tight truncate lg:text-2xl">
                    {{ $policy->exists ? $policy->version : 'New policy' }}
                </h1>
                <p class="mt-1 text-sm text-indigo-100/90">Section 19 — written policy for employees</p>
            </div>
        </div>
        @if($policy->is_active)
            <span class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-500/30 px-3 py-1.5 text-xs font-semibold ring-1 ring-emerald-300/40 shrink-0">
                <i class="fas fa-circle-check"></i> Currently active
            </span>
        @endif
    </div>
</div>
@endsection

@section('content')

<form method="POST" action="{{ $policy->exists ? route('policies.update', $policy) : route('policies.store') }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @if($policy->exists) @method('PUT') @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 space-y-4">
            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-blue-950 mb-4">Version details</h2>
                <div class="space-y-4">
                    <div>
                        <label class="{{ $label }}">Version ID <span class="text-rose-500">*</span></label>
                        <input type="text" name="version" value="{{ old('version', $policy->version) }}" required class="{{ $input }} font-mono" placeholder="v2026.1">
                        <p class="mt-1 text-[11px] text-slate-500">Shown to employees (e.g. v2026.1)</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Policy title <span class="text-rose-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $policy->title) }}" required class="{{ $input }}" placeholder="POSH Workplace Policy">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm">
                <h2 class="text-sm font-semibold text-blue-950 mb-3">PDF attachment</h2>
                <p class="text-xs text-slate-500 mb-3">Optional downloadable copy alongside the online policy.</p>
                <input type="file" name="policy_file" accept="application/pdf"
                    class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100">
                @if($policy->file_path)
                    <p class="mt-2 text-xs text-emerald-700"><i class="fas fa-file-pdf mr-1"></i> PDF already attached</p>
                @endif
            </section>

            <section class="rounded-xl border border-indigo-200/80 bg-indigo-50/50 p-4 text-xs text-indigo-950 leading-relaxed">
                <p class="font-semibold mb-2">Publishing</p>
                <ul class="space-y-1.5 list-disc list-inside text-indigo-900/90">
                    <li><strong>Save draft</strong> — stored but not shown to employees</li>
                    <li><strong>Save &amp; publish</strong> — becomes the active policy immediately</li>
                </ul>
            </section>
        </div>

        <div class="lg:col-span-2">
            <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden h-full flex flex-col">
                <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-blue-950">Policy content</h2>
                    <span class="text-[10px] font-medium text-slate-500 uppercase tracking-wide">HTML supported</span>
                </div>
                <div class="p-5 flex-1">
                    <textarea name="content" rows="18" required
                        class="w-full min-h-[320px] rounded-xl border border-slate-200 bg-slate-50/50 px-4 py-3 text-sm leading-relaxed text-blue-950 font-mono focus:border-indigo-500 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 outline-none transition resize-y"
                        placeholder="<h2>Policy title</h2>&#10;<p>Your policy text…</p>">{{ old('content', $policy->content) }}</textarea>
                </div>
            </section>
        </div>
    </div>

    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-3 rounded-2xl border border-slate-200/80 bg-white px-5 py-4 shadow-sm">
        <a href="{{ route('policies.index') }}" class="inline-flex items-center justify-center gap-2 text-sm font-semibold text-slate-600 hover:text-indigo-700 transition">
            <i class="fas fa-arrow-left"></i> Back to list
        </a>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" name="publish" value="0"
                class="inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-blue-950 hover:bg-slate-50 transition">
                <i class="fas fa-save"></i> Save draft
            </button>
            <button type="submit" name="publish" value="1"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700 transition">
                <i class="fas fa-broadcast-tower"></i> Save &amp; publish
            </button>
        </div>
    </div>
</form>

@endsection
