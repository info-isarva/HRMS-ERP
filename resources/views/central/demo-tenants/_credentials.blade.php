{{-- Client credentials panel — requires $credentials array and optional $shareMessage string --}}
@if($credentials)
<div class="cred-panel" id="client-credentials">
    <div class="cred-panel__head">
        <div>
            <div class="cred-panel__title"><i class="fas fa-key me-2"></i>Client login credentials</div>
            <div class="cred-panel__sub">Share these details with the client after provisioning</div>
        </div>
        <button type="button" class="btn-copy-all" data-copy-target="share-message-text">
            <i class="fas fa-copy me-1"></i> Copy all for client
        </button>
    </div>

    <div class="cred-grid">
        <div class="cred-item">
            <span class="cred-label">Workspace URL</span>
            <div class="cred-value-row">
                <code class="cred-value">{{ $credentials['login_url'] }}</code>
                <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['login_url'] }}" title="Copy"><i class="fas fa-copy"></i></button>
            </div>
        </div>
        <div class="cred-item">
            <span class="cred-label">Payroll URL</span>
            <div class="cred-value-row">
                <code class="cred-value">{{ $credentials['payroll_url'] }}</code>
                <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['payroll_url'] }}" title="Copy"><i class="fas fa-copy"></i></button>
            </div>
        </div>
        <div class="cred-item">
            <span class="cred-label">Attendance URL</span>
            <div class="cred-value-row">
                <code class="cred-value">{{ $credentials['attendance_url'] }}</code>
                <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['attendance_url'] }}" title="Copy"><i class="fas fa-copy"></i></button>
            </div>
        </div>
        <div class="cred-item cred-item--highlight">
            <span class="cred-label">Company code</span>
            <div class="cred-value-row">
                <code class="cred-value cred-value--lg">{{ $credentials['company_code'] }}</code>
                <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['company_code'] }}" title="Copy"><i class="fas fa-copy"></i></button>
            </div>
        </div>
        <div class="cred-item cred-item--highlight">
            <span class="cred-label">Super admin email</span>
            <div class="cred-value-row">
                <code class="cred-value">{{ $credentials['admin_email'] }}</code>
                <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['admin_email'] }}" title="Copy"><i class="fas fa-copy"></i></button>
            </div>
        </div>
        <div class="cred-item cred-item--highlight">
            <span class="cred-label">Password</span>
            <div class="cred-value-row">
                @if($credentials['password'])
                    <code class="cred-value cred-value--lg" id="demo-password">{{ $credentials['password'] }}</code>
                    <button type="button" class="btn-copy-mini" data-copy="{{ $credentials['password'] }}" title="Copy"><i class="fas fa-copy"></i></button>
                @else
                    <span class="text-muted small">Not stored for this demo</span>
                @endif
            </div>
        </div>
        @if($credentials['expires_at'])
        <div class="cred-item">
            <span class="cred-label">Valid until</span>
            <div class="cred-value-row">
                <span class="cred-value-text">{{ $credentials['expires_at'] }}</span>
            </div>
        </div>
        @endif
    </div>

    @if(!empty($shareMessage))
    <div class="cred-share-box mt-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small fw-semibold text-muted">Ready-to-send message</span>
        </div>
        <pre class="cred-share-text mb-0" id="share-message-text">{{ $shareMessage }}</pre>
    </div>
    @endif
</div>
@endif
