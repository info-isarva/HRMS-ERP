# HRMS Technology Stack - Quick Reference Card

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────┐
│          USER / WEB BROWSER CLIENT              │
└──────────────────┬──────────────────────────────┘
                   │ HTTPS
       ┌───────────┴───────────┐
       │                       │
       ▼ (8000)                ▼ (8001/8002)
   [WORKSPACE HUB]         [SUB-MODULES]
   Session-Based           JWT-Based Auth
   ├─ Laravel 12           ├─ Payroll
   ├─ Tailwind CSS         ├─ Attendance
   └─ Breeze Auth          └─ HMAC Verify
       │
       ├─→ Generates JWT + HMAC
       │
       ▼
   [MySQL Database]
   ├─ hrms_workspace
   ├─ hrms_payroll
   └─ hrms_attendance
```

---

## 📊 Technology Matrix

### Backend Stack
```
┌──────────────┬──────────┬──────────┬───────────┐
│  Component   │ HUB      │ Payroll  │ Attendance│
├──────────────┼──────────┼──────────┼───────────┤
│ Laravel      │ 12.0     │ 12.0     │ 12.0      │
│ PHP          │ 8.2+     │ 8.2+     │ 8.2+      │
│ Auth Type    │ Session  │ JWT+HMAC │ JWT+HMAC  │
│ ORM          │ Eloquent │ Eloquent │ Eloquent  │
└──────────────┴──────────┴──────────┴───────────┘
```

### Frontend Stack
```
┌─────────────┬──────────────┬──────────────┬──────────────┐
│  Component  │ HUB          │ Payroll      │ Attendance   │
├─────────────┼──────────────┼──────────────┼──────────────┤
│ CSS         │ Tailwind 3.1 │ Bootstrap 5  │ Tailwind 3.4 │
│ JavaScript  │ Alpine.js    │ jQuery 3.7.1 │ Minimal      │
│ Build Tool  │ Vite 6.2.4   │ Vite 5.0     │ Vite 6.2.4   │
│ Tables      │ ❌           │ DataTables   │ ❌           │
│ Dropdowns   │ ❌           │ Select2      │ ❌           │
│ UI Pattern  │ Modern       │ Traditional  │ Modern       │
└─────────────┴──────────────┴──────────────┴──────────────┘
```

---

## 🔐 Authentication Flow

### 1️⃣ Login Phase
```
User @ hrms.example.com
    ↓
Login Form (Username/Password)
    ↓
AuthenticatedSessionController::store()
    ↓
✅ Session Created (web guard)
✅ JWT Generated (5-min expiry)
✅ HMAC Signature Added
✅ Token Stored in Cookie
    ↓
Redirect to Dashboard
```

### 2️⃣ Sub-Module Access
```
User @ payroll.hrms.example.com
    ↓
Token Retrieved from Cookie
    ↓
CheckRoutePermission Middleware
    ├─ Is user authenticated?
    └─ Does user have permission?
    ↓
VerifyJwtHmac Middleware
    ├─ Extract JWT token
    ├─ Compute HMAC signature
    ├─ Compare with token HMAC
    └─ Valid? ✅ / Invalid? ❌ 401
    ↓
Access Granted / Denied
```

### 3️⃣ Logout Phase
```
User Clicks Logout @ hrms.example.com
    ↓
destroy() Method Triggered
    ↓
✅ Session Destroyed
✅ Token Blacklisted
    ↓
SSO Passive Logout URLs:
    ├─ payroll.hrms.example.com/sso-passive-logout
    └─ attendance.hrms.example.com/sso-passive-logout
    ↓
✅ All Cookies Cleared
✅ Redirect to Login Page
```

---

## 🛠️ Technology Stack by Function

### Authentication & Security
```
┌─ Session Management
│  └─ Server-side sessions (HUB)
│
├─ JWT Tokens
│  ├─ Algorithm: HS256 (HMAC-SHA256)
│  ├─ TTL: 60 minutes (configurable)
│  ├─ Refresh: 20160 minutes (14 days)
│  └─ Expiry: Set at 5 minutes for sensitive ops
│
├─ HMAC Signature
│  ├─ Hash Function: SHA-256
│  ├─ Input: user_id + email + secret
│  └─ Verification: Timing-safe comparison (hash_equals)
│
└─ Encryption
   ├─ Cookies: Encrypted by default
   ├─ Passwords: bcrypt hashing
   └─ Database: UTF-8mb4 encoding
