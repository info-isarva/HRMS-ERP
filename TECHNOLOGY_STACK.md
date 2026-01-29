# HRMS System - Complete Technology Stack (All 3 Systems)

## System Overview
This is a comprehensive Human Resource Management System (HRMS) consisting of three independent but integrated systems:
1. **Workspace (HUB)** - Central authentication and SSO portal
2. **Attendance Module** - Employee attendance tracking and leave management
3. **Payroll Module** - Salary processing and payment management

All three systems communicate via JWT tokens with HMAC verification for secure SSO integration.

---

## 1. BACKEND - PHP & LARAVEL FRAMEWORK

### Core Framework & PHP

#### All Three Systems
| Component | Version | Details |
|-----------|---------|---------|
| **PHP** | 8.2+ | Required minimum version for all applications |
| **Laravel** | 12.0 | Latest stable version for Workspace (HUB), Payroll, and Attendance |

#### Workspace (HUB) - Main Portal
| Component | Version | Details |
|-----------|---------|---------|
| **Laravel Framework** | 12.0 | Main application framework |
| **Laravel Breeze** | 2.3 | Session-based authentication scaffolding |
| **Laravel Tinker** | 2.10.1 | Interactive REPL shell |

#### Payroll Module - `/payroll`
| Component | Version | Details |
|-----------|---------|---------|
| **Laravel Framework** | 12.0 | Independent application |
| **Laravel UI** | 4.5 | UI scaffolding components |
| **Laravel Sanctum** | 4.1 | API authentication (optional) |
| **Laravel Tinker** | 2.9 | Interactive shell |

#### Attendance Module - `/attendance`
| Component | Version | Details |
|-----------|---------|---------|
| **Laravel Framework** | 12.0 | Independent application |
| **Laravel Tinker** | Latest | Interactive shell |

### Package Managers & Build Tools
| Tool | Version | Purpose |
|------|---------|---------|
| **Composer** | Latest | PHP package dependency manager |
| **NPM** | Latest | Node package manager for JavaScript dependencies |

---

## 2. AUTHENTICATION & SECURITY

### Authentication System

#### Primary Authentication (HUB Application)
- **Guard Type**: `web` (Session-based)
- **Provider**: Eloquent ORM
- **User Model**: `App\Models\User`
- **Login Flow**: Traditional session-based authentication with Laravel Breeze

#### JWT (JSON Web Token) Authentication
| Feature | Details |
|---------|---------|
| **Library** | `tymon/jwt-auth` v2.2 (tymon/laravel-jwt-auth) |
| **Algorithm** | HS256 (HMAC SHA-256) - Default symmetric algorithm |
| **Supported Algorithms** | HS256, HS384, HS512, RS256, RS384, RS512, ES256, ES384, ES512 |
| **TTL (Time to Live)** | 60 minutes (default, configurable) |
| **Refresh TTL** | 20160 minutes (14 days) - Token refresh window |
| **Token Blacklist** | Enabled - Tokens are invalidated upon logout |
| **Blacklist Grace Period** | 0 seconds - No grace period for token invalidation |

#### JWT Token Structure
```javascript
{
  "exp": 1732456789,           // Expiration timestamp
  "jti": "uuid-string",         // JWT unique ID
  "iss": "...",                 // Issuer claim
  "iat": 1732452789,            // Issued at timestamp
  "nbf": 1732452789,            // Not before timestamp
  "sub": "user-id",             // Subject (user ID)
  "user": {                     // Custom user claims
    "id": "1",
    "email": "user@example.com",
    "hmac": "hash_hmac_signature"
  }
}
```

#### JWT Security Features
1. **Custom Claims**:
   - User ID, email, and HMAC signature embedded in token
   - Expiration set to 5 minutes for sensitive operations

2. **HMAC Verification** (VerifyJwtHmac Middleware):
   - SHA-256 HMAC signature validation
   - Computed using: `hash_hmac('sha256', user_id + email, JWT_HMAC_SECRET)`
   - Prevents token tampering and ensures authenticity
   - Uses `hash_equals()` for timing-safe comparison

