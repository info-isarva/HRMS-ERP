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
                    <h1 class="ml-3 text-xl font-bold text-white sidebar-text">HRMS</h1>
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
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-home text-lg flex-shrink-0 {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Dashboard</span>
                </a>

                <!-- Self Attendance -->
                <a href="{{ route('self-attendance.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('self-attendance.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-user-clock text-lg flex-shrink-0 {{ request()->routeIs('self-attendance.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Self Attendance</span>
                </a>

                 @php
                    $isManager = false;
                    $managerEmployee = \App\Models\Employee::where('email', auth()->user()->email)->first();
                    if ($managerEmployee) {
                        $isManager = \App\Models\Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)->exists();
                    }
                    $canManageLeaves = auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->role === 'hr' || $isManager;
                    
                    $mgmtPendingCount = 0;
                    if($canManageLeaves) {
                        if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->role === 'hr') {
                             $mgmtPendingCount = \App\Models\LeaveApplication::whereIn('status', ['pending', 'forwarded_to_manager', 'approved_by_manager'])->count();
                        } else {
                            $reporteeEmails = \App\Models\Employee::where('reporting_manager_payroll_id', $managerEmployee->payroll_id)->pluck('email')->toArray();
                            $reporteeIds = \App\Models\User::whereIn('email', $reporteeEmails)->pluck('id')->toArray();
                            $mgmtPendingCount = \App\Models\LeaveApplication::whereIn('user_id', $reporteeIds)->where('status', 'forwarded_to_manager')->count();
                        }
                    }
                @endphp

                
                 @if($canManageLeaves)
                <!-- Pending Leaves (Admin/HR/Manager only) -->
                <a href="{{ route('leaves.pending') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('leaves.pending') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-clock text-lg flex-shrink-0 {{ request()->routeIs('leaves.pending') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Pending Leaves</span>
                    @if($mgmtPendingCount > 0)
                        <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-badge">
                            {{ $mgmtPendingCount }}
                        </span>
                    @endif
                </a>
                @endif

                <!-- My Leaves (Always visible) -->
                <a href="{{ route('leaves.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('leaves.index') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-calendar-alt text-lg flex-shrink-0 {{ request()->routeIs('leaves.index') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Leaves</span>
                    @php
                        $userOwnPendingCount = auth()->user()->leaveApplications()->where('status', 'pending')->count();
                    @endphp
                    @if($userOwnPendingCount > 0)
                        <span class="ml-auto bg-blue-100 text-blue-600 text-xs px-2 py-1 rounded-full notification-badge">
                            {{ $userOwnPendingCount }}
                        </span>
                    @endif
                </a>
                <!-- Apply Leave -->
                <a href="{{ route('leaves.create') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('leaves.create') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-plus-circle text-lg flex-shrink-0 {{ request()->routeIs('leaves.create') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Apply Leave</span>
                </a>

                <!-- Apply Public Leave -->
                <a href="{{ route('public-holiday-applications.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('public-holiday-applications.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                    <i class="fas fa-calendar-check text-lg flex-shrink-0 {{ request()->routeIs('public-holiday-applications.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Apply Public Leave</span>
                </a>

                <!-- My Payslips -->
                <a href="{{ route('payslips.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('payslips.*') ? 'bg-emerald-50 text-emerald-700 border-r-4 border-emerald-600' : '' }}">
                    <i class="fas fa-file-invoice-dollar text-lg flex-shrink-0 {{ request()->routeIs('payslips.*') ? 'text-emerald-600' : 'text-gray-400' }} group-hover:text-emerald-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Payslips</span>
                </a>
                
                <!-- My Form 16 -->
                <a href="{{ route('form16.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('form16.*') ? 'bg-emerald-50 text-emerald-700 border-r-4 border-emerald-600' : '' }}">
                    <i class="fas fa-file-pdf text-lg flex-shrink-0 {{ request()->routeIs('form16.*') ? 'text-emerald-600' : 'text-gray-400' }} group-hover:text-emerald-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Form 16</span>
                </a>
                
                <!-- My Advances -->
                <a href="{{ route('advances.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('advances.*') ? 'bg-emerald-50 text-emerald-700 border-r-4 border-emerald-600' : '' }}">
                    <i class="fas fa-hand-holding-dollar text-lg flex-shrink-0 {{ request()->routeIs('advances.*') ? 'text-emerald-600' : 'text-gray-400' }} group-hover:text-emerald-600 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Advances</span>
                </a>
                
                  <!-- Expense Software -->
                <a href="https://expense.isarvait.com/" target="_blank" class="flex items-center px-4 py-3 text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 transition-colors group">
                    <i class="fas fa-file-invoice-dollar text-lg flex-shrink-0 text-emerald-600 group-hover:text-emerald-700 transition-colors"></i>
                    <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Expense Software</span>
                    <i class="fas fa-external-link-alt ml-auto text-xs text-emerald-400 group-hover:text-emerald-600 sidebar-text"></i>
                </a>
                
                @if (auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                    <!-- Admin Section -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Administration</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Public Holidays Menu -->
                            <div class="space-y-1">
                                <button data-submenu="holidays-submenu" onclick="toggleSubmenu('holidays-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('public-holidays.*') || request()->routeIs('holiday-department-configs.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-day text-lg flex-shrink-0 {{ request()->routeIs('public-holidays.*') || request()->routeIs('holiday-department-configs.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Public Holidays</span>
                                    </div>
                                    <i id="holidays-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ request()->routeIs('public-holidays.*') || request()->routeIs('holiday-department-configs.*') ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="holidays-submenu" class="submenu ml-4 space-y-1 {{ request()->routeIs('public-holidays.*') || request()->routeIs('holiday-department-configs.*') ? '' : 'hidden' }}" data-parent="holidays-submenu">
                                    <a href="{{ route('public-holidays.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('public-holidays.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ request()->routeIs('public-holidays.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Manage Holidays</span>
                                    </a>
                                    
                                    <a href="{{ route('holiday-department-configs.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('holiday-department-configs.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ request()->routeIs('holiday-department-configs.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Department Holidays</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Employees -->
                            <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('employees.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-user-tie text-lg flex-shrink-0 {{ request()->routeIs('employees.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Employees</span>
                            </a>

                            <!-- GPS Field Tracking -->
                            <a href="{{ route('admin.gps-tracking.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('admin.gps-tracking.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-route text-lg flex-shrink-0 {{ request()->routeIs('admin.gps-tracking.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">GPS Tracking</span>
                            </a>

                            <!-- Portal Punches -->
                            <a href="{{ route('self-attendance.admin-logs') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('self-attendance.admin-logs') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-fingerprint text-lg flex-shrink-0 {{ request()->routeIs('self-attendance.admin-logs') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Portal Punches</span>
                            </a>
                            
                            <!-- Leave Types -->
                            <a href="{{ route('leave-types.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('leave-types.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-clipboard-list text-lg flex-shrink-0 {{ request()->routeIs('leave-types.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Leave Types</span>
                            </a>
                            
                                <!-- Process Attendance -->
                            <a href="{{ route('admin.attendance.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('admin.attendance.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-calendar-week text-lg flex-shrink-0 {{ request()->routeIs('admin.attendance.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Process Attendance</span>
                            </a>

                            <!-- Shift Management Menu -->
                            <div class="space-y-1">
                                                                <button data-submenu="shift-management-submenu" onclick="toggleSubmenu('shift-management-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('shifts.*') || request()->routeIs('duty-rosters.*') || request()->routeIs('attendance.*') || request()->routeIs('overtime.*') || request()->routeIs('attendance-policies.*') || request()->routeIs('manual-punches.*') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-clock text-lg flex-shrink-0 {{ request()->routeIs('shifts.*') || request()->routeIs('duty-rosters.*') || request()->routeIs('attendance.*') || request()->routeIs('overtime.*') || request()->routeIs('attendance-policies.*') || request()->routeIs('manual-punches.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Scheduling</span>
                                    </div>
                                                                        <i id="shift-management-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ request()->routeIs('shifts.*') || request()->routeIs('duty-rosters.*') || request()->routeIs('attendance.*') || request()->routeIs('overtime.*') || request()->routeIs('attendance-policies.*') || request()->routeIs('manual-punches.*') ? 'rotate-180' : '' }}"></i>
                                </button>

                                                                <div id="shift-management-submenu" class="submenu ml-4 space-y-1 {{ request()->routeIs('shifts.*') || request()->routeIs('duty-rosters.*') || request()->routeIs('attendance.*') || request()->routeIs('overtime.*') || request()->routeIs('attendance-policies.*') || request()->routeIs('manual-punches.*') ? '' : 'hidden' }}" data-parent="shift-management-submenu">
                                    <a href="{{ route('shifts.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('shifts.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-clock text-xs flex-shrink-0 {{ request()->routeIs('shifts.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Shift Master</span>
                                    </a>

                                    <a href="{{ route('duty-rosters.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('duty-rosters.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-calendar-alt text-xs flex-shrink-0 {{ request()->routeIs('duty-rosters.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Duty Roster</span>
                                    </a>

                                    <a href="{{ route('attendance.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('attendance.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-fingerprint text-xs flex-shrink-0 {{ request()->routeIs('attendance.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Biometric Attendance</span>
                                    </a>

                                    <a href="{{ route('overtime.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('overtime.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-clock text-xs flex-shrink-0 {{ request()->routeIs('overtime.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Overtime Management</span>
                                    </a>

                                    <a href="{{ route('attendance-policies.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('attendance-policies.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-cog text-xs flex-shrink-0 {{ request()->routeIs('attendance-policies.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Biometric Policy</span>
                                    </a>

                                    <a href="{{ route('manual-punches.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('manual-punches.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-user-clock text-xs flex-shrink-0 {{ request()->routeIs('manual-punches.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Manual Punch Entry</span>
                                    </a>

                                    <a href="{{ route('timestation.fetch.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('timestation.fetch.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-cloud-download-alt text-xs flex-shrink-0 {{ request()->routeIs('timestation.fetch.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Fetch from TimeStation</span>
                                    </a>

                                    <a href="{{ route('attendance-rules.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('attendance-rules.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-gavel text-xs flex-shrink-0 {{ request()->routeIs('attendance-rules.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Long-Shift Rules</span>
                                    </a>
                                </div>
                            </div>

                         
                            
                            <!-- API Sync Menu -->
                            <div class="space-y-1">
                              <button data-submenu="sync-submenu" onclick="toggleSubmenu('sync-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('admin.departments.sync*') || request()->routeIs('admin.employee-sync*') || request()->routeIs('admin.api-sync.test') ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-sync-alt text-lg flex-shrink-0 {{ request()->routeIs('admin.department-sync*') || request()->routeIs('admin.employee-sync*') || request()->routeIs('admin.api-sync.test') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ml-3 font-medium sidebar-text whitespace-nowrap">API Sync</span>
                                    </div>
                                    <i id="sync-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ request()->routeIs('admin.department-sync*') || request()->routeIs('admin.employee-sync*') || request()->routeIs('admin.api-sync.test') ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="sync-submenu" class="submenu ml-4 space-y-1 {{ request()->routeIs('admin.department-sync*') || request()->routeIs('admin.employee-sync*') || request()->routeIs('admin.api-sync.test') ? '' : 'hidden' }}" data-parent="sync-submenu">
                                    <a href="{{ route('admin.department-sync') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('admin.department-sync*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-building text-xs flex-shrink-0 {{ request()->routeIs('admin.department-sync*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Department Sync</span>
                                    </a>
                                    
                                    <a href="{{ route('admin.employee-sync') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('admin.employee-sync*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-users text-xs flex-shrink-0 {{ request()->routeIs('admin.employee-sync*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Employee Sync</span>
                                    </a>
                                    
                                    <a href="{{ route('timestation.mapping') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('timestation.*') ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-id-card-clip text-xs flex-shrink-0 {{ request()->routeIs('timestation.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors mr-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">TimeStation Mapping</span>
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Financial Year -->
                            <a href="{{ route('financial-years.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('financial-years.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-calendar-alt text-lg flex-shrink-0 {{ request()->routeIs('financial-years.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Financial Year</span>
                            </a>
                            
                            <!-- Reports -->
                            <a href="{{ route('reports.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('reports.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                                <i class="fas fa-chart-bar text-lg flex-shrink-0 {{ request()->routeIs('reports.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Reports</span>
                            </a>
                        </div>
                    </div>
                @endif
                
                @if (auth()->user()->isSuperAdmin())
                    <!-- Super Admin Section -->
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Super Admin</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Activity Logs -->
                            <a href="{{ route('activity-logs.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('activity-logs.*') ? 'bg-gradient-to-r from-red-50 to-pink-50 text-red-600 border-r-4 border-red-600' : '' }}">
                                <i class="fas fa-history text-lg flex-shrink-0 {{ request()->routeIs('activity-logs.*') ? 'text-red-600' : 'text-gray-400' }} group-hover:text-red-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Activity Logs</span>
                                <span class="ml-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-badge">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- Settings Section -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Settings</h3>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('profile.show') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('profile.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                            <i class="fas fa-user-cog text-lg flex-shrink-0 {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Profile Settings</span>
                        </a>

                        @if(config('posh.legacy_enabled'))
                            <a href="{{ route('compliance.posh.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('compliance.posh.*') ? 'bg-red-50 text-red-600 border-r-4 border-red-600' : '' }}">
                                <i class="fas fa-users-shield text-lg flex-shrink-0 {{ request()->routeIs('compliance.posh.*') ? 'text-red-600' : 'text-gray-400' }} group-hover:text-red-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">POSH Safety Portal <span class="text-xs text-amber-600">(Legacy)</span></span>
                            </a>
                        @else
                            <a href="{{ config('posh.workspace_url') }}/posh-access" target="_blank" rel="noopener" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group">
                                <i class="fas fa-shield-halved text-lg flex-shrink-0 text-gray-400 group-hover:text-indigo-700 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">{{ config('posh.product_name') }}</span>
                            </a>
                        @endif

                        <!-- Permission Management (Admin / Super Admin) -->
                        @if(auth()->user()->hasAdminAccess())
                        <a href="{{ route('permissions.manage') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('permissions.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                            <i class="fas fa-key text-lg flex-shrink-0 {{ request()->routeIs('permissions.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Permission</span>
                        </a>
                        @endif

                        <!-- Email Settings (Super Admin Only) -->
                        @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('email-settings.index') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ request()->routeIs('email-settings.*') ? 'bg-blue-50 text-blue-600 border-r-4 border-blue-600' : '' }}">
                            <i class="fas fa-envelope text-lg flex-shrink-0 {{ request()->routeIs('email-settings.*') ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Email Settings</span>
                        </a>
                        @endif

                        <!-- Back to Work Space Button -->
                        @php
                            $employee = \App\Models\Employee::where('payroll_id', auth()->user()->payroll_id)->first();
                            $showWorkspaceButton = !$employee || $employee->exclude_from_payroll != 1;
                        @endphp
                        @if($showWorkspaceButton)
                        <a href="{{ env('SSO_WORKSPACE_URL') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-yellow-50 hover:text-yellow-600 transition-colors group">
                            <i class="fas fa-th-large text-yellow-600 text-lg flex-shrink-0"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Back to Work Space</span>
                        </a>
                        @endif

                        <!-- <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-red-50 hover:text-red-600 transition-colors group">
                                <i class="fas fa-sign-out-alt text-lg flex-shrink-0 text-gray-400 group-hover:text-red-600 transition-colors"></i>
                                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Sign Out</span>
                            </button>
                        </form> -->
                    </div>
                </div>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-gray-200">
                <div class="user-info flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="ml-3 flex-1 sidebar-text">
                        <p class="text-sm font-medium text-gray-900 sidebar-text">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500 sidebar-text">{{ ucfirst(auth()->user()->role) }}</p>
                    </div>
                </div>
            </div>
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
        #sidebar.collapsed .logo .ml-3 {
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
        #sidebar.collapsed .user-info .ml-3 {
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
                cloned.classList.remove('ml-4');
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
                    cloned.classList.remove('ml-3', 'ml-auto', 'mr-3');
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