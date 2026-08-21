# Multi-Tenant Sharding — Phase 2 (Domain → Tenant Resolution)

**Status:** Active — **lookup only**, no database switching

## What Phase 2 does

On every web/API request (workspace, payroll, attendance):

1. Read the domain (`hrmsdev.isarva.in`, etc.)
2. Look up company in **`hrms_central.tenants`**
3. Store tenant on the request (`tenant_id`, `company_code`)
4. Log resolution (and optional debug headers when `APP_DEBUG=true`)
5. **Still use existing `.env` databases** for all data queries

Unknown domain → friendly **“Company not found”** page (404).

## What Phase 2 does NOT do

- Does not switch `DB_DATABASE` at runtime (`TENANT_SWITCH_DATABASE=false`)
- Does not change SSO JWT yet (Phase 5)
- Does not affect CRM/POSH yet

## Environment flags

```env
TENANT_RESOLVE_DOMAIN=true    # Phase 2 ON
TENANT_SWITCH_DATABASE=false  # Must stay false until Phase 3+
TENANT_STRICT_DOMAIN=true     # 404 if domain not registered
TENANT_DEBUG_HEADER=true      # X-Tenant-Code header when APP_DEBUG=true
```

## Test commands (workspace root)

```bash
cd /home/hrmsdev.isarva.in/public_html
php artisan config:clear
php artisan tenant:resolve hrmsdev.isarva.in
php artisan tenant:resolve attendancedev.isarva.in
php artisan tenant:resolve payrolldev.isarva.in
```

## Test in browser

1. Open **https://hrmsdev.isarva.in** — login and dashboard work as before
2. Open payroll and attendance from workspace — SSO still works
3. With browser dev tools → Network → any page → Response headers should show:
   - `X-Tenant-Code: ISARVADEV` (when `APP_DEBUG=true`)

## Rollback (instant)

Set in workspace, payroll, and attendance `.env`:

```env
TENANT_RESOLVE_DOMAIN=false
```

Then: `php artisan config:clear` in each app (or restart PHP).

## Next phase

**Phase 3:** Dynamic DB connection for **Attendance** — `TENANT_SWITCH_DATABASE` per module after provisioning Company B.