3. **Required Claims**:
   - `iss` (Issuer)
   - `iat` (Issued at)
   - `exp` (Expiration)
   - `nbf` (Not before)
   - `sub` (Subject)
   - `jti` (JWT ID - unique identifier)

#### Session Management
- **Session Guard**: Web (Session-based)
- **Storage**: Server-side session management
- **Cookie Encryption**: Enabled by default
- **Tokens Used**:
  - `payroll_token` - JWT for Payroll module API access
  - `attendance_token` - JWT for Attendance module API access
- **Cross-Domain Support**: Cookies set with configurable domain
- **Logout**: Multi-module logout with SSO passive logout URLs

#### API Authentication (Payroll Module)
- **Guard**: `api` (configured but can use JWT or Sanctum)
- **Provider**: Eloquent ORM
- **Sanctum Integration**: Available for stateless API authentication

---

## 3. AUTHORIZATION & PERMISSION SYSTEM

### Role-Based Access Control (RBAC)

#### Permission Model
- **Database Model**: `Permission` (Eloquent Model)
- **Permission Storage**: Route-based permission mapping
- **Permission JSON**: `permissions_json` column stores user permissions

#### Permission Levels
1. **Super Admin**: Unrestricted access to all routes
2. **Admin**: Full access to admin/management routes
3. **User**: Limited access based on assigned permissions

#### Permission Checking Middleware

##### CheckRoutePermission Middleware
- **Location**: Payroll & Attendance modules
- **Purpose**: Validates user permissions for specific routes
- **Implementation**:
  ```php
  // Route-based permission lookup
  Permission::where('route_name', $currentRoute)
           ->orWhereJsonContains('route_names', $currentRoute)
  
  // Permission checking
  $user->hasPermission($permission)
  $user->hasUnrestrictedAccess()
  $user->hasAdminAccess()
  ```

##### VerifyJwtHmac Middleware
- **Location**: Payroll & Attendance modules (Middleware/VerifyJwtHmac.php)
- **Purpose**: Validates JWT token HMAC signature for API requests
- **Security Check**: Token must include valid HMAC signature
- **Response**: 401 Unauthorized if signature invalid

#### Protected Route Categories
1. **Admin/Management Routes**:
   - Permission management
   - Public holiday management
   - Leave types management
   - Holiday department configurations

2. **User Routes** (Restricted by permission):
   - Leaves management
   - Public holiday applications
   - Profile management
   - Dashboard access

3. **Routes Without Permission Checks**:
   - Basic user routes requiring only authentication
   - Allow access to authenticated users by default

---

## 4. DATABASE LAYER

### Database Configuration

#### Supported Databases
| Database | Driver | Default Port | Features |
|----------|--------|--------------|----------|
| **SQLite** | sqlite | N/A | Default, used for development (database.sqlite) |
| **MySQL** | mysql | 3306 | Primary production database |
| **MariaDB** | mariadb | 3306 | MySQL compatible alternative |
| **PostgreSQL** | pgsql | 5432 | Advanced relational database |
| **SQL Server** | sqlsrv | 1433 | Enterprise database option |

