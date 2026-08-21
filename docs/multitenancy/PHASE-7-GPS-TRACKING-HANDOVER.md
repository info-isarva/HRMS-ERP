# Multi-Tenant Sharding — Phase 7 (GPS Tracking + Login Hardening + Deployment Handover)

**Date:** 2026-06-16 (updated 2026-06-19)  
**Status:** Implemented in dev/test (`COMPBTEST`) and ready for controlled rollout

This document is a complete handover for GPS field tracking, tenant login fixes, and how to move the same setup to another server or to live.

---

## 1) What was completed today

### A. GPS field tracking (Attendance app)

Implemented end-to-end employee GPS tracking:

- GPS ping ingestion API for mobile
- Check-in and check-out location APIs with **dual open sessions** (office + visit independently)
- Daily timeline API with `open_office` and `open_visit` state for mobile button UX
- Server-side session rules (422 on invalid transitions)
- IST (`Asia/Kolkata`) timestamps in all API responses
- Office geofence: coords within radius → `place_name` = `ISARVA Office`
- Admin GPS tracking page with:
  - map route rendering
  - timeline cards
  - playback control
  - active stop highlight
  - route progress behavior

### B. Tenant login/session hardening

Fixed cross-tenant login issues where stale tenant cookie/session could force wrong DB usage (example: `COMPBTEST` session interfering with `ISARVADEV` login).

### C. Test data seeded for demo/testing

Added realistic Mangalore-area GPS sample data for `COMPBTEST` attendance shard.

---

## 2) Code changes (files touched)

## Workspace app (`public_html`)

- `app/Services/TenantDatabaseManager.php`
  - DB connection fallback hardening (use original env credentials where needed instead of mutated runtime config)
- `app/Http/Middleware/ResolveTenant.php`
  - login flow protection: skip cookie-based tenant override on login POST with `company_code`
- `app/Http/Requests/Auth/LoginRequest.php`
  - clears stale session/tenant context before applying new tenant

## Attendance app (`public_html/attendance`)

- `database/migrations/2026_06_16_000001_create_employee_gps_tracking_tables.php`
  - creates GPS raw pings + field events tables
- `app/Models/EmployeeGpsPing.php`
- `app/Models/EmployeeFieldEvent.php`
- `app/Services/GpsTrackingService.php`
  - timeline composition
  - distance/travel summary logic
  - dual open session model (`open_office` / `open_visit`)
  - check-in/check-out validation (`assertCanCheckIn`, `assertCanCheckOut`)
  - IST timestamp formatting (`DISPLAY_TIMEZONE = Asia/Kolkata`)
- `app/Exceptions/GpsSessionException.php`
  - business-rule violations → HTTP 422 (or 404) with `{ success, message }`
- `app/Http/Controllers/Api/GpsTrackingController.php`
  - mobile endpoints implementation
  - `event_type` required on check-in (`office` | `visit`)
- `config/gps.php`
  - office geofence (lat/lng/radius/name/address via `.env`)
- `database/seeders/CompbtestEmployeeProfileSeeder.php`
  - links COMPBTEST users to employee profiles for GPS API resolution
- `app/Http/Controllers/Admin/GpsTrackingController.php`
  - admin page + JSON data endpoint
- `routes/api.php`
  - GPS API routes (JWT-protected)
- `resources/views/admin/gps-tracking/index.blade.php`
  - map/timeline/playback UI + styling/interaction updates
- `database/seeders/EmployeeGpsTrackingSeeder.php`
  - realistic dummy movement route data

## Shared auth aliasing

- tenant login service alias support added for company code mapping:
  - `ISARVA` -> `ISARVADEV`

---

## 3) Data model introduced

### `employee_gps_pings`
Raw location ping stream from mobile app.

Typical columns include:

- `employee_id`
- `user_id` (no strict FK due to MyISAM users table constraints in existing system)
- latitude/longitude
- altitude/speed/accuracy
- `recorded_at`
- metadata/device fields (as implemented in migration)

### `employee_field_events`
Higher-level business events:

