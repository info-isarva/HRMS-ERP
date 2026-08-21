@extends('layouts.posh')

@section('title', 'Employee Portal')
@section('page-title', 'Employee Portal')
@section('page-subtitle', '')

@section('page-banner')
<div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-800 via-blue-900 to-indigo-900 text-white shadow-xl">
    <div class="absolute inset-0 opacity-20">
        <div class="absolute -top-10 -right-10 h-48 w-48 rounded-full bg-blue-400 blur-3xl"></div>
    </div>
    <div class="relative px-6 py-7 lg:px-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-start gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white/10 ring-1 ring-white/20">
                    <i class="fas fa-user-shield text-2xl text-blue-200"></i>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-blue-200/90">Employee portal</p>
                    <h1 class="mt-1 text-2xl font-bold tracking-tight lg:text-3xl">Your rights &amp; safe reporting</h1>
                    <p class="mt-2 max-w-xl text-sm text-slate-300 leading-relaxed">
                        Confidential access under the POSH Act, 2013 — policy, complaints, and Internal Committee contacts.
                    </p>
                </div>
            </div>
            @if($activePolicy && !$hasAcked)
                <a href="{{ route('employee.policy') }}" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-5 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-amber-600 transition shrink-0">
                    <i class="fas fa-file-signature"></i> Acknowledge policy
                </a>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .portal-action {
        display: flex;
        flex-direction: column;
        height: 100%;
        padding: 1.35rem;
        border-radius: 1rem;
        border: 1px solid #e2e8f0;
        background: #fff;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
        transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    }
    .portal-action:hover {
        transform: translateY(-3px);
        border-color: #bfdbfe;
        box-shadow: 0 12px 28px rgba(59, 130, 246, 0.12);
    }
    .portal-action__icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 1rem;
    }
    .portal-action__title { font-weight: 600; font-size: 1rem; color: #1e3a8a; }
    .portal-action__hint { font-size: 0.8rem; color: #64748b; margin-top: 0.35rem; }
    .portal-action__arrow {
        margin-top: auto;
        padding-top: 1rem;
        font-size: 0.75rem;
        font-weight: 600;
        color: #2563eb;
    }
    .ic-contact-card {
        display: flex;
        gap: 1rem;
        padding: 1rem 1.15rem;
        border-radius: 0.75rem;
        border: 1px solid #f1f5f9;
        background: linear-gradient(180deg, #fafbfc 0%, #fff 100%);
    }
    .ic-contact-card:hover { border-color: #dbeafe; }
</style>
@endpush

@section('content')

@if($activePolicy && !$hasAcked)
<div class="flex items-start gap-4 rounded-2xl border border-amber-200/80 bg-gradient-to-r from-amber-50 via-white to-orange-50 p-5 shadow-sm">
    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700">
        <i class="fas fa-circle-exclamation"></i>
    </div>
    <div class="flex-1 min-w-0">
        <p class="font-semibold text-amber-950">Policy acknowledgement pending</p>
        <p class="mt-1 text-sm text-amber-800/80">Please review and accept the active workplace policy ({{ $activePolicy->version }}) before your compliance record is complete.</p>
    </div>
    <a href="{{ route('employee.policy') }}" class="shrink-0 inline-flex items-center gap-2 rounded-xl bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md hover:bg-amber-700 transition">
        Acknowledge now
    </a>
</div>
@elseif($activePolicy && $hasAcked)
<div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50/80 px-4 py-3 text-sm text-emerald-800">
    <i class="fas fa-circle-check text-emerald-600"></i>
    <span>Policy <strong>{{ $activePolicy->version }}</strong> acknowledged — thank you.</span>
</div>
@endif

{{-- Quick actions --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
    <a href="{{ route('employee.policy') }}" class="portal-action">
        <div class="portal-action__icon bg-blue-50 text-blue-600"><i class="fas fa-file-contract"></i></div>
        <span class="portal-action__title">View policy</span>
        <span class="portal-action__hint">{{ $activePolicy ? 'Version ' . $activePolicy->version : 'Not published yet' }}</span>
        <span class="portal-action__arrow">Open policy <i class="fas fa-arrow-right text-[10px]"></i></span>
    </a>
    <a href="{{ route('complaints.create') }}" class="portal-action">
        <div class="portal-action__icon bg-indigo-50 text-indigo-600"><i class="fas fa-file-circle-plus"></i></div>
        <span class="portal-action__title">File complaint</span>
        <span class="portal-action__hint">Confidential intake — no ack required</span>
        <span class="portal-action__arrow">Start form <i class="fas fa-arrow-right text-[10px]"></i></span>
    </a>
    <a href="{{ route('complaints.my') }}" class="portal-action">
        <div class="portal-action__icon bg-slate-100 text-slate-600"><i class="fas fa-folder-open"></i></div>
        <span class="portal-action__title">My cases</span>
        <span class="portal-action__hint">Track status of your filings</span>
        <span class="portal-action__arrow">View cases <i class="fas fa-arrow-right text-[10px]"></i></span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Reporting channels --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-violet-100 text-violet-700">
                <i class="fas fa-bullhorn"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-blue-950">Ways to report</h2>
                <p class="text-xs text-slate-500">Choose what works for you</p>
            </div>
        </div>
        <div class="p-5 space-y-4 text-sm text-slate-600">
            <div class="flex gap-3 rounded-xl bg-slate-50 p-4">
                <i class="fas fa-qrcode text-violet-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-medium text-slate-700">Workplace QR intake</p>
                    <p class="mt-1 text-slate-500">Scan the official poster at your office — no login needed. Ask HR for the link.</p>
                </div>
            </div>
            @php $wa = auth()->user()->organization?->settings['whatsapp_number'] ?? null; @endphp
            @if($wa)
            <div class="flex gap-3 rounded-xl bg-emerald-50/80 p-4 border border-emerald-100">
                <i class="fab fa-whatsapp text-emerald-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-medium text-slate-700">WhatsApp helpline</p>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $wa) }}" class="mt-1 inline-block font-semibold text-emerald-700 hover:underline">{{ $wa }}</a>
                </div>
            </div>
            @endif
            <div class="flex gap-3 rounded-xl border border-blue-100 bg-blue-50/50 p-4">
                <i class="fas fa-clock text-blue-600 text-lg mt-0.5"></i>
                <div>
                    <p class="font-medium text-slate-700">Filing deadline</p>
                    <p class="mt-1 text-slate-500">Normally within <strong>3 months</strong> of the incident (IC may extend 3 more months for valid reasons).</p>
                </div>
            </div>
        </div>
    </div>

    {{-- IC contacts --}}
    <div class="rounded-2xl border border-slate-200/80 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-slate-50/80 px-5 py-4">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-blue-700">
                <i class="fas fa-people-group"></i>
            </div>
            <div>
                <h2 class="text-base font-semibold text-blue-950">Internal Committee</h2>
                <p class="text-xs text-slate-500">Confidential contacts</p>
            </div>
        </div>
        <div class="p-5 space-y-3">
            @forelse($icMembers as $m)
                <div class="ic-contact-card">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-sm font-bold">
                        {{ strtoupper(substr($m->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0">
                        <p class="font-semibold text-blue-950">{{ $m->name }}</p>
                        <p class="text-xs font-medium text-blue-700">{{ $m->roleLabel() }}</p>
                        @if($m->email)
                            <p class="mt-1 text-xs text-slate-500 truncate"><i class="fas fa-envelope mr-1"></i>{{ $m->email }}</p>
                        @endif
                        @if($m->contact_number)
                            <p class="text-xs text-slate-500"><i class="fas fa-phone mr-1"></i>{{ $m->contact_number }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-200 py-10 text-center text-sm text-slate-500">
                    <i class="fas fa-users-slash text-2xl text-slate-300 mb-2"></i>
                    <p>IC contacts will appear here once HR configures the committee.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div class="rounded-xl border border-slate-200/60 bg-white/60 px-4 py-3 text-xs text-slate-500 flex items-center gap-2">
    <i class="fas fa-lock text-slate-400"></i>
    All communications are handled confidentially under Section 16 of the POSH Act.
</div>

@endsection