#### MySQL/MariaDB Configuration (Production)
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'strict' => true,
    'engine' => null
]
```

#### ORM
| Component | Details |
|-----------|---------|
| **ORM** | Eloquent (Laravel's built-in) |
| **Relationships** | One-to-Many, Many-to-Many, Has-One, etc. |
| **Query Builder** | Fluent API for building SQL queries |
| **Migrations** | Version control for database schema |

#### Database Features
- **Foreign Key Constraints**: Enabled (`DB_FOREIGN_KEYS=true`)
- **Charset**: UTF-8 Multi-byte (utf8mb4)
- **Collation**: Case-insensitive Unicode (utf8mb4_unicode_ci)
- **Connection Pooling**: Available through environment configuration
- **SSL Support**: MySQL SSL CA certificate support for secure connections

---

## 5. FRONTEND - JAVASCRIPT & CSS

### JavaScript Libraries & Frameworks

### Frontend JavaScript & Libraries

#### Workspace (HUB)
| Library | Version | Purpose |
|---------|---------|---------|
| **Axios** | 1.8.2 | HTTP client for AJAX |
| **Alpine.js** | 3.4.2 | Lightweight reactive framework |

#### Payroll Module
| Library | Version | Purpose |
|---------|---------|---------|
| **jQuery** | 3.7.1 | DOM manipulation and AJAX |
| **Axios** | 1.6.4 | Promise-based HTTP client |
| **Bootstrap** | 5.3.8 | Component library |
| **Popper.js** | 2.11.6+ | Positioning engine |
| **DataTables** | 1.10+ | Advanced table processing |
| **Select2** | 4.1.0-rc.0 | Custom dropdown selection |
| **jQuery Validation** | Latest | Form validation |
| **Multiselect** | Latest | Multi-select widget |
| **Moment.js** | Latest | Date/time handling |
| **Bootstrap DateTimePicker** | Latest | DateTime selection |
| **SlimScroll** | Latest | Custom scrollbars |

#### Attendance Module
| Library | Version | Purpose |
|---------|---------|---------|
| **Axios** | 1.8.2 | HTTP client for AJAX |

**Note**: Attendance uses modern Tailwind CSS approach while Payroll uses traditional jQuery plugins with Bootstrap.

#### AJAX & HTTP
- **HTTP Client**: Axios (handles CORS, interceptors, request/response handling)
- **Requests**: POST, GET, PUT, DELETE with CSRF token
- **Response Format**: JSON (typically with data, error, and status fields)
- **Error Handling**: Try-catch in promises, error callbacks

### CSS & Styling

#### Workspace (HUB)
| Framework | Version | Purpose |
|-----------|---------|---------|
| **Tailwind CSS** | 3.1.0 | Utility-first CSS framework |
| **Bootstrap** | 5.x (implicit) | Optional component framework |
| **PostCSS** | 8.4.31 | CSS transformation |
| **Autoprefixer** | 10.4.2 | Vendor prefix automation |

#### Payroll Module
| Framework | Version | Purpose |
|-----------|---------|---------|
| **Bootstrap** | 5.3.8 | Primary CSS framework for UI |
| **Tailwind CSS** | 3.4.18 | Additional utility classes |
| **SASS/SCSS** | 1.56.1+ | Advanced CSS preprocessing |
| **PostCSS** | 8.5.6 | CSS processing and optimization |
| **Autoprefixer** | 10.4.21 | Cross-browser compatibility |

#### Attendance Module
| Framework | Version | Purpose |
|-----------|---------|---------|
| **Tailwind CSS** | 3.4.0 | Primary CSS framework |
| **@tailwindcss/forms** | 0.5.10 | Form styling plugin |
| **@tailwindcss/typography** | 0.5.16 | Typography plugin |
| **PostCSS** | 8.5.6 | CSS processing |
| **Autoprefixer** | 10.4.21 | Vendor prefix automation |

### Build Tools

#### Workspace (HUB)
| Tool | Version | Details |
|------|---------|---------|
| **Vite** | 6.2.4 | Modern build tool |
| **laravel-vite-plugin** | 1.2.0 | Laravel integration |
| **Entry Points** | N/A | resources/css/app.css, resources/js/app.js |

#### Payroll Module
| Tool | Version | Details |
|------|---------|---------|
| **Vite** | 5.0 | Build tool |
| **laravel-vite-plugin** | 1.0 | Laravel integration |
| **Entry Points** | N/A | resources/sass/app.scss, resources/js/app.js |

#### Attendance Module
| Tool | Version | Details |
|------|---------|---------|
| **Vite** | 6.2.4 | Modern build tool |
| **laravel-vite-plugin** | 1.2.0 | Laravel integration |
| **Entry Points** | N/A | resources/css/app.css, resources/js/app.js |

---

## 6. DEVELOPMENT & TESTING

### Testing Framework
| Tool | Version | Purpose |
|------|---------|---------|
| **PHPUnit** | 11.0.1 | PHP unit testing framework |
| **Pest PHP** | 3.8 | Modern PHP testing framework |
| **Pest Laravel Plugin** | 3.2 | Laravel integration for Pest |
| **Mockery** | 1.6 | Mocking library for unit tests |

### Development Tools
| Tool | Version | Purpose |
|------|---------|---------|
| **Laravel Sail** | 1.26 - 1.41 | Docker development environment |
| **Laravel Pint** | 1.13 | Laravel code style fixer (PSR-12) |
| **Laravel Pail** | 1.2.2 | Real-time log viewer |
| **Faker** | 1.23 | Generates realistic test data |
| **Collision** | 8.0 - 8.6 | Error display beautifier for testing |
| **Concurrently** | 9.0.1 | Run multiple commands concurrently |

### Development Scripts
```json
{
  "dev": "vite",
  "build": "vite build",
  "test": "artisan test"
}
```

---

## 7. EMAIL & NOTIFICATIONS

### Mail Configuration
| Aspect | Details |
|--------|---------|
| **Default Mailer** | SMTP (configurable via .env) |
| **Transport** | SMTP |
| **Port** | 2525 (default), configurable |
| **Authentication**: Username/password based |
| **Timeout**: Configurable, no hard limit by default |
| **Domain**: Dynamic EHLO domain from APP_URL |
| **Fallback Options**: Log (development), Array (testing) |

### Supported Mail Drivers
- SMTP (Primary)
- Sendmail
- Mailgun
- Amazon SES
- Postmark
- Resend
- Log (Development)
- Array (Testing)
- Failover & Round-robin (Multi-provider)

---

## 8. MODULES & ARCHITECTURE

### System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    HRMS - 3 SYSTEM ARCHITECTURE                │
└─────────────────────────────────────────────────────────────────┘

                    ┌──────────────────────────┐
                    │   USER / BROWSER CLIENT  │
                    └───────────┬──────────────┘
                                │ HTTPS
                 ┌──────────────┴──────────────┐
                 │                             │
        ┌────────▼────────┐          ┌────────▼────────┐
        │ WORKSPACE (HUB) │          │  SUB-MODULES    │
        │  Main Portal    │          │  (Payroll,      │
        │ Session-Based   │          │   Attendance)   │
        │   JWT Gen.      │          │  JWT-Based      │
        └────────┬────────┘          └────────┬────────┘
                 │                            │
          JWT Token with                 Token Validation
           HMAC Signature                & HMAC Verify
                 │                            │
                 └──────────┬─────────────────┘
                            │
         ┌──────────────────┼──────────────────┐
         │                  │                  │
         ▼                  ▼                  ▼
    ┌─────────┐        ┌─────────┐       ┌──────────┐
    │Attendance│       │ Payroll │       │Session   │
    │Module    │       │ Module  │       │Storage   │
    └─────────┘       └─────────┘       │(Server)  │
                                        └──────────┘
         │                  │
         └──────────┬───────┘
                    │
         ┌──────────┴──────────┐
         │                     │
         ▼                     ▼
    ┌─────────────┐      ┌──────────┐
    │  Database   │      │Middleware│
    │(MySQL/      │      │- CSRF    │
    │ MariaDB)    │      │- JWT     │
    │             │      │- HMAC    │
    │             │      │- Permiss │
    └─────────────┘      └──────────┘
```

