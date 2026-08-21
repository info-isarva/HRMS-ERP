@extends('central.demo-tenants.layout')

@section('title', $tenant->company_code)

@section('content')
@php
    $score = $usage['score'];
    $barClass = $score >= 70 ? 'usage-ok' : ($score >= 35 ? 'usage-warn' : 'usage-bad');
    $days = $tenant->demoDaysRemaining();
    $pillClass = match(true) {
        $tenant->demoStatusLabel() === 'Expired' => 'pill--expired',
        $tenant->demoStatusLabel() === 'Ending soon' => 'pill--ending',
        default => 'pill--active',
    };
@endphp

@if(session('provision_result'))
    <div class="alert-dtm alert-dtm--ok mb-3">
        <i class="fas fa-check-circle me-1"></i>
        <strong>Demo created successfully.</strong> Copy credentials below and share with the client.
    </div>
@endif

@include('central.demo-tenants._credentials', ['credentials' => $credentials, 'shareMessage' => $shareMessage])

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dtm-panel mb-3">
            <div class="dtm-panel__head">
                <div>
                    <h1 class="dtm-panel__title">{{ $tenant->name }}</h1>
                    <p class="dtm-panel__sub mb-0">
                        <code>{{ $tenant->company_code }}</code>
                        @if($tenant->contact_name) · {{ $tenant->contact_name }} @endif
                    </p>
                </div>
                <span class="pill {{ $pillClass }}">{{ $tenant->demoStatusLabel() }}</span>
            </div>
            <div class="dtm-panel__body">
                <div class="row g-3 text-center text-md-start">
                    <div class="col-4">
                        <div class="section-label mb-1">Demo ends</div>
                        <div class="fw-bold" style="font-size:.9rem">{{ $tenant->demo_expires_at?->timezone('Asia/Kolkata')->format('d M Y') ?? '—' }}</div>
                    </div>
                    <div class="col-4">
                        <div class="section-label mb-1">Days left</div>
                        <div class="fw-bold" style="font-size:.9rem">{{ $days === null ? '—' : ($days < 0 ? 'Expired' : $days.' days') }}</div>
                    </div>
                    <div class="col-4">
                        <div class="section-label mb-1">Data profile</div>
                        <div class="fw-bold" style="font-size:.9rem">{{ $tenant->seed_profile === 'standard' ? 'Admin + samples' : 'Admin only' }}</div>
                    </div>
                </div>

                @if($tenant->internal_notes)
                    <div class="mt-3 p-3 rounded-3" style="background:#f8fafc;border:1px solid var(--line)">
                        <div class="section-label mb-1">Internal notes</div>
                        <div style="font-size:.875rem">{{ $tenant->internal_notes }}</div>
                    </div>
                @endif

                <div class="mt-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-semibold" style="font-size:.875rem">Usage score</span>
                        <span class="fw-bold">{{ $score }}%</span>
                    </div>
                    <div class="usage-track" style="height:10px"><span class="{{ $barClass }}" style="width:{{ $score }}%"></span></div>
                    <div class="form-hint mt-1">Login, company setup, employees, attendance, and recent activity</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="dtm-panel">
                    <div class="dtm-panel__head"><span class="fw-semibold" style="font-size:.85rem">Usage milestones</span></div>
                    <div class="dtm-panel__body" style="padding:.75rem 1rem">
                        @foreach($usage['milestones'] as $m)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom" style="font-size:.82rem">
                                <span>
                                    <i class="fas {{ $m['done'] ? 'fa-check-circle' : 'fa-circle' }} me-2" style="color:{{ $m['done'] ? 'var(--ok)' : '#cbd5e1' }}"></i>
                                    {{ $m['label'] }}
                                </span>
                                <span class="text-muted">{{ $m['value'] ?? '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="dtm-panel">
                    <div class="dtm-panel__head"><span class="fw-semibold" style="font-size:.85rem">Live stats</span></div>
                    <div class="dtm-panel__body" style="padding:.75rem 1rem;font-size:.82rem">
                        @foreach([
                            'payroll_employees' => 'Employees',
                            'payroll_departments' => 'Departments',
                            'payroll_users' => 'Payroll users',
                            'attendance_records' => 'Attendance records',
                            'recent_sessions' => 'Sessions (7d)',
                        ] as $key => $label)
                            <div class="d-flex justify-content-between py-2 border-bottom">
                                <span class="text-muted">{{ $label }}</span>
                                <span class="fw-semibold">{{ $usage['stats'][$key] ?? 0 }}</span>
                            </div>
                        @endforeach
                        <div class="d-flex justify-content-between py-2">
                            <span class="text-muted">Last login</span>
                            <span class="fw-semibold">{{ app(\App\Services\DemoTenantUsageService::class)->formatLastLogin($usage['stats']['last_login_at'] ?? null) ?? 'Never' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="dtm-panel mb-3">
            <div class="dtm-panel__head"><span class="fw-semibold" style="font-size:.85rem">Actions</span></div>
            <div class="dtm-panel__body d-grid gap-2">
                <a href="#client-credentials" class="dtm-btn dtm-btn--primary justify-content-center">
                    <i class="fas fa-key"></i> View credentials
                </a>
                <form method="POST" action="{{ route('platform.demo-tenants.extend', $tenant) }}" class="d-flex gap-2">
                    @csrf
                    <select name="extra_days" class="form-select form-select-sm" style="border-radius:8px">
                        <option value="7">+7 days</option>
                        <option value="10">+10 days</option>
                        <option value="15" selected>+15 days</option>
                    </select>
                    <button class="dtm-btn dtm-btn--ghost" style="white-space:nowrap">Extend</button>
                </form>
                <form method="POST" action="{{ route('platform.demo-tenants.refresh-usage', $tenant) }}">
                    @csrf
                    <button class="dtm-btn dtm-btn--ghost w-100 justify-content-center"><i class="fas fa-rotate"></i> Refresh usage</button>
                </form>
                @if($tenant->status === 'active')
                    <form method="POST" action="{{ route('platform.demo-tenants.deactivate', $tenant) }}" onsubmit="return confirm('Deactivate this demo? Client login will be blocked.')">
                        @csrf
                        <button class="dtm-btn dtm-btn--ghost w-100 justify-content-center" style="color:var(--bad);border-color:#fecaca">
                            <i class="fas fa-ban"></i> Deactivate
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="dtm-panel">
            <div class="dtm-panel__head"><span class="fw-semibold" style="font-size:.85rem">Shard databases</span></div>
            <div class="dtm-panel__body" style="font-family:ui-monospace,monospace;font-size:.75rem;color:var(--muted)">
                <div class="mb-1">{{ $tenant->workspace_database }}</div>
                <div class="mb-1">{{ $tenant->payroll_database }}</div>
                <div>{{ $tenant->attendance_database }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
