# POSH Phase 3 — Compliance & SLA

## Scope delivered

- **Statutory SLA tracking**: 90-day inquiry, 10-day report + 60-day management action deadlines on cases
- **Filing deadline**: 3 + 3 month window flagged on intake
- **Employer duties checklist** (Section 19) — 14 items from prototype
- **Prevention events**: workshops, IC orientation, display tracking
- **SLA alerts** on IC dashboard
- **Artisan command**: `php artisan posh:check-slas` (scheduled daily 08:00)

## Routes

| Route | Role |
|-------|------|
| `/compliance` | IC |
| `/reports/annual` | IC |

## Migrations

`2026_05_22_300000_create_posh_compliance_tables.php`
