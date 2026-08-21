@extends('layouts.posh')

@section('title', 'Settings')
@section('page-title', 'Organization Settings')
@section('page-subtitle', '')

@php
    $input = 'w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-blue-950 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 outline-none transition';
    $label = 'mb-1.5 block text-xs font-semibold uppercase tracking-wide text-slate-500';
    $locale = $org->settings['locale_default'] ?? 'en';
    $wa = $org->settings['whatsapp_number'] ?? '';
@endphp

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-indigo-800 to-violet-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-6 right-0 h-32 w-32 rounded-full bg-violet-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-5 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25">
                <i class="fas fa-building text-xl text-indigo-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200/90">Administration</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">Organization settings</h1>
                <p class="mt-1 text-sm text-indigo-100/90">{{ $org->display_name }} · {{ config('posh.product_name') }}</p>
            </div>
        </div>
        @if($org->intake_key)
            <span class="inline-flex items-center gap-2 rounded-xl bg-white/15 px-4 py-2 text-xs font-semibold ring-1 ring-white/25 shrink-0">
                <i class="fas fa-qrcode text-indigo-200"></i> Public intake enabled
            </span>
        @endif
    </div>
</div>
@endsection

@section('content')

@php
    $currentMode = $org->usesPayrollEmployees() ? 'erp' : 'standalone';
@endphp

{{-- Deployment mode toggle (demo / sales) --}}
<section class="mb-6 rounded-2xl border-2 border-indigo-200/80 bg-gradient-to-r from-indigo-50 via-white to-violet-50 p-5 shadow-sm">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <h2 class="text-sm font-bold text-blue-950 flex items-center gap-2">
                <i class="fas fa-sliders text-indigo-600"></i> Deployment mode
            </h2>
            <p class="mt-1 text-xs text-slate-600 max-w-xl">Switch how this organisation handles employees and login. POSH-only setting — does not change Payroll or HRMS.</p>
            <p class="mt-2 inline-flex items-center gap-2 rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-800">
                Active: {{ $org->deploymentLabel() }} · {{ $org->authModeLabel() }}
            </p>
        </div>
        <form method="POST" action="{{ route('settings.update') }}" class="flex flex-col sm:flex-row gap-2 shrink-0">
            @csrf
            @method('PUT')
            <input type="hidden" name="name" value="{{ $org->name }}">
            <input type="hidden" name="employee_count" value="{{ $org->employee_count }}">
            <input type="hidden" name="locale_default" value="{{ $locale }}">
            <button type="submit" name="deployment_mode" value="erp"
                class="rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $currentMode === 'erp' ? 'bg-indigo-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-indigo-300' }}">
                <i class="fas fa-link mr-1"></i> ERP (Payroll linked)
            </button>
            <button type="submit" name="deployment_mode" value="standalone"
                class="rounded-xl px-4 py-2.5 text-sm font-semibold transition {{ $currentMode === 'standalone' ? 'bg-violet-600 text-white shadow-md' : 'bg-white text-slate-700 border border-slate-200 hover:border-violet-300' }}">
                <i class="fas fa-cube mr-1"></i> Standalone POSH
            </button>
        </form>
    </div>
