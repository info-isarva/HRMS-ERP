# Multi-Tenant Sharding — Phase 1 (Central Registry)

**Status:** Active — registry only, **no runtime DB switching yet**

## What Phase 1 does

- Creates central database `hrms_central` (configurable via `CENTRAL_DB_*`)
- Stores `tenants` table: company code, domains, shard database names
- Provides Artisan commands to install and register companies
- **Does not** change login, SSO, payroll, attendance, or any HTTP request handling

## Current dev mapping (Tenant A)

| Module     | Domain                    | Database (today)              |
|------------|---------------------------|-------------------------------|
| Workspace  | `hrmsdev.isarva.in`       | `hrms_dev_demo_payroll`       |
| Payroll    | `payrolldev.isarva.in`    | `hrms_dev_demo_payroll`       |
| Attendance | `attendancedev.isarva.in` | `hrms_dev_latest_attendance_v2` |

Apps continue using their existing `.env` `DB_DATABASE` values.

## Setup (one time)

From workspace root (`public_html`):

```bash
# Preferred (needs MySQL user with CREATE DATABASE):
php artisan tenant:install

# Dev fallback if hrms_central cannot be created (registry lives in workspace DB):
php artisan tenant:install --on-workspace

php artisan tenant:register-current
php artisan tenant:list
```

> **Note:** On this server, `hrms_dev_demo_payroll_user` cannot create `hrms_central`.
> Phase 1 uses `--on-workspace` until a DBA creates `hrms_central` and data is migrated.

## Commands

| Command | Purpose |
|---------|---------|
| `tenant:install` | Create `hrms_central` + run migrations |
| `tenant:register-current` | Register this dev environment as `ISARVADEV` |
| `tenant:register` | Register another company (manual options) |
| `tenant:list` | Show registered tenants |

### Register a second company (placeholder, Phase 2+)

```bash
php artisan tenant:register \
  --code=COMPB \
  --name="Company B Demo" \
  --workspace-domain=companyb-hrmsdev.isarva.in \
  --payroll-domain=companyb-payrolldev.isarva.in \
  --attendance-domain=companyb-attendancedev.isarva.in \
  --workspace-db=hrms_company_b_workspace \
  --payroll-db=hrms_company_b_payroll \
  --attendance-db=hrms_company_b_attendance \
  --inactive
```

## Environment variables (optional)

```env
# Omit CENTRAL_DB_DATABASE to use workspace DB until hrms_central exists:
# CENTRAL_DB_DATABASE=hrms_central

# Feature flags — keep false until later phases
TENANT_RESOLVE_DOMAIN=false
TENANT_SWITCH_DATABASE=false
```

If `CENTRAL_DB_DATABASE` is not set, the registry uses the same database as workspace (`DB_DATABASE`). Set `CENTRAL_DB_DATABASE=hrms_central` once your DBA creates that database.

## How to test Phase 1

1. **Regression:** Log in to workspace, payroll, attendance — everything works as before
2. **Registry:** `php artisan tenant:list` shows `ISARVADEV` with correct domains/DB names
3. **Isolation check:** No new middleware; grep confirms `TENANT_SWITCH_DATABASE=false`

## Next phase (not started)

**Phase 2:** `ResolveTenant` middleware — read domain, lookup tenant, log only (still use `.env` DB)

Do not enable `TENANT_RESOLVE_DOMAIN` until Phase 2 is implemented and approved.
