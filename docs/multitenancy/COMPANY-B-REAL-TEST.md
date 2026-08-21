# Company B — Real-World Multi-Tenant Test Playbook

> **Updated:** Use **company code login on the same URL** — no extra subdomains.  
> See **[COMPANY-CODE-LOGIN.md](./COMPANY-CODE-LOGIN.md)** for the current model.

Use this when you create **multiple attendance and payroll databases** with a **different company code** (`COMPBTEST`) to prove tenant isolation.

ISARVADEV (`hrmsdev.isarva.in`) stays your live dev tenant. Company B is a **parallel test tenant** with its own shards on the **same domains**.

---

## Quick reference: who does what

| Step | Who | Action |
|------|-----|--------|
| **A** | **You** | Create 3 empty MySQL DBs in CyberPanel |
| **B** | **You** | Grant each app's `DB_USERNAME` access to those DBs |
| **C** | **Artisan** | `tenant:provision COMPBTEST --migrate-only` |
| **D** | **You** | Browser/mobile test with company code `COMPBTEST` on same URLs |
| ~~E~~ | ~~You~~ | ~~Create subdomains~~ **Not needed** |

**Tell us when step A+B are done** — we run step C.  
**Then test step D** on `hrmsdev.isarva.in` with code `COMPBTEST` vs `ISARVADEV`.

---

## Suggested naming (example code: `COMPBTEST`)

| Item | Value |
|------|-------|
| Company code | `COMPBTEST` |
| Workspace domain | `compbtest-hrmsdev.isarva.in` |
| Payroll domain | `compbtest-payrolldev.isarva.in` |
| Attendance domain | `compbtest-attendancedev.isarva.in` |
| Workspace DB | `hrms_compbtest_workspace` |
| Payroll DB | `hrms_compbtest_payroll` |
| Attendance DB | `hrms_compbtest_attendance` |

You can change names — just use the same values in CyberPanel and in `tenant:provision` flags.

---

## Step A — Create databases (YOU — CyberPanel)

In **CyberPanel → Databases → Create Database**, create three **empty** databases:

1. `hrms_compbtest_workspace`
2. `hrms_compbtest_payroll`
3. `hrms_compbtest_attendance`

Do **not** import ISARVADEV data into these unless you intentionally want a copy for testing. Empty DBs + artisan migrations = clean isolated tenant.

---

## Step B — Grant MySQL user (YOU — CyberPanel)

The Laravel apps use one MySQL user (check workspace `.env` `DB_USERNAME`). That user needs **ALL PRIVILEGES** on the three new databases.

Same pattern you used for `hrms_central` and existing shard DBs.

---

## Step C — Register + migrate (ARTISAN)

Only run this **after steps A and B succeed**.

```bash
cd /home/hrmsdev.isarva.in/public_html

php artisan tenant:provision COMPBTEST \
  --name="Company B Test" \
  --workspace-domain=compbtest-hrmsdev.isarva.in \
  --payroll-domain=compbtest-payrolldev.isarva.in \
  --attendance-domain=compbtest-attendancedev.isarva.in \
  --workspace-db=hrms_compbtest_workspace \
  --payroll-db=hrms_compbtest_payroll \
  --attendance-db=hrms_compbtest_attendance
```

Expected output:

- ✓ all three databases connect
- migrations run on workspace, payroll, attendance
- tenant status → `active`

If you registered early without DBs:

```bash
php artisan tenant:provision COMPBTEST --migrate-only
```

---

## Step D — Subdomains (YOU — CyberPanel)

Create websites / subdomains pointing to existing app roots:

| Domain | Points to |
|--------|-----------|
| `compbtest-hrmsdev.isarva.in` | Workspace root (`public_html`) |
| `compbtest-payrolldev.isarva.in` | Payroll (`public_html/payroll`) |
| `compbtest-attendancedev.isarva.in` | Attendance (`public_html/attendance`) |

Issue SSL certificates for each.

---

## Step E — Verify (ARTISAN)

```bash
php artisan tenant:verify COMPBTEST
```

Each app should return headers like:

```
X-Tenant-Code: COMPBTEST
X-Tenant-Database: hrms_compbtest_<module>
```

Quick manual check:

```bash
curl -sI https://compbtest-hrmsdev.isarva.in/login | grep -i x-tenant
curl -sI https://compbtest-payrolldev.isarva.in/login | grep -i x-tenant
curl -sI https://compbtest-attendancedev.isarva.in/login | grep -i x-tenant
```

---

## Step F — Reality tests (YOU — browser)

### F1 — Workspace login

1. Open `https://compbtest-hrmsdev.isarva.in`
2. Create or seed a test user in **COMPBTEST workspace DB** (fresh DB — no users yet)
3. You may need to insert a user manually or run a one-off seeder for COMPBTEST only

> **Note:** COMPBTEST workspace DB starts empty. ISARVADEV users do not exist there until you add them.

### F2 — SSO to payroll

1. From COMPBTEST workspace dashboard → **Payroll**
2. Must redirect to `compbtest-payrolldev.isarva.in` (not `payrolldev.isarva.in`)
3. Login succeeds; payroll uses `hrms_compbtest_payroll`

### F3 — SSO to attendance

1. Dashboard → **Attendance**
2. Must redirect to `compbtest-attendancedev.isarva.in`
3. Attendance uses `hrms_compbtest_attendance`

### F4 — Cross-tenant token rejection (security)

1. Log in to **ISARVADEV** (`hrmsdev.isarva.in`)
2. Copy the SSO URL or token from payroll redirect
3. Try opening that token on `compbtest-payrolldev.isarva.in/sso-authenticate?token=...`
4. **Must fail** with tenant mismatch error

### F5 — Data isolation

| Check | Expected |
|-------|----------|
| Employee list in COMPBTEST attendance | Only COMPBTEST data |
| Employee list in ISARVADEV attendance | Only ISARVADEV data |
| Payslips in COMPBTEST | Empty or COMPB-only |
| `php artisan tenant:list` | Both tenants `active` |

---

## Seeding a test user in COMPBTEST (optional)

After migrations, COMPBTEST workspace has tables but no users. Options:

1. **Register via workspace** if registration is enabled
2. **Copy one test user** from ISARVADEV into `hrms_compbtest_workspace.users` with a new email
3. Ask us to add a `tenant:seed-admin COMPBTEST` command in a future phase

For payroll/attendance SSO, the user email must exist in those shards too (SSO creates users on first login in attendance; payroll has its own user table).

---

## When to contact / continue development

| You completed | We can help with |
|---------------|------------------|
| Steps A + B | Run or debug `tenant:provision` |
| Step D live | Interpret `tenant:verify` / header issues |
| Step F failures | SSO mismatch, wrong DB, domain mapping |
| All tests pass | Phase 7 (GPS + mobile `company_code`) |

---

## Cleanup after testing

```bash
php artisan tinker --execute="App\Models\Central\Tenant::where('company_code','COMPBTEST')->update(['status'=>'inactive']);"
```

Drop the three `hrms_compbtest_*` databases in CyberPanel when no longer needed.

ISARVADEV remains the default dev environment.