</section>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Workplace</p>
        <p class="mt-1 text-lg font-bold text-blue-950 truncate" title="{{ $org->display_name }}">{{ $org->display_name }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Employees</p>
        <p class="mt-1 text-2xl font-bold text-blue-950">{{ number_format($org->employee_count ?? 0) }}</p>
    </div>
    <div class="rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm">
        <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">Default language</p>
        <p class="mt-1 text-sm font-semibold text-blue-950">{{ config('posh.locales')[$locale] ?? 'English' }}</p>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <div class="xl:col-span-2">
        <section class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/80 px-5 py-4">
                <h2 class="text-sm font-semibold text-blue-950">Organization profile</h2>
                <p class="text-xs text-slate-500 mt-0.5">Name and defaults shown across the portal and reports</p>
            </div>
            <form method="POST" action="{{ route('settings.update') }}" class="p-5 space-y-5">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Organization name <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $org->name) }}" required class="{{ $input }}"
                            placeholder="e.g. Demo Workplace">
                        <p class="mt-1.5 text-[11px] text-slate-500">Displayed as <strong class="text-blue-950">{{ $org->display_name }}</strong> in the product UI.</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Employee count</label>
                        <input type="number" name="employee_count" value="{{ old('employee_count', $org->employee_count) }}" min="1" class="{{ $input }}" placeholder="50">
                        <p class="mt-1.5 text-[11px] text-slate-500">Used for compliance sizing context</p>
                    </div>
                    <div>
                        <label class="{{ $label }}">Default language</label>
                        <select name="locale_default" class="{{ $input }}">
                            @foreach(config('posh.locales') as $k => $v)
                                <option value="{{ $k }}" @selected(old('locale_default', $locale) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">WhatsApp helpline <span class="font-normal normal-case text-slate-400">(optional)</span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <i class="fab fa-whatsapp"></i>
                            </span>
                            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $wa) }}" class="{{ $input }} pl-10" placeholder="+91 98765 43210">
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-500">Shown on the employee portal for confidential help</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-100">
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:from-blue-700 hover:to-indigo-700 transition">
                        <i class="fas fa-save"></i> Save settings
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="xl:col-span-1 space-y-4">
        <section class="rounded-2xl border-2 border-indigo-200/80 bg-gradient-to-b from-indigo-50/80 to-white shadow-sm overflow-hidden">
            <div class="border-b border-indigo-100 bg-indigo-600/10 px-5 py-4">
                <h2 class="text-sm font-semibold text-blue-950 flex items-center gap-2">
                    <i class="fas fa-qrcode text-indigo-600"></i>
                    Public intake link
                </h2>
                <p class="text-xs text-slate-600 mt-1">Section 19 — poster &amp; QR display</p>
            </div>
            <div class="p-5 space-y-4">
                <p class="text-xs text-slate-600 leading-relaxed">
                    Share this link or print it as a QR code so anyone can file a complaint without logging in.
                </p>

                <div class="rounded-xl border border-slate-200 bg-slate-50/80 p-3">
                    <p class="text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-2">Intake URL</p>
                    <p id="intake-url" class="break-all font-mono text-xs text-indigo-800 leading-relaxed select-all">{{ $intakeUrl }}</p>
                </div>

                <div class="flex flex-col gap-2">
                    <button type="button" id="copy-intake-btn"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-white px-4 py-2.5 text-sm font-semibold text-indigo-800 hover:bg-indigo-50 transition">
                        <i class="fas fa-copy"></i> Copy link
                    </button>
                    <a href="{{ $intakeUrl }}" target="_blank" rel="noopener"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-blue-950 hover:border-indigo-300 hover:bg-indigo-50/50 transition">
                        <i class="fas fa-external-link-alt"></i> Open intake page
                    </a>
                </div>

                <form method="POST" action="{{ route('settings.intake.regenerate') }}"
                    onsubmit="return confirm('Regenerate this link? Existing QR codes and posters will stop working.');">
                    @csrf
                    <button type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-100 transition">
                        <i class="fas fa-rotate"></i> Regenerate link
                    </button>
                </form>
            </div>
        </section>

        <div class="rounded-xl border border-indigo-200/80 bg-indigo-50/50 px-4 py-3 text-xs text-indigo-950 leading-relaxed">
            <p class="font-semibold mb-1.5 flex items-center gap-1.5">
                <i class="fas fa-circle-info text-indigo-500"></i> Tips
            </p>
            <ul class="space-y-1.5 list-disc list-inside text-indigo-900/90">
                <li>Place the QR on notice boards and in induction packs</li>
                <li>Regenerate only if the link was shared publicly by mistake</li>
            </ul>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('copy-intake-btn');
    var urlEl = document.getElementById('intake-url');
    if (!btn || !urlEl) return;
    btn.addEventListener('click', function () {
        var text = urlEl.textContent.trim();
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(function () {
                btn.innerHTML = '<i class="fas fa-check"></i> Copied';
                setTimeout(function () {
                    btn.innerHTML = '<i class="fas fa-copy"></i> Copy link';
                }, 2000);
            });
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            btn.innerHTML = '<i class="fas fa-check"></i> Copied';
            setTimeout(function () { btn.innerHTML = '<i class="fas fa-copy"></i> Copy link'; }, 2000);
        }
    });
})();
</script>
@endpush

@endsection
