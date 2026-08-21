# POSH Phase 5 — Polish & product foundations

## Scope delivered

- **Locale switcher** (English / Hindi UI labels in header — full i18n strings can be expanded)
- **WhatsApp helpline** field in org settings (employee portal link)
- **QR intake** messaging on employee portal
- **Hub SSO** seeds employer duties + intake key on first login
- **Sidebar** complete navigation for all modules

## Deferred (your change list)

- Full 28-status lifecycle from `poshactresearch`
- Email/SMS notifications
- Payroll interim transfer/leave API hooks
- Standalone billing / multi-tenant SaaS
- Production vhost + MySQL (`hrms_poshdev`)
- Deep operate-case help panels from `isarva-posh-help.js`

Run migrations after deploy:

```bash
cd posh && php artisan migrate --force
php artisan posh:check-slas
```
