# POSH Phase 4 — Reports, intake & management

## Scope delivered

- **Annual compliance report** (Section 22) — generate, view, print/export, mark submitted to District Officer
- **QR / public intake** — `/intake/{orgKey}` (no login)
- **Respondent notice** — printable PDF-style notice from operate-case step
- **Evidence download** — IC-only with audit log
- **Management portal** — pending 60-day actions & IC recommendations
- **Organization settings** — employee count, locale, WhatsApp, intake link regeneration

## Routes

| Route | Access |
|-------|--------|
| `/intake/{orgKey}` | Public |
| `/management` | Authenticated |
| `/settings` | HR Admin |
| `/reports/annual/{id}/export` | IC (print) |
| `/cases/{id}/notice` | IC |
