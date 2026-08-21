# ISARVA POSH — Design System (for development)

Use these tokens when building the production app (React/Angular/HRMS module).

## Brand
- **Product name:** ISARVA POSH Compliance Platform
- **Tone:** Trustworthy, enterprise, human (HR/legal compliance)

## Colors
| Token | Hex | Use |
|-------|-----|-----|
| `--isarva-primary` | `#1e3a5f` | Sidebar, primary buttons, headings |
| `--isarva-primary-dark` | `#152a45` | Dark text, toast |
| `--isarva-primary-light` | `#2d5a8a` | Gradients, focus |
| `--isarva-accent` | `#d4622a` | CTA, active nav, step tabs |
| `--isarva-teal` | `#0d9488` | Success accents, audit |
| `--isarva-surface` | `#eef1f8` | Page background |
| `--isarva-card` | `#ffffff` | Cards |
| `--isarva-border` | `#dde3ef` | Borders |

## Typography
- **Font:** Plus Jakarta Sans (Google Fonts)
- **Page title:** 800 weight, -0.03em letter-spacing
- **Body:** 15px / 1.5 line-height

## Components
- **Sidebar:** Light blue-gray gradient `#f4f7fc`, 260px, active item = white pill + coral left border
- **Content:** Full width (no max-width column); case cards 2-column on screens ≥1200px
- **Cards:** 16px radius, subtle shadow `0 2px 8px rgba(30,58,95,0.06)`
- **Buttons:** Primary = navy gradient; Accent = coral gradient; Ghost = white border
- **Forms:** 11px padding inputs, focus ring blue 12% opacity
- **Stat cards:** Top 3px color bar + icon badge
- **Step tabs:** Pill style, active = coral gradient

## Spacing
- Content max-width: `1120px`
- Content padding: `28px 32px`
- Card padding: `24px`

## Files in prototype
- `css/isarva-posh.css` — all styles
- `index.html` — layout structure
- `js/isarva-posh-help.js` — inline help copy
