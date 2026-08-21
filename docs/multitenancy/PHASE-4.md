# Multi-Tenant Sharding — Phase 4 (Payroll DB Switch)

**Status:** Active for **payroll app only**

## What Phase 4 does

On **payrolldev.isarva.in** (or any registered payroll domain):

1. Resolve tenant from `hrms_central`
2. Switch `mysql` connection to `tenant.payroll_database`
3. All payroll queries use that shard

| App | DB switch |
|-----|-----------|
| Attendance | Phase 3 — ON |
| Payroll | Phase 4 — ON |
| Workspace | OFF (Phase 5) |

## Tenant A (dev) — same data

| Domain | Shard DB |
|--------|----------|
| payrolldev.isarva.in | `hrms_dev_demo_payroll` |

Matches existing `.env` — no data migration needed.

## Environment (payroll `.env`)

```env
TENANT_RESOLVE_DOMAIN=true
TENANT_SWITCH_DATABASE=true
```

## Verify

```bash
curl -sI https://payrolldev.isarva.in/login | grep -i x-tenant
```

Expected:

```
x-tenant-code: ISARVADEV
x-tenant-database: hrms_dev_demo_payroll
```

Browser: login via workspace → payroll, run payroll features, payslip API from attendance.

## Provision Company B payroll shard

```bash
cd /home/hrmsdev.isarva.in/public_html/payroll
php artisan tenant:migrate-shard hrms_company_b_payroll
```

Register in `hrms_central` with `payroll_database` and `payroll_domain` for Company B.

## Cross-app sync note

Attendance payslip API calls payroll using `PAYROLL_SYNC_URL`. Both apps must resolve the **same tenant** and each must have access to their shard DB. Company A ↔ Company A only.

## Rollback

`payroll/.env`: `TENANT_SWITCH_DATABASE=false` + `php artisan config:clear`

## Next

**Phase 6:** `tenant:provision` automation, CyberPanel domain docs.
