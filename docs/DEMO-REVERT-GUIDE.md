# Revert Demo Client Setup — Back to Production

Use this guide when the **15-day demo ends** and you want the site to work exactly as before, with your **original production databases** and full module access.

Your production databases were **never deleted** during demo setup. Reverting is mainly switching `.env` files back and clearing cache.

---

## Before you start

1. **Locate your backup `.env` files** (you should have saved these before the demo switch).
2. Confirm production databases still exist in MySQL (they were not dropped).
3. Plan a short maintenance window — users may need to log in again after session clear.

---

## Step 1 — Restore database connection (all 3 apps)

Edit these files and restore **production** values from your backup:

| App | File |
|-----|------|
| Workspace hub | `/public_html/.env` |
| Payroll | `/public_html/payroll/.env` |
| Attendance | `/public_html/attendance/.env` |

Change these keys back to production (use your backed-up values):

```env
DB_DATABASE=<your_production_database_name>
DB_USERNAME=<your_production_db_user>
DB_PASSWORD="<your_production_db_password>"
```

**Important:** If the password contains `#`, `$`, or spaces, wrap it in double quotes:

```env
DB_PASSWORD="your#password_here"
```

### Current demo values (replace these)

| App | Demo database |
|-----|----------------|
| Workspace + Payroll | `hrms_client_dev_payroll` |
| Attendance | `hrms_client_dev_attendance` |

### Typical production values (verify from your backup)

| App | Example (from project defaults — use YOUR backup) |
|-----|---------------------------------------------------|
| Workspace | `hrms_workspace` or your original hub DB |
| Payroll | `hr_database` or your original payroll DB |
| Attendance | your original attendance DB name |

---

## Step 2 — Turn off demo mode flags

In **all three** `.env` files (workspace, payroll, attendance), remove or update:

```env
# Disable demo banner and demo-only behaviour
DEMO_MODE_ENABLED=false

# Optional — remove these lines entirely when reverting
# DEMO_EXPIRES_AT=2026-06-24
```

---

## Step 3 — Re-enable CRM and POSH

### Workspace (`/public_html/.env`)

```env
CRM_MODULE_ENABLED=true
POSH_MODULE_PLACEHOLDER_ENABLED=true
```

### Payroll (`/public_html/payroll/.env`)

```env
CRM_MODULE_ENABLED=true
CRM_SYNC_ENABLED=true
POSH_MODULE_PLACEHOLDER_ENABLED=true
```

### Attendance (`/public_html/attendance/.env`)

```env
CRM_MODULE_ENABLED=true
POSH_MODULE_PLACEHOLDER_ENABLED=true
```

CRM and POSH apps (`/crm/.env`, `/posh/.env`) were **not** switched to demo DBs — no DB change needed there unless you changed them manually.

---

## Step 4 — Clear Laravel cache (required)

Run in each app:

```bash
cd /home/hrmsdev.isarva.in/public_html
php artisan config:clear
php artisan view:clear
php artisan cache:clear

cd /home/hrmsdev.isarva.in/public_html/payroll
php artisan config:clear
php artisan view:clear
php artisan cache:clear

cd /home/hrmsdev.isarva.in/public_html/attendance
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

---

## Step 5 — Verify production is back

1. Open `https://hrmsdev.isarva.in` — **demo banner should be gone**.
2. Log in with a **production** user (not only demo Super Admin).
3. Dashboard should show **CRM and POSH** again (if enabled for that user).
4. Open Payroll and Attendance — confirm **real employee data** is visible.
5. Test SSO from workspace → payroll and attendance.

---

## Step 6 — Optional: keep or remove demo databases

Demo databases are safe to keep for future demos:

- `hrms_client_dev_payroll`
- `hrms_client_dev_attendance`

To remove them later (only when you no longer need the demo copy):

```sql
-- Run only when you are sure you no longer need demo data
DROP DATABASE hrms_client_dev_payroll;
DROP DATABASE hrms_client_dev_attendance;
```

**Do not run DROP unless you are certain.** Production DBs are separate and unaffected.

---

## Quick revert checklist

- [ ] Restore `DB_*` in workspace `.env` from backup
- [ ] Restore `DB_*` in payroll `.env` from backup
- [ ] Restore `DB_*` in attendance `.env` from backup
- [ ] Set `DEMO_MODE_ENABLED=false` in all three apps
- [ ] Set `CRM_MODULE_ENABLED=true` and `POSH_MODULE_PLACEHOLDER_ENABLED=true`
- [ ] Set `CRM_SYNC_ENABLED=true` in payroll `.env`
- [ ] Run `config:clear` + `view:clear` in workspace, payroll, attendance
- [ ] Test login and data on production

---

## Re-run demo setup later

If you need another client demo:

1. Import fresh copy of production → demo databases (or re-run mysqldump).
2. Run: `php scripts/setup-demo-client.php` (only works on `*client_dev*` databases).
3. Point `.env` files to demo databases again.
4. Set demo flags (`DEMO_MODE_ENABLED=true`, etc.).

See team notes or the demo setup conversation for full demo provisioning steps.

---

## Files changed for demo (reference)

| Purpose | Location |
|---------|----------|
| Demo config | `config/demo.php`, `payroll/config/demo.php`, `attendance/config/demo.php` |
| Demo banner UI | `resources/views/components/demo-banner.blade.php` (+ payroll & attendance copies) |
| Dashboard CRM/POSH hide | `resources/views/dashboard.blade.php` |
| DB cleanup script | `scripts/setup-demo-client.php` |
| Layout banner placement | `payroll/resources/views/layouts/master.blade.php`, `attendance/resources/views/layouts/app.blade.php` |

Reverting `.env` + cache clear is enough for day-to-day restore; code changes stay harmless when `DEMO_MODE_ENABLED=false`.

---

*Last updated: June 9, 2026*
