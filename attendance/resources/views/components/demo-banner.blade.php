@php
    $tenant = app(\App\Services\TenantContext::class)->tenant();
    $showDemo = false;
    $expiresAt = null;

    if ($tenant && $tenant->is_demo && $tenant->demo_expires_at) {
        $showDemo = true;
        $expiresAt = $tenant->demo_expires_at->copy()->endOfDay();
    } elseif (config('demo.enabled') && config('demo.expires_at')) {
        $showDemo = true;
        $expiresAt = \Carbon\Carbon::parse(config('demo.expires_at'))->endOfDay();
    }

    $daysLeft = $showDemo && $expiresAt ? max(0, (int) now()->startOfDay()->diffInDays($expiresAt, false)) : 0;
@endphp
@if($showDemo)
    <div class="demo-alert-bar" role="alert">
        <span class="demo-alert-bar__icon" aria-hidden="true"><i class="fas fa-circle-info"></i></span>
        <span class="demo-alert-bar__text">
            <strong>Demo Environment</strong> —
            @if($daysLeft === 0)
                Your evaluation access expires today ({{ $expiresAt->format('F j, Y') }}).
            @elseif($daysLeft === 1)
                You have 1 day remaining (expires {{ $expiresAt->format('F j, Y') }}).
            @else
                You have {{ $daysLeft }} days remaining (expires {{ $expiresAt->format('F j, Y') }}).
            @endif
            This is sample data for client evaluation only — not for production use.
        </span>
    </div>
    @once
        <style>
            .demo-alert-bar {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                width: 100%;
                padding: 0.55rem 1rem;
                background: linear-gradient(90deg, #fef3c7 0%, #fde68a 50%, #fef3c7 100%);
                border-top: 1px solid #fcd34d;
                color: #78350f;
                font-size: 0.8125rem;
                line-height: 1.45;
                text-align: center;
            }
            .demo-alert-bar__icon {
                flex-shrink: 0;
                color: #d97706;
                font-size: 0.9rem;
            }
            .demo-alert-bar__text strong {
                color: #92400e;
            }
            @media (max-width: 767px) {
                .demo-alert-bar {
                    font-size: 0.75rem;
                    padding: 0.5rem 0.75rem;
                    text-align: left;
                }
                .demo-alert-bar__icon {
                    margin-top: 0.1rem;
                    align-self: flex-start;
                }
            }
        </style>
    @endonce
@endif