- office check-in/check-out
- customer/site visits
- travel segments between events

---

## 4) API contract (Attendance mobile)

**Base URL (dev):** `https://attendancedev.isarva.in/api`  
**Auth:** JWT Bearer token from `POST /api/login` (include `company_code` in body)

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/gps/ping` | Record GPS coordinate stream |
| `POST` | `/api/gps/check-in` | Office sign-in or visit check-in |
| `POST` | `/api/gps/check-out` | Office sign-out or visit check-out |
| `GET` | `/api/gps/timeline?date=YYYY-MM-DD` | Day summary, open sessions, timeline, route |

All timestamps in responses use **IST** with offset, e.g. `2026-06-19T09:00:00+05:30`.

---

### 4.1 Dual open session model (Option A)

An employee may have **both** simultaneously open on the same `track_date`:

| Session | `event_type` | Lifecycle |
|---------|--------------|-----------|
| Office | `office` | Work-day sign-in → sign-out |
| Visit | `visit` | Site check-in → check-out |

Office and visit are **independent**. Signing in at office does not block visit check-in. Visit check-out does **not** close office.

Mobile v1.1.4 uses **four buttons** driven by timeline `open_office` / `open_visit`:

| Button | Enabled when | API call |
|--------|--------------|----------|
| Office in | `open_office` is `null` | `POST check-in` `event_type=office` |
| Office out | `open_office` is set | `POST check-out` `event_id=open_office.event_id` |
| Visit in | `open_office` set **and** `open_visit` is `null` | `POST check-in` `event_type=visit` |
| Visit out | `open_visit` is set | `POST check-out` `event_id=open_visit.event_id` |

---

### 4.2 Business rules (server-enforced)

#### Office (`event_type = office`)

1. **Office in** — allowed only if no open office event for that `track_date`.
2. **Office out** — allowed only if an open office event exists; must pass that event's `event_id`.
3. Second office sign-in while one is open → **HTTP 422**:
   ```json
   { "success": false, "message": "Already signed in at office. Sign out first." }
   ```

#### Visit (`event_type = visit`)

1. **Visit in** — allowed only if:
   - employee has an **open office session** for that `track_date` (required), and
   - employee has **no open visit event**.
2. Visit without office → **HTTP 422**:
   ```json
   { "success": false, "message": "Sign in at office before recording a visit." }
   ```
3. Second visit while one is open → **HTTP 422**:
   ```json
   { "success": false, "message": "Already checked in at a visit. Check out from visit first." }
   ```
4. **Visit out** — must pass the **visit** event's `event_id` (not office).

#### Check-out (`POST /api/gps/check-out`)

- Event must belong to authenticated employee.
- Event must be open (`check_out_at` is null).
- Event type must be `office` or `visit` (not `travel`).
- Returns updated event in `data.event`.

---

### 4.3 `POST /api/gps/check-in`

**Request body:**

```json
{
  "event_type": "office",
  "place_name": "Bajpe",
  "address": "Optional address string",
  "latitude": 12.97,
  "longitude": 74.87,
  "check_in_at": "2026-06-19T09:00:00+05:30",
  "metadata": {}
}
```

| Field | Required | Notes |
|-------|----------|-------|
| `event_type` | **Yes** | `office` or `visit` |
| `place_name` | Yes | Display label; overridden to `ISARVA Office` inside geofence |
| `latitude` / `longitude` | Yes | |
| `address` | No | |
| `check_in_at` | No | ISO8601; defaults to now (IST) |
| `metadata` | No | Arbitrary JSON |

**Success (201):**

```json
{
  "success": true,
  "message": "Check-in recorded",
  "data": {
    "event": {
      "event_id": 12,
      "type": "office",
      "place_name": "ISARVA Office",
      "address": "3rd Floor, Empire Arcade...",
      "check_in_at": "2026-06-19T09:00:00+05:30",
      "check_out_at": null,
      "is_open": true,
      "lat": 12.97,
      "lng": 74.87
    }
  }
}
```

---

### 4.4 `POST /api/gps/check-out`

**Request body:**

```json
{
  "event_id": 15,
  "check_out_at": "2026-06-19T11:30:00+05:30",
  "address": "Optional address on checkout"
}
```

**Success (200):**

```json
{
  "success": true,
  "message": "Check-out recorded",
  "data": {
    "event": {
      "event_id": 15,
      "type": "visit",
      "place_name": "Kinnigoli",
      "check_in_at": "2026-06-19T10:30:00+05:30",
      "check_out_at": "2026-06-19T11:30:00+05:30",
      "is_open": false,
      "lat": 12.98,
      "lng": 74.88
    }
  }
}
```

---

### 4.5 `GET /api/gps/timeline?date=YYYY-MM-DD`

**Query:** `date` optional; defaults to today in IST.

**Response** — note `open_office` and `open_visit` (replaces legacy single `open_event`):

```json
{
  "success": true,
  "data": {
    "employee": {
      "id": 4,
      "name": "Rahul Shetty",
      "employee_id": "MNG-001",
      "designation": "Field Executive"
    },
    "date": "2026-06-19",
    "summary": {
      "travel_time_minutes": 19,
      "travel_time_label": "19 min",
      "distance_km": 12.4,
      "distance_label": "12.4 km",
      "visits": 2
    },
    "open_office": {
      "event_id": 12,
      "type": "office",
      "place_name": "ISARVA Office",
      "address": "...",
      "check_in_at": "2026-06-19T09:00:00+05:30",
      "check_out_at": null,
      "is_open": true,
      "lat": 12.97,
      "lng": 74.87
    },
    "open_visit": {
      "event_id": 15,
      "type": "visit",
      "place_name": "Kinnigoli",
      "address": "...",
      "check_in_at": "2026-06-19T10:30:00+05:30",
      "check_out_at": null,
      "is_open": true,
      "lat": 12.98,
      "lng": 74.88
    },
    "timeline": [
      {
        "event_id": 12,
        "type": "office",
        "action": "check_in",
        "title": "Office sign-in",
        "status": "Signed in at office at 9:00 AM",
        "occurred_at": "2026-06-19T09:00:00+05:30",
        "check_in_at": "2026-06-19T09:00:00+05:30",
        "check_out_at": "2026-06-19T18:00:00+05:30",
        "is_open": false,
        "lat": 12.97,
        "lng": 74.87
      },
      {
        "type": "travel",
        "title": "Travel",
        "detail": "5.2 km · 19 min",
        "time_range": "9:00 AM – 9:19 AM"
      },
      {
        "event_id": 15,
        "type": "visit",
        "action": "check_in",
        "title": "Kinnigoli — Visit in",
        "status": "Visit started at 10:30 AM",
        "occurred_at": "2026-06-19T10:30:00+05:30",
        "is_open": false
      },
      {
        "event_id": 15,
        "type": "visit",
        "action": "check_out",
        "title": "Kinnigoli — Visit out",
        "status": "Visit ended at 11:30 AM",
        "occurred_at": "2026-06-19T11:30:00+05:30",
        "is_open": false
      },
      {
        "event_id": 12,
        "type": "office",
        "action": "check_out",
        "title": "Office sign-out",
        "status": "Signed out at office at 6:00 PM",
        "occurred_at": "2026-06-19T18:00:00+05:30",
        "is_open": false
      }
    ],
    "route": [ { "lat": 12.97, "lng": 74.87, "recorded_at": "..." } ],
    "markers": []
  }
}
```

- `open_office` / `open_visit` are `null` when no session is open.
- `summary.visits` counts **completed** visits only (`check_out_at` set).
- Timeline: each office/visit session produces **separate cards** for in and out (`action`: `check_in` | `check_out`), sorted by `occurred_at`
  - Office: `"Office sign-in"` / `"Office sign-out"`
  - Visit: `"{place} — Visit in"` / `"{place} — Visit out"`

---

### 4.6 `POST /api/gps/ping`

Single ping or batch:

```json
{
  "latitude": 12.97,
  "longitude": 74.87,
  "recorded_at": "2026-06-19T09:05:00+05:30"
}
```

Or `pings: [ { latitude, longitude, recorded_at, ... }, ... ]`.

---

### 4.7 Road-aligned route display (free — no extra server)

Admin map can show a **neat road-following line** using the free **OpenRouteService** hosted API (2,000 requests/day on free plan).

1. Sign up (free): https://openrouteservice.org/dev/#/signup  
2. Add to `attendance/.env`:

```env
OPENROUTESERVICE_API_KEY=your_key_here
GPS_ROUTE_MATCHING_ENABLED=true
```

3. `php artisan config:clear`

**How it works:**
- Raw GPS pings stay in `route` (used for playback timestamps)
- `route_display` = road-aligned geometry from OpenRouteService
- `route_matched: true` when snapping succeeded
- Outlier GPS jumps are filtered before matching
- Results cached 12 hours per employee/day to save API quota

**Without API key:** map falls back to filtered raw polyline (same as before).

---

### 4.8 Office geofence (`.env`)

When check-in coords are within `GPS_OFFICE_RADIUS_M` (default 250 m) of office lat/lng, `place_name` becomes `GPS_OFFICE_NAME` (default `ISARVA Office`).

```env
GPS_OFFICE_GEOFENCE_ENABLED=true
GPS_OFFICE_LAT=12.87112
GPS_OFFICE_LNG=74.84208
GPS_OFFICE_RADIUS_M=250
GPS_OFFICE_NAME=ISARVA Office
GPS_OFFICE_ADDRESS=3rd Floor, Empire Arcade, Kadri Road, Bejai, Mangaluru 575004
```

Config file: `attendance/config/gps.php`.

---

### 4.9 Mobile login (required for GPS)

```json
POST /api/login
{
  "email": "compb.emp1@isarva.in",
  "password": "Compb@123",
  "company_code": "COMPBTEST"
}
```

Response token: `data.token` (use as `Authorization: Bearer <token>`).

Employee resolution order: `users.employee_id` → `users.payroll_id` → `users.email` matched to `employees` table. User must have a linked employee row or GPS returns 404.

---

## 5) Admin pages added

- GPS tracking page route:
  - `/admin/gps-tracking`
- JSON data route:
  - `/admin/gps-tracking/data`

Sidebar entry added under Administration.

---

## 6) Tenant/test environment used today

### Test tenant

- Company code: `COMPBTEST`
- Workspace DB: `hrms_compbtest_workspace`
- Payroll DB: `hrms_compbtest_payroll`
- Attendance DB: `hrms_compbtest_attendance`

### Existing default dev tenant (kept intact)

- Company code: `ISARVADEV` (alias: `ISARVA`)
- Attendance DB: `hrms_dev_latest_attendance_v2`

### Test logins (dev only)

| Role | Company code | Email | Password | Employee code | Use |
|------|--------------|-------|----------|---------------|-----|
| Super admin | `COMPBTEST` | `sup_admin@gmail.com` | `Compb@123` | `COMPB001` | Admin GPS map |
| Field employee | `COMPBTEST` | `compb.emp1@isarva.in` | `Compb@123` | `MNG-001` (Rahul Shetty) | Mobile GPS + seeded routes |

> For live, rotate credentials and do not keep test passwords.  
> Admin map shows data for the employee linked to the logged-in mobile user — use `compb.emp1@isarva.in` for MNG-001 demo data, not super admin unless testing admin's own profile.

---

## 7) Move to another server (full procedure)

Use this if you want the exact same feature set on a new server.

### Step 1 — Copy code

Deploy latest codebase to new server:

- workspace root: `public_html`
- attendance app: `public_html/attendance`
- payroll app: `public_html/payroll`

Make sure all changed files listed in section 2 are included.

### Step 2 — Environment and services

Set production/staging environment values:

- central DB connection
- shard DB credentials
- JWT/app keys
- cache/session/queue drivers

Then run:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Step 3 — Tenant metadata and shard DB preparation

Create/provision tenant shard DBs for target company (if not already present):

- workspace shard DB
- payroll shard DB
- attendance shard DB

Grant app DB user required permissions.

If using existing tenant provisioning flow, run:

```bash
php artisan tenant:provision <COMPANY_CODE> ...args...
```

(Use the same style as `PHASE-6.md` and your domain/DB naming convention.)

### Step 4 — Run migrations (critical)

From attendance app context on target server, run GPS migration:

```bash
cd attendance
php artisan migrate --force
```

If you run tenant-wise shard migrations through your tenant command, use that standardized command instead.

### Step 5 — Seed demo data (optional, non-live)

Only for staging/demo:

```bash
cd attendance
php artisan db:seed --class=EmployeeGpsTrackingSeeder --force
```

For live: skip seeder unless explicitly required for testing sandbox users.

### Step 6 — Domain/vhost setup

Create/verify all required domains/subdomains in panel (CyberPanel/Nginx/Apache), same mapping pattern used in Phase 6:

- workspace domain -> `public_html`
- payroll domain -> `public_html/payroll`
- attendance domain -> `public_html/attendance`

Enable SSL for all domains.

### Step 7 — Permissions and runtime checks

Ensure writable dirs:

- `storage`
- `bootstrap/cache`

Restart services as needed (php-fpm, web server, queue workers).

### Step 8 — Post-deploy smoke tests

1. Login using company code flow.
2. Confirm wrong/stale cookie does not force wrong tenant.
3. Mobile/API test (field employee `compb.emp1@isarva.in`):
   - login with `company_code`
   - ping
   - office check-in → visit check-in (both open) → visit check-out (office still open) → office check-out
   - duplicate office check-in → expect 422
   - visit without office → expect 422
   - timeline fetch → `open_office` / `open_visit` present when expected
   - verify timestamps show `+05:30` offset
4. Admin GPS page:
   - route loads
   - playback works
   - active timeline state updates
5. Verify no cross-tenant data leakage.

---

## 8) Go-live checklist

Before production release:

- [ ] Backup central + tenant shard DBs
- [ ] Confirm migration is backward-safe in staging
- [ ] Remove or disable demo seeders for production users
- [ ] Rotate known test credentials
- [ ] Validate JWT expiry/refresh behavior on mobile
- [ ] Confirm logging/monitoring is enabled for GPS APIs
- [ ] Confirm rate limiting/abuse protection for GPS ping endpoint
- [ ] Confirm timezone consistency — `APP_TIMEZONE=Asia/Kolkata`, API returns `+05:30` offsets
- [ ] Final regression:
  - [ ] tenant login
  - [ ] payroll SSO
  - [ ] attendance SSO
  - [ ] GPS admin screens

---

## 9) Rollback plan

If issue after release:

1. Disable GPS UI route temporarily (feature flag or route guard)
2. Revert code to previous release tag/commit
3. Keep new GPS tables (safe), or roll back migration only if absolutely required and data-loss accepted
4. Clear config/cache after rollback
5. Validate tenant login behavior immediately

---

## 10) Known notes and precautions

- `user_id` FK was not enforced in GPS migration because existing users table engine constraints do not allow clean FK usage in current architecture.
- Avoid running demo seeder in production tenant data unless explicitly intended.
- Mobile app must read `open_office` and `open_visit` from timeline — **not** legacy `open_event`.
- `event_type` is **required** on check-in; omitting it returns validation error (422).
- Stale duplicate open events from pre-dual-session testing may need manual DB cleanup on dev shards.
- Always test login with:
  - fresh browser
  - stale cookie scenario
  - company code switch scenario

---

## 11) Suggested next tasks (optional)

- Wire mobile v1.1.4 four-button UX to `open_office` / `open_visit` on timeline refresh
- Add feature flag for GPS module per tenant
- Clean duplicate open events from COMPBTEST dev shard after API migration testing
- Add automated test coverage for:
  - dual session check-in/check-out rules (422 messages)
  - tenant resolution during login POST
  - GPS timeline summary calculations
  - cross-tenant isolation checks

