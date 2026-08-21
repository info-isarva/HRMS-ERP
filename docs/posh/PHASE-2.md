# Phase 2 — Complaints & case lifecycle

## Delivered

- **Employee intake:** File complaint (anonymous option), evidence upload, auto Case ID `POSH-YYYY-####`
- **My cases:** Employees track their own complaints
- **IC case management:** All cases list, filter, search
- **Operate case wizard:** 9 steps from prototype (IC Review → Close)
- **Status machine:** Core statuses from `poshactresearch` config
- **Timeline & audit:** Per-case logs + organization audit trail
- **Dashboard:** Live open/closed case counts

## Roles

| Role | Phase 2 access |
|------|----------------|
| Employee | File complaint, view own cases |
| IC / HR Admin | All cases, operate wizard, audit log |
| HR Admin | + IC setup, policy (Phase 1) |

## How to test

### Employee flow

1. SSO as non-admin user (or admin testing as employee)
2. **Employee Portal** → **File Complaint**
3. Fill form → Submit → note Case ID (e.g. `POSH-2026-0001`)
4. **My Cases** → open case → view timeline

### IC flow (bootstrap admin email)

1. SSO as `POSH_BOOTSTRAP_ADMIN_EMAILS` user
2. **All Cases** → see submitted complaint
3. Click **Operate** → walk through 9 steps → **Save & Next Step**
4. **Audit Log** → see entries
5. **Dashboard** → open/closed counts update

## Database tables (new)

- `posh_complaints`
- `posh_complaint_logs`
- `posh_complaint_evidence`
- `posh_audit_logs`

## Phase 3 preview

- Statutory SLA alerts (90/10/60 days)
- Employer duties checklist
- Annual compliance report
- Evidence download for IC (authorized)

Sign off with **“Phase 2 perfect”** to continue Phase 3.