### Module Structure - Detailed Comparison

#### 1. **WORKSPACE (HUB Application)**
**Location**: `/home/hrmsdev.isarva.in/public_html`

| Aspect | Details |
|--------|---------|
| **Purpose** | Central authentication hub and SSO portal |
| **Framework** | Laravel 12.0 |
| **Auth Method** | Session-based (web guard) with Breeze |
| **Entry Point** | `public/index.php` |
| **Environment** | Production-ready |

**Architecture**:
```
/workspace (root)
├── app/
│   ├── Http/Controllers/Auth/
│   │   └── AuthenticatedSessionController.php (Login/Logout)
│   ├── Models/
│   │   └── User.php (Main user model)
│   └── View/
├── resources/
│   ├── views/auth/     (Login/Register views)
│   ├── css/            (Tailwind CSS)
│   └── js/app.js
├── routes/
│   └── web.php         (Web routes)
├── config/
│   ├── app.php
│   ├── auth.php        (Session guard config)
│   ├── jwt.php         (JWT generation config)
│   └── database.php
└── .env                (Configuration)
```

**Key Features**:
- Session-based user authentication
- JWT token generation for sub-modules
- User role management
- SSO integration
- Token distribution to Payroll & Attendance

#### 2. **ATTENDANCE MODULE**
**Location**: `/home/hrmsdev.isarva.in/public_html/attendance`

