<!DOCTYPE html>
<html lang="{{ session('posh_locale', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('posh.product_name')) — {{ config('posh.product_name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { inter: ['Inter', 'sans-serif'] },
                },
            },
        };
    </script>

    <style>
        :root { --sidebar-width: 256px; }
        body { font-family: 'Inter', sans-serif; }
        .main-content-wrapper { margin-left: var(--sidebar-width); transition: margin-left 0.3s ease; }
        .main-header-wrapper { left: var(--sidebar-width); transition: left 0.3s ease; border-left: 1px solid #e5e7eb; }
        body.sidebar-collapsed { --sidebar-width: 64px; }
        @media (max-width: 1024px) {
            .main-content-wrapper { margin-left: 0 !important; }
            .main-header-wrapper { left: 0 !important; }
        }
        #sidebar { z-index: 60; overflow-x: hidden; }
        #sidebar.open { transform: translateX(0) !important; }
        #sidebar.collapsed { width: 4rem; }
        #sidebar.collapsed .sidebar-text,
        #sidebar.collapsed .section-title { display: none !important; }
        #sidebar.collapsed .logo {
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 0.25rem;
            padding: 0.5rem 0.25rem;
            height: 4.5rem;
        }
        #sidebar.collapsed .sidebar-logo-brand {
            justify-content: center;
            flex: none;
            min-width: 0;
            width: 100%;
        }
        #sidebar.collapsed .sidebar-logo-brand .ml-3 {
            margin-left: 0;
        }
        #sidebar.collapsed .logo button.lg\:hidden {
            display: none !important;
        }
        #sidebar.collapsed .logo #sidebar-toggle {
            position: static;
            transform: none;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            padding: 0;
            line-height: 1;
        }
        #sidebar.collapsed nav a {
            justify-content: center;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            border-right-width: 0 !important;
        }
        #sidebar.collapsed nav a.bg-blue-50 {
            border-left: 4px solid #2563eb;
        }
        #sidebar.collapsed .user-info {
            justify-content: center;
        }
        #sidebar.collapsed .user-info .ml-3 {
            margin-left: 0;
        }
        #sidebar.collapsed .border-t {
            margin-left: 0.5rem;
            margin-right: 0.5rem;
        }
        #sidebar nav { overflow-y: auto; scrollbar-width: thin; scrollbar-color: #cbd5e1 transparent; }

        /* POSH view compatibility — matches Attendance card/button patterns */
        .card { background: #fff; border-radius: 0.5rem; box-shadow: 0 1px 2px 0 rgba(0,0,0,.05); border: 1px solid #e5e7eb; overflow: hidden; }
        .card-header { padding: 1rem 1.25rem; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem; background: #fff; }
        .card-header h2 { margin: 0; font-size: 1rem; font-weight: 600; color: #1e40af; }
        .card-body { padding: 1rem 1.25rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .stat-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.25rem; box-shadow: 0 1px 2px rgba(0,0,0,.05); transition: box-shadow .2s, transform .2s; }
        .stat-card.clickable:hover { box-shadow: 0 4px 12px rgba(37,99,235,.12); transform: translateY(-2px); }
        .stat-icon { width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; display: flex; align-items: center; justify-content: center; margin-bottom: 0.75rem; font-size: 1.1rem; }
        .stat-total .stat-icon { background: #eff6ff; color: #2563eb; }
        .stat-open .stat-icon { background: #fef3c7; color: #d97706; }
        .stat-closed .stat-icon { background: #ecfdf5; color: #059669; }
        .stat-value { font-size: 1.75rem; font-weight: 700; color: #1e40af; line-height: 1.2; }
        .stat-label { font-size: 0.8rem; font-weight: 500; color: #6b7280; margin-top: 0.25rem; }
        .stat-hint { font-size: 0.75rem; color: #9ca3af; margin-top: 0.25rem; }
        .case-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 0.875rem 1rem; }
        .badge-prototype { display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; font-size: 0.7rem; font-weight: 600; border-radius: 9999px; background: #eff6ff; color: #1d4ed8; }
        .btn-accent, .btn-primary { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #fff; background: #2563eb; border: none; border-radius: 0.5rem; cursor: pointer; text-decoration: none; transition: background .15s; }
        .btn-accent:hover, .btn-primary:hover { background: #1d4ed8; color: #fff; }
        .btn-ghost { display: inline-flex; align-items: center; gap: 0.35rem; padding: 0.5rem 1rem; font-size: 0.875rem; font-weight: 500; color: #374151; background: #fff; border: 1px solid #d1d5db; border-radius: 0.5rem; text-decoration: none; transition: background .15s, border-color .15s; }
        .btn-ghost:hover { background: #f9fafb; border-color: #9ca3af; color: #1e40af; }
        .btn-sm { padding: 0.35rem 0.75rem; font-size: 0.8rem; }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
        .form-group label { display: block; font-weight: 500; font-size: 0.875rem; margin-bottom: 0.35rem; color: #374151; }
        .form-control { width: 100%; padding: 0.5rem 0.75rem; font-size: 0.875rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: #fff; color: #1e40af; }
        .form-control:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.2); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 0.75rem 1rem; text-align: left; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: #6b7280; background: #f9fafb; border-bottom: 1px solid #e5e7eb; }
        .data-table td { padding: 0.75rem 1rem; border-bottom: 1px solid #f3f4f6; font-size: 0.875rem; color: #374151; }
        .data-table tbody tr:hover { background: #f9fafb; }
        .help-panel { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 0.5rem; padding: 1rem; }
        .help-panel-body { font-size: 0.875rem; color: #1e40af; }
        .step-tab.active { background: linear-gradient(135deg, #2563eb, #9333ea) !important; color: #fff !important; border: none !important; }
        .toast-success { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 100; padding: 0.75rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,.15); font-size: 0.875rem; }
        .toast-error { position: fixed; top: 1.25rem; right: 1.25rem; z-index: 100; padding: 0.75rem 1.25rem; border-radius: 0.5rem; background: #dc2626; color: #fff; box-shadow: 0 10px 25px rgba(0,0,0,.15); font-size: 0.875rem; }
        .policy-content { font-size: 0.9375rem; line-height: 1.6; color: #374151; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 font-inter">
    @if(session('success'))
        <div id="toast" class="toast-success"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div id="toast" class="toast-error"><i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}</div>
    @endif

    <div class="min-h-screen">
        @include('layouts.sidebar')

        <div id="main-content" class="min-h-screen flex flex-col main-content-wrapper">
            @include('layouts.header')

            <main class="flex-1 overflow-y-auto bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50" style="padding-top: 64px;">
                <div class="max-w-full mx-auto p-4 lg:p-6 space-y-6">
                    @hasSection('page-banner')
                        @yield('page-banner')
                    @else
                        @if(trim($__env->yieldContent('page-subtitle')))
                            <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-5 lg:p-6 text-white shadow-sm">
                                <h1 class="text-xl lg:text-2xl font-bold">@yield('page-title', 'Dashboard')</h1>
                                <p class="text-blue-100 text-sm lg:text-base mt-1">@yield('page-subtitle')</p>
                            </div>
                        @endif
                    @endif
                    @yield('content')
                </div>
            </main>

            @include('layouts.footer')
        </div>
    </div>

    <div id="sidebar-backdrop" class="fixed inset-0 bg-indigo-900/40 hidden z-50 lg:hidden" onclick="toggleMobileSidebar()"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar?.classList.contains('collapsed')) {
                document.body.classList.add('sidebar-collapsed');
            }
        });
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const icon = document.getElementById('mobile-menu-icon');
            sidebar?.classList.toggle('open');
            backdrop?.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('fa-bars', !sidebar?.classList.contains('open'));
                icon.classList.toggle('fa-times', sidebar?.classList.contains('open'));
            }
        }
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            sidebar?.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed');
            if (toggleIcon) {
                if (sidebar?.classList.contains('collapsed')) {
                    toggleIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
                } else {
                    toggleIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
                }
            }
        }
        window.toggleMobileSidebar = toggleMobileSidebar;
        window.toggleSidebar = toggleSidebar;
        setTimeout(() => document.getElementById('toast')?.remove(), 4500);
    </script>
    @stack('scripts')
</body>
</html>
