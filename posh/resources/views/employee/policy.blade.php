@extends('layouts.posh')

@section('title', 'POSH Policy')
@section('page-title', 'POSH Policy')
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-8 right-0 h-40 w-40 rounded-full bg-indigo-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-6 lg:px-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/20">
                <i class="fas fa-file-contract text-xl text-blue-200"></i>
            </div>
            <div>
                <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Policy acknowledgement</p>
                <h1 class="text-xl font-bold tracking-tight lg:text-2xl">{{ $policy->title }}</h1>
                <p class="mt-1 text-sm text-slate-300">Version <span class="font-semibold text-white">{{ $policy->version }}</span>
                    @if($policy->published_at)
                        · Published {{ $policy->published_at->format('d M Y') }}
                    @endif
                </p>
            </div>
        </div>
        <a href="{{ route('employee.portal') }}" class="inline-flex items-center gap-2 rounded-xl bg-white/10 px-4 py-2 text-sm font-medium ring-1 ring-white/20 hover:bg-white/20 transition shrink-0">
            <i class="fas fa-arrow-left"></i> Back to portal
        </a>
    </div>
</div>
@endsection

@section('content')

@push('styles')
<style>
    .policy-doc-panel {
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }
    .policy-doc-toolbar {
        background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
        border-bottom: 1px solid #e2e8f0;
        padding: 1rem 1.25rem;
    }
    .policy-doc-scroll {
        max-height: min(480px, 55vh);
        overflow-y: auto;
        scroll-behavior: smooth;
    }
    .policy-doc-scroll::-webkit-scrollbar { width: 6px; }
    .policy-doc-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 999px; }
    .policy-doc-body {
        padding: 1.75rem 2rem 2rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        border-left: 4px solid #3b82f6;
        margin: 1rem 1.25rem 1.25rem;
        border-radius: 0 0.75rem 0.75rem 0;
        box-shadow: inset 0 0 0 1px #e2e8f0;
    }
    .policy-doc-body .policy-content h2 {
        margin: 0 0 1rem;
        font-size: 1.35rem;
        font-weight: 700;
        color: #1e3a8a;
        letter-spacing: -0.02em;
        padding-bottom: 0.75rem;
        border-bottom: 2px solid #e0e7ff;
    }
    .policy-doc-body .policy-content p {
        margin: 0 0 1.15rem;
        font-size: 0.9375rem;
        line-height: 1.75;
        color: #475569;
    }
    .policy-doc-body .policy-content ul {
        margin: 0 0 1.25rem;
        padding: 0;
        list-style: none;
    }
    .policy-doc-body .policy-content ul li {
        position: relative;
        padding: 0.65rem 0 0.65rem 2.25rem;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        line-height: 1.6;
        color: #334155;
        background: #fff;
        border: 1px solid #f1f5f9;
        border-radius: 0.5rem;
    }
    .policy-doc-body .policy-content ul li::before {
        content: '\f00c';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0.75rem;
        top: 0.85rem;
        color: #2563eb;
        font-size: 0.7rem;
    }
    .policy-highlight {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 0.75rem;
        padding: 0 1.25rem 1rem;
    }
    .policy-highlight-card {
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        border: 1px solid #e2e8f0;
        background: #fff;
    }
    .policy-highlight-card i {
        font-size: 1rem;
        margin-bottom: 0.35rem;
        display: block;
    }
    .policy-highlight-card span {
        font-size: 0.72rem;
        font-weight: 600;
        color: #475569;
        line-height: 1.35;
    }
    .policy-read-track {
        height: 3px;
        background: #e2e8f0;
    }
    #policy-read-progress {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, #2563eb, #6366f1);
        transition: width 0.12s ease-out;
    }
    .ack-trust-row {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.65rem 0.75rem;
        border-radius: 0.75rem;
        border: 1px solid #f1f5f9;
        background: #fff;
    }
    .ack-trust-icon {
        flex-shrink: 0;
        display: flex;
        height: 2rem;
        width: 2rem;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 0.75rem;
    }
    .ack-trust-text {
        font-size: 0.8125rem;
        line-height: 1.35;
        color: #475569;
    }
    .ack-consent:has(input:checked) {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
    }