| Aspect | Details |
|--------|---------|
| **Purpose** | Employee attendance tracking and leave management |
| **Framework** | Laravel 12.0 (Independent) |
| **Auth Method** | JWT-based (from parent HUB) |
| **Entry Point** | `public/index.php` |
| **Build Tool** | Vite 6.2.4 |
| **CSS Framework** | Tailwind CSS 3.4.0 |

**Architecture**:
```
/attendance
├── app/
│   ├── Http/
│   │   ├── Controllers/    (Attendance business logic)
│   │   ├── Middleware/
│   │   │   ├── CheckRoutePermission.php
│   │   │   ├── VerifyJwtHmac.php
│   │   │   └── RoleMiddleware.php
│   │   └── Kernel.php      (Middleware registration)
│   ├── Models/             (Eloquent models)
│   └── Http/Requests/
├── resources/
│   ├── views/              (Blade templates)
│   ├── css/app.css         (Tailwind CSS)
│   └── js/app.js
├── routes/
│   ├── web.php             (Web routes)
│   └── api.php             (API routes)
├── config/
│   ├── app.php
│   ├── auth.php            (JWT guard config)
│   ├── jwt.php
│   └── database.php
└── .env                    (Configuration)
```

**Key Features**:
- JWT token validation with HMAC verification
- Permission-based route protection
- Role middleware for access control
- Leave management
- Attendance reporting
- Email notifications
- Dynamic email system

#### 3. **PAYROLL MODULE**
**Location**: `/home/hrmsdev.isarva.in/public_html/payroll`

| Aspect | Details |
|--------|---------|
| **Purpose** | Salary processing and payment management |
| **Framework** | Laravel 12.0 (Independent) |
| **Auth Method** | JWT-based (from parent HUB) |
| **Entry Point** | `public/index.php` |
| **Build Tool** | Vite 5.0 |
| **CSS Framework** | Bootstrap 5.3.8 + Tailwind CSS 3.4.18 |

**Architecture**:
```
/payroll
├── app/
│   ├── Http/
│   │   ├── Controllers/    (Payroll business logic)
│   │   │   ├── UserManagementController.php
│   │   │   ├── SalaryController.php
│   │   │   └── ReportController.php
│   │   ├── Middleware/
│   │   │   ├── CheckRoutePermission.php
│   │   │   ├── VerifyJwtHmac.php
│   │   │   └── CheckPermission.php
│   │   └── Kernel.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Permission.php
│   │   └── [Other models]
│   └── Http/Requests/
├── resources/
│   ├── views/
│   │   ├── layouts/master.blade.php    (Main layout)
│   │   ├── usermanagement/
│   │   ├── salary/
│   │   └── [Other pages]
│   ├── sass/app.scss       (SCSS)
│   └── js/app.js
├── routes/
│   ├── web.php
│   └── api.php
├── config/
│   ├── app.php
│   ├── auth.php            (Sanctum + JWT config)
│   ├── jwt.php
│   └── database.php
└── .env
```

**Key Features**:
- Advanced salary calculations
- User management with DataTables
- PDF/Excel reports (DOMPDF, mPDF, PHPSpreadsheet)
- Permission management UI
- Select2 dropdowns
- Complex form validation
- Modal-based edit/delete operations
- AJAX-based data loading

### Module Communication Flow

#### 1. Initial Login (Workspace HUB)
```
User Login Form
        ↓
AuthenticatedSessionController::store()
        ↓
Session Created (web guard)
        ↓
JWT Token Generated with Claims:
  - User ID
  - Email
  - HMAC Signature
  - Expiration (5 mins)
        ↓
Token Stored in Session:
  - 'payroll_token'
  - 'attendance_token'
        ↓
Cookies Set Across Domain
```

