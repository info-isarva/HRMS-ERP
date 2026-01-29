# HRMS - Complete Documentation Index

## 📚 Documentation Overview

This HRMS system consists of **3 independent but integrated systems**:
1. **Workspace (HUB)** - Central authentication and SSO portal
2. **Payroll Module** - Salary processing and payment management
3. **Attendance Module** - Attendance tracking and leave management

All systems are documented with comprehensive technology stack, deployment guides, and configuration instructions.

---

## 📖 Available Documentation

### 1. **TECHNOLOGY_STACK.md** (Comprehensive)
**Purpose**: Complete technology stack for all 3 systems

**Contents**:
- ✅ PHP & Laravel framework versions
- ✅ Frontend libraries (jQuery, Bootstrap, Tailwind, etc.)
- ✅ Authentication mechanisms (Session, JWT, HMAC)
- ✅ Database configuration (MySQL, SQLite)
- ✅ Build tools (Vite, NPM, Composer)
- ✅ Email & notification systems
- ✅ Testing frameworks & tools
- ✅ Security implementation details
- ✅ System architecture diagrams
- ✅ Integration points between systems
- ✅ Environment variables & deployment info

**When to Use**: Need detailed technical specifications

---

### 2. **SYSTEM_COMPARISON.md** (Quick Reference)
**Purpose**: Quick comparison between the 3 systems

**Contents**:
- ✅ Side-by-side technology comparison
- ✅ Authentication flow diagrams
- ✅ Feature comparison matrix
- ✅ Environment configuration templates
- ✅ Development commands
- ✅ Deployment checklist
- ✅ System-specific features overview

**When to Use**: Need quick overview or comparing systems

---

### 3. **DEPLOYMENT_GUIDE.md** (Implementation)
**Purpose**: Step-by-step setup and deployment instructions

**Contents**:
- ✅ System architecture overview
- ✅ Prerequisites and requirements
- ✅ Step-by-step installation for each system
- ✅ Detailed .env configuration
- ✅ Security hardening guide
- ✅ SSL/TLS setup
- ✅ Database security configuration
- ✅ File permissions
- ✅ Troubleshooting common issues
- ✅ Performance optimization
- ✅ Backup & recovery procedures

**When to Use**: Setting up or deploying the system

---

## 🔍 Quick Facts

### Technology Stack at a Glance

| Component | Version | Details |
|-----------|---------|---------|
| **PHP** | 8.2+ | Server runtime |
| **Laravel** | 12.0 | Web framework (all 3 systems) |
| **Database** | MySQL 8.0+ | Primary DBMS |
| **Node.js** | 16+ | Build tool runtime |

### Frontend by System

| System | CSS Framework | JS Framework | Build Tool |
|--------|--------------|--------------|-----------|
| **Workspace (HUB)** | Tailwind 3.1.0 | Alpine.js 3.4.2 | Vite 6.2.4 |
| **Payroll** | Bootstrap 5.3.8 | jQuery 3.7.1 | Vite 5.0 |
| **Attendance** | Tailwind 3.4.0 | Minimal | Vite 6.2.4 |

### Authentication at a Glance

| System | Type | Details |
|--------|------|---------|
| **Workspace (HUB)** | Session-based | Laravel Breeze, generates JWT |
| **Payroll** | JWT + HMAC | Validates tokens from HUB |
| **Attendance** | JWT + HMAC | Validates tokens from HUB |

---

## 📂 Directory Structure

