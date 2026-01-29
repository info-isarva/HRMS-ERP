# HRMS Technology Stack - Deployment & Configuration Guide

## Table of Contents
1. [System Architecture Overview](#system-architecture-overview)
2. [Technology Stack Summary](#technology-stack-summary)
3. [Installation & Setup](#installation--setup)
4. [Configuration Guide](#configuration-guide)
5. [Security Configuration](#security-configuration)
6. [Troubleshooting](#troubleshooting)

---

## System Architecture Overview

### Three Independent Systems with SSO Integration

```
┌─────────────────────────────────────────────────────────────┐
│              HRMS - Three System Setup                       │
└─────────────────────────────────────────────────────────────┘

System 1: WORKSPACE (HUB)           System 2: PAYROLL           System 3: ATTENDANCE
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Location: /                           Location: /payroll         Location: /attendance
Domain:   hrms.example.com            Domain:   payroll.         Domain:   attendance.
                                              hrms.example.com            hrms.example.com

Auth: Session-Based                  Auth: JWT + HMAC           Auth: JWT + HMAC
(Laravel Breeze)                     (tymon/jwt-auth)           (tymon/jwt-auth)

Tech Stack:                          Tech Stack:                Tech Stack:
- Laravel 12.0                       - Laravel 12.0             - Laravel 12.0
- PHP 8.2+                           - PHP 8.2+                 - PHP 8.2+
- Tailwind CSS 3.1.0                 - Bootstrap 5.3.8           - Tailwind CSS 3.4.0
- Alpine.js 3.4.2                    - jQuery 3.7.1             - Minimal JS
- Vite 6.2.4                         - DataTables 1.10+         - Vite 6.2.4
                                     - Select2 4.1.0-rc.0
                                     - DOMPDF, mPDF
                                     - Vite 5.0

Key Functions:                       Key Functions:             Key Functions:
✓ User Login/Logout                  ✓ Salary Processing        ✓ Attendance Tracking
✓ JWT Generation                     ✓ User Management          ✓ Leave Management
✓ SSO Hub                            ✓ PDF/Excel Reports        ✓ Permission Control
✓ Token Distribution                 ✓ Permission Mgmt          ✓ Email Notifications
✓ Session Management                 ✓ DataTable Display        ✓ Role-Based Access
```

---

## Technology Stack Summary

### Backend Stack (All Systems)
| Layer | Technology | Version | Purpose |
|-------|-----------|---------|---------|
| **Runtime** | PHP | 8.2+ | Server-side runtime |
| **Framework** | Laravel | 12.0 | Web application framework |
| **ORM** | Eloquent | Built-in | Database abstraction |
| **Authentication** | Laravel Breeze (HUB) | 2.3 | Session-based auth |
| **API Auth** | tymon/jwt-auth | 2.2 | JWT token authentication |
| **API Auth Alt** | Laravel Sanctum | 4.1 | Alternative API auth |

### Frontend Stack Comparison

#### Workspace (HUB)
```
├── CSS Framework: Tailwind CSS 3.1.0
├── JS Framework: Alpine.js 3.4.2
├── HTTP Client: Axios 1.8.2
├── Build Tool: Vite 6.2.4
├── Styling Approach: Utility-first (Modern)
└── UI Components: Minimal, Modern
```

#### Payroll Module
```
├── CSS Framework: Bootstrap 5.3.8 + Tailwind 3.4.18
├── JS Framework: jQuery 3.7.1
├── HTTP Client: Axios 1.6.4 + jQuery AJAX
├── Build Tool: Vite 5.0
├── Styling Approach: Component-based (Traditional)
├── UI Components:
│   ├── DataTables 1.10+ (Advanced tables)
│   ├── Select2 4.1.0-rc.0 (Enhanced dropdowns)
│   ├── Bootstrap Modals (Dialogs)
│   ├── jQuery Validation (Form validation)
│   ├── Moment.js (Date/time)
│   └── Bootstrap DateTimePicker
└── Export Libraries:
    ├── DOMPDF 3.1 (PDF generation)
    ├── mPDF 8.2 (Advanced PDF)
    └── Maatwebsite Excel 3.1
```

#### Attendance Module
```
├── CSS Framework: Tailwind CSS 3.4.0
├── CSS Plugins:
│   ├── Tailwind Forms 0.5.10 (Form styling)
│   └── Tailwind Typography 0.5.16 (Text styling)
├── JS Framework: None (Minimal JS)
├── HTTP Client: Axios 1.8.2
├── Build Tool: Vite 6.2.4
├── Styling Approach: Utility-first (Modern)
└── UI Components: Tailwind native + forms plugin
```

### Database Stack (All Systems)
| Component | Technology | Version | Purpose |
|-----------|-----------|---------|---------|
| **DBMS** | MySQL | 8.0+ | Primary database |
| **Alt DBMS** | SQLite | Latest | Development/Testing |
| **Connection Pool** | PDO | Native | Database connections |
| **Charset** | UTF-8 MB4 | Latest | Unicode support |

---

## Installation & Setup

### Prerequisites
```bash
# System Requirements
PHP: 8.2 or higher
MySQL: 8.0 or higher (MariaDB 10.5+)
Node.js: 16 or higher (for building assets)
Composer: Latest version
NPM: Latest version
Git: Latest version
OpenSSL: Latest version (for JWT secrets)
```

### Step 1: Clone or Setup Repository

```bash
# Navigate to project root
cd /home/hrmsdev.isarva.in/public_html

# For existing installation, ensure all systems are present:
ls -la
# Expected:
# workspace (.)
# /payroll
# /attendance
# /backup (backup copies)
```

### Step 2: Install Workspace (HUB)

```bash
# Install PHP dependencies
composer install

# Create .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Generate JWT secret
php artisan jwt:secret

# Install NPM dependencies
npm install

# Build frontend assets
npm run build

# Run database migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Step 3: Install Payroll Module

```bash
# Navigate to payroll directory
cd /home/hrmsdev.isarva.in/public_html/payroll

# Install PHP dependencies
composer install

# Create .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Copy JWT secret from workspace (or generate separately)
# Edit .env and set JWT_SECRET and JWT_HMAC_SECRET

# Install NPM dependencies
npm install

# Build frontend assets
npm run build

# Run database migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

### Step 4: Install Attendance Module

```bash
# Navigate to attendance directory
cd /home/hrmsdev.isarva.in/public_html/attendance

# Install PHP dependencies
composer install

# Create .env file
cp .env.example .env

# Generate app key
php artisan key:generate

# Copy JWT secret from workspace
# Edit .env and set JWT_SECRET and JWT_HMAC_SECRET

# Install NPM dependencies
npm install

# Build frontend assets
npm run build

# Run database migrations
php artisan migrate

# Seed database (optional)
php artisan db:seed
```

---

## Configuration Guide

### Workspace (.env) Configuration

```env
# Application Settings
APP_NAME=HRMS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hrms.example.com
APP_TIMEZONE=Asia/Kolkata

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms_workspace
DB_USERNAME=hrms_user
DB_PASSWORD=strong_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.hrms.example.com
SESSION_COOKIE=XSRF-TOKEN
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=Lax

# JWT Configuration
JWT_SECRET=your_generated_jwt_secret_here
JWT_HMAC_SECRET=your_hmac_secret_here
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@hrms.example.com
MAIL_FROM_NAME="${APP_NAME}"

# Sub-module URLs
PAYROLL_URL=https://payroll.hrms.example.com
ATTENDANCE_URL=https://attendance.hrms.example.com

# Queue Configuration
QUEUE_CONNECTION=sync

# Redis Configuration (Optional)
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Monitoring & Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
LOG_DEPRECATIONS_CHANNEL=null

# Sentry Integration (Optional)
SENTRY_LARAVEL_DSN=
SENTRY_TRACES_SAMPLE_RATE=1.0
```

### Payroll Module (.env) Configuration

```env
# Application Settings
APP_NAME=Payroll
APP_ENV=production
APP_DEBUG=false
APP_URL=https://payroll.hrms.example.com
APP_TIMEZONE=Asia/Kolkata

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms_payroll
DB_USERNAME=hrms_user
DB_PASSWORD=strong_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.hrms.example.com
SESSION_COOKIE=XSRF-TOKEN
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# JWT Configuration (MUST MATCH HUB)
JWT_SECRET=same_secret_as_hub_here
JWT_HMAC_SECRET=same_hmac_secret_as_hub_here
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=payroll@hrms.example.com
MAIL_FROM_NAME="${APP_NAME}"

# API Configuration
API_GUARD=sanctum
API_PREFIX=/api

# Queue & Jobs
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

### Attendance Module (.env) Configuration

```env
# Application Settings
APP_NAME=Attendance
APP_ENV=production
APP_DEBUG=false
APP_URL=https://attendance.hrms.example.com
APP_TIMEZONE=Asia/Kolkata

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hrms_attendance
DB_USERNAME=hrms_user
DB_PASSWORD=strong_password_here
DB_CHARSET=utf8mb4
DB_COLLATION=utf8mb4_unicode_ci

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_DOMAIN=.hrms.example.com
SESSION_COOKIE=XSRF-TOKEN
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true

# JWT Configuration (MUST MATCH HUB)
JWT_SECRET=same_secret_as_hub_here
JWT_HMAC_SECRET=same_hmac_secret_as_hub_here
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_BLACKLIST_ENABLED=true

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=attendance@hrms.example.com
MAIL_FROM_NAME="${APP_NAME}"

# Dynamic Email System
DYNAMIC_EMAIL_ENABLED=true
EMAIL_NOTIFICATION_QUEUE=sync

# Queue & Jobs
QUEUE_CONNECTION=sync

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error
```

---

## Security Configuration

### 1. SSL/TLS Configuration

```nginx
# Nginx Configuration Example
server {
    listen 443 ssl http2;
    server_name hrms.example.com;

    ssl_certificate /etc/ssl/certs/hrms.example.com.crt;
    ssl_certificate_key /etc/ssl/private/hrms.example.com.key;
    
    # SSL Configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;
    
    # Redirect HTTP to HTTPS
    error_page 497 https://$server_name:$server_port$request_uri;
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name hrms.example.com;
    return 301 https://$server_name$request_uri;
}
```

### 2. PHP Security Headers

Add to web server configuration:
```
Strict-Transport-Security: max-age=31536000; includeSubDomains
X-Content-Type-Options: nosniff
X-Frame-Options: SAMEORIGIN
X-XSS-Protection: 1; mode=block
Referrer-Policy: strict-origin-when-cross-origin
Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' cdn.example.com
```

### 3. JWT Security

```php
// config/jwt.php - Key Settings
'secret' => env('JWT_SECRET'),
'algo' => Tymon\JWTAuth\Providers\JWT\Provider::ALGO_HS256,
'ttl' => env('JWT_TTL', 60),
'refresh_ttl' => env('JWT_REFRESH_TTL', 20160),
'blacklist_enabled' => env('JWT_BLACKLIST_ENABLED', true),
'required_claims' => ['iss', 'iat', 'exp', 'nbf', 'sub', 'jti'],
```

### 4. Database Security

```bash
# Create dedicated database user with minimal permissions
CREATE USER 'hrms_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP ON hrms_workspace.* TO 'hrms_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP ON hrms_payroll.* TO 'hrms_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, DROP ON hrms_attendance.* TO 'hrms_user'@'localhost';
FLUSH PRIVILEGES;
```

### 5. File Permissions

```bash
# Set proper permissions for all systems
chmod -R 755 /home/hrmsdev.isarva.in/public_html
chmod -R 644 /home/hrmsdev.isarva.in/public_html/*.php
chmod -R 755 /home/hrmsdev.isarva.in/public_html/storage
chmod -R 755 /home/hrmsdev.isarva.in/public_html/bootstrap/cache

# Set write permissions for storage directories
chmod -R 775 /home/hrmsdev.isarva.in/public_html/storage/logs
chmod -R 775 /home/hrmsdev.isarva.in/public_html/storage/framework

# Same for all three systems
chmod -R 755 /home/hrmsdev.isarva.in/public_html/payroll
chmod -R 755 /home/hrmsdev.isarva.in/public_html/attendance
```

---

## Troubleshooting

### Common Issues and Solutions

#### 1. JWT Token Invalid
**Error**: "Token could not be parsed from the request"

**Solution**:
```bash
# Verify JWT_SECRET and JWT_HMAC_SECRET are set correctly
php artisan config:cache
php artisan config:clear

# Regenerate JWT secret if needed
php artisan jwt:secret
```

#### 2. HMAC Signature Mismatch
**Error**: "Invalid token signature"

**Solution**:
- Ensure `JWT_HMAC_SECRET` is identical across all three systems
- Check that user ID and email in database match
- Verify the middleware is receiving correct token

#### 3. Session Not Persisting Across Domains
**Error**: Token lost when navigating between modules

**Solution**:
```env
# Verify these settings in all .env files:
SESSION_DOMAIN=.hrms.example.com  # Note the leading dot
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
```

#### 4. CORS Errors
**Error**: "Access to XMLHttpRequest blocked by CORS policy"

**Solution**:
```php
// Add CORS middleware in app/Http/Middleware/HandleCors.php
$allowed_origins = [
    'https://hrms.example.com',
    'https://payroll.hrms.example.com',
    'https://attendance.hrms.example.com'
];
```

#### 5. Permission Denied on Routes
**Error**: "403 Unauthorized - You are not authorized to access this route"

**Solution**:
- Check user permissions in database
- Verify permission middleware is configured
- Check permission sync between systems
- Review database permission entries

#### 6. Database Connection Failed
**Error**: "SQLSTATE[HY000]: General error: 2054 The server requested authentication method unknown"

**Solution**:
```bash
# Update MySQL password authentication
# For MySQL 8.0+
ALTER USER 'hrms_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'password';
FLUSH PRIVILEGES;

# Or check .env database connection settings
php artisan config:clear
```

#### 7. Vite Build Issues
**Error**: "resources/js/app.js not found" or asset compilation fails

**Solution**:
```bash
# For each system
rm -rf node_modules package-lock.json
npm install
npm run build

# Verify Vite config exists
ls vite.config.js  # Should exist in each system
```

---

## Performance Optimization

### 1. Enable Query Caching
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),

// .env
CACHE_DRIVER=redis
```

### 2. Asset Optimization
```bash
# Production build
npm run build

# Ensure assets are minified
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 3. Database Indexing
```sql
-- Add indexes for common queries
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_permissions_route ON permissions(route_name);
```

### 4. Enable Output Buffering
```php
// config/session.php
'secure_cookies' => true,
'http_only' => true,
```

---

## Backup & Recovery

### Database Backup

```bash
# Backup all databases
mysqldump -u hrms_user -p hrms_workspace > backup_workspace.sql
mysqldump -u hrms_user -p hrms_payroll > backup_payroll.sql
mysqldump -u hrms_user -p hrms_attendance > backup_attendance.sql

# Restore database
mysql -u hrms_user -p hrms_workspace < backup_workspace.sql
```

### File Backup

```bash
# Backup all system files
tar -czf hrms_backup_$(date +%Y%m%d).tar.gz /home/hrmsdev.isarva.in/public_html

# Restore from backup
tar -xzf hrms_backup_20250114.tar.gz
```

---

**Last Updated**: November 14, 2025
**Version**: 1.0
**Status**: Production Ready