#### 2. Access Sub-Module (Payroll/Attendance)
```
User Navigates to Sub-Module
        ↓
Token Retrieved from Cookie/Session
        ↓
CheckRoutePermission Middleware
  ├─ Check if user authenticated
  ├─ Check route permissions
  └─ Handle AJAX requests differently
        ↓
VerifyJwtHmac Middleware
  ├─ Extract JWT token
  ├─ Compute HMAC: hash_hmac('sha256', id+email, secret)
  ├─ Compare with token HMAC
  └─ Return 401 if invalid
        ↓
Request Allowed/Denied
```

#### 3. Logout (Across All Systems)
```
User Clicks Logout
        ↓
destroy() Method in AuthenticatedSessionController
        ↓
Session Invalidated (web guard)
        ↓
SSO Passive Logout URLs Called:
  - PAYROLL_URL/sso-passive-logout
  - ATTENDANCE_URL/sso-passive-logout
        ↓
All Cookies Cleared Across Domain:
  - XSRF-TOKEN
  - attendance_token
  - dev_payroll_session
  - dev_attendance_session
        ↓
Redirect to Logout Hub
```

---

## 9. ADDITIONAL LIBRARIES & TOOLS

### PDF & Document Generation
| Library | Version | Purpose |
|---------|---------|---------|
| **DOMPDF** | 3.1 | HTML to PDF conversion |
| **mPDF** | 8.2 | Advanced PDF generation |
| **PHPSpreadsheet** | 1.29 | Excel spreadsheet generation |
| **Maatwebsite Excel** | 3.1 | Laravel wrapper for Excel handling |

### Flash Messaging
| Library | Version | Purpose |
|---------|---------|---------|
| **PHP Flasher** | 2.1 | Session-based flash notifications |

### Utilities
- **Helper Functions**: Custom helpers in `app/Helper/helpers.php`
- **Facades**: Swift services access via facades
- **Service Providers**: Custom service providers for business logic

---

## 10. ENVIRONMENT & DEPLOYMENT

### Environment Variables (Key Configuration)
```env
# Application
APP_NAME=HRMS
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hrms.example.com

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hrms
DB_USERNAME=hrms_user
DB_PASSWORD=secure_password

# JWT
JWT_SECRET=secret_key_generated_by_artisan
JWT_HMAC_SECRET=hmac_secret_for_token_verification
JWT_ALGO=HS256
JWT_TTL=60
JWT_REFRESH_TTL=20160

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=2525
MAIL_USERNAME=no-reply@example.com
MAIL_PASSWORD=email_password

# Module URLs
PAYROLL_URL=https://payroll.hrms.example.com
ATTENDANCE_URL=https://attendance.hrms.example.com

# Session
SESSION_DOMAIN=.hrms.example.com
SESSION_COOKIE=XSRF-TOKEN
```

### Security Features
1. **CSRF Protection**: Token-based via `VerifyCsrfToken` middleware
2. **Password Hashing**: bcrypt (Laravel default)
3. **Encryption**: Laravel's encryption service (config/hashing.php)
4. **Rate Limiting**: Configurable throttle middleware
5. **SQL Injection Prevention**: Eloquent ORM parameterized queries
6. **XSS Protection**: Laravel blade escaping
7. **HTTPS/SSL**: Enforced for all production URLs
8. **Secure Cookies**: HttpOnly, Secure, SameSite attributes
9. **HMAC Verification**: Additional token integrity check

### Deployment Platform
- **Web Server**: Apache/Nginx
- **PHP**: 8.2+ (CLI and FPM)
- **Database**: MySQL 8.0+ or MariaDB 10.5+
- **Session Storage**: Server-side (database or file-based)
- **File Permissions**: 755 for directories, 644 for files
- **Cache**: File or Redis (configurable)
- **Queue**: Synchronous (configurable to Redis, Database, etc.)

---

## 11. SYSTEM-SPECIFIC FEATURES

