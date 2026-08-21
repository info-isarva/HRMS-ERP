<x-app-layout>
    <!-- Modern light background -->
    <div class="modern-dashboard">
        <!-- Floating background elements -->
        <div class="bg-shapes">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>
            <div class="shape shape-4"></div>
        </div>
        
        <!-- Dashboard Header -->
        <header class="dashboard-header">
            <div class="container">
                <div class="header-content">
                    <div class="logo-section">
                        <img src="{{ asset('images/logo_image.1749279521.svg') }}" alt="Divya Roopa Infracon Logo" class="company-logo">
                        <span class="portal-title">Workspace Portal</span>
                    </div>
                    <div class="user-actions">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @include('components.demo-banner')
        </header>

        <!-- Main Dashboard Content -->
        <main class="dashboard-main">
            <div class="container">
                @if($permissions)
                    <!-- Employee User Dashboard -->
                    <div class="welcome-section">
                        <div class="welcome-content">
                            <h1 class="welcome-title">Welcome back, {{ $permissions['employee_name'] }}!</h1>
                            <p class="welcome-subtitle">Your workspace applications are ready</p>
                        </div>
                        <div class="user-avatar">
                            <div class="avatar-circle">
                                <i class="fas fa-user"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="apps-section">
                        <div class="apps-grid">
                            @if($permissions['enable_payroll'])
                                <!-- Payroll Application Card -->
                                <a href="{{ route('payroll.sso') }}" target="_blank" class="modern-app-card">
                                    <div class="card-icon-wrapper payroll-icon-bg">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">Payroll Management</h3>
                                        <p class="card-desc">Manage salaries, benefits, and financial records</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif
                            
                            @if($permissions['enable_self_portal'])
                                <!-- Attendance Application Card -->
                                <a href="{{ route('attendance.redirect') }}" target="_blank" class="modern-app-card">
                                    <div class="card-icon-wrapper attendance-icon-bg">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">Attendance Tracking</h3>
                                        <p class="card-desc">Track time, leaves, and attendance records</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif

                            @if(config('demo.crm_enabled', true) && ($permissions['enable_crm'] ?? false))
                                <!-- CRM Application Card -->
                                <a href="{{ route('crm.sso') }}" target="_blank" class="modern-app-card">
                                    <div class="card-icon-wrapper crm-icon-bg">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">CRM System</h3>
                                        <p class="card-desc">Manage leads, deals, and customer relationships</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif

                            @if(config('posh.module_placeholder_enabled'))
                                <a href="{{ config('services.posh.url') ? route('posh.sso') : route('posh.coming-soon') }}" class="modern-app-card posh-app-card">
                                    <div class="card-icon-wrapper posh-icon-bg">
                                        <i class="fas fa-shield-halved"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">{{ config('posh.product_name') }}</h3>
                                        <p class="card-desc">Sexual harassment compliance — full Act workflow</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif

                            @if(\App\Http\Controllers\Central\DemoTenantController::canAccess(auth()->user()?->email))
                                <a href="{{ route('platform.demo-tenants.index') }}" class="modern-app-card" style="border:2px dashed #93c5fd;background:linear-gradient(135deg,#eff6ff,#f8fafc)">
                                    <div class="card-icon-wrapper" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                                        <i class="fas fa-building-circle-check"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">Demo Tenant Manager</h3>
                                        <p class="card-desc">Provision client demos, expiry dates & usage report</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                @else
                    <!-- Regular User Dashboard -->
                    <div class="welcome-section">
                        <div class="welcome-content">
                            <h1 class="welcome-title">Welcome to HRMS Workspace</h1>
                            <p class="welcome-subtitle">Access your management applications</p>
                        </div>
                        <div class="user-avatar">
                            <div class="avatar-circle admin">
                                <i class="fas fa-user-tie"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div class="apps-section">
                        <div class="apps-grid">
                            <!-- Payroll Application Card -->
                            <a href="{{ route('payroll.sso') }}" target="_blank" class="modern-app-card">
                                <div class="card-icon-wrapper payroll-icon-bg">
                                    <i class="fas fa-calculator"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Payroll Management</h3>
                                    <p class="card-desc">Manage salaries, benefits, and financial records</p>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            
                            <!-- Attendance Application Card -->
                            <a href="{{ route('attendance.redirect') }}" target="_blank" class="modern-app-card">
                                <div class="card-icon-wrapper attendance-icon-bg">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">Attendance Tracking</h3>
                                    <p class="card-desc">Track time, leaves, and attendance records</p>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            
                            <!-- CRM Application Card -->
                            @if(config('demo.crm_enabled', true))
                            <a href="{{ route('crm.sso') }}" target="_blank" class="modern-app-card">
                                <div class="card-icon-wrapper crm-icon-bg">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="card-content">
                                    <h3 class="card-title">CRM System</h3>
                                    <p class="card-desc">Manage leads, deals, and customer relationships</p>
                                </div>
                                <div class="card-arrow">
                                    <i class="fas fa-chevron-right"></i>
                                </div>
                            </a>
                            @endif

                            @if(config('posh.module_placeholder_enabled'))
                                <a href="{{ config('services.posh.url') ? route('posh.sso') : route('posh.coming-soon') }}" class="modern-app-card posh-app-card">
                                    <div class="card-icon-wrapper posh-icon-bg">
                                        <i class="fas fa-shield-halved"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">{{ config('posh.product_name') }}</h3>
                                        <p class="card-desc">Sexual harassment compliance — full Act workflow</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif

                            @if(\App\Http\Controllers\Central\DemoTenantController::canAccess(auth()->user()?->email))
                                <a href="{{ route('platform.demo-tenants.index') }}" class="modern-app-card" style="border:2px dashed #93c5fd;background:linear-gradient(135deg,#eff6ff,#f8fafc)">
                                    <div class="card-icon-wrapper" style="background:linear-gradient(135deg,#2563eb,#1d4ed8)">
                                        <i class="fas fa-building-circle-check"></i>
                                    </div>
                                    <div class="card-content">
                                        <h3 class="card-title">Demo Tenant Manager</h3>
                                        <p class="card-desc">Provision client demos, expiry dates & usage report</p>
                                    </div>
                                    <div class="card-arrow">
                                        <i class="fas fa-chevron-right"></i>
                                    </div>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </main>

        <!-- Footer -->
        <footer class="dashboard-footer">
            <div class="container">
                <p class="footer-text">
                    <i class="fas fa-shield-alt"></i>
                    Secure Workspace Portal &copy; {{ date('Y') }} | Payroll & Attendance Solutions
                </p>
            </div>
        </footer>
    </div>

    <!-- Modern Light Color Scheme Styles -->
    <style>
        /* CSS Reset and Base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #374151;
            overflow-x: hidden;
            background-color: #f8fafc;
        }
        
        /* Modern Dashboard Container */
        .modern-dashboard {
            min-height: 100vh;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #f1f5f9 100%);
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Container */
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
            width: 100%;
            position: relative;
            z-index: 2;
        }
        
        /* Header */
        .dashboard-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.5);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .dashboard-header .demo-alert-bar {
            border-top: 1px solid #fcd34d;
            border-bottom: none;
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .logo-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .company-logo {
            height: 45px;
            width: auto;
        }
        
        .portal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e293b;
            display: none;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: linear-gradient(45deg, #ef4444, #dc2626);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.2);
            cursor: pointer;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.3);
            background: linear-gradient(45deg, #ff0000, #bd0e0e);
            color: white;
        }
        
        /* Main Content */
        .dashboard-main {
            padding: 4rem 0;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        /* Welcome Section */
        /* Welcome Section */
        .welcome-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .welcome-content h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
            background: linear-gradient(135deg, #1e293b, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .welcome-subtitle {
            font-size: 1.2rem;
            color: #64748b;
            margin: 0;
        }
        
        .avatar-circle {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
            box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
        }

        .avatar-circle.admin {
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
        }
        
        /* Apps Grid & Cards */
        .apps-section {
            width: 100%;
        }

        .apps-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(340px, 1fr));
            gap: 2rem;
        }
        
        /* Sleek Modern Card */
        .modern-app-card {
            display: flex;
            align-items: center;
            padding: 2rem;
            background: white;
            border-radius: 20px;
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.05), 
                0 2px 4px -1px rgba(0, 0, 0, 0.03);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            position: relative;
            overflow: hidden;
            height: 100%;
        }
        
        .modern-app-card:hover {
            transform: translateY(-5px);
            box-shadow: 
                0 20px 25px -5px rgba(0, 0, 0, 0.1), 
                0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: rgba(59, 130, 246, 0.2);
        }

        /* Card Icon */
        .card-icon-wrapper {
            flex-shrink: 0;
            width: 64px;
            height: 64px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.75rem;
            color: white;
            margin-right: 1.5rem;
            transition: transform 0.3s ease;
        }

        .modern-app-card:hover .card-icon-wrapper {
            transform: scale(1.1) rotate(-5deg);
        }

        /* Icon Gradients */
        .payroll-icon-bg {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.25);
        }

        .attendance-icon-bg {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.25);
        }

        .crm-icon-bg {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.25);
        }

        .posh-icon-bg {
            background: linear-gradient(135deg, #1e3a5f 0%, #d4622a 100%);
            box-shadow: 0 10px 15px -3px rgba(30, 58, 95, 0.25);
        }

        .posh-app-card {
            border: 2px solid rgba(212, 98, 42, 0.2);
        }

        /* Card Text */

        /* Card Text */
        .card-content {
            flex-grow: 1;
            padding-right: 1rem;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.35rem;
            line-height: 1.2;
        }

        .card-desc {
            font-size: 0.95rem;
            color: #64748b;
            margin: 0;
            line-height: 1.5;
        }

        /* Card Arrow */
        .card-arrow {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: #f1f5f9;
            color: #94a3b8;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .modern-app-card:hover .card-arrow {
            background-color: #3b82f6;
            color: white;
            transform: translateX(5px);
        }

        /* Footer */
        .dashboard-footer {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-top: 1px solid rgba(226, 232, 240, 0.5);
            padding: 1.5rem 0;
            margin-top: auto;
        }
        
        .footer-text {
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        /* Floating Background Shapes */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
        }
        
        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
            animation: float 20s infinite ease-in-out;
        }
        
        .shape-1 {
            width: 200px;
            height: 200px;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6);
            top: 10%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape-2 {
            width: 150px;
            height: 150px;
            background: linear-gradient(45deg, #10b981, #06b6d4);
            top: 60%;
            right: 15%;
            animation-delay: 7s;
        }
        
        .shape-3 {
            width: 100px;
            height: 100px;
            background: linear-gradient(45deg, #f59e0b, #ef4444);
            bottom: 20%;
            left: 20%;
            animation-delay: 14s;
        }
        
        .shape-4 {
            width: 120px;
            height: 120px;
            background: linear-gradient(45deg, #8b5cf6, #ec4899);
            top: 30%;
            right: 30%;
            animation-delay: 21s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            33% { transform: translateY(-30px) rotate(120deg); }
            66% { transform: translateY(20px) rotate(240deg); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }
            
            .dashboard-main {
                padding: 2rem 0;
            }
            
            .portal-title {
                display: none;
            }
        }

        @media (min-width: 769px) {
            .portal-title {
                display: block;
            }
        }
    </style>
</x-app-layout>