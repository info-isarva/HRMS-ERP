# Phase 1 — ISARVA POSH app + HRMS SSO

## Delivered

- Standalone Laravel app at `posh/` (fourth module, like CRM)
- SSO from HRMS hub (`/posh-access` → `posh/sso-authenticate`)
- Dashboard with compliance stats (cases = Phase 2)
- **IC Setup** — CRUD for Internal Committee members (50% women indicator)
- **Policy** — publish versions, employee acknowledgement
- **Employee portal** — policy view, IC contacts, complaint placeholder (Phase 2)

## Temporary dev server (no vhost yet)

If `poshdev.isarva.in` DNS/vhost is not ready, use **port 8001** on the dev server:

**Mac `/etc/hosts`:**
```text
139.84.143.214   poshdev.isarva.in
139.84.143.214   hrmsdev.isarva.in
```

**Hub `.env`:** `POSH_URL=http://poshdev.isarva.in:8001`  
**POSH `.env`:** `APP_URL=http://poshdev.isarva.in:8001`

Start on server:
```bash
cd posh && ./start-dev-server.sh
```

### Why it stops after a few days

POSH on dev uses **`php artisan serve` on port 8001** (not the OpenLiteSpeed vhost). That process is **not** managed by systemd/cron on this account, so it disappears when:

- the server **reboots** (kernel updates, host maintenance),
- the PHP process is **killed** (OOM, manual cleanup, failed deploy),
- or nobody has run **`./start-dev-server.sh`** since the last incident.

`ERR_CONNECTION_REFUSED` in the browser means nothing is listening on `:8001` — not a DNS or SSO bug.

**Keep it running (ask server admin once):**

1. **systemd (best):** copy `posh/deploy/systemd/posh-dev-server.service` to `/etc/systemd/system/`, then `systemctl daemon-reload && systemctl enable --now posh-dev-server`.
2. **cron fallback:** add lines from `posh/deploy/cron/posh-dev-server.cron` to the `hrmsd4721` crontab (`@reboot` + `*/5` watchdog via `ensure-dev-server.sh`).

**Long-term:** point `poshdev.isarva.in` vhost at `posh/public` (HTTPS, no `:8001`) — see “Server setup” below.

Test: **http://poshdev.isarva.in:8001** (use `http`, not `https`, and include `:8001`).

Login at **https://hrmsdev.isarva.in** → ISARVA POSH → SSO to port 8001.

---

## Server setup (required for production)

### 1. Web vhost for POSH

Point a subdomain to `public_html/posh/public` (same pattern as CRM):

- Example: `https://poshdev.isarva.in` → `/home/hrmsdev.isarva.in/public_html/posh/public`

### 2. MySQL database (production)

```sql
CREATE DATABASE hrms_poshdev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hrms_poshdev_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL ON hrms_poshdev.* TO 'hrms_poshdev_user'@'localhost';
FLUSH PRIVILEGES;
```

Copy `posh/.env.example` → `posh/.env`, set DB credentials and shared JWT keys.

```bash
cd posh
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```

### 3. HRMS hub `.env`

```env
POSH_URL=https://poshdev.isarva.in
```

Must use the **same** `JWT_SECRET` and `JWT_HMAC_SECRET` as `posh/.env`.

### 4. HR Admin access

Add your hub login email(s):

```env
POSH_BOOTSTRAP_ADMIN_EMAILS=you@company.com,hr@company.com
```

Users not in this list and not listed on an IC member email get **Employee** role.

IC member emails map to: Presiding Officer, IC Member, or External Member.

```bash
php artisan config:clear   # in hub, payroll, attendance, posh
```

## How to test Phase 1

### SSO launch

1. Log in to **HRMS workspace** (`hrmsdev.isarva.in`).
2. Click **ISARVA POSH** (opens new tab).
3. You should land on POSH **Dashboard** without a separate POSH password.

### HR Admin (bootstrap email)

1. Set `POSH_BOOTSTRAP_ADMIN_EMAILS` to your hub email in `posh/.env`.
2. SSO again.
3. Sidebar shows **IC Setup** and **Policy**.
4. Add IC members (include Presiding Officer + external member).
5. Create policy → **Save & publish**.
6. Confirm **50% women** badge on IC Setup when applicable.

### Employee flow

1. SSO with a user **not** in bootstrap admins and **not** on IC list.
2. Open **Employee Portal** → **View Policy** → check box → **I Agree**.
3. Dashboard should show acknowledgement complete.

### Workspace links

- Payroll / Attendance sidebar **ISARVA POSH** → hub `/posh-access` → SSO.

### Legacy POSH

Still off when `POSH_LEGACY_ENABLED=false` (Phase 0).

## Sign-off checklist

- [ ] Vhost serves `posh/public`
- [ ] Migrations ran on `hrms_poshdev` (or SQLite for local dev)
- [ ] `POSH_URL` set on hub; SSO opens POSH dashboard
- [ ] Bootstrap admin can manage IC + policy
- [ ] Employee can acknowledge published policy
- [ ] JWT secrets match across hub and posh

Reply **“Phase 1 perfect”** to start **Phase 2** (complaint intake + case state machine from `poshactresearch`).
