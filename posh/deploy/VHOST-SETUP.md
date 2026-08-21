# Fix poshdev.isarva.in — one CyberPanel change

## What went wrong

You created the subdomain correctly, but CyberPanel set:

- **Document root:** `/home/hrmsdev.isarva.in/poshdev.isarva.in/` ❌  
- It also dropped a default **"CyberPanel Installed"** page there.

POSH code lives here (same as CRM/Payroll):

`/home/hrmsdev.isarva.in/public_html/posh/public`

Symlinks from the subdomain folder **do not work** (PHP `open_basedir` blocks them).

---

## Fix (2 minutes in CyberPanel)

### Step 1 — Modify the website
1. **Websites** → **List Websites**
2. Click **poshdev.isarva.in**
3. Click **Modify Website** (or **Manage** → **Modify**)

### Step 2 — Change document root
Set **Document Root** / **Path** to:

```
public_html/posh/public
```

Full path on server:

```
/home/hrmsdev.isarva.in/public_html/posh/public
```

This is the same pattern as `crmdev` → `public_html/crm/public`.

### Step 3 — PHP version
Select **PHP 8.2** (not 8.3).

### Step 4 — Save & SSL
- Save changes
- **Issue SSL** for `poshdev.isarva.in` if not already done

### Step 5 — Test
Open **https://poshdev.isarva.in** → POSH login page.

Then HRMS → **ISARVA POSH** (SSO).

---

## If you cannot find "Modify Website"

Delete the current `poshdev.isarva.in` subdomain and recreate:

| Field | Value |
|--------|--------|
| Domain Type | Sub Domain |
| Select Website | `hrmsdev.isarva.in` |
| Domain Name | `poshdev.isarva.in` |
| **Path** | **`public_html/posh/public`** |
| PHP | **8.2** |

Do **not** use path `poshdev.isarva.in` — that creates a separate empty folder.

---

## Already configured in code

- Hub `.env`: `POSH_URL=https://poshdev.isarva.in`
- POSH `.env`: `APP_URL=https://poshdev.isarva.in`
- No port `:8001` needed
