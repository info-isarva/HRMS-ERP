# Phase 0 — Deprecate legacy POSH & HRMS placeholder

Phase 0 prepares the codebase for the full ISARVA POSH module without building case management yet.

## What changed

1. **HRMS workspace (root app)**
   - New route: `/posh` → ISARVA POSH “coming soon” page
   - Dashboard card: **ISARVA POSH** (when `POSH_MODULE_PLACEHOLDER_ENABLED=true`)

2. **Payroll** — basic POSH deprecated
   - Routes under `compliance/posh/*` and API `api/compliance/posh/*` blocked by default
   - Sidebar: legacy links hidden; **ISARVA POSH** links to workspace `/posh`
   - Controllers marked `@deprecated`

3. **Attendance** — employee POSH portal deprecated
   - Routes under `/compliance/posh` blocked by default
   - Sidebar updated same as Payroll

4. **Kept as-is**
   - HRMS login flow: `compliance/posh-policy` (one-time policy acknowledgement) — not case management

5. **Documentation**
   - `posh/README.md` — module roadmap
   - `poshactresearch/` — unchanged R&D prototype

## Environment variables

Add to **HRMS** `.env` (root):

```env
# Phase 0 — ISARVA POSH integration
POSH_LEGACY_ENABLED=false
POSH_MODULE_PLACEHOLDER_ENABLED=true
POSH_SHOW_PROTOTYPE_LINK=false
# POSH_PROTOTYPE_URL=/poshactresearch/index.html
SSO_WORKSPACE_URL=https://your-hrms-hub.example.com
```

Add the same to **Payroll** and **Attendance** `.env`:

```env
POSH_LEGACY_ENABLED=false
SSO_WORKSPACE_URL=https://your-hrms-hub.example.com
```

| Variable | Default | Meaning |
|----------|---------|---------|
| `POSH_LEGACY_ENABLED` | `false` | `true` = temporarily allow old Payroll/Attendance POSH (export data only) |
| `POSH_MODULE_PLACEHOLDER_ENABLED` | `true` | Show ISARVA POSH card on HRMS dashboard |
| `POSH_SHOW_PROTOTYPE_LINK` | `false` | Show link to `poshactresearch` on coming-soon page (dev only) |
| `SSO_WORKSPACE_URL` | `APP_URL` | HRMS hub URL used for redirects |

After changing `.env`, run `php artisan config:clear` in each app (root, payroll, attendance).

## How to test Phase 0

### 1. HRMS dashboard & placeholder

1. Log in to the **HRMS workspace** (root app).
2. Confirm you see an **ISARVA POSH** card on the dashboard.
3. Click it → should open `/posh` with the coming-soon page (navy/coral branding).
4. Click **Back to Workspace** → returns to dashboard.

### 2. Legacy POSH blocked (default)

With `POSH_LEGACY_ENABLED=false` in Payroll and Attendance:

**Payroll (as Admin):**

1. Open Payroll app (SSO from hub).
2. Sidebar **Compliance** should show **ISARVA POSH** (not ICC Board / Auditing / Complaints).
3. Manually visit: `{PAYROLL_URL}/compliance/posh/icc-board`  
   → should redirect to `{SSO_WORKSPACE_URL}/posh`.

**Attendance (as employee):**

1. Open Attendance app.
2. Settings area should show **ISARVA POSH** instead of “POSH Safety Portal”.
3. Visit: `{ATTENDANCE_URL}/compliance/posh`  
   → redirect to workspace `/posh`.

**API (optional, curl):**

```bash
curl -s "{PAYROLL_URL}/api/compliance/posh/icc-board?sync_token=YOUR_TOKEN"
```

Expect HTTP **410** JSON with `"phase": 0` and a `redirect` URL.

### 3. Temporary legacy access (migration / data export)

1. Set `POSH_LEGACY_ENABLED=true` in Payroll and Attendance `.env`.
2. `php artisan config:clear` in both apps.
3. Legacy sidebar items reappear with **(Legacy)** label.
4. Open ICC Board / Complaints — yellow deprecation banner at top.
5. When done exporting, set back to `false`.

### 4. Policy acknowledgement (unchanged)

1. New user login on HRMS may still show **POSH Policy** acceptance (`/compliance/posh-policy`).
2. This is separate from case management and remains active.

### 5. Prototype link (optional, dev)

```env
POSH_SHOW_PROTOTYPE_LINK=true
POSH_PROTOTYPE_URL=/poshactresearch/index.html
```

Reload `/posh` → **View R&D Prototype** button appears.

## Sign-off checklist

- [ ] ISARVA POSH card visible on HRMS dashboard
- [ ] `/posh` coming-soon page loads
- [ ] Payroll legacy POSH menus hidden (default env)
- [ ] Attendance legacy POSH menu hidden (default env)
- [ ] Direct legacy URLs redirect to `/posh`
- [ ] Legacy mode works when `POSH_LEGACY_ENABLED=true` with deprecation banner
- [ ] POSH policy login flow still works

When all pass, reply **“Phase 0 perfect”** to start **Phase 1** (new `posh/` Laravel app + SSO).

## Next: Phase 1 preview

- Scaffold `posh/` Laravel application
- `PoshSSOController` on HRMS hub (like Payroll SSO)
- Employee context from hub JWT
- IC setup + policy + employee landing (first real screens)
