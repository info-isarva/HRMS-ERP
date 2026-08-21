@extends('central.demo-tenants.layout')

@section('title', 'Demo tenants')

@section('content')
<div class="dtm-stats">
    <div class="dtm-stat">
        <div class="dtm-stat__label">Total demos</div>
        <div class="dtm-stat__value" style="color:var(--brand)">{{ $stats['total'] }}</div>
    </div>
    <div class="dtm-stat">
        <div class="dtm-stat__label">Active</div>
        <div class="dtm-stat__value" style="color:var(--ok)">{{ $stats['active'] }}</div>
    </div>
    <div class="dtm-stat">
        <div class="dtm-stat__label">Ending soon</div>
        <div class="dtm-stat__value" style="color:var(--warn)">{{ $stats['ending_soon'] }}</div>
    </div>
    <div class="dtm-stat">
        <div class="dtm-stat__label">Avg usage</div>
        <div class="dtm-stat__value">{{ $stats['avg_usage'] }}%</div>
    </div>
</div>

<div class="dtm-panel">
    <div class="dtm-panel__head">
        <div>
            <h1 class="dtm-panel__title">Demo clients</h1>
            <p class="dtm-panel__sub mb-0">Expiry dates, usage scores, and client credentials</p>
        </div>
        <a href="{{ route('platform.demo-tenants.create') }}" class="dtm-btn dtm-btn--primary">
            <i class="fas fa-rocket"></i> Provision new demo
        </a>
    </div>
    <div class="table-responsive">
        <table class="dtm-table">
            <thead>
                <tr>
                    <th>Client</th>
                    <th>Code</th>
                    <th>Admin email</th>
                    <th>Expires</th>
                    <th>Days</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $row)
                    @php
                        $t = $row['tenant'];
                        $score = $row['usage']['score'];
                        $barClass = $score >= 70 ? 'usage-ok' : ($score >= 35 ? 'usage-warn' : 'usage-bad');
                        $pillClass = match(true) {
                            $row['status'] === 'Expired' => 'pill--expired',
                            $row['status'] === 'Ending soon' => 'pill--ending',
                            default => 'pill--active',
                        };
                    @endphp
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $t->name }}</div>
                            @if($t->contact_name)
                                <div style="font-size:.78rem;color:var(--muted)">{{ $t->contact_name }}</div>
                            @endif
                        </td>
                        <td><code style="font-size:.8rem">{{ $t->company_code }}</code></td>
                        <td style="font-size:.82rem">{{ $t->demo_admin_email ?? '—' }}</td>
                        <td style="font-size:.82rem;white-space:nowrap">{{ $t->demo_expires_at?->timezone('Asia/Kolkata')->format('d M Y') ?? '—' }}</td>
                        <td>
                            @if($row['days'] === null)—
                            @elseif($row['days'] < 0)<span style="color:var(--bad);font-weight:600">Expired</span>
                            @else<span class="fw-semibold">{{ $row['days'] }}</span>
                            @endif
                        </td>
                        <td style="min-width:120px">
                            <div class="d-flex align-items-center gap-2">
                                <div class="usage-track flex-grow-1"><span class="{{ $barClass }}" style="width:{{ $score }}%"></span></div>
                                <span style="font-size:.75rem;font-weight:700;min-width:32px">{{ $score }}%</span>
                            </div>
                        </td>
                        <td><span class="pill {{ $pillClass }}">{{ $row['status'] }}</span></td>
                        <td style="text-align:right;white-space:nowrap">
                            <a href="{{ route('platform.demo-tenants.show', $t) }}#client-credentials" class="dtm-btn dtm-btn--ghost" style="padding:.35rem .65rem;font-size:.75rem" title="Credentials">
                                <i class="fas fa-key"></i>
                            </a>
                            <a href="{{ route('platform.demo-tenants.show', $t) }}" class="dtm-btn dtm-btn--ghost" style="padding:.35rem .65rem;font-size:.75rem">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5" style="color:var(--muted)">
                            No demo tenants yet.
                            <a href="{{ route('platform.demo-tenants.create') }}" class="fw-semibold">Create the first one</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