```
/home/hrmsdev.isarva.in/public_html/
│
├── TECHNOLOGY_STACK.md          ← Complete tech stack (926 lines)
├── SYSTEM_COMPARISON.md         ← System comparison & overview
├── DEPLOYMENT_GUIDE.md          ← Setup & deployment guide
│
├── Workspace (HUB)
│   ├── app/                     (Laravel app code)
│   ├── resources/               (Views, CSS, JS)
│   ├── config/                  (Configuration files)
│   ├── routes/                  (Web routes)
│   ├── .env                     (Environment variables)
│   ├── composer.json            (PHP dependencies)
│   ├── package.json             (NPM dependencies)
│   └── vite.config.js           (Vite configuration)
│
├── payroll/
│   ├── app/                     (Laravel app code)
│   ├── resources/               (Views, SCSS, JS)
│   ├── config/                  (Configuration files)
│   ├── routes/                  (Web & API routes)
│   ├── .env                     (Environment variables)
│   ├── composer.json            (PHP dependencies)
│   ├── package.json             (NPM dependencies)
│   └── vite.config.js           (Vite configuration)
│
├── attendance/
│   ├── app/                     (Laravel app code)
│   ├── resources/               (Views, CSS, JS)
│   ├── config/                  (Configuration files)
│   ├── routes/                  (Web & API routes)
│   ├── .env                     (Environment variables)
│   ├── composer.json            (PHP dependencies)
│   ├── package.json             (NPM dependencies)
│   └── vite.config.js           (Vite configuration)
│
└── backup/
    ├── payroll/                 (Backup copy)
    └── attendance/              (Backup copy)
```

---

## 🚀 Quick Start Commands

### Setup All Systems

```bash
# 1. Install Workspace (HUB)
cd /home/hrmsdev.isarva.in/public_html
composer install
npm install
php artisan key:generate
php artisan jwt:secret
npm run build
php artisan migrate

# 2. Install Payroll Module
cd payroll
composer install
npm install
php artisan key:generate
npm run build
php artisan migrate

# 3. Install Attendance Module
cd ../attendance
composer install
npm install
php artisan key:generate
npm run build
php artisan migrate

# 4. Start development servers
# Terminal 1: Workspace
cd /home/hrmsdev.isarva.in/public_html
php artisan serve --port=8000

# Terminal 2: Payroll
cd payroll
php artisan serve --port=8001

# Terminal 3: Attendance
cd attendance
php artisan serve --port=8002

# Terminal 4: Frontend builds (watch mode)
# In each directory: npm run dev
```

### Environment Setup Checklist

```bash
# 1. Copy .env files
cp .env.example .env                    # Workspace
cp payroll/.env.example payroll/.env    # Payroll
cp attendance/.env.example attendance/.env  # Attendance

# 2. Update .env with your settings
# - Database credentials
# - JWT secrets (same across all systems)
# - Mail configuration
# - Domain settings
# - API URLs

# 3. Generate keys
php artisan key:generate
php artisan jwt:secret

# 4. Run migrations
php artisan migrate
cd payroll && php artisan migrate && cd ..
cd attendance && php artisan migrate && cd ..

# 5. Build assets
npm run build
cd payroll && npm run build && cd ..
cd attendance && npm run build && cd ..
```

---

## 🔐 Security Checklist

- [ ] SSL/TLS certificates installed
- [ ] JWT_SECRET and JWT_HMAC_SECRET configured (same across all systems)
- [ ] Database user with minimal permissions created
- [ ] File permissions set correctly (755 dirs, 644 files)
- [ ] .env files not in version control
- [ ] SESSION_DOMAIN configured with leading dot (.hrms.example.com)
- [ ] CORS headers configured
- [ ] Email authentication enabled
- [ ] Database backups configured
- [ ] Error logging configured

---

## 📊 Key Metrics

### System Sizes
- **Total PHP Dependencies**: ~20+ packages (combined)
- **Total NPM Dependencies**: ~8-10 packages per system
- **Total Code Lines**: ~10,000+ (combined)
- **Database Tables**: ~30+ (combined across all systems)

### Performance Expectations
- **Page Load Time**: 300-500ms (with caching)
- **API Response Time**: 50-150ms
- **Database Query Time**: 1-50ms (with indexes)
- **Asset Load Time**: 100-200ms (gzipped)

---

## 🆘 Getting Help

### Common Issues