```

### Frontend Rendering
```
┌─ HUB (Workspace)
│  ├─ Alpine.js (Reactive components)
│  ├─ Tailwind CSS (Utility classes)
│  ├─ Axios (AJAX requests)
│  └─ Vite (Build & dev server)
│
├─ Payroll Module
│  ├─ jQuery (DOM manipulation)
│  ├─ DataTables (Advanced tables)
│  ├─ Select2 (Custom dropdowns)
│  ├─ Bootstrap (Component framework)
│  ├─ Bootstrap Modals (Dialogs)
│  ├─ Moment.js (Date handling)
│  └─ Vite (Build tool)
│
└─ Attendance Module
   ├─ Tailwind CSS (Styling)
   ├─ Axios (API requests)
   └─ Vite (Build tool)
```

### Data Processing
```
┌─ Database
│  └─ MySQL 8.0+ (Primary)
│     ├─ Charset: UTF-8mb4
│     ├─ Collation: utf8mb4_unicode_ci
│     └─ Engines: InnoDB
│
├─ ORM
│  └─ Eloquent
│     ├─ Query builder
│     ├─ Relationships
│     ├─ Eager loading
│     └─ SQL parameterization
│
├─ Report Generation (Payroll)
│  ├─ DOMPDF (HTML to PDF)
│  ├─ mPDF (Advanced PDF)
│  └─ PHPSpreadsheet (Excel generation)
│
└─ Email
   ├─ SMTP (Primary)
   ├─ Laravel Mail (Wrapper)
   └─ Dynamic templates (Attendance)
```

---

## 📦 Dependency Summary

### All Systems
```
Runtime:
  ✓ PHP 8.2+
  ✓ MySQL 8.0+
  ✓ Node.js 16+
  ✓ Composer (PHP package manager)
  ✓ NPM (JavaScript package manager)

PHP Packages:
  ✓ laravel/framework (12.0)
  ✓ tymon/jwt-auth (2.2)
  ✓ laravel/sanctum (4.1) - Payroll only
  ✓ laravel/ui (4.5) - Payroll only

JavaScript Packages:
  ✓ axios (HTTP client)
  ✓ tailwindcss (CSS framework)
  ✓ vite (Build tool)
  ✓ autoprefixer (CSS processing)
  ✓ postcss (CSS transformation)
```

### Workspace (HUB) Specific
```
PHP:
  ✓ laravel/breeze (2.3) - Session auth scaffolding
  ✓ laravel/tinker (2.10.1)

JavaScript:
  ✓ alpinejs (3.4.2) - Reactive framework
  ✓ @tailwindcss/forms (0.5.2)
```

### Payroll Specific
```
PHP:
  ✓ barryvdh/laravel-dompdf (3.1)
  ✓ mpdf/mpdf (8.2)
  ✓ maatwebsite/excel (3.1)
  ✓ phpoffice/phpspreadsheet (1.29)

JavaScript:
  ✓ jquery (3.7.1)
  ✓ bootstrap (5.3.8)
  ✓ select2 (4.1.0-rc.0)
  ✓ datatables.net (1.10+)
  ✓ moment.js
  ✓ bootstrap-datetimepicker
```

### Attendance Specific
```
CSS:
  ✓ @tailwindcss/forms (0.5.10)
  ✓ @tailwindcss/typography (0.5.16)

Email:
  ✓ php-flasher/flasher-laravel (2.1)
```

---

## 🔑 Configuration Keys

### Essential .env Variables

```ini
# Authentication & JWT
JWT_SECRET=xxxxxxxxxxxxx
JWT_HMAC_SECRET=xxxxxxxxxxxxx
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true

