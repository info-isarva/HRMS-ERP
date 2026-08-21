# Multi-Tenant — Company Code Login (One App, Many Companies)

**This is the primary model.** One URL for all companies. Users enter **company code** at login.

## How it works

```
Same URL (hrmsdev.isarva.in / payrolldev / attendancedev)
        ↓
Login: company_code + email + password
        ↓
Lookup hrms_central.tenants by company_code
        ↓
Switch to that company's shard database
        ↓
Session (web) or JWT (mobile/API) carries tenant_id
```

**No per-company subdomains required.** Domains are optional legacy only.

## Environment (all 3 apps)

```env
TENANT_RESOLVE_DOMAIN=false
TENANT_RESOLVE_FROM_SESSION=true
TENANT_RESOLVE_FROM_JWT=true
TENANT_SWITCH_DATABASE=true
```

## Web login

Workspace login form includes **Company Code** (default placeholder: `ISARVADEV`).

Test isolation on the **same URL**:

1. Login with `ISARVADEV` → your existing data
2. Logout
3. Login with `COMPBTEST` → separate empty/test shard

## Mobile API (attendance)

```http
POST /api/login
Content-Type: application/json

{
  "company_code": "ISARVADEV",
  "email": "user@example.com",
  "password": "secret"
}
```

Response includes `tenant.company_code` and JWT with `tenant_id` claim.  
All subsequent API calls use that JWT; middleware switches DB from the token.

## SSO (workspace → payroll / attendance)

- Workspace issues JWT with `tenant_id` + `company_code` from session
- Child apps read tenant from JWT (not domain)
- Same payroll/attendance URLs for every company

## COMPBTEST databases you created

| Database | Company |
|----------|---------|
| `hrms_compbtest_workspace` | COMPBTEST workspace |
| `hrms_compbtest_payroll` | COMPBTEST payroll |
| `hrms_compbtest_attendance` | COMPBTEST attendance |

### Before migrations work

Grant the **existing app MySQL user** (from each app's `.env` `DB_USERNAME`) access to all three `hrms_compbtest_*` databases.  
Per-tenant DB users you created in CyberPanel are fine for isolation later; the app currently uses one shared DB user per app.

Then run:

```bash
cd /home/hrmsdev.isarva.in/public_html
php artisan tenant:provision COMPBTEST --migrate-only
```

**No domains needed** for testing.

## Verify

```bash
php artisan tenant:list
php artisan config:clear   # in each app after .env change
```

Browser: `https://hrmsdev.isarva.in/login` → company code `ISARVADEV` or `COMPBTEST`.

Debug headers (when logged in, `APP_DEBUG=true`):

```
X-Tenant-Code: ISARVADEV
X-Tenant-Source: session
X-Tenant-Database: hrms_dev_demo_payroll
```