| Issue | Solution | Document |
|-------|----------|----------|
| JWT Token Invalid | Check JWT_SECRET | DEPLOYMENT_GUIDE.md |
| HMAC Signature Error | Verify secrets across systems | SYSTEM_COMPARISON.md |
| Permission Denied | Check user permissions | TECHNOLOGY_STACK.md |
| CSS/JS Not Loading | Run npm run build | DEPLOYMENT_GUIDE.md |
| Database Connection Error | Check .env settings | DEPLOYMENT_GUIDE.md |

### Document Navigation

**Looking for:**
- **Technology specifications** → TECHNOLOGY_STACK.md
- **System comparison** → SYSTEM_COMPARISON.md
- **Setup instructions** → DEPLOYMENT_GUIDE.md
- **Quick reference** → This file (README)

---

## 📝 Key Concepts

### JWT Flow
1. User logs in at Workspace (HUB)
2. Session created, JWT token generated with HMAC signature
3. Token passed to sub-modules via cookie
4. Sub-modules validate token and HMAC signature
5. User gains access to module features

### Permission System
1. Permissions stored in database
2. Routes mapped to permissions
3. Middleware checks user permissions
4. Based on user role (Admin, Manager, Employee)

### Email System
1. Workspace & Payroll: Standard Laravel Mail
2. Attendance: Dynamic email system with templates
3. SMTP configuration in .env
4. Queue system for async emails (optional)

---

## 🔗 Important URLs

### Development
```
Workspace (HUB):   http://localhost:8000
Payroll Module:    http://localhost:8001
Attendance Module: http://localhost:8002
```

### Production
```
Workspace (HUB):   https://hrms.example.com
Payroll Module:    https://payroll.hrms.example.com
Attendance Module: https://attendance.hrms.example.com
```

---

## 📦 Important Files

### Configuration Files
- `config/app.php` - Application configuration
- `config/auth.php` - Authentication configuration
- `config/jwt.php` - JWT configuration
- `config/database.php` - Database configuration
- `config/mail.php` - Email configuration
- `.env` - Environment variables

### Routes
- `routes/web.php` - Web routes
- `routes/api.php` - API routes (Payroll & Attendance)

### Middleware
- `app/Http/Middleware/CheckRoutePermission.php` - Permission checking
- `app/Http/Middleware/VerifyJwtHmac.php` - JWT HMAC verification
- `app/Http/Middleware/CheckPermission.php` - Additional permission checks

### Models
- `app/Models/User.php` - User model
- `app/Models/Permission.php` - Permission model (Payroll & Attendance)

---

## 🎓 Learning Resources

### Architecture
- System Architecture Diagram → TECHNOLOGY_STACK.md (Section 15)
- Request Flow Diagram → DEPLOYMENT_GUIDE.md

### Security
- Security Layers → TECHNOLOGY_STACK.md (Section 14)
- Security Configuration → DEPLOYMENT_GUIDE.md (Section 5)
- JWT Token Structure → TECHNOLOGY_STACK.md (Section 2)

### Development
- Frontend Stack Details → TECHNOLOGY_STACK.md (Section 5)
- API Endpoints → TECHNOLOGY_STACK.md (Section 17)

---

## 📅 Version Information

| Component | Version | Release Date |
|-----------|---------|--------------|
| **Laravel** | 12.0 | Nov 2024 |
| **PHP** | 8.2+ | Dec 2022 |
| **Bootstrap** | 5.3.8 | Latest |
| **Tailwind CSS** | 3.1.0 - 3.4.18 | Latest |
| **jQuery** | 3.7.1 | Latest |
| **Vite** | 5.0 - 6.2.4 | Latest |

---

## 📞 Support

For detailed information about:
1. **Technology Stack** → See `TECHNOLOGY_STACK.md`
2. **System Setup** → See `DEPLOYMENT_GUIDE.md`
3. **System Comparison** → See `SYSTEM_COMPARISON.md`

---

**Documentation Version**: 1.0
**Last Updated**: November 14, 2025
**Status**: Production Ready ✅

**3 Independent Systems, 1 Integrated HRMS Platform**
