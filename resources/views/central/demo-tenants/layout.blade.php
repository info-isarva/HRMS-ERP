<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Demo Tenant Manager') — ISARVA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --brand: #2563eb;
            --brand-dark: #1d4ed8;
            --brand-soft: #eff6ff;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --surface: #ffffff;
            --ok: #059669;
            --warn: #d97706;
            --bad: #dc2626;
            --radius: 14px;
            --shadow: 0 1px 3px rgba(15,23,42,.06), 0 8px 24px rgba(15,23,42,.06);
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            color: var(--ink);
            font-size: 0.9375rem;
            line-height: 1.5;
        }
        .dtm-shell { max-width: 1200px; margin: 0 auto; padding: 1.25rem 1rem 2.5rem; }

        /* Header */
        .dtm-header {
            display: flex; align-items: center; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 1.5rem;
            padding: 1rem 1.25rem; background: var(--surface);
            border: 1px solid var(--line); border-radius: var(--radius);
            box-shadow: var(--shadow);
        }
        .dtm-brand { display: flex; align-items: center; gap: .875rem; }
        .dtm-brand__icon {
            width: 44px; height: 44px; border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff; display: grid; place-items: center; font-size: 1.1rem;
        }
        .dtm-brand__title { font-weight: 700; font-size: 1.05rem; letter-spacing: -.02em; }
        .dtm-brand__sub { font-size: .8rem; color: var(--muted); margin-top: 1px; }
        .dtm-badge-internal {
            display: inline-flex; align-items: center; gap: .35rem;
            font-size: .7rem; font-weight: 600; color: #1e40af;
            background: #dbeafe; padding: .25rem .55rem; border-radius: 999px;
            margin-top: .35rem;
        }
        .dtm-nav { display: flex; gap: .5rem; flex-wrap: wrap; align-items: center; }
        .dtm-btn {
            display: inline-flex; align-items: center; gap: .4rem;
            padding: .5rem .9rem; border-radius: 10px; font-size: .8125rem;
            font-weight: 600; text-decoration: none; border: 1px solid transparent;
            transition: all .15s ease; cursor: pointer; background: none;
        }
        .dtm-btn--ghost { color: var(--muted); border-color: var(--line); background: #fff; }
        .dtm-btn--ghost:hover { color: var(--ink); border-color: #cbd5e1; }
        .dtm-btn--primary {
            color: #fff; background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none; box-shadow: 0 2px 8px rgba(37,99,235,.25);
        }
        .dtm-btn--primary:hover { color: #fff; filter: brightness(1.04); }

        /* Stats */
        .dtm-stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: .875rem; margin-bottom: 1.25rem; }
        @media (max-width: 768px) { .dtm-stats { grid-template-columns: repeat(2, 1fr); } }
        .dtm-stat {
            background: var(--surface); border: 1px solid var(--line);
            border-radius: var(--radius); padding: 1rem 1.1rem; box-shadow: var(--shadow);
        }
        .dtm-stat__label {
            font-size: .7rem; font-weight: 600; color: var(--muted);
            text-transform: uppercase; letter-spacing: .05em;
        }
        .dtm-stat__value { font-size: 1.65rem; font-weight: 700; margin-top: .2rem; line-height: 1; }

        /* Panel */
        .dtm-panel {
            background: var(--surface); border: 1px solid var(--line);
            border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden;
        }
        .dtm-panel__head {
            padding: 1rem 1.25rem; border-bottom: 1px solid var(--line);
            display: flex; align-items: center; justify-content: space-between;
            gap: .75rem; flex-wrap: wrap;
        }
        .dtm-panel__title { font-weight: 700; font-size: 1rem; margin: 0; }
        .dtm-panel__sub { font-size: .8rem; color: var(--muted); margin-top: 2px; }
        .dtm-panel__body { padding: 1.25rem; }

        /* Table */
        .dtm-table { width: 100%; border-collapse: collapse; }
        .dtm-table th {
            font-size: .7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: .04em; color: var(--muted); padding: .75rem 1rem;
            background: #f8fafc; border-bottom: 1px solid var(--line); text-align: left;
        }
        .dtm-table td { padding: .9rem 1rem; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .dtm-table tbody tr:hover { background: #fafbfc; }
        .dtm-table tbody tr:last-child td { border-bottom: none; }

        .usage-track { height: 6px; border-radius: 999px; background: #e2e8f0; overflow: hidden; min-width: 80px; }
        .usage-track > span { display: block; height: 100%; border-radius: 999px; }
        .usage-ok { background: linear-gradient(90deg, #34d399, #059669); }
        .usage-warn { background: linear-gradient(90deg, #fbbf24, #d97706); }
        .usage-bad { background: linear-gradient(90deg, #f87171, #dc2626); }

        .pill { font-size: .68rem; font-weight: 700; padding: .28rem .6rem; border-radius: 999px; white-space: nowrap; }
        .pill--active { background: #dcfce7; color: #166534; }
        .pill--ending { background: #fef3c7; color: #92400e; }
        .pill--expired { background: #fee2e2; color: #991b1b; }

        /* Form */
        .dtm-form .form-label { font-size: .8rem; font-weight: 600; color: #334155; margin-bottom: .35rem; }
        .dtm-form .form-control, .dtm-form .form-select {
            border-radius: 10px; border-color: #cbd5e1; padding: .6rem .85rem; font-size: .875rem;
        }
        .dtm-form .form-control:focus, .dtm-form .form-select:focus {
            border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(59,130,246,.12);
        }
        .dtm-form .form-hint { font-size: .75rem; color: var(--muted); margin-top: .3rem; }

        .option-card {
            position: relative; border: 2px solid var(--line); border-radius: 12px;
            padding: 1rem 1.1rem; cursor: pointer; transition: all .15s ease;
            background: #fafbfc; height: 100%;
        }
        .option-card:hover { border-color: #93c5fd; background: #fff; }
        .option-card input { position: absolute; opacity: 0; pointer-events: none; }
        .option-card input:checked + .option-card__inner { }
        .option-card:has(input:checked) {
            border-color: #3b82f6; background: #eff6ff;
            box-shadow: 0 0 0 3px rgba(59,130,246,.1);
        }
        .option-card__title { font-weight: 700; font-size: .9rem; display: flex; align-items: center; gap: .5rem; }
        .option-card__title i { color: #3b82f6; font-size: .85rem; }
        .option-card__desc { font-size: .78rem; color: var(--muted); margin-top: .4rem; line-height: 1.45; }
        .option-card__tag {
            display: inline-block; margin-top: .5rem; font-size: .68rem; font-weight: 600;
            color: #1d4ed8; background: #dbeafe; padding: .15rem .45rem; border-radius: 6px;
        }

        /* Credentials */
        .cred-panel {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border-radius: var(--radius); padding: 1.25rem; color: #e2e8f0;
            margin-bottom: 1.25rem; border: 1px solid #334155;
        }
        .cred-panel__head {
            display: flex; align-items: flex-start; justify-content: space-between;
            gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;
        }
        .cred-panel__title { font-weight: 700; font-size: .95rem; color: #f8fafc; }
        .cred-panel__sub { font-size: .75rem; color: #94a3b8; margin-top: 2px; }
        .btn-copy-all {
            background: #3b82f6; color: #fff; border: none; border-radius: 8px;
            padding: .45rem .85rem; font-size: .78rem; font-weight: 600; cursor: pointer;
        }
        .btn-copy-all:hover { background: #2563eb; }
        .cred-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: .75rem;
        }
        @media (max-width: 640px) { .cred-grid { grid-template-columns: 1fr; } }
        .cred-item {
            background: rgba(255,255,255,.04); border: 1px solid #334155;
            border-radius: 10px; padding: .65rem .8rem;
        }
        .cred-item--highlight { border-color: #3b82f6; background: rgba(59,130,246,.08); }
        .cred-label { font-size: .68rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; }
        .cred-value-row { display: flex; align-items: center; gap: .5rem; margin-top: .25rem; }
        .cred-value {
            font-family: ui-monospace, monospace; font-size: .8rem; color: #f1f5f9;
            word-break: break-all; flex: 1;
        }
        .cred-value--lg { font-size: .9rem; font-weight: 600; color: #fff; }
        .cred-value-text { font-size: .85rem; font-weight: 600; color: #f8fafc; }
        .btn-copy-mini {
            background: rgba(255,255,255,.08); border: 1px solid #475569; color: #cbd5e1;
            border-radius: 6px; width: 28px; height: 28px; display: grid; place-items: center;
            cursor: pointer; flex-shrink: 0; font-size: .7rem;
        }
        .btn-copy-mini:hover { background: rgba(255,255,255,.14); color: #fff; }
        .cred-share-box {
            background: rgba(0,0,0,.25); border: 1px solid #334155;
            border-radius: 10px; padding: .85rem 1rem;
        }
        .cred-share-text {
            font-family: ui-monospace, monospace; font-size: .75rem;
            color: #cbd5e1; white-space: pre-wrap; word-break: break-word;
        }

        .alert-dtm {
            border-radius: 10px; border: none; padding: .85rem 1rem;
            font-size: .875rem; margin-bottom: 1rem;
        }
        .alert-dtm--ok { background: #ecfdf5; color: #065f46; }
        .alert-dtm--err { background: #fef2f2; color: #991b1b; }

        .copy-toast {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
            background: #0f172a; color: #fff; padding: .6rem 1rem; border-radius: 10px;
            font-size: .8rem; font-weight: 600; opacity: 0; transform: translateY(8px);
            transition: all .2s ease; pointer-events: none;
        }
        .copy-toast.is-visible { opacity: 1; transform: translateY(0); }

        .section-label {
            font-size: .72rem; font-weight: 700; text-transform: uppercase;
            letter-spacing: .06em; color: var(--muted); margin-bottom: .65rem;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="dtm-shell">
    <header class="dtm-header">
        <div class="dtm-brand">
            <div class="dtm-brand__icon"><i class="fas fa-building-circle-check"></i></div>
            <div>
                <div class="dtm-brand__title">Demo Tenant Manager</div>
                <div class="dtm-brand__sub">Provision client trials · track usage · share credentials</div>
                <span class="dtm-badge-internal"><i class="fas fa-lock"></i> ISARVADEV internal only</span>
            </div>
        </div>
        <nav class="dtm-nav">
            <a href="{{ route('dashboard') }}" class="dtm-btn dtm-btn--ghost"><i class="fas fa-home"></i> Workspace</a>
            @if(request()->routeIs('platform.demo-tenants.index') || request()->routeIs('platform.demo-tenants.show'))
                <a href="{{ route('platform.demo-tenants.create') }}" class="dtm-btn dtm-btn--primary"><i class="fas fa-plus"></i> New demo</a>
            @endif
            @if(!request()->routeIs('platform.demo-tenants.index'))
                <a href="{{ route('platform.demo-tenants.index') }}" class="dtm-btn dtm-btn--ghost"><i class="fas fa-table-list"></i> All demos</a>
            @endif
        </nav>
    </header>

    @if(session('status'))
        <div class="alert-dtm alert-dtm--ok"><i class="fas fa-check-circle me-1"></i> {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-dtm alert-dtm--err">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<div class="copy-toast" id="copy-toast">Copied to clipboard</div>

<script>
(function () {
    function showToast() {
        const t = document.getElementById('copy-toast');
        if (!t) return;
        t.classList.add('is-visible');
        setTimeout(() => t.classList.remove('is-visible'), 2000);
    }
    function copyText(text) {
        if (!text) return;
        navigator.clipboard.writeText(text).then(showToast).catch(function () {
            const ta = document.createElement('textarea');
            ta.value = text;
            document.body.appendChild(ta);
            ta.select();
            document.execCommand('copy');
            document.body.removeChild(ta);
            showToast();
        });
    }
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-copy], [data-copy-target]');
        if (!btn) return;
        if (btn.dataset.copyTarget) {
            const el = document.getElementById(btn.dataset.copyTarget);
            if (el) copyText(el.textContent.trim());
        } else if (btn.dataset.copy) {
            copyText(btn.dataset.copy);
        }
    });
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