### Workspace (HUB) Specific
- **User Authentication**: Traditional form-based login with Laravel Breeze
- **Session Management**: Server-side sessions with encrypted cookies
- **JWT Generation**: Creates JWT tokens for sub-modules
- **SSO Hub**: Handles login/logout across all systems
- **Dashboard**: Central hub for accessing sub-modules

### Payroll Module Specific
- **Advanced Tables**: DataTables with sorting, filtering, pagination
- **User Management**: Complex UI with modals and AJAX operations
- **Dropdown Enhancement**: Select2 for searchable selects
- **Form Validation**: Client-side and server-side validation
- **PDF Generation**: DOMPDF & mPDF for report generation
- **Excel Export**: Maatwebsite Excel & PHPSpreadsheet support
- **Salary Calculations**: Complex business logic
- **Permission System**: UI-based permission management
- **Bootstrap UI**: Traditional bootstrap-based design

### Attendance Module Specific
- **Modern UI**: Tailwind CSS-based responsive design
- **Leave Management**: Advanced leave tracking and approvals
- **Permission System**: Role-based permission middleware
- **Email Notifications**: Dynamic email system with templates
- **Attendance Tracking**: Real-time attendance updates
- **Reports**: Leave and attendance reporting
- **Lightweight**: Minimal JavaScript dependencies

### All Systems Features
- **JWT Authentication**: Shared JWT mechanism across all systems
- **HMAC Verification**: SHA-256 HMAC signature validation
- **Permission Middleware**: Route-based permission checking
- **API Structure**: RESTful API for inter-module communication
- **Error Handling**: JSON error responses with status codes

---

## 12. PERFORMANCE & OPTIMIZATION

### Caching
- **Cache Driver**: File-based (default), Redis available
- **Session Cache**: Server-side session management
- **Query Optimization**: Eager loading with Eloquent relations

### Database Optimization
- **Indexing**: Configured on permission routes and user lookups
- **Connection Pooling**: Available through PDO
- **Query Logging**: Development-only to prevent performance impact

### Frontend Optimization
- **Asset Minification**: Vite handles CSS/JS minification
- **Code Splitting**: Vite automatically splits modules
- **Lazy Loading**: Asset loading on demand
- **CDN Support**: Public assets can be served via CDN

---

## 13. COMPARISON MATRIX - All 3 Systems

| Feature | Workspace (HUB) | Payroll Module | Attendance Module |
|---------|-----------------|----------------|-------------------|
| **Location** | `/` (root) | `/payroll` | `/attendance` |
| **Laravel Version** | 12.0 | 12.0 | 12.0 |
| **Authentication** | Session-based (Breeze) | JWT + HMAC | JWT + HMAC |
| **Build Tool** | Vite 6.2.4 | Vite 5.0 | Vite 6.2.4 |
| **CSS Framework** | Tailwind CSS 3.1.0 | Bootstrap 5.3.8 + Tailwind | Tailwind CSS 3.4.0 |
| **jQuery** | No | Yes (3.7.1) | No |
| **DataTables** | No | Yes | No |
| **Select2** | No | Yes (4.1.0-rc.0) | No |
| **UI Pattern** | Modern (Alpine/Tailwind) | Traditional (jQuery/Bootstrap) | Modern (Tailwind) |
| **Primary Function** | SSO & Auth Hub | Salary Processing | Attendance Tracking |
| **Key Tables** | Users | Employees, Salary, Payments | Attendance, Leave |
| **Reports** | Dashboard | PDF/Excel (DOMPDF, mPDF) | Attendance Reports |
| **Token Gen** | Yes | Validates | Validates |
| **Token HMAC** | Generates | Verifies | Verifies |
| **Database** | MySQL/SQLite | MySQL/SQLite | MySQL/SQLite |
| **API Guard** | Session | JWT + Sanctum | JWT |
| **NPM Packages** | ~10 | ~8 | ~8 |

---

## 14. SECURITY LAYERS - SUMMARY

### Layer 1: Authentication
- Session-based for web UI (Workspace HUB)
- JWT-based for API requests (Payroll & Attendance)
- HMAC signature verification for token integrity

### Layer 2: Authorization
- Role-Based Access Control (RBAC)
- Permission-based route protection
- Middleware for permission checking

### Layer 3: Transport Security
- HTTPS/SSL for all communications
- Secure cookies with HttpOnly, Secure flags
- CORS headers management

### Layer 4: Data Security
- SQL parameterization via Eloquent
- CSRF token validation
- XSS protection via Blade escaping
- Password hashing with bcrypt

### Layer 5: Token Security
- JWT expiration (5-60 minutes)
- HMAC signature validation
- Token blacklisting on logout
- UUID-based JWT ID (jti) for uniqueness

---

## 15. SYSTEM ARCHITECTURE DIAGRAM

```
┌─────────────────────────────────────────────────────┐
│            WEB BROWSER / CLIENT APPLICATION         │
└────────────────┬────────────────────────────────────┘
                 │ HTTPS
    ┌────────────┴────────────┐
    │                         │
    ▼                         ▼
┌────────────┐          ┌──────────────┐
│ HUB Portal │          │ Sub-modules  │
│ (Main App) │          │ (Payroll,    │
│ Session    │          │  Attendance) │
│ Based Auth │          │ JWT Based    │
└───┬────────┘          └───┬──────────┘
    │                       │
    │ Generates JWT         │ Validates JWT
    │ & HMAC                │ & HMAC
    │                       │
    └───────────┬───────────┘
                │
    ┌───────────┴──────────────┐
    │                          │
    ▼                          ▼
┌─────────────┐         ┌────────────────┐
│   Session   │         │ Middleware     │
│   Storage   │         │ - CSRF Check   │
│  (Server)   │         │ - Permission   │
│             │         │   Check        │
└─────────────┘         │ - JWT Verify   │
                        │ - HMAC Verify  │
                        └────────┬───────┘
                                 │
                        ┌────────┴────────┐
                        │                 │
                        ▼                 ▼
                    ┌────────┐      ┌──────────┐
                    │Business│      │Database  │
                    │ Logic  │      │ (MySQL   │
                    │        │      │ /MariaDB)│
                    └────────┘      └──────────┘
```

---

## 14. KEY ENDPOINTS & INTEGRATION POINTS

### Authentication Endpoints
- **Login**: `POST /login` (HUB)
- **Logout**: `POST /logout` (All modules)
- **SSO Passive Logout**: `GET /sso-passive-logout` (Payroll, Attendance)
- **Token Refresh**: Automatic JWT refresh within TTL window

### API Endpoints (Payroll & Attendance)
- Protected with `auth:api` middleware
- Require valid JWT token in Authorization header
- Return JSON responses
- Support pagination and filtering

### Data Synchronization
- User sync between HUB and sub-modules
- Permission sync via API calls
- Real-time updates via AJAX

---

## 15. TROUBLESHOOTING & LOGS

### Logging
- **Default Driver**: Log file
- **Location**: `storage/logs/laravel.log`
- **Channels**: Single (default), Stack, Syslog, Slack
- **Log Level**: Configurable (debug, info, warning, error, critical, alert, emergency)

### Common Issues & Resolution
1. **JWT Token Invalid**: Check JWT_SECRET and JWT_HMAC_SECRET in .env
2. **HMAC Signature Mismatch**: Ensure user ID and email match across systems
3. **Permission Denied**: Verify user permissions in database
4. **CORS Errors**: Check middleware and allowed headers
5. **Session Timeout**: Adjust TTL and refresh_ttl in config/jwt.php

---

## 16. DEPENDENCIES SUMMARY

### Production Dependencies
- laravel/framework (v12.0)
- tymon/jwt-auth (v2.2)
- laravel/sanctum (v4.1) - Optional
- maatwebsite/excel (v3.1)
- barryvdh/laravel-dompdf (v3.1)

### Development Dependencies
- pestphp/pest (v3.8)
- phpunit/phpunit (v11.0.1)
- laravel/sail (v1.41)
- laravel/pint (v1.13)

---

**Last Updated**: November 14, 2025
**System**: HRMS v1.0 - General Sector
**Status**: Production Ready