</style>
@endpush

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    {{-- Policy document (left) --}}
    <div class="xl:col-span-2">
        <div class="policy-doc-panel">
            <div class="policy-read-track"><div id="policy-read-progress"></div></div>

            <div class="policy-doc-toolbar">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-white shadow-sm">
                            <i class="fas fa-scale-balanced"></i>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-blue-950">Official policy document</p>
                            <p class="text-xs text-slate-500">Sexual Harassment of Women at Workplace Act, 2013</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-800 ring-1 ring-blue-200/60">
                            <i class="fas fa-tag text-[10px]"></i> {{ $policy->version }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">
                            <i class="fas fa-clock text-[10px]"></i> ~2 min read
                        </span>
                    </div>
                </div>
            </div>

            <div class="policy-highlight">
                <div class="policy-highlight-card">
                    <i class="fas fa-user-shield text-blue-600"></i>
                    <span>Right to file with Internal Committee</span>
                </div>
                <div class="policy-highlight-card">
                    <i class="fas fa-lock text-indigo-600"></i>
                    <span>Confidential handling (S.16)</span>
                </div>
                <div class="policy-highlight-card">
                    <i class="fas fa-ban text-rose-600"></i>
                    <span>No retaliation — misconduct if violated</span>
                </div>
            </div>

            <div id="policy-doc-scroll" class="policy-doc-scroll">
                <div class="policy-doc-body">
                    <div class="policy-content">
                        {!! $policy->content !!}
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-100 bg-slate-50/90 px-5 py-3 text-xs text-slate-500">
                <span><i class="fas fa-shield-halved mr-1 text-slate-400"></i> Employer-published workplace policy</span>
                @if($policy->published_at)
                    <span>Effective {{ $policy->published_at->format('d M Y') }}</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Acknowledgement panel --}}
    <div class="xl:col-span-1">
        <div class="ack-panel sticky top-24 overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-lg shadow-slate-200/50">
            <div class="relative bg-gradient-to-br from-blue-600 via-indigo-600 to-violet-700 px-5 py-5 text-white">
                <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>
                <div class="relative flex items-start gap-3">
                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25 shadow-inner">
                        <i class="fas fa-file-signature text-lg"></i>
                    </span>
                    <div>
                        <h2 class="text-base font-bold tracking-tight">Confirm acknowledgement</h2>
                        <p class="mt-1 text-xs leading-relaxed text-blue-100/95">Section 19 compliance record for your organisation</p>
                    </div>
                </div>
                <span class="relative mt-3 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-[10px] font-semibold uppercase tracking-wider ring-1 ring-white/20">
                    <i class="fas fa-shield-halved"></i> Legally recorded
                </span>
            </div>

            @if($hasAcked ?? false)
                <div class="p-5">
                    <div class="rounded-2xl border border-emerald-200 bg-gradient-to-br from-emerald-50 to-teal-50/50 p-5 text-center">
                        <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-500 text-white shadow-lg shadow-emerald-500/30">
                            <i class="fas fa-check text-xl"></i>
                        </span>
                        <p class="mt-4 text-sm font-bold text-emerald-900">You’re all set</p>
                        <p class="mt-1 text-xs text-emerald-800/80 leading-relaxed">
                            Policy <strong>{{ $policy->version }}</strong> acknowledged
                            @if($acknowledgement?->acknowledged_at)
                                on {{ $acknowledgement->acknowledged_at->format('d M Y') }}
                            @endif
                        </p>
                        <a href="{{ route('employee.portal') }}"
                            class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50">
                            <i class="fas fa-arrow-left"></i> Back to portal
                        </a>
                    </div>
                </div>
            @else
                <div class="p-5 space-y-4">
                    <div class="rounded-xl border border-slate-100 bg-slate-50/80 px-3 py-2.5">
                        <div class="flex items-center justify-between text-[10px] font-semibold uppercase tracking-wide text-slate-500 mb-1.5">
                            <span>Policy read progress</span>
                            <span id="ack-read-label">0%</span>
                        </div>
                        <div class="h-1.5 rounded-full bg-slate-200 overflow-hidden">
                            <div id="ack-read-bar" class="h-full rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-200" style="width: 0%"></div>
                        </div>
                        <p class="mt-1.5 text-[11px] text-slate-400">Scroll the document on the left before confirming</p>
                    </div>

                    <ul class="space-y-2">
                        <li class="ack-trust-row">
                            <span class="ack-trust-icon bg-emerald-100 text-emerald-600"><i class="fas fa-book-open"></i></span>
                            <span class="ack-trust-text">You have reviewed the policy above</span>
                        </li>
                        <li class="ack-trust-row">
                            <span class="ack-trust-icon bg-blue-100 text-blue-600"><i class="fas fa-lock"></i></span>
                            <span class="ack-trust-text">Acceptance is logged with date &amp; time</span>
                        </li>
                        <li class="ack-trust-row">
                            <span class="ack-trust-icon bg-violet-100 text-violet-600"><i class="fas fa-scale-balanced"></i></span>
                            <span class="ack-trust-text">Filing a complaint does <strong>not</strong> require this</span>
                        </li>
                    </ul>

                    <form method="POST" action="{{ route('employee.policy.acknowledge') }}" id="ack-form" class="space-y-4">
                        @csrf
                        <label class="ack-consent group flex cursor-pointer items-start gap-3 rounded-2xl border-2 border-slate-200 bg-white p-4 shadow-sm transition hover:border-indigo-300 hover:shadow-md has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50/30 has-[:checked]:shadow-indigo-100">
                            <input type="checkbox" name="ack" id="ack-checkbox" required
                                class="mt-0.5 h-5 w-5 shrink-0 rounded-md border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-sm font-medium leading-snug text-slate-700 group-has-[:checked]:text-indigo-950">
                                I have read and understood the POSH workplace policy and agree to abide by it.
                            </span>
                        </label>

                        <button type="submit" id="ack-submit" disabled
                            class="ack-submit-btn w-full inline-flex items-center justify-center gap-2 rounded-xl px-4 py-3.5 text-sm font-bold text-white shadow-lg transition disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none enabled:bg-gradient-to-r enabled:from-blue-600 enabled:to-indigo-600 enabled:hover:from-blue-700 enabled:hover:to-indigo-700 enabled:shadow-blue-500/25">
                            <i class="fas fa-circle-check"></i>
                            <span>I agree &amp; submit</span>
                        </button>
                    </form>

                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50/50 px-4 py-3 text-center">
                        <p class="text-[11px] text-slate-500 leading-relaxed">
                            <i class="fas fa-headset text-slate-400 mr-1"></i>
                            Questions? Contact your <a href="{{ route('employee.portal') }}" class="font-semibold text-blue-600 hover:text-blue-800">Internal Committee</a> from the employee portal.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const scroll = document.getElementById('policy-doc-scroll');
    const bar = document.getElementById('policy-read-progress');
    const ackBar = document.getElementById('ack-read-bar');
    const ackLabel = document.getElementById('ack-read-label');
    const ackCheckbox = document.getElementById('ack-checkbox');
    const ackSubmit = document.getElementById('ack-submit');

    function updateReadProgress() {
        if (!scroll) return 0;
        const max = scroll.scrollHeight - scroll.clientHeight;
        const pct = max > 0 ? scroll.scrollTop / max : 1;
        const width = (Math.min(1, pct) * 100);
        if (bar) bar.style.width = width + '%';
        if (ackBar) ackBar.style.width = width + '%';
        if (ackLabel) ackLabel.textContent = Math.round(width) + '%';
        return width;
    }

    if (scroll) {
        scroll.addEventListener('scroll', updateReadProgress);
        updateReadProgress();
    }

    if (ackCheckbox && ackSubmit) {
        function syncSubmit() {
            ackSubmit.disabled = !ackCheckbox.checked;
        }
        ackCheckbox.addEventListener('change', syncSubmit);
        syncSubmit();
    }
})();
</script>
@endpush

@endsection
