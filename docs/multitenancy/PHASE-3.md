# Multi-Tenant Sharding — Phase 3 (Attendance DB Switch)

**Status:** Active for **attendance app only**

## What Phase 3 does

When a request hits **attendancedev.isarva.in** (or any registered attendance domain):

1. Phase 2: resolve tenant from `hrms_central.tenants`
2. Phase 3: switch Laravel `mysql` connection to `tenant.attendance_database`
3. All attendance queries (users, leaves, sessions, etc.) use that shard

**Workspace and payroll are unchanged** (`TENANT_SWITCH_DATABASE=false`).

## Tenant A (current dev) — no data change

| Domain | Shard DB (from registry) | Same as `.env`? |
|--------|--------------------------|-----------------|
| attendancedev.isarva.in | `hrms_dev_latest_attendance_v2` | Yes |

ISARVADEV continues using the same database; only the **routing mechanism** changed.

## Environment (attendance `.env` only)

```env
TENANT_RESOLVE_DOMAIN=true
TENANT_SWITCH_DATABASE=true
```

Rollback: set `TENANT_SWITCH_DATABASE=false` and `php artisan config:clear`.

## Verify Phase 3

### Browser
1. Log in to **https://attendancedev.isarva.in**
2. DevTools → Network → any page → Response headers:
   - `X-Tenant-Code: ISARVADEV`
   - `X-Tenant-Database: hrms_dev_latest_attendance_v2`
3. Leaves, payslips, dashboard — all work as before

### CLI
```bash
curl -sI https://attendancedev.isarva.in/login | grep -i x-tenant
```

## Provision Company B (when ready)

### 1. Create database in CyberPanel
e.g. `hrms_company_b_attendance` — grant access to attendance DB user.

### 2. Run migrations on shard
```bash
cd /home/hrmsdev.isarva.in/public_html/attendance
php artisan tenant:migrate-shard hrms_company_b_attendance
# or with CREATE privilege:
php artisan tenant:migrate-shard hrms_company_b_attendance --create
```

### 3. Register tenant (workspace root)
```bash
cd /home/hrmsdev.isarva.in/public_html
php artisan tenant:register \
  --code=COMPB \
  --name="Company B Demo" \
  --workspace-domain=companyb-hrmsdev.isarva.in \
  --payroll-domain=companyb-payrolldev.isarva.in \
  --attendance-domain=companyb-attendancedev.isarva.in \
  --workspace-db=hrms_company_b_workspace \
  --payroll-db=hrms_company_b_payroll \
  --attendance-db=hrms_company_b_attendance
```

### 4. CyberPanel child domain
Point `companyb-attendancedev.isarva.in` → same `public_html/attendance` path.

### 5. Test isolation
- Company A employees must **not** appear on Company B domain
- Create a test user only in Company B DB

## MySQL user note

The attendance `.env` DB user must have access to **every** tenant attendance database you add. In CyberPanel, grant the user on each new shard DB.

## Next phase

**Phase 4:** Payroll DB switching (same pattern as attendance).
