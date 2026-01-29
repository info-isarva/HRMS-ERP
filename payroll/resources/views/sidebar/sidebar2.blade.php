<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Sidebar</title>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Tailwind CSS for base styling -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transition-all duration-300 transform -translate-x-full lg:translate-x-0">
        <div class="flex flex-col h-full">
            <!-- Logo -->
            <div class="logo flex items-center justify-between h-16 px-4 lg:px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600">
                <div class="flex items-center">
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-users text-blue-600 text-lg"></i>
                    </div>
                    <h1 class="ms-3 text-xl font-bold text-white sidebar-text">HRMS</h1>
                </div>
                
                <!-- Mobile Close Button (visible only on mobile when sidebar is open) -->
                <button onclick="toggleMobileSidebar()" class="lg:hidden p-2 text-white hover:text-gray-200 transition-colors rounded-md hover:bg-white hover:bg-opacity-20">
                    <i class="fas fa-times text-lg"></i>
                </button>
                
                <!-- Desktop Collapse Toggle -->
                <button id="sidebar-toggle" onclick="toggleSidebar()" class="hidden lg:block p-1 text-white hover:text-gray-200 transition-colors">
                    <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-sm transition-transform duration-300"></i>
                </button>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 px-4 py-6 space-y-2">
                <!-- Dashboard -->
                <a href="{{ route('home') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-home text-lg flex-shrink-0 {{ request()->routeIs('home') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Dashboard</span>
                </a>
                
            </nav>
        </div>
    </aside>



    <!-- Backdrop for Mobile Menu -->
    <div id="sidebar-backdrop" class="fixed inset-0 bg-black bg-opacity-50 hidden z-30" onclick="toggleMobileSidebar()"></div>

  

    <style>
        /* Custom Scrollbar for Navigation */
        #sidebar nav {
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 transparent;
        }
        
        #sidebar nav::-webkit-scrollbar {
            width: 1px;
        }
        
        #sidebar nav::-webkit-scrollbar-track {
            background: transparent;
        }
        
        #sidebar nav::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 0.5px;
            transition: background 0.3s ease;
        }
        
        #sidebar nav::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        #sidebar nav::-webkit-scrollbar-thumb:active {
            background: #64748b;
        }
        
        /* Hide scrollbar when collapsed but keep functionality */
        #sidebar.collapsed nav::-webkit-scrollbar {
            width: 0px;
        }
        
        #sidebar.collapsed nav {
            scrollbar-width: none;
        }
        
        /* Fixed logo section */
        #sidebar .logo {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        #sidebar.collapsed {
            width: 4rem;
        }
        #sidebar.collapsed .sidebar-text,
        #sidebar.collapsed .section-title,
        #sidebar.collapsed .notification-badge,
        #sidebar.collapsed .submenu {
            display: none !important;
        }
        #sidebar.collapsed .logo {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar.collapsed .logo .ms-3 {
            margin-left: 0;
        }
        /* When collapsed, hide only the mobile close button (which uses the Tailwind class `lg:hidden`).
           Keep the desktop toggle button (#sidebar-toggle) visible so users can reopen the sidebar on desktop. */
        #sidebar.collapsed .logo button.lg\:hidden {
            display: none;
        }

        /* Ensure the desktop toggle remains visible and uses inline-flex for proper alignment. Use !important to
           override the generic rules safely here (specific to collapsed state). */
        #sidebar.collapsed .logo #sidebar-toggle {
            display: inline-flex !important;
        }

        /* Hide submenu chevrons in collapsed state on desktop to reduce visual clutter */
        #sidebar.collapsed .submenu-chevron {
            display: none !important;
        }
        #sidebar.collapsed nav a,
        #sidebar.collapsed nav button {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar.collapsed form button {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar.collapsed .user-info {
            justify-content: center;
        }
        #sidebar.collapsed .user-info .ms-3 {
            margin-left: 0;
        }


    </style>

    <style>
        /* Floating submenu popover for collapsed sidebar on desktop */
        .floating-submenu {
            position: absolute;
            top: 0;
            left: 0;
            min-width: 12rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
            border-radius: 0.5rem;
            z-index: 50;
            display: none;
            padding: 0.5rem;
        }

        /* Show floating submenu when active */
        .floating-submenu.show {
            display: block;
        }

        /* Style submenu links inside floating popover */
        .floating-submenu a {
            display: flex;
            align-items: center;
            padding: 0.5rem 0.75rem;
            color: #374151;
            border-radius: 0.375rem;
        }

        .floating-submenu a:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
    </style>
    <script>
        function toggleSubmenu(submenuId) {
            const submenu = document.getElementById(submenuId);
            const icon = document.getElementById(submenuId + '-icon');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                icon.classList.add('rotate-180');
            } else {
                submenu.classList.add('hidden');
                icon.classList.remove('rotate-180');
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleIcon = document.getElementById('sidebar-toggle-icon');
            const body = document.body;
            
            sidebar.classList.toggle('collapsed');
            body.classList.toggle('sidebar-collapsed');
            
            if (sidebar.classList.contains('collapsed')) {
                document.querySelectorAll('.submenu').forEach(function(submenu) {
                    submenu.classList.add('hidden');
                    const iconId = submenu.id + '-icon';
                    const icon = document.getElementById(iconId);
                    if (icon) {
                        icon.classList.remove('rotate-180');
                    }
                });
                toggleIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
            } else {
                toggleIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
            }
        }

        // Floating submenu logic: show submenu as a popover on hover when sidebar is collapsed (desktop only)
        (function() {
            const sidebar = document.getElementById('sidebar');

            // Create floating container
            const floating = document.createElement('div');
            floating.className = 'floating-submenu';
            floating.id = 'floating-submenu-container';
            document.body.appendChild(floating);

            // Helper to position floating relative to element
            function positionFloating(triggerEl) {
                const rect = triggerEl.getBoundingClientRect();
                // Position to the right of the sidebar icon area
                floating.style.top = (rect.top + window.scrollY) + 'px';
                floating.style.left = (rect.right + 8 + window.scrollX) + 'px';
            }

            // Show floating submenu with content from the real submenu
            function showFloating(submenuId, triggerEl) {
                const submenu = document.getElementById(submenuId);
                if (!submenu) return;

                // Clone submenu items (links)
                const cloned = submenu.cloneNode(true);
                // Remove any layout classes that affect indentation
                cloned.classList.remove('ms-4');
                // Extract anchor elements
                const anchors = cloned.querySelectorAll('a');

                floating.innerHTML = '';
                anchors.forEach(a => {
                    const node = a.cloneNode(true);
                    node.classList.remove('group');
                    floating.appendChild(node);
                });

                positionFloating(triggerEl);
                floating.classList.add('show');
            }

            function hideFloating() {
                floating.classList.remove('show');
            }

            // Attach hover listeners to menu buttons that have data-submenu
            document.querySelectorAll('[data-submenu]').forEach(function(btn) {
                const submenuId = btn.getAttribute('data-submenu');

                btn.addEventListener('mouseenter', function(e) {
                    // Only show floating when sidebar is collapsed AND on desktop (min-width: 1024px)
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
                    if (isCollapsed && isDesktop) {
                        showFloating(submenuId, btn);
                    }
                });

                btn.addEventListener('mouseleave', function(e) {
                    // small timeout to allow moving into floating
                    setTimeout(function() {
                        if (!floating.matches(':hover')) hideFloating();
                    }, 150);
                });
            });

            // Also attach hover listeners to top-level anchors (single-link menu items) so when the sidebar
            // is collapsed the floating popover shows their label and icon. We skip anchors that live
            // inside a submenu to avoid duplication.
            document.querySelectorAll('#sidebar nav a').forEach(function(anchor) {
                // skip anchors that are inside submenu containers
                if (anchor.closest('.submenu')) return;

                anchor.addEventListener('mouseenter', function() {
                    const isCollapsed = sidebar.classList.contains('collapsed');
                    const isDesktop = window.matchMedia('(min-width: 1024px)').matches;
                    if (!isCollapsed || !isDesktop) return;

                    // clone the anchor and show it inside the floating popover
                    floating.innerHTML = '';
                    const cloned = anchor.cloneNode(true);
                    // remove indentation classes that may hide label in the cloned node
                    cloned.classList.remove('ms-3', 'ms-auto', 'me-3');
                    cloned.style.display = 'flex';
                    cloned.style.alignItems = 'center';
                    floating.appendChild(cloned);

                    positionFloating(anchor);
                    floating.classList.add('show');
                });

                anchor.addEventListener('mouseleave', function() {
                    setTimeout(function() {
                        if (!floating.matches(':hover')) hideFloating();
                    }, 150);
                });
            });

            // Hide when mouse leaves floating
            floating.addEventListener('mouseleave', hideFloating);
            // Keep visible while hovering
            floating.addEventListener('mouseenter', function() {});
        })();


    </script>
</body>
</html>