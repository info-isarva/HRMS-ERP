<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="SoengSouy Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, accounts, invoice, html5, responsive, CRM, Projects">
    <meta name="author" content="SoengSouy Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>HRMS | @yield('title')</title>
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ isset($companySettings->favicon) && !empty($companySettings->favicon) ? asset($companySettings->favicon) : asset('assets/img/favicon-dri.png') }}">
    <!-- Vite CSS Only (NOT app.js - we'll load it after libraries) -->
    @vite(['resources/sass/app.scss'])
    <!-- Font Awesome 6 CSS - Updated for better icon support -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Lineawesome CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <!-- Datatable CSS (Bootstrap 4 still compatible with Bootstrap 5) -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/dataTables.bootstrap4.min.css') }}">
    <!-- Select2 CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <!-- Datetimepicker CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <!-- Flasher CSS (optional, only needed if using themes) -->
    {{-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flasher/flasher@2.1.6/dist/flasher.min.css"> --}}
    {{-- <link rel="stylesheet" href="{{ URL::to('public/vendor/flasher/flasher.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('vendor/flasher/flasher.min.css') }}">

    {{-- message toastr --}}
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>

    <script>
        window.ATTENDANCE_API_BASE_URL = '{{ env('ATTENDANCE_API_BASE_URL', 'http://default-attendance-url/api') }}';
        window.globalCurrencySymbol = '{{ get_currency_symbol() }}';
        window.globalCurrencyLocale = '{{ get_currency_locale() }}';
        window.globalCurrencyName = '{{ get_currency_name() }}';
        window.globalCurrencySubunit = '{{ get_currency_subunit() }}';
        window.globalCurrencyCode = '{{ get_currency_code() }}';
        
        // Global error handler to prevent chart-related errors from showing
        window.addEventListener('error', function(e) {
            // Suppress common chart errors
            if (e.message && (
                e.message.includes('getContext') || 
                e.message.includes('Morris') || 
                e.message.includes('Graph container element not found') ||
                e.message.includes('Chart')
            )) {
                e.preventDefault();
                return false;
            }
        });
        
        // Suppress console errors for missing chart elements
        var originalError = console.error;
        console.error = function(message) {
            if (typeof message === 'string' && (
                message.includes('getContext') || 
                message.includes('Morris') ||
                message.includes('Graph container') ||
                message.includes('Chart')
            )) {
                return; // Don't show chart-related errors
            }
            originalError.apply(console, arguments);
        };
    </script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> 
    
    <!-- Modern Header Styles -->
    <style>
        /* Modern Header */
        .modern-header {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            position: fixed;
            top: 0;
            right: 0;
            left: 0;
            z-index: 1000;
            transition: margin-left 0.3s ease;
            height: auto !important;
            min-height: auto !important;
        }

        .dropdown-item:hover{
            transform: translateX(0px) !important;
        }

        .user-dropdown .dropdown-item {
            margin: 0 !important;
        }
        
        @media (min-width: 992px) {
            .modern-header {
                margin-left: 280px;
            }
            
            .modern-header.sidebar-collapsed {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 991.98px) {
            .modern-header {
                margin-left: 0 !important;
            }
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            height: 70px;
            background: transparent;
        }
        
        /* Mobile Menu Button */
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: #4b5563;
            padding: 0.5rem;
            cursor: pointer;
            transition: color 0.2s ease;
            border-radius: 0.375rem;
        }
        
        .mobile-menu-btn:hover {
            color: #1976d2;
            background: #f3f4f6;
        }
        
        /* Page Title */
        .page-title-section {
            flex: 1;
            margin-left: 1rem;
        }
        
        .page-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }
        
        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        /* Header Right */
        .header-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .header-item {
            position: relative;
        }
        
        /* Financial Year Switcher Visibility */
        .header-item select,
        .header-item .form-control,
        .header-item label,
        .header-item .select2-container {
            color: #1f2937 !important;
        }
        
        /* Override financial year switcher for modern header */
        .modern-header .financial-year-switcher {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
        }
        
        .modern-header .financial-year-switcher i,
        .modern-header .financial-year-switcher span {
            color: #1f2937 !important;
        }
        
        .modern-header .financial-year-switcher .btn-outline-light {
            color: #1f2937 !important;
            border: 1px solid #d1d5db !important;
            background: #f9fafb !important;
        }
        
        .modern-header .financial-year-switcher .btn-outline-light:hover,
        .modern-header .financial-year-switcher .btn-outline-light:focus {
            background: #f3f4f6 !important;
            border-color: #9ca3af !important;
            color: #1f2937 !important;
        }
        
        .modern-header .financial-year-switcher .bg-success {
            background-color: #10b981 !important;
        }
        
        .modern-header .financial-year-switcher .bg-warning text-dark {
            background-color: #f59e0b !important;
        }
        
        /* Global Form Spacing */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .header-item .select2-container--default .select2-selection--single {
            background-color: #f9fafb !important;
            border: 1px solid #d1d5db !important;
            border-radius: 0.375rem !important;
            height: 38px !important;
            padding: 5px 10px !important;
        }
        
        .header-item .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1f2937 !important;
            line-height: 26px !important;
        }
        
        .header-item .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px !important;
        }
        
        .header-btn {
            background: none;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6b7280;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }
        
        .header-btn:hover {
            background: #f3f4f6;
            color: #1976d2;
        }
        
        .badge-dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .notification-count {
            position: absolute;
            top: 4px;
            right: 4px;
            background: #ef4444;
            color: white;
            font-size: 0.65rem;
            font-weight: 600;
            padding: 2px 5px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
            line-height: 1;
        }
        
        /* Notification Dropdown */
        .notification-dropdown .dropdown-menu {
            min-width: 380px;
            max-width: 420px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            border-radius: 0.75rem;
            padding: 0;
            margin-top: 0.75rem;
            max-height: 600px;
            overflow: hidden;
        }
        
        .notification-header {
            padding: 1rem 1.25rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 0.75rem 0.75rem 0 0;
        }
        
        .notification-header h6 {
            color: white;
            font-weight: 600;
            margin: 0;
        }
        
        .notification-body {
            max-height: 400px;
            overflow-y: auto;
            padding: 0;
        }
        
        .notification-body::-webkit-scrollbar {
            width: 6px;
        }
        
        .notification-body::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        .notification-body::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }
        
        .notification-body::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }
        
        .notification-item {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #f0f0f0;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: start;
            gap: 0.75rem;
        }
        
        .notification-item:hover {
            background: #f8f9fa;
        }
        
        .notification-item.unread {
            background: #f0f7ff;
        }
        
        .notification-item.unread:hover {
            background: #e3f2ff;
        }
        
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }
        
        .notification-icon.bg-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .notification-icon.bg-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .notification-icon.bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .notification-icon.bg-info {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
        }
        
        .notification-icon.bg-danger {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .notification-content {
            flex: 1;
            min-width: 0;
        }
        
        .notification-title {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }
        
        .notification-message {
            color: #6b7280;
            font-size: 0.813rem;
            margin-bottom: 0.25rem;
            line-height: 1.4;
        }
        
        .notification-time {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        .notification-badge {
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            display: inline-block;
            margin-left: 0.5rem;
        }
        
        .notification-footer {
            padding: 0.75rem 1.25rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            border-radius: 0 0 0.75rem 0.75rem;
        }
        
        .notification-footer a {
            color: #667eea;
            font-weight: 600;
            font-size: 0.875rem;
            text-decoration: none;
        }
        
        .notification-footer a:hover {
            color: #764ba2;
            text-decoration: none;
        }
        
        .notification-empty {
            padding: 3rem 1.5rem;
            text-align: center;
            color: #9ca3af;
        }
        
        .notification-empty i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        
        .notification-empty p {
            margin: 0;
            font-size: 0.875rem;
        }
        
        .notification-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        /* User Dropdown */
        .user-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #4b5563;
            padding: 0.5rem;
            border-radius: 0.5rem;
            transition: background 0.2s ease;
        }
        
        .user-link:hover {
            background: #f3f4f6;
            text-decoration: none;
            color: #1976d2;
        }
        
        .user-avatar {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #e5e7eb;
            background: white !important;
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 12px;
            height: 12px;
            background: #10b981;
            border: 2px solid white;
            border-radius: 50%;
        }
        
        .user-name {
            font-weight: 500;
            color: #1f2937;
        }
        
        /* Modern Dropdown */
        .modern-dropdown {
            min-width: 280px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            padding: 0;
            margin-top: 0.5rem;
        }
        
        .dropdown-header {
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .dropdown-header * {
            color: white !important;
        }
        
        .dropdown-header .text-muted {
            color: rgba(255, 255, 255, 0.85) !important;
        }
        
        .dropdown-header .badge {
            color: white !important;
        }
        
        .dropdown-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid white;
            background: white;
        }
        
        .modern-dropdown .dropdown-item {
            padding: 0.75rem 1rem;
            color: #4b5563;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        
        .modern-dropdown .dropdown-item:hover {
            background: #f3f4f6;
            color: #1976d2;
            padding-left: 1.5rem;
        }
        
        .modern-dropdown .dropdown-item i {
            width: 20px;
            font-size: 1rem;
        }
        
        .modern-dropdown .dropdown-divider {
            margin: 0.5rem 0;
        }
        
        .badge-sm {
            font-size: 0.7rem;
            padding: 2px 6px;
        }
        
        /* Responsive */
        @media (max-width: 767.98px) {
            .page-title {
                font-size: 1rem;
            }
            
            .header-content {
                padding: 0 1rem;
                height: 60px;
            }
        }
        /* Global Modal Styling */
        .modal-content {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header .modal-title {
            font-weight: bold;
            font-size: 1.25rem;
        }

        .modal-header .close {
            color: white;
            text-shadow: none;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            border: none;
            transition: all 0.3s ease;
            opacity: 0.8;
        }

        .modal-header .close:hover {
            background: rgba(255, 255, 255, 0.3);
            opacity: 1;
            transform: rotate(90deg);
        }
        
        .modal-header .close span {
            display: block;
            line-height: 1;
            padding-bottom: 2px;
            font-size: 1.5rem;
            font-weight: 300;
            margin-left: 0;
        }
    </style>
    
    <!-- Custom CSS for User Management Sync -->
    <style>
        .badge-sync {
            background-color: #17a2b8;
            color: white;
            font-size: 10px;
            padding: 2px 6px;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.7; }
            100% { opacity: 1; }
        }
        
        .user-sync-menu .fa {
            margin-right: 8px;
            color: #17a2b8;
        }
        
        .sidebar .badge {
            font-size: 9px;
            padding: 2px 5px;
        }
    </style>
</head>

<body>
    @yield('style')
    <style>    
        .invalid-feedback{
            font-size: 14px;
        }
        .error{
            color: red;
        }
    </style>
    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Loader -->
        <div id="loader-wrapper">
            <div id="loader">
                <div class="loader-ellips">
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                  <span class="loader-ellips__dot"></span>
                </div>
            </div>
        </div>
        <!-- /Loader -->

        <!-- Modern Header -->
        <div class="modern-header header">
            <div class="header-content">
                <!-- Mobile Menu Button -->
                <button class="mobile-menu-btn d-lg-none" onclick="toggleMobileSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                
                <!-- Page Title -->
                <div class="page-title-section">
                    <h3 class="page-title mb-0">{{ $companySettings->company_name }}</h3>
                    <!-- <p class="page-subtitle mb-0 d-none d-md-block">Payroll Management System</p> -->
                </div>
                
                <!-- Header Right Section -->
                <div class="header-right d-flex align-items-center">
                    <!-- Financial Year Switcher -->
                    <div class="header-item d-none d-md-block me-3">
                        @include('components.financial-year-switcher')
                    </div>
                    
                    <!-- Notifications -->
                    <div class="header-item d-none d-lg-block me-3">
                        <div class="dropdown notification-dropdown">
                            <button class="header-btn dropdown-toggle" id="notificationDropdown" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bell"></i>
                                <span class="badge-dot" id="notification-badge" style="display: none;"></span>
                                <span class="notification-count" id="notification-count" style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right notification-menu modern-dropdown" aria-labelledby="notificationDropdown">
                                <div class="notification-header">
                                    <h6 class="mb-0">Notifications</h6>
                                    <button class="btn btn-sm btn-link text-white" id="mark-all-read" style="font-size: 0.75rem;">Mark all as read</button>
                                </div>
                                <div class="notification-body" id="notification-list">
                                    <div class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                        <p class="text-muted mt-2 mb-0">Loading notifications...</p>
                                    </div>
                                </div>
                                <div class="notification-footer">
                                    <a href="{{ route('notifications.all') }}" class="text-center d-block">View All Notifications</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- User Dropdown -->
                    <div class="header-item user-dropdown">
                        <div class="dropdown">
                            <a href="#" class="user-link dropdown-toggle" data-toggle="dropdown">
                                <div class="user-avatar">
                                    @php
                                        $userAvatar = Auth::user()->avatar;
                                        $displayAvatar = null;
                                        
                                        if (Auth::user()->employee_id) {
                                            $employeeData = DB::table('employee_basic_details')
                                                ->where('id', Auth::user()->employee_id)
                                                ->first();
                                            if ($employeeData && !empty($employeeData->profile_image)) {
                                                $displayAvatar = $employeeData->profile_image;
                                            }
                                        }
                                        
                                        if (!$displayAvatar) {
                                            $displayAvatar = $userAvatar;
                                        }
                                        
                                        if ($displayAvatar) {
                                            if (strpos($displayAvatar, 'assets/') !== false) {
                                                $imageUrl = url($displayAvatar);
                                            } else {
                                                $imageUrl = url('/assets/employee_profile_image/' . $displayAvatar);
                                            }
                                        } else {
                                            $imageUrl = url('/assets/img/user-icon.webp');
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" alt="{{ Session::get('name') }}">
                                    <span class="status-indicator"></span>
                                </div>
                                <span class="user-name d-none d-md-inline-block ms-2">{{ Session::get('name') }}</span>
                                <i class="fas fa-chevron-down ms-2 d-none d-md-inline-block"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right modern-dropdown">
                                <div class="dropdown-header">
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $imageUrl }}" alt="{{ Session::get('name') }}" class="dropdown-avatar">
                                        <div class="ms-3">
                                            <div class="font-weight-bold">{{ Session::get('name') }}</div>
                                            <small class="text-muted">{{ Auth::user()->role_name }}</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="{{ route('profile_user') }}">
                                    <i class="fas fa-user me-2"></i> My Profile
                                </a>
                                @if (Auth::user()->role_name=='Admin' || Auth::user()->role_name=='Super Admin')
                                    <a class="dropdown-item" href="{{ route('userManagement') }}">
                                        <i class="fas fa-users me-2"></i> User Management
                                        <span class="badge bg-info text-dark badge-sm ms-1">Sync</span>
                                    </a>
                                @endif
                                <a class="dropdown-item" href="{{ route('company/settings/page') }}">
                                    <i class="fas fa-cog me-2"></i> Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="{{ route('logout') }}">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @include('components.demo-banner')
        </div>
        <!-- /Modern Header -->
        <!-- Sidebar -->
        @include('sidebar.sidebar')
        <!-- /Sidebar -->
        
        <!-- Page Wrapper -->
        @yield('content')

        <!-- Footer -->
        @include('layouts.footer')
        <!-- /Footer -->
    </div>
    <!-- /Main Wrapper -->

    <!-- jQuery (Load First - CRITICAL) -->
    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    
    <!-- Popper JS (required for Bootstrap 5) -->
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    
    <!-- Core Library Scripts (BEFORE any plugins that depend on them) -->
    <!-- Slimscroll JS -->
    <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
    
    <!-- DataTable JS (MUST load before custom scripts use it) -->
    <script src="{{ URL::to('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    
    <!-- Select2 JS - Load local version (BEFORE app.js) -->
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    
    <!-- Datetimepicker JS -->
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    
    <!-- Multiselect JS -->
    <script src="{{ URL::to('assets/js/multiselect.min.js') }}"></script>
    
    <!-- Validation -->
    <script src="{{ URL::to('assets/js/jquery.validate.js') }}"></script>
    
    <!-- Custom App JS (the traditional one, after all dependencies) -->
    <script src="{{ URL::to('assets/js/app.js') }}"></script>
    
    <!-- Chart JS - Only load if specific chart elements exist -->
    @if(Request::is('home') || Request::is('dashboard'))
        <!-- Modern Chart.js for dashboard analytics -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <script>
            // Only load charts if elements exist
            document.addEventListener('DOMContentLoaded', function() {
                if (document.getElementById('lineChart')) {
                    // Load line chart script only if element exists
                    var script = document.createElement('script');
                    script.src = "{{ URL::to('assets/js/line-chart.js') }}";
                    script.onerror = function() { console.log('Chart script failed to load'); };
                    document.head.appendChild(script);
                }
            });
        </script>
    @endif
    
    @if(Request::is('home') || Request::is('dashboard') || Request::is('analytics') || Request::is('reports'))
        <!-- Morris charts for specific pages only -->
        <script src="{{ URL::to('assets/plugins/raphael/raphael.min.js') }}"></script>
        <script src="{{ URL::to('assets/plugins/morris/morris.min.js') }}"></script>
        <script>
            // Only load Morris charts if elements exist and Morris is available
            document.addEventListener('DOMContentLoaded', function() {
                if (typeof Morris !== 'undefined' && (document.getElementById('bar-charts') || document.getElementById('line-charts'))) {
                    var script = document.createElement('script');
                    script.src = "{{ URL::to('assets/js/chart.js') }}";
                    script.onerror = function() { console.log('Morris chart script failed to load'); };
                    document.head.appendChild(script);
                }
            });
        </script>
    @endif
    
    <!-- Flasher JS -->
    <script src="{{ URL::to('vendor/flasher/flasher.min.js') }}"></script>
    {!! Flasher::render() !!}
    
    <!-- Ensure libraries are initialized if not done by app.js -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Wait for app.js to complete, then reinitialize if needed
            setTimeout(function() {
                // Initialize Select2 if not already done and library is available
                if (typeof $.fn.select2 === 'function' && $('.select:not(.select2-hidden-accessible)').length > 0) {
                    $('.select:not(.select2-hidden-accessible)').select2({
                        minimumResultsForSearch: -1,
                        width: '100%'
                    });
                }
                
                // Initialize form controls
                if ($('.floating').length > 0) {
                    $('.floating').on('focus blur', function (e) {
                        $(this).parents('.form-focus').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
                    }).trigger('blur');
                }
            }, 100);
        });
    </script>
    
    <!-- Page-specific scripts (Load AFTER all libraries are ready) -->
    @yield('script')
    
    <!-- Notification System JavaScript -->
    <script>
        $(document).ready(function() {
            // Fetch notifications on page load
            fetchNotifications();
            
            // Refresh notifications every 30 seconds
            setInterval(fetchNotifications, 30000);
            
            // Fetch notifications when dropdown is opened
            $('#notificationDropdown').on('click', function() {
                fetchNotifications();
            });
            
            // Mark all as read
            $('#mark-all-read').on('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const $btn = $(this);
                
                // Prevent double-clicking
                if ($btn.prop('disabled')) {
                    return;
                }
                
                console.log('Mark all as read button clicked');
                
                // Disable button and show loading state
                $btn.prop('disabled', true);
                const originalText = $btn.html();
                $btn.html('<i class="fas fa-spinner fa-spin"></i> Marking...');
                
                markAllAsRead($btn, originalText);
            });
            
            function fetchNotifications() {
                $.ajax({
                    url: '{{ route("notifications.get") }}',
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            displayNotifications(response.notifications, response.unread_count);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching notifications:', xhr);
                        $('#notification-list').html(
                            '<div class="notification-empty">' +
                            '<i class="fas fa-exclamation-triangle"></i>' +
                            '<p>Failed to load notifications</p>' +
                            '</div>'
                        );
                    }
                });
            }
            
            function displayNotifications(notifications, unreadCount) {
                const $notificationList = $('#notification-list');
                const $badge = $('#notification-badge');
                const $count = $('#notification-count');
                
                // Update badge
                if (unreadCount > 0) {
                    $badge.show();
                    $count.text(unreadCount > 99 ? '99+' : unreadCount).show();
                } else {
                    $badge.hide();
                    $count.hide();
                }
                
                // Clear list
                $notificationList.empty();
                
                if (notifications.length === 0) {
                    $notificationList.html(
                        '<div class="notification-empty">' +
                        '<i class="fas fa-bell-slash"></i>' +
                        '<p>No notifications</p>' +
                        '</div>'
                    );
                    return;
                }
                
                // Display notifications
                notifications.forEach(function(notification) {
                    const timeAgo = getTimeAgo(notification.created_at);
                    const unreadClass = notification.is_read === false ? 'unread' : '';
                    
                    let iconHtml = '';
                    if (notification.profile_image) {
                        const imageUrl = notification.profile_image.includes('assets/') 
                            ? '{{ url("/") }}/' + notification.profile_image
                            : '{{ url("/assets/employee_profile_image/") }}/' + notification.profile_image;
                        iconHtml = '<img src="' + imageUrl + '" class="notification-avatar" alt="">';
                    } else {
                        iconHtml = '<div class="notification-icon bg-' + notification.color + '">' +
                                   '<i class="fas ' + notification.icon + '"></i>' +
                                   '</div>';
                    }
                    
                    const $item = $('<div>')
                        .addClass('notification-item ' + unreadClass)
                        .attr('data-id', notification.id)
                        .html(
                            iconHtml +
                            '<div class="notification-content">' +
                            '<div class="notification-title">' + notification.title + '</div>' +
                            '<div class="notification-message">' + notification.message + '</div>' +
                            '<div class="notification-time">' + timeAgo + '</div>' +
                            '</div>'
                        );
                    
                    // Add click handler
                    $item.on('click', function() {
                        markAsRead(notification.id);
                        // For manual notifications, navigate to the detail view
                        if (notification.type === 'manual') {
                            window.location.href = '{{ route("notifications.show", ":id") }}'.replace(':id', notification.id);
                        } else if (notification.action_url) {
                            window.location.href = notification.action_url;
                        }
                    });
                    
                    $notificationList.append($item);
                });
            }
            
            function markAsRead(notificationId) {
                $.ajax({
                    url: '{{ route("notifications.mark-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        notification_id: notificationId
                    },
                    success: function(response) {
                        if (response.success) {
                            fetchNotifications();
                        }
                    },
                    error: function(xhr) {
                        console.error('Error marking notification as read:', xhr);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to mark notification as read');
                        }
                    }
                });
            }
            
            function markAllAsRead($btn, originalText) {
                console.log('markAllAsRead function called');
                console.log('Route URL:', '{{ route("notifications.mark-all-read") }}');
                $.ajax({
                    url: '{{ route("notifications.mark-all-read") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Mark all as read - success response:', response);
                        if (response.success) {
                            fetchNotifications();
                            // Show success message if toastr is available
                            if (typeof toastr !== 'undefined') {
                                toastr.success('All notifications marked as read');
                            }
                        }
                    },
                    error: function(xhr) {
                        console.error('Error marking all notifications as read:', xhr);
                        console.error('Status:', xhr.status, 'Response:', xhr.responseText);
                        if (typeof toastr !== 'undefined') {
                            toastr.error('Failed to mark all notifications as read');
                        }
                    },
                    complete: function() {
                        // Re-enable button and restore original text
                        if ($btn && originalText) {
                            $btn.prop('disabled', false);
                            $btn.html(originalText);
                        }
                    }
                });
            }
            
            function getTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const seconds = Math.floor((now - date) / 1000);
                
                const intervals = {
                    year: 31536000,
                    month: 2592000,
                    week: 604800,
                    day: 86400,
                    hour: 3600,
                    minute: 60
                };
                
                for (const [name, value] of Object.entries(intervals)) {
                    const interval = Math.floor(seconds / value);
                    if (interval >= 1) {
                        return interval === 1 ? `1 ${name} ago` : `${interval} ${name}s ago`;
                    }
                }
                
                return 'Just now';
            }
        });
    </script>
    
    @if(Auth::check() && (Auth::user()->role_name === 'Admin' || Auth::user()->role_name === 'Super Admin'))
        @php
            $unclosedYear = \App\Models\FinancialYear::where('is_closed', false)
                ->where('end_date', '<', \Carbon\Carbon::now())
                ->first();
        @endphp
        @if($unclosedYear)
            <!-- Unclosed Financial Year Modal -->
            <div class="modal fade" id="unclosedFyModal" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-warning" style="border-width: 2px;">
                        <div class="modal-header bg-warning text-dark" style="border-bottom: none;">
                            <h5 class="modal-title" style="color: #000;"><i class="fas fa-exclamation-triangle"></i> Action Required</h5>
                            <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <i class="fas fa-calendar-times text-warning mb-3" style="font-size: 48px;"></i>
                            <h4>Previous Financial Year Not Closed</h4>
                            <p class="mt-2 text-dark">The financial year <strong>{{ $unclosedYear->name }}</strong> has ended but is not yet closed.</p>
                            <p class="text-muted small">Please navigate to Financial Year settings to close it and generate the annual reports to ensure system accuracy.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Remind Me Later</button>
                            <a href="{{ route('financial-years.index') }}" class="btn btn-warning" style="color: #000; font-weight: 500;">Go to Settings</a>
                        </div>
                    </div>
                </div>
            </div>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Check if already shown in this browser session
                    if (!sessionStorage.getItem('fy_alert_shown')) {
                        setTimeout(function() {
                            $('#unclosedFyModal').modal('show');
                            sessionStorage.setItem('fy_alert_shown', 'true');
                        }, 1000);
                    }
                });
            </script>
        @endif
    @endif
    
    @flasher_render
</body>
</html>