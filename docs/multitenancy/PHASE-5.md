# Multi-Tenant Sharding — Phase 5 (Workspace DB + SSO Hardening)

**Status:** Active for **workspace hub** + SSO tenant binding on child apps

## What Phase 5 does

### Workspace (`hrmsdev.isarva.in`)

1. Resolve tenant from `hrms_central`
2. Switch `mysql` to `tenant.workspace_database`
3. SSO tokens include `tenant_id` and `company_code`
4. SSO redirects use tenant registry domains (`payroll_domain`, `attendance_domain`, etc.)

### Attendance & Payroll

- `/sso-authenticate` **rejects** tokens when:
  - `tenant_id` in JWT ≠ domain-resolved tenant, or
  - `company_code` mismatch
- Prevents using a workspace login token on another company's subdomain

## App DB switch summary

| App | DB switch |
|-----|-----------|
| Workspace | Phase 5 — ON |
| Attendance | Phase 3 — ON |
| Payroll | Phase 4 — ON |

## Tenant A (dev) — same data

| Domain | Shard DB |
|--------|----------|
| hrmsdev.isarva.in | `hrms_dev_demo_payroll` |

Matches existing workspace `.env` — no data migration needed.

## Environment (workspace `.env`)

```env
TENANT_RESOLVE_DOMAIN=true
TENANT_SWITCH_DATABASE=true
```

Attendance and payroll keep `TENANT_SWITCH_DATABASE=true` from Phases 3–4.

## Verify workspace headers

```bash
curl -sI https://hrmsdev.isarva.in/login | grep -i x-tenant
```

Expected:

```
x-tenant-code: ISARVADEV
x-tenant-database: hrms_dev_demo_payroll
```

## Verify SSO flow

1. Log in at `https://hrmsdev.isarva.in`
2. Open **Payroll** or **Attendance** from dashboard
3. Should land on `payrolldev.isarva.in` / `attendancedev.isarva.in` logged in
4. Decode JWT (debug): claims include `tenant_id` and `company_code`

## Cross-tenant rejection test

An old token without `tenant_id`, or a token from another tenant, should fail SSO with:

> SSO token missing tenant binding / tenant does not match this domain

## Provision Company B workspace shard

```bash
cd /home/hrmsdev.isarva.in/public_html
php artisan tenant:migrate-shard hrms_company_b_workspace
```

Register in `hrms_central` with `workspace_database` and `workspace_domain`.

## Rollback

`public_html/.env`: `TENANT_SWITCH_DATABASE=false` + `php artisan config:clear`

Child apps can keep DB switching; only workspace hub reverts to static `.env` DB.

## Next

**Phase 7:** GPS tables tenant-scoped, prep for mobile `company_code` login.
