# POSH Product Page — Handoff for Website Developer

Standalone HTML reference for the **POSH Compliance** product page on [isarvait.com](https://www.isarvait.com/).

## Live preview (dev server)

**https://hrmsdev.isarva.in/posh-product-page/**

Files must live under `public_html/public/posh-product-page/` (Laravel public folder).

Source copy (keep in sync): `public_html/posh-product-page/`

## Files

| File | Purpose |
|------|---------|
| `index.html` | Full product page (Tailwind CDN + vanilla JS) |
| `README.md` | This handoff note |

## How to use

1. Open the live URL above for layout and copy reference.
2. Port sections into Next.js components (suggested split below).
3. Replace all `placehold.co` module images with real POSH software screenshots.
4. Hero uses a temporary Unsplash photo — replace with dashboard screenshot.
5. Wire CTAs to contact/demo routes (currently links to isarvait.com contact page).

## Suggested Next.js component split

- `PoshProductHero`
- `PoshModuleStrip` (sticky module pills)
- `PoshDeepDive` (alternating image + text blocks)
- `PoshProblemGrid`
- `PoshFeatureSuite` (card grid)
- `PoshComplianceTimeline`
- `PoshKeyFeaturesTabs`
- `PoshDeploymentCompare`
- `PoshGoLiveChecklist`
- `PoshStatutoryCoverage`
- `PoshCtaBanner`

## Product copy source

Content reflects the live POSH Laravel app at `/posh/`:

- Dashboard, Employee Portal, Complaints, 9-Step IC Operate
- IC Setup (Section 4), Compliance (Section 19), Annual Report (Section 22)
- Policy admin, QR public intake, Audit log, Management portal
- ERP (Payroll-linked) vs Standalone deployment

## Design tokens (match Isarva HRMS product page)

- Font: **Outfit** (headings) + **Inter** (body)
- Primary: emerald `#059669` / teal `#0d9488`
- Category label: **HR & Workforce**
- Section padding: `py-20` / `py-24`
- Container: `max-w-7xl mx-auto px-6`

## Screenshots to provide

1. Dashboard / Command Center (hero)
2. Employee portal
3. Complaint filing form
4. IC Operate wizard (9 steps)
5. IC Setup with quota indicator
6. Compliance checklist
7. Annual report export
8. QR intake poster view
9. Audit log
10. Policy publish screen
