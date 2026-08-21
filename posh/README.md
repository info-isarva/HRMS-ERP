# POSH Compliance Module

Laravel application for full POSH Act (2013) workplace compliance.

## URLs

| Environment | App URL | Hub SSO |
|-------------|---------|---------|
| Dev | `https://poshdev.isarva.in` | `https://hrmsdev.isarva.in/posh-access` |

## Phase status

| Phase | Status |
|-------|--------|
| 0 | Legacy Payroll/Attendance POSH deprecated |
| 1 | App + SSO + IC Setup + Policy + Employee portal |
| 2 | Complaints, operate wizard, audit log |
| 3 | SLA alerts, S.19 employer duties checklist, prevention events |
| 4 | Annual report, QR intake, notices, evidence download, management portal |
| 5 | Settings, locale switcher, WhatsApp helpline, product polish |

## Docs

- [Phase 0 testing](../docs/posh/PHASE-0.md)
- [Phase 1 setup & testing](../docs/posh/PHASE-1.md)
- [Phase 2 complaints & cases](../docs/posh/PHASE-2.md)
- [Phase 3 compliance & SLA](../docs/posh/PHASE-3.md)
- [Phase 4 reports & intake](../docs/posh/PHASE-4.md)
- [Phase 5 polish](../docs/posh/PHASE-5.md)

## Product spec

UX and workflow source: [`../poshactresearch/`](../poshactresearch/)

## Quick start (dev)

```bash
cd posh
cp .env.example .env
# Set JWT_SECRET, JWT_HMAC_SECRET same as hub
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
```
