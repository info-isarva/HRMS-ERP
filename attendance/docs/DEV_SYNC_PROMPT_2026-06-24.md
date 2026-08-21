# Dev Sync — Production Parity (2026-06-24)

Source: attendance.isarva.in live changes ported to dev.

## Layout — Dashboard (Admin / Super Admin / HR)

1. **FIRST** — Employees on Leave roster (full width)
2. **SECOND** — Two columns: Public Holidays + Leave Status
3. **THEN** — Quick Stats

Regular employees: Upcoming Leaves + Leave Status at top (unchanged).

## Roster table columns (5 only)

Employee | Department | Leave Type | Available Leave | Status

No Duration / Period / Days columns.

## Test plan

### Payslips
- [ ] Employee `/payslips` loads with px-4 py-3 filters, preview, PDF download
- [ ] Payroll Sanctum: `GET /api/payslips`, `/{month}/{year}`, `/{month}/{year}/pdf`
- [ ] Sidebar "My Payslips" after Apply Public Leave
- [ ] `.env`: `PAYROLL_API_BASE_URL`, `PAYROLL_API_JWT_TOKEN`

### Dashboard
- [ ] Admin/HR: roster appears **before** holidays + leave status cards
- [ ] Roster: 5 columns only; `.available-leave-badge` red animated
- [ ] Date bar: green calendar badge animates; date text stays solid (no shine/dim)
- [ ] `leave_date` param defaults today; onchange auto-submit
- [ ] Statuses: pending, forwarded_to_manager, approved_by_manager, approved
- [ ] Pair cards 11.75rem; scroll hints + "Scroll for more"

### Approved Leaves Report
- [ ] Default from/to = today
- [ ] Employee name search (name/email LIKE)
- [ ] Quick pills: Yesterday | Today | Tomorrow | Day After Tomorrow