# Session
SESSION_DRIVER=file
SESSION_DOMAIN=.hrms.example.com
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hrms_workspace
DB_USERNAME=hrms_user
DB_PASSWORD=xxxxxxxxxxxxx

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=xxx@example.com
MAIL_PASSWORD=xxxxxxxxxxxxx

# Module URLs
PAYROLL_URL=https://payroll.hrms.example.com
ATTENDANCE_URL=https://attendance.hrms.example.com
```

---

## 📈 Performance Metrics

### Expected Performance
```
Page Load Time:         300-500ms
API Response Time:      50-150ms
Database Query Time:    1-50ms (with indexes)
Asset Load Time:        100-200ms (gzipped)
JWT Token Size:         ~500 bytes
HMAC Signature Size:    64 bytes (SHA-256)
```

### Optimization Techniques
```
✓ Asset minification (Vite)
✓ Code splitting
✓ Database indexing
✓ Query eager loading
✓ Session caching
✓ Asset versioning
✓ Gzip compression
✓ Browser caching
```

---

## 🚨 Security Layers

### Layer 1: Transport
```
✅ HTTPS/SSL (TLS 1.2+)
✅ Secure cookies (HttpOnly, Secure, SameSite)
✅ CORS headers
✅ X-Frame-Options header
✅ X-Content-Type-Options header
```

### Layer 2: Application
```
✅ CSRF token validation
✅ Input validation
✅ SQL parameterization
✅ XSS protection (Blade escaping)
✅ Rate limiting
```

### Layer 3: Authentication
```
✅ Password hashing (bcrypt)
✅ Session signing
✅ JWT expiration
✅ HMAC signature verification
✅ Token blacklisting
```

### Layer 4: Authorization
```
✅ Role-based access control (RBAC)
✅ Permission checking middleware
✅ Route-based permission mapping
✅ User privilege validation
```

---

## 🔧 Common Commands

### Setup Commands
```bash
# Generate keys
php artisan key:generate
php artisan jwt:secret

# Database
php artisan migrate
php artisan migrate:refresh --seed
php artisan db:seed

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Assets
npm install
npm run dev
npm run build
```

### Development Commands
```bash
# Start servers
php artisan serve --port=8000
npm run dev

# Tinker REPL
php artisan tinker

# Run tests
php artisan test
php artisan test --filter TestName

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Deployment Commands
```bash
# Production build
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Backup
mysqldump -u user -p database > backup.sql
tar -czf backup.tar.gz /path/to/app
```

---

## 📋 Quick Troubleshooting

| Problem | Cause | Solution |
|---------|-------|----------|
| JWT Invalid | Wrong JWT_SECRET | Check .env, regenerate if needed |
| HMAC Mismatch | Secrets differ across systems | Ensure JWT_HMAC_SECRET same everywhere |
| Permission Denied | Missing permission | Check database permission entries |
| CORS Error | Blocked by browser | Configure CORS middleware |
| Session Lost | Domain mismatch | Verify SESSION_DOMAIN has leading dot |
| CSS Not Applied | Assets not built | Run `npm run build` |
| Database Error | Connection failed | Check DB credentials in .env |
| Email Not Sent | SMTP config | Verify MAIL_* variables in .env |

---

## 📚 Document Map

```
README_TECH_STACK.md (This file)
    └─→ Quick reference & overview

TECHNOLOGY_STACK.md (Detailed)
    └─→ Complete specifications

SYSTEM_COMPARISON.md (Comparison)
    └─→ Side-by-side comparison

DEPLOYMENT_GUIDE.md (Setup)
    └─→ Installation & troubleshooting
```

---

## 🎯 Next Steps

1. **Review** → Read `TECHNOLOGY_STACK.md` for full details
2. **Compare** → Check `SYSTEM_COMPARISON.md` for differences
3. **Deploy** → Follow `DEPLOYMENT_GUIDE.md` for setup
4. **Reference** → Use this card for quick lookups

---

**Version**: 1.0
**Last Updated**: November 14, 2025
**Status**: ✅ Production Ready

**3 Systems | 1 HRMS Platform | Complete Technology Documentation**
