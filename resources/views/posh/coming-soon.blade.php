<x-app-layout>
    <div class="posh-placeholder">
        <div class="posh-placeholder-card">
            <div class="posh-brand">
                <div class="posh-brand-logo"><i class="fas fa-shield-halved"></i></div>
                <div>
                    <h1>{{ config('posh.product_name') }}</h1>
                    <p class="posh-tagline">Prevention of Sexual Harassment — Compliance Platform</p>
                </div>
            </div>

            <div class="posh-phase-badge">
                <i class="fas fa-hammer"></i> Phase 0 complete — Module integration starts in Phase 1
            </div>

            <p class="posh-lead">
                The full POSH Act workflow (complaints, IC inquiry, statutory timelines, annual compliance)
                is being built as a <strong>dedicated product module</strong>, integrated with this HRMS workspace.
            </p>

            <ul class="posh-checklist">
                <li><i class="fas fa-check-circle"></i> Legacy POSH in Payroll &amp; Attendance is <strong>deprecated</strong> and hidden by default</li>
                <li><i class="fas fa-check-circle"></i> Product specification lives in <code>poshactresearch/</code> (your R&amp;D prototype)</li>
                <li><i class="fas fa-check-circle"></i> Phase 1: <code>posh/</code> app + SSO — configure <code>POSH_URL</code> to launch</li>
            </ul>

            @if($legacyEnabled)
                <div class="posh-alert posh-alert-warn">
                    <strong>Legacy POSH is temporarily enabled</strong> (<code>POSH_LEGACY_ENABLED=true</code>).
                    Use only to export existing data. Do not file new complaints there.
                </div>
            @else
                <div class="posh-alert posh-alert-info">
                    Basic POSH menus in Payroll and Attendance are turned off.
                    Old URLs redirect here automatically.
                </div>
            @endif

            <div class="posh-actions">
                @if(config('services.posh.url'))
                    <a href="{{ route('posh.sso') }}" class="posh-btn posh-btn-primary">
                        <i class="fas fa-shield-halved"></i> Open {{ config('posh.product_name') }}
                    </a>
                @endif
                <a href="{{ route('dashboard') }}" class="posh-btn posh-btn-secondary">
                    <i class="fas fa-th-large"></i> Back to Workspace
                </a>
                @if($showPrototypeLink)
                    <a href="{{ $prototypeUrl }}" target="_blank" rel="noopener" class="posh-btn posh-btn-secondary">
                        <i class="fas fa-flask"></i> View R&amp;D Prototype
                    </a>
                @endif
            </div>
        </div>
    </div>

    <style>
        .posh-placeholder {
            min-height: calc(100vh - 4rem);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #eef1f8 0%, #e8edf5 100%);
        }
        .posh-placeholder-card {
            max-width: 640px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            padding: 2.5rem;
            box-shadow: 0 8px 32px rgba(30, 58, 95, 0.1);
            border: 1px solid #dde3ef;
        }
        .posh-brand { display: flex; align-items: center; gap: 1rem; margin-bottom: 1.5rem; }
        .posh-brand-logo {
            width: 56px; height: 56px; border-radius: 12px;
            background: linear-gradient(135deg, #1e3a5f, #2d5a8a);
            color: #fff; font-weight: 800; font-size: 1.5rem;
            display: flex; align-items: center; justify-content: center;
        }
        .posh-brand h1 { font-size: 1.75rem; font-weight: 800; color: #1e3a5f; margin: 0; letter-spacing: -0.02em; }
        .posh-tagline { color: #64748b; margin: 0.25rem 0 0; font-size: 0.95rem; }
        .posh-phase-badge {
            display: inline-flex; align-items: center; gap: 0.5rem;
            background: linear-gradient(135deg, #d4622a, #e07840);
            color: #fff; padding: 0.5rem 1rem; border-radius: 999px;
            font-size: 0.85rem; font-weight: 600; margin-bottom: 1.25rem;
        }
        .posh-lead { color: #334155; line-height: 1.6; margin-bottom: 1.25rem; }
        .posh-checklist { list-style: none; padding: 0; margin: 0 0 1.5rem; }
        .posh-checklist li {
            padding: 0.5rem 0; color: #475569; display: flex; align-items: flex-start; gap: 0.5rem;
        }
        .posh-checklist .fa-check-circle { color: #0d9488; margin-top: 0.2rem; }
        .posh-checklist .fa-clock { color: #d4622a; margin-top: 0.2rem; }
        .posh-alert { padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem; }
        .posh-alert-info { background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .posh-alert-warn { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; }
        .posh-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; }
        .posh-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.25rem; border-radius: 10px; font-weight: 600;
            text-decoration: none; font-size: 0.95rem;
        }
        .posh-btn-primary {
            background: linear-gradient(135deg, #1e3a5f, #2d5a8a); color: #fff;
        }
        .posh-btn-secondary {
            background: #fff; color: #1e3a5f; border: 2px solid #dde3ef;
        }
        code { background: #f1f5f9; padding: 0.1rem 0.35rem; border-radius: 4px; font-size: 0.85em; }
    </style>
</x-app-layout>
