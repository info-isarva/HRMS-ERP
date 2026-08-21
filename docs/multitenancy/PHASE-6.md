# Multi-Tenant Sharding — Phase 6 (Provisioning + Real-World Testing)

**Status:** Active — automation ready; **you create DBs and domains in CyberPanel**

## What Phase 6 adds

| Command | Purpose |
|---------|---------|
| `tenant:provision` | Register company + migrate all 3 shard DBs |
| `tenant:verify` | Check DB connectivity + live domain headers |

Existing commands still work: `tenant:register`, `tenant:migrate-shard`, `tenant:list`, `tenant:resolve`.

## The main thing: when YOU act vs when artisan runs

```
┌─────────────────────────────────────────────────────────────────┐
│  YOU (CyberPanel)          │  ARTISAN (server)                  │
├────────────────────────────┼────────────────────────────────────┤
│  1. Create 3 empty MySQL   │                                    │
│     databases              │                                    │
├────────────────────────────┼────────────────────────────────────┤
│  2. Grant MySQL user       │  3. tenant:provision COMPBTEST     │
│     access to new DBs      │     (register + run migrations)    │
├────────────────────────────┼────────────────────────────────────┤
│  4. Create 3 subdomains    │  5. tenant:verify COMPBTEST        │
│     in CyberPanel          │                                    │
├────────────────────────────┼────────────────────────────────────┤
│  6. Browser reality test   │                                    │
│     (login, SSO, isolation)│                                    │
└────────────────────────────┴────────────────────────────────────┘
```

**Do not run `tenant:provision` migrations until the empty databases exist.**  
Artisan will connect-check first and tell you to create them if missing.

## Example: Company B test tenant (`COMPBTEST`)

### Step 1 — YOU: Create databases in CyberPanel

Create **three empty databases** (names are suggestions — use your own convention):

| Database | Used by |
|----------|---------|
| `hrms_compbtest_workspace` | Workspace hub (users, SSO source) |
| `hrms_compbtest_payroll` | Payroll app |
| `hrms_compbtest_attendance` | Attendance app |

Grant the same MySQL user that ISARVADEV uses (`hrms_dev_demo_payroll_user` or your app user) **full access** to all three.

### Step 2 — ARTISAN: Register + migrate

From workspace root (`public_html`):

```bash
php artisan tenant:provision COMPBTEST \
  --name="Company B Test" \
  --workspace-domain=compbtest-hrmsdev.isarva.in \
  --payroll-domain=compbtest-payrolldev.isarva.in \
  --attendance-domain=compbtest-attendancedev.isarva.in \
  --workspace-db=hrms_compbtest_workspace \
  --payroll-db=hrms_compbtest_payroll \
  --attendance-db=hrms_compbtest_attendance
```

This will:

1. Add `COMPBTEST` to `hrms_central.tenants` (status → `provisioning`, then `active`)
2. Run `tenant:migrate-shard` on workspace, payroll, and attendance apps
3. Print CyberPanel steps still needed

**If DBs are not ready yet**, register only first:

```bash
php artisan tenant:provision COMPBTEST \
  --name="Company B Test" \
  --workspace-domain=compbtest-hrmsdev.isarva.in \
  --payroll-domain=compbtest-payrolldev.isarva.in \
  --attendance-domain=compbtest-attendancedev.isarva.in \
  --workspace-db=hrms_compbtest_workspace \
  --payroll-db=hrms_compbtest_payroll \
  --attendance-db=hrms_compbtest_attendance \
  --register-only
```

Then after you create the DBs in CyberPanel:

```bash
php artisan tenant:provision COMPBTEST --migrate-only
```

### Step 3 — YOU: CyberPanel subdomains

Point each subdomain to the **same document root** as the matching ISARVADEV app:

| Subdomain | Document root (same as ISARVADEV) |
|-----------|-----------------------------------|
| `compbtest-hrmsdev.isarva.in` | workspace `public_html` |
| `compbtest-payrolldev.isarva.in` | `public_html/payroll` |
| `compbtest-attendancedev.isarva.in` | `public_html/attendance` |

No code deploy needed — domain resolution picks the tenant from `hrms_central`.

SSL: issue Let's Encrypt cert for each subdomain.

### Step 4 — ARTISAN: Verify

```bash
php artisan tenant:verify COMPBTEST
```

If domains are not live yet:

```bash
php artisan tenant:verify COMPBTEST --skip-http
```

### Step 5 — YOU: Reality browser test

See **[COMPANY-B-REAL-TEST.md](./COMPANY-B-REAL-TEST.md)** for the full checklist.

## ISARVADEV is untouched

`COMPBTEST` (or any new code) uses **separate shard DBs**. ISARVADEV keeps:

- `hrmsdev.isarva.in` → `hrms_dev_demo_payroll` / `hrms_dev_latest_attendance_v2`

## Rollback / remove test tenant

```bash
# Deactivate (domains will 404 or fall through depending on strict mode)
php artisan tinker --execute="App\Models\Central\Tenant::where('company_code','COMPBTEST')->update(['status'=>'inactive']);"
```

Drop test databases in CyberPanel when finished. ISARVADEV data is unaffected.

## Next

**Phase 7:** GPS tables tenant-scoped, prep for mobile `company_code` login.
