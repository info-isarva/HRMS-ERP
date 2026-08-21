<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'HRMS - Human Resource Management System')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --sidebar-width: 256px;
        }
        
        /* Main content responsive to sidebar state */
        .main-content-wrapper {
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
        }
        
        /* Header responsive to sidebar state */
        .main-header-wrapper {
            left: var(--sidebar-width);
            transition: left 0.3s ease;
            border-left: 1px solid #e5e7eb;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 1024px) {
            .main-content-wrapper {
                margin-left: 0 !important;
            }
            .main-header-wrapper {
                left: 0 !important;
            }
        }
        
        /* Collapsed state class */
        body.sidebar-collapsed {
            --sidebar-width: 64px;
        }
        
        /* Mobile sidebar styles */
        #sidebar {
            z-index: 60;
        }
        
        #sidebar-backdrop {
            z-index: 50;
        }
        
        /* Ensure mobile menu button is visible */
        #mobile-menu-toggle {
            z-index: 70;
        }
        
        /* Mobile sidebar animation */
        #sidebar.open {
            transform: translateX(0) !important;
        }
        
        #sidebar-backdrop:not(.hidden) {
            display: block;
        }
        
        /* Mobile sidebar spacing improvements */
        @media (max-width: 1024px) {
            #sidebar .logo {
                padding-left: 1rem;
                padding-right: 1rem;
                min-height: 4rem;
            }
            
            #sidebar .logo .flex.items-center {
                flex: 1;
                min-width: 0;
            }
            
            #sidebar .sidebar-text {
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-50 font-inter">
    <div class="min-h-screen">
        <!-- Sidebar -->
        @include('layouts.sidebar')
        
        <!-- Main Content -->
        <div id="main-content" class="min-h-screen flex flex-col main-content-wrapper">
            <!-- Header -->
            @include('layouts.header')
            
            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50" style="padding-top: {{ (config('demo.enabled') && config('demo.expires_at')) ? '106px' : '64px' }};">
                @yield('content')
            </main>
            
            <!-- Footer -->
            @include('layouts.footer')
        </div>
    </div>
    
    <script>
        // Ensure sidebar state is properly managed
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('collapsed')) {
                document.body.classList.add('sidebar-collapsed');
            }
        });
        
        // Mobile sidebar toggle function
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('sidebar');
            const backdrop = document.getElementById('sidebar-backdrop');
            const mobileMenuIcon = document.getElementById('mobile-menu-icon');
            
            if (sidebar && backdrop && mobileMenuIcon) {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('hidden');
                
                if (sidebar.classList.contains('open')) {
                    mobileMenuIcon.classList.replace('fa-bars', 'fa-times');
                } else {
                    mobileMenuIcon.classList.replace('fa-times', 'fa-bars');
                }
            }
        }
        
        // Make function globally accessible
        window.toggleMobileSidebar = toggleMobileSidebar;
    </script>
    
    @stack('scripts')
</body>
</html>