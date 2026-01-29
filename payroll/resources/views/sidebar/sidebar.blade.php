
<!-- Modern Sidebar - Bootstrap 4 -->
@php
    // Helper function to check if user is Super Admin or has specific permission
    $isSuperAdmin = Auth::user()->role_name === 'Super Admin';
    $canAccess = function($permission) use ($isSuperAdmin) {
        return $isSuperAdmin || Auth::user()->hasPermission($permission);
    };
    $canAccessAny = function($permissions) use ($isSuperAdmin) {
        if ($isSuperAdmin) return true;
        foreach ($permissions as $permission) {
            if (Auth::user()->hasPermission($permission)) return true;
        }
        return false;
    };
@endphp

<aside id="sidebar" class="modern-sidebar fixed-top d-flex flex-column bg-white border-right">
    <div class="d-flex flex-column h-100">
        <!-- Logo Section -->
        <div class="sidebar-header d-flex align-items-center justify-content-between p-3 bg-gradient-primary text-white">
            <div class="d-flex align-items-center">
                <div class="sidebar-logo-icon bg-white rounded d-flex align-items-center justify-content-center me-3">
                    <img src="{{ asset('assets/img/favicon.png') }}" alt="Logo" class="sidebar-logo-img">
                </div>
                <h5 class="mb-0 sidebar-text font-weight-bold">Payroll HRMS</h5>
            </div>
            
            <!-- Mobile Close Button -->
            <button class="btn btn-link text-white p-2 d-lg-none" onclick="toggleMobileSidebar()">
                <i class="fas fa-times"></i>
            </button>
            
            <!-- Desktop Collapse Toggle -->
            <button id="sidebar-toggle" class="btn btn-link text-white p-1 d-none d-lg-block" onclick="toggleSidebar()">
                <i id="sidebar-toggle-icon" class="fas fa-chevron-left"></i>
            </button>
        </div>
        
        <!-- Navigation -->
        <nav class="flex-fill p-3 sidebar-nav">
            <!-- Main Section -->
            <div class="nav-section">
                <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Main</h6>
                
                <!-- Dashboard -->
                <a href="{{ route('home') }}" class="nav-link {{ set_active(['home', 'em/dashboard']) ? 'active' : '' }}">
                    <i class="fas fa-home nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </div>

            <!-- User Management Section -->
            @if ($canAccess('userManagement'))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">User Management</h6>
                    
                    <!-- User Control -->
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('userManagement*') || request()->is('search/user/list*') || request()->is('users/sync*') || request()->is('activity/log*') || request()->is('activity/login/logout*') ? 'active' : '' }}" 
                                data-submenu="user-control-submenu" onclick="toggleSubmenu('user-control-submenu')">
                            <i class="fas fa-users nav-icon"></i>
                            <span class="nav-text">User Control</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="user-control-submenu" class="submenu {{ request()->is('userManagement*') || request()->is('search/user/list*') || request()->is('users/sync*') || request()->is('activity/log*') || request()->is('activity/login/logout*') ? '' : 'd-none' }}">
                            @if ($canAccess('userManagement'))
                            <a href="{{ route('userManagement') }}" class="submenu-link {{ request()->is('userManagement*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">All Users</span>
                            </a>
                            @endif
                            @if ($isSuperAdmin)
                            <a href="{{ route('users.sync') }}" class="submenu-link {{ request()->is('users/sync*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Sync Users</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Employees Section -->
            @if ($canAccessAny(['employees.index', 'employees.add_create', 'employees.edit_update']))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Employees</h6>
                    
                    <!-- Employees -->
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('employees*') || request()->is('form/department/manage*') || request()->is('form/designation/manage*') || request()->is('form/role/manage*') || request()->is('form/employee-status/manage*') || request()->is('form/document-type/manage*') || request()->is('form/location/manage*') ? 'active' : '' }}" 
                                data-submenu="employees-submenu" onclick="toggleSubmenu('employees-submenu')">
                            <i class="fas fa-user-tie nav-icon"></i>
                            <span class="nav-text">Employees</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="employees-submenu" class="submenu {{ request()->is('employees*') || request()->is('form/department/manage*') || request()->is('form/designation/manage*') || request()->is('form/role/manage*') || request()->is('form/employee-status/manage*') || request()->is('form/document-type/manage*') || request()->is('form/location/manage*') ? '' : 'd-none' }}">
                            @if ($canAccess('employees.index'))
                            <a href="{{ route('employees.index') }}" class="submenu-link {{ set_active(['employees']) || request()->is('employees/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">All Employees</span>
                            </a>
                            @endif

                            @if ($canAccess('employees.index'))
                            <a href="{{ route('exit-employees.index') }}" class="submenu-link {{ request()->is('exit-employees*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Exit Details</span>
                            </a>
                            @endif
                            
                            @if ($canAccess('employees.edit_update'))
                            <a href="{{ route('increments.index') }}" class="submenu-link {{ request()->is('increments*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Increments & Promotions</span>
                            </a>
                            @endif
                            
                            @if ($canAccess('department.index'))
                            <a href="{{ route('form/department/manage') }}" class="submenu-link {{ set_active(['form/department/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Departments</span>
                            </a>
                            @endif
                            @if ($canAccess('designation.index'))
                            <a href="{{ route('form/designation/manage') }}" class="submenu-link {{ set_active(['form/designation/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Designations</span>
                            </a>
                            @endif
                            @if ($canAccess('employee_role.view'))
                            <a href="{{ route('form/role/manage') }}" class="submenu-link {{ set_active(['form/role/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Roles</span>
                            </a>
                            @endif
                            @if ($canAccess('employee_status.view'))
                            <a href="{{ route('form/employee-status/manage') }}" class="submenu-link {{ set_active(['form/employee-status/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Employee Status</span>
                            </a>
                            @endif
                            @if ($canAccess('employee_doctype.view'))
                            <a href="{{ route('form/document-type/manage') }}" class="submenu-link {{ set_active(['form/document-type/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Document Types</span>
                            </a>
                            @endif
                            @if ($canAccess('designation.index'))
                            <a href="{{ route('form/location/manage') }}" class="submenu-link {{ set_active(['form/location/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Location</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- Communication Section -->
            @if ($isSuperAdmin || $canAccess('notifications.manage') || $canAccess('userManagement'))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Communication</h6>
                    
                    <!-- Manual Notifications -->
                    <a href="{{ route('manual-notifications.index') }}" class="nav-link {{ request()->is('manual-notifications*') ? 'active' : '' }}">
                        <i class="fas fa-bell nav-icon"></i>
                        <span class="nav-text">Notifications</span>
                    </a>
                </div>
            @endif

            <!-- HR Section -->
            @if ($canAccessAny(['payroll.index', 'payslip.view', 'master.statutory_components.view', 'master.salary_components.view', 'ot_incentive.view']))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">HR Management</h6>
                    
                    <!-- Payroll -->
                    @if ($canAccessAny(['payroll.index', 'payslip.view', 'master.statutory_components.view', 'master.salary_components.view']))
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('payroll/*') || request()->is('payslip/employee-list*') || request()->is('form/statutory-component/manage*') || request()->is('form/salary-component/manage*') ? 'active' : '' }}" 
                                data-submenu="payroll-submenu" onclick="toggleSubmenu('payroll-submenu')">
                            <i class="fas fa-rupee-sign nav-icon"></i>
                            <span class="nav-text">Payroll</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="payroll-submenu" class="submenu {{ request()->is('payroll/*') || request()->is('payslip/employee-list*') || request()->is('form/statutory-component/manage*') || request()->is('form/salary-component/manage*') ? '' : 'd-none' }}">
                            @if ($canAccess('payroll.index'))
                            <a href="{{ route('payroll.index') }}" class="submenu-link {{ set_active(['payroll']) || request()->is('payroll/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Salary Process</span>
                            </a>
                            @endif
                            @if ($canAccess('payslip.view'))
                            <a href="{{ route('payroll/employee-list') }}" class="submenu-link {{ set_active(['payslip/employee-list']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Payslips</span>
                            </a>
                            @endif
                            @if ($canAccess('master.statutory_components.view'))
                            <a href="{{ route('form/statutory-component/manage') }}" class="submenu-link {{ set_active(['form/statutory-component/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Statutory Components</span>
                            </a>
                            @endif
                            @if ($canAccess('master.salary_components.view'))
                            <a href="{{ route('form/salary-component/manage') }}" class="submenu-link {{ set_active(['form/salary-component/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Salary Components</span>
                            </a>
                            @endif
                            @if ($canAccess('payroll.index'))
                            <a href="{{ route('hold-salary.index') }}" class="submenu-link {{ set_active(['hold-salary']) || request()->is('payroll/hold-salary*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Hold Salary</span>
                            </a>
                            @endif
                            @if ($canAccess('payroll.index'))
                            <a href="{{ route('hold-salary.process') }}" class="submenu-link {{ request()->routeIs('hold-salary.process') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Process & Release</span>
                            </a>
                            @endif
                            <!-- Salary Settings -->
                             @if ($canAccess('settings.view'))
                            <a href="{{ route('salary/settings/page') }}" class="submenu-link {{ set_active(['salary/settings/page']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Salary Settings</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- OT and Incentives -->
                    @if ($canAccess('ot_incentive.view'))
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('ot-incentive*') ? 'active' : '' }}" 
                                data-submenu="ot-incentive-submenu" onclick="toggleSubmenu('ot-incentive-submenu')">
                            <i class="fas fa-money-bill-wave nav-icon"></i>
                            <span class="nav-text">OT & Incentives</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="ot-incentive-submenu" class="submenu {{ request()->is('ot-incentive*') ? '' : 'd-none' }}">
                            <a href="{{ route('ot-incentive.index') }}" class="submenu-link {{ set_active(['ot-incentive']) || request()->is('ot-incentive/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Process OT & Incentive</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Reports Section -->
            @if ($canAccessAny(['payroll_reports.view', 'overtime_reports.view', 'incentive_reports.view', 'combined_reports.view', 'comparision_reports.view', 'payroll.analytics.reports']))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Reports</h6>
                    
                    <!-- Payroll Reports -->
                    @if ($canAccessAny(['payroll_reports.view', 'overtime_reports.view', 'incentive_reports.view', 'combined_reports.view', 'comparision_reports.view']))
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('payroll-reports*') || request()->is('combined-reports*') || request()->is('overtime-reports*') || request()->is('incentive-reports*') || request()->is('comparison-reports*') ? 'active' : '' }}" 
                                data-submenu="reports-submenu" onclick="toggleSubmenu('reports-submenu')">
                            <i class="fas fa-chart-bar nav-icon"></i>
                            <span class="nav-text">Payroll Reports</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="reports-submenu" class="submenu {{ request()->is('payroll-reports*') || request()->is('combined-reports*') || request()->is('overtime-reports*') || request()->is('incentive-reports*') || request()->is('comparison-reports*') ? '' : 'd-none' }}">
                            @if ($canAccess('payroll_reports.view'))
                            <a href="{{ route('payroll.reports.index') }}" class="submenu-link {{ set_active(['payroll-reports']) || request()->is('payroll-reports/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Payroll Report</span>
                            </a>
                            @endif
                            @if ($canAccess('combined_reports.view'))
                            <a href="{{ route('combined.reports.index') }}" class="submenu-link {{ set_active(['combined-reports']) || request()->is('combined-reports/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Combined Reports</span>
                            </a>
                            @endif
                            @if ($canAccess('overtime_reports.view'))
                            <a href="{{ route('overtime.reports.index') }}" class="submenu-link {{ set_active(['overtime-reports']) || request()->is('overtime-reports/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Overtime Reports</span>
                            </a>
                            @endif
                            @if ($canAccess('incentive_reports.view'))
                            <a href="{{ route('incentive.reports.index') }}" class="submenu-link {{ set_active(['incentive-reports']) || request()->is('incentive-reports/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Incentive Reports</span>
                            </a>
                            @endif
                            @if ($canAccess('comparision_reports.view'))
                            <a href="{{ route('payroll.comparison.index') }}" class="submenu-link {{ set_active(['comparison-reports']) || request()->is('comparison-reports/*') ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Comparison Reports</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <!-- Analytical Reports -->
                    @if ($canAccess('payroll.analytics.reports'))
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('reports/payroll-analytics*') || request()->is('reports/payroll-comparison*') ? 'active' : '' }}" 
                                data-submenu="analytics-submenu" onclick="toggleSubmenu('analytics-submenu')">
                            <i class="fas fa-chart-line nav-icon"></i>
                            <span class="nav-text">Analytics</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="analytics-submenu" class="submenu {{ request()->is('reports/payroll-analytics*') || request()->is('reports/payroll-comparison*') ? '' : 'd-none' }}">
                            <a href="{{ route('reports.payroll.analytics') }}" class="submenu-link {{ set_active(['reports/payroll-analytics']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Payroll Analytics</span>
                            </a>
                            <a href="{{ route('reports.payroll.comparison') }}" class="submenu-link {{ set_active(['reports/payroll-comparison']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Analytical Comparison</span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Financial Year Management -->
            @if ($canAccess('financial_years.view'))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Financial</h6>
                    
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('financial-years*') ? 'active' : '' }}" 
                                data-submenu="financial-submenu" onclick="toggleSubmenu('financial-submenu')">
                            <i class="fas fa-calendar-alt nav-icon"></i>
                            <span class="nav-text">Financial Year</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="financial-submenu" class="submenu {{ request()->is('financial-years*') ? '' : 'd-none' }}">
                            <a href="{{ route('financial-years.index') }}" class="submenu-link {{ set_active(['financial-years']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Manage Financial Years</span>
                            </a>
                            @if ($canAccess('financial_years.add'))
                            <a href="{{ route('financial-years.create') }}" class="submenu-link {{ set_active(['financial-years/create']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Create New FY</span>
                            </a>
                            @endif
                            @if ($canAccess('financial_years_setting.view'))
                            <a href="{{ route('financial-years.settings') }}" class="submenu-link {{ set_active(['financial-years/settings/index']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">FY Settings</span>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            <!-- System Settings -->
            @if ($canAccessAny(['activityLogs.view', 'company_settings.view', 'settings.view']))
                <div class="nav-section">
                    <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">System Settings</h6>
                    
                    @if ($canAccess('activityLogs.view'))
                    <a href="{{ route('activity-logs') }}" class="nav-link {{ set_active(['activity-logs']) ? 'active' : '' }}">
                        <i class="fas fa-history nav-icon"></i>
                        <span class="nav-text">Activity Logs</span>
                    </a>
                    @endif
                    
                    @if ($canAccessAny(['company_settings.view', 'settings.view']))
                    <div class="nav-submenu">
                        <button class="nav-link nav-toggle {{ request()->is('company/settings/page*') || request()->is('master-settings*') || request()->is('permissions/manage*') || request()->is('localization/page*') || request()->is('salary/settings/page*') || request()->is('email/settings/page*') ? 'active' : '' }}" 
                                data-submenu="settings-submenu" onclick="toggleSubmenu('settings-submenu')">
                            <i class="fas fa-cog nav-icon"></i>
                            <span class="nav-text">Settings</span>
                            <i class="fas fa-chevron-down nav-arrow"></i>
                        </button>
                        
                        <div id="settings-submenu" class="submenu {{ request()->is('company/settings/page*') || request()->is('master-settings*') || request()->is('permissions/manage*') || request()->is('localization/page*') || request()->is('salary/settings/page*') || request()->is('email/settings/page*') ? '' : 'd-none' }}">
                            @if ($canAccess('company_settings.view'))
                            <a href="{{ route('company/settings/page') }}" class="submenu-link {{ set_active(['company/settings/page']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Company Settings</span>
                            </a>
                            @endif
                            @if ($canAccess('settings.view'))
                            <a href="{{ route('settings.index') }}" class="submenu-link {{ set_active(['master-settings']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Master Settings</span>
                            </a>
                            @endif
                            @if ($canAccess('settings.view'))
                            <a href="{{ route('permissions.manage') }}" class="submenu-link {{ set_active(['permissions/manage']) ? 'active' : '' }}">
                                <i class="fas fa-circle submenu-icon"></i>
                                <span class="nav-text">Permission Management</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Workspace -->
            <div class="nav-section">
                <h6 class="nav-section-title text-muted text-uppercase font-weight-bold small mb-2">Workspace</h6>
                
                <a href="{{ env('SSO_WORKSPACE_URL') }}" class="nav-link">
                    <i class="fas fa-th-large nav-icon text-warning"></i>
                    <span class="nav-text">Back to Work Space</span>
                </a>
            </div>
        </nav>
        
        <!-- User Info -->
        <div class="sidebar-footer p-3 border-top">
            <div class="user-info d-flex align-items-center">
                <div class="user-avatar bg-gradient-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                    <span class="text-white font-weight-bold">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
                <div class="user-details flex-fill sidebar-text">
                    <p class="mb-1 font-weight-medium">{{ auth()->user()->name }}</p>
                    <small class="text-muted">{{ ucfirst(auth()->user()->role_name ?? auth()->user()->role) }}</small>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- Backdrop for Mobile Menu -->
<div id="sidebar-backdrop" class="sidebar-backdrop d-none"></div>

<!-- Custom Styles -->
<style>
/* Modern Sidebar Styles - Bootstrap 4 */
.modern-sidebar {
    width: 280px;
    height: 100vh;
    z-index: 1050;
    transform: translateX(-100%);
    transition: transform 0.3s ease, width 0.3s ease;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    top: 0;
    left: 0;
    overflow-x: hidden;
    overflow-y: auto;
}

.modern-sidebar.show {
    transform: translateX(0);
}

@media (min-width: 992px) {
    .modern-sidebar {
        position: fixed !important;
        transform: translateX(0);
    }
    .modern-sidebar.collapsed {
        width: 70px;
    }
}

/* Adjust existing header and layout */
@media (min-width: 992px) {
    .header {
        margin-left: 280px;
        transition: margin-left 0.3s ease;
    }
    
    .header.sidebar-collapsed {
        margin-left: 70px;
    }
}

/* Remove margin on mobile */
@media (max-width: 991.98px) {
    .header,
    #main-header {
        margin-left: 0 !important;
        left: 0 !important;
        width: 100% !important;
    }
}

/* Override existing sidebar styles */
.sidebar {
    display: none !important;
}

/* Logo Section */
.sidebar-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 70px;
}

.sidebar-logo-icon {
    width: 35px;
    height: 35px;
    font-size: 18px;
}

.sidebar-logo-img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    padding: 2px;
}

/* Navigation */
.sidebar-nav {
    overflow-y: auto;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}

.sidebar-nav::-webkit-scrollbar {
    width: 4px;
}

.sidebar-nav::-webkit-scrollbar-track {
    background: transparent;
}

.sidebar-nav::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.sidebar-nav::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Navigation Sections */
.nav-section {
    margin-bottom: 1rem;
    border-bottom: solid 1px #e5e7eb;
}

.nav-section-title {
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    padding-left: 1rem;
}

/* Navigation Links */
.nav-link,
.nav-toggle {
    display: flex;
    align-items: center;
    padding: 0.75rem 1rem;
    color: #4a5568;
    text-decoration: none;
    border-radius: 0.5rem;
    margin-bottom: 0.25rem;
    transition: all 0.2s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.nav-link:hover,
.nav-toggle:hover {
    background-color: #f7fafc;
    color: #2d3748;
    text-decoration: none;
}

.nav-link.active,
.nav-toggle.active {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    color: #1976d2 !important;
    border-left: 4px solid #1976d2 !important;
    font-weight: 600;
}

.nav-link.active .nav-icon,
.nav-toggle.active .nav-icon {
    color: #1976d2 !important;
}

.nav-link.active:hover,
.nav-toggle.active:hover {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    color: #1976d2 !important;
}

/* Navigation Icons */
.nav-icon {
    width: 20px;
    font-size: 1.1rem;
    color: #a0aec0;
    transition: color 0.2s ease;
    flex-shrink: 0;
    display: inline-block;
}

.nav-text {
    margin-left: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
}

.nav-arrow {
    margin-left: auto;
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

.nav-toggle.active .nav-arrow {
    transform: rotate(180deg);
}

/* Ensure active parent menus are clearly visible */
.nav-toggle.active {
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
    color: #1976d2 !important;
    border-left: 4px solid #1976d2 !important;
    font-weight: 600;
}

.nav-toggle.active .nav-icon {
    color: #1976d2 !important;
}

/* Submenus */
.nav-submenu {
    margin-bottom: 0.25rem;
}

.submenu {
    margin-left: 0.5rem;
    padding-left: 0.5rem;
    border-left: 2px solid #e2e8f0;
}

.submenu-link {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: #718096;
    text-decoration: none;
    border-radius: 0.375rem;
    margin-bottom: 0.125rem;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.submenu-link:hover {
    background-color: #f7fafc;
    color: #4a5568;
    text-decoration: none;
}

.submenu-link.active {
    background-color: #e3f2fd !important;
    color: #1976d2 !important;
    border-left: 3px solid #1976d2 !important;
    font-weight: 600;
}

.submenu-link.active:hover {
    background-color: #e3f2fd !important;
    color: #1976d2 !important;
}

.submenu-icon {
    width: 12px;
    font-size: 0.5rem;
    margin-right: 0.75rem;
    color: #a0aec0;
}

.submenu-link.active .submenu-icon {
    color: #1976d2;
}

/* User Info */
.sidebar-footer {
    background-color: #f8f9fa;
}

.user-avatar {
    width: 40px;
    height: 40px;
    font-size: 0.875rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.user-details p {
    font-size: 0.875rem;
    color: #2d3748;
}

.user-details small {
    font-size: 0.75rem;
}

/* Mobile Backdrop */
.sidebar-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
}

/* Main Content Adjustment */
.page-wrapper,
.main-content {
    margin-left: 280px;
    transition: margin-left 0.3s ease;
}

.page-wrapper.sidebar-collapsed,
.main-content.sidebar-collapsed {
    margin-left: 70px;
}

@media (max-width: 991.98px) {
    .page-wrapper,
    .main-content,
    .page-wrapper.sidebar-collapsed,
    .main-content.sidebar-collapsed {
        margin-left: 0;
    }
}

/* Collapsed State - Icons Only Like Attendance System */
@media (min-width: 992px) {
    .modern-sidebar.collapsed {
        width: 70px !important;
        overflow: hidden;
    }
    
    .modern-sidebar.collapsed .sidebar-text,
    .modern-sidebar.collapsed .nav-section-title,
    .modern-sidebar.collapsed .nav-arrow,
    .modern-sidebar.collapsed .submenu {
        display: none !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    .modern-sidebar.collapsed .sidebar-header {
        justify-content: center;
        padding: 1rem 0.25rem;
        min-height: 70px;
    }
    
    .modern-sidebar.collapsed .sidebar-header .sidebar-logo-icon {
        margin-right: 0 !important;
        width: 35px;
        height: 35px;
        display: flex !important;
        align-items: center;
        justify-content: center;
    }
    
    .modern-sidebar.collapsed .nav-link,
    .modern-sidebar.collapsed .nav-toggle {
        display: flex !important;
        align-items: center;
        justify-content: center;
        padding: 0.75rem 0.25rem !important;
        margin-bottom: 0.25rem;
        position: relative;
        width: 100%;
        min-height: 50px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: clip;
        border: none;
        background: transparent;
    }
    
    .modern-sidebar.collapsed .nav-link:focus,
    .modern-sidebar.collapsed .nav-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(25, 118, 210, 0.3);
    }
    
    .modern-sidebar.collapsed .nav-icon {
        margin: 0 !important;
        margin-left: 0 !important;
        margin-right: 0 !important;
        font-size: 1.25rem !important;
        display: block !important;
        flex-shrink: 0;
        width: 20px;
        text-align: center;
        color: #a0aec0 !important;
    }
    
    /* Ensure nav-text is completely hidden and doesn't affect layout */
    .modern-sidebar.collapsed .nav-text {
        position: absolute !important;
        left: -9999px !important;
        width: 0 !important;
        height: 0 !important;
        overflow: hidden !important;
        visibility: hidden !important;
        opacity: 0 !important;
    }
    
    .modern-sidebar.collapsed .user-info {
        justify-content: center;
        padding: 1rem 0.25rem;
    }
    
    .modern-sidebar.collapsed .user-avatar {
        margin-right: 0 !important;
        width: 35px;
        height: 35px;
        display: flex !important;
    }
    
    .modern-sidebar.collapsed .user-details {
        display: none !important;
    }
    
    /* Hide all nav-section containers when collapsed */
    .modern-sidebar.collapsed .nav-section {
        margin-bottom: 0.5rem;
    }
    
    /* Enhanced visual feedback for collapsed icons */
    .modern-sidebar.collapsed .nav-link:hover,
    .modern-sidebar.collapsed .nav-toggle:hover {
        background-color: #e3f2fd;
        transform: translateX(2px);
        transition: all 0.2s ease;
    }
    
    /* Stable hover area for better UX */
    .modern-sidebar.collapsed .nav-link,
    .modern-sidebar.collapsed .nav-toggle {
        position: relative;
    }
    
    .modern-sidebar.collapsed .nav-link::before,
    .modern-sidebar.collapsed .nav-toggle::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: transparent;
        z-index: 1;
    }
    
    .modern-sidebar.collapsed .nav-link.active,
    .modern-sidebar.collapsed .nav-toggle.active {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%) !important;
        color: white !important;
        border-left: none;
        border-radius: 0.375rem;
    }
    
    .modern-sidebar.collapsed .nav-link.active .nav-icon,
    .modern-sidebar.collapsed .nav-toggle.active .nav-icon {
        color: white !important;
    }
    
    /* Special handling for parent menu items when child is active */
    .modern-sidebar.collapsed .nav-toggle.active {
        background: linear-gradient(135deg, #1976d2 0%, #1565c0 100%) !important;
    }
    
    /* Ensure no horizontal scrolling */
    .modern-sidebar.collapsed {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    
    .modern-sidebar.collapsed::-webkit-scrollbar {
        display: none;
    }
    
    .modern-sidebar.collapsed .sidebar-nav {
        padding: 0.75rem 0.25rem !important;
        overflow-x: hidden;
        width: 100%;
    }
    
    /* Force all child elements to not exceed sidebar width */
    .modern-sidebar.collapsed * {
        max-width: 70px;
        box-sizing: border-box;
    }
    
    /* Exception for icons and essential elements */
    .modern-sidebar.collapsed .nav-icon,
    .modern-sidebar.collapsed .sidebar-logo-icon,
    .modern-sidebar.collapsed .user-avatar {
        max-width: none;
    }
}



/* Floating popover for collapsed sidebar - like attendance system */
.floating-submenu {
    position: absolute;
    top: 0;
    left: 0;
    min-width: 12rem;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.08);
    border-radius: 0.5rem;
    z-index: 1060;
    display: none;
    padding: 0.5rem;
    opacity: 0;
    transform: translateX(-5px);
    transition: opacity 0.2s ease, transform 0.2s ease;
    pointer-events: none;
}

.floating-submenu.show {
    display: block;
    opacity: 1;
    transform: translateX(0);
    pointer-events: auto;
}

.floating-submenu a {
    display: flex;
    align-items: center;
    padding: 0.5rem 0.75rem;
    color: #374151;
    border-radius: 0.375rem;
    text-decoration: none;
    font-size: 0.875rem;
}

.floating-submenu a:hover {
    background: #f3f4f6;
    color: #1f2937;
    text-decoration: none;
}

.floating-submenu a.active {
    background: #e3f2fd;
    color: #1976d2;
    font-weight: 600;
}

.floating-submenu a.active .submenu-icon {
    color: #1976d2;
}

/* Single item floating tooltip */
.floating-tooltip {
    position: absolute;
    left: 70px;
    top: 50%;
    transform: translateY(-50%) translateX(-5px);
    background: #2d3748;
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    white-space: nowrap;
    z-index: 1060;
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    pointer-events: none;
}

.floating-tooltip::before {
    content: '';
    position: absolute;
    left: -4px;
    top: 50%;
    transform: translateY(-50%);
    border: 4px solid transparent;
    border-right-color: #2d3748;
}

.floating-tooltip.show {
    opacity: 1;
    visibility: visible;
    transform: translateY(-50%) translateX(0);
}

/* Responsive */
@media (max-width: 991.98px) {
    .modern-sidebar {
        position: fixed !important;
        top: 0;
        left: 0;
        z-index: 1050 !important;
        width: 280px !important;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        height: 100vh;
        overflow-y: auto;
    }
    
    .modern-sidebar.show {
        transform: translateX(0) !important;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    body.sidebar-open {
        overflow: hidden;
    }
    
    /* Ensure backdrop shows properly on mobile */
    .sidebar-backdrop:not(.d-none) {
        display: block !important;
        z-index: 1040 !important;
    }
    
    /* Hide desktop toggle button on mobile */
    #sidebar-toggle {
        display: none !important;
    }
}

/* Bootstrap 4 Utility Classes for Gradient */
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

/* Hide old sidebar overlay from app.js to prevent conflicts */
.sidebar-overlay {
    display: none !important;
}
</style>

<!-- JavaScript -->
<script>
function toggleSubmenu(submenuId) {
    const submenu = document.getElementById(submenuId);
    const button = document.querySelector(`[data-submenu="${submenuId}"]`);
    const arrow = button ? button.querySelector('.nav-arrow') : null;
    
    if (submenu.classList.contains('d-none')) {
        submenu.classList.remove('d-none');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        submenu.classList.add('d-none');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
}

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const toggleIcon = document.getElementById('sidebar-toggle-icon');
    
    // Target multiple possible main content containers
    const pageWrapper = document.querySelector('.page-wrapper');
    const mainContent = document.querySelector('.main-content');
    const contentWrapper = document.querySelector('.content-wrapper');
    const header = document.querySelector('.header');
    
    sidebar.classList.toggle('collapsed');
    
    // Adjust main content margin for all possible containers
    if (sidebar.classList.contains('collapsed')) {        
        // Close all submenus when collapsing, but keep parent buttons active if they have active children
        document.querySelectorAll('.submenu').forEach(submenu => {
            submenu.classList.add('d-none');
        });
        
        // Only remove active class from toggles that don't have active submenu items
        document.querySelectorAll('.nav-toggle').forEach(toggle => {
            const submenuId = toggle.getAttribute('data-submenu');
            if (submenuId) {
                const submenu = document.getElementById(submenuId);
                const hasActiveChild = submenu && submenu.querySelector('.submenu-link.active');
                
                // Keep active class if the toggle itself has active class from server-side rendering
                // or if it has an active child
                if (!hasActiveChild && !toggle.classList.contains('active')) {
                    toggle.classList.remove('active');
                }
            }
        });
        
        toggleIcon.classList.replace('fa-chevron-left', 'fa-chevron-right');
        
        // Apply collapsed class to all main content containers and header
        if (pageWrapper) pageWrapper.classList.add('sidebar-collapsed');
        if (mainContent) mainContent.classList.add('sidebar-collapsed');
        if (contentWrapper) contentWrapper.classList.add('sidebar-collapsed');
        if (header) header.classList.add('sidebar-collapsed');
        
        // Initialize floating menus for collapsed state
        initializeFloatingMenus();
    } else {
        toggleIcon.classList.replace('fa-chevron-right', 'fa-chevron-left');
        
        // Remove collapsed class from all main content containers and header
        if (pageWrapper) pageWrapper.classList.remove('sidebar-collapsed');
        if (mainContent) mainContent.classList.remove('sidebar-collapsed');
        if (contentWrapper) contentWrapper.classList.remove('sidebar-collapsed');
        if (header) header.classList.remove('sidebar-collapsed');
        
        // Re-initialize active submenus when expanding
        initializeActiveSubmenus();
        
        // Hide floating menus when expanded
        const floating = document.getElementById('floating-menu-container');
        const tooltip = document.getElementById('floating-tooltip');
        if (floating) floating.classList.remove('show');
        if (tooltip) tooltip.remove();
    }
}

function toggleMobileSidebar() {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');
    const body = document.body;
    const html = document.documentElement;
    const wrapper = document.querySelector('.main-wrapper');
    
    // Remove old sidebar overlay classes that conflict
    const oldOverlay = document.querySelector('.sidebar-overlay');
    if (oldOverlay) {
        oldOverlay.classList.remove('opened');
    }
    html.classList.remove('menu-opened');
    if (wrapper) {
        wrapper.classList.remove('slide-nav');
    }
    
    if (sidebar.classList.contains('show')) {
        sidebar.classList.remove('show');
        backdrop.classList.add('d-none');
        body.classList.remove('sidebar-open');
    } else {
        sidebar.classList.add('show');
        backdrop.classList.remove('d-none');
        body.classList.add('sidebar-open');
    }
    
    // Prevent default behavior
    return false;
}

// Close sidebar when clicking backdrop
document.getElementById('sidebar-backdrop').addEventListener('click', toggleMobileSidebar);

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth >= 992) {
        const sidebar = document.getElementById('sidebar');
        const backdrop = document.getElementById('sidebar-backdrop');
        const body = document.body;
        const html = document.documentElement;
        const wrapper = document.querySelector('.main-wrapper');
        
        sidebar.classList.remove('show');
        backdrop.classList.add('d-none');
        body.classList.remove('sidebar-open');
        
        // Clean up old overlay classes
        const oldOverlay = document.querySelector('.sidebar-overlay');
        if (oldOverlay) {
            oldOverlay.classList.remove('opened');
        }
        html.classList.remove('menu-opened');
        if (wrapper) {
            wrapper.classList.remove('slide-nav');
        }
    }
});

// Floating popover functionality - like attendance system
function initializeFloatingMenus() {
    const sidebar = document.getElementById('sidebar');
    
    // Create floating container if it doesn't exist
    let floating = document.getElementById('floating-menu-container');
    if (!floating) {
        floating = document.createElement('div');
        floating.className = 'floating-submenu';
        floating.id = 'floating-menu-container';
        document.body.appendChild(floating);
    }

    // Helper to position floating relative to element
    function positionFloating(triggerEl) {
        const rect = triggerEl.getBoundingClientRect();
        floating.style.top = (rect.top + window.scrollY) + 'px';
        floating.style.left = (rect.right + 8 + window.scrollX) + 'px';
    }

    // Show floating submenu with content from the real submenu
    function showFloating(submenuId, triggerEl) {
        const submenu = document.getElementById(submenuId);
        if (!submenu) return;

        // Clone submenu items (links)
        const cloned = submenu.cloneNode(true);
        const anchors = cloned.querySelectorAll('a');

        floating.innerHTML = '';
        anchors.forEach(a => {
            const node = a.cloneNode(true);
            node.classList.remove('submenu-link');
            // Preserve active state in floating menu
            if (a.classList.contains('active')) {
                node.classList.add('active');
            }
            floating.appendChild(node);
        });

        positionFloating(triggerEl);
        floating.classList.add('show');
    }

    // Show floating tooltip for single menu items
    function showFloatingTooltip(text, triggerEl) {
        // Remove existing tooltip
        const existingTooltip = document.getElementById('floating-tooltip');
        if (existingTooltip) existingTooltip.remove();

        const tooltip = document.createElement('div');
        tooltip.className = 'floating-tooltip show';
        tooltip.id = 'floating-tooltip';
        tooltip.textContent = text;
        
        const rect = triggerEl.getBoundingClientRect();
        tooltip.style.top = (rect.top + window.scrollY) + 'px';
        tooltip.style.left = (rect.right + 8 + window.scrollX) + 'px';
        
        document.body.appendChild(tooltip);
    }

    function hideFloating() {
        floating.classList.remove('show');
        const tooltip = document.getElementById('floating-tooltip');
        if (tooltip) tooltip.remove();
    }

    // Enhanced hover listeners with better stability
    let hoverTimer = null;
    let currentTrigger = null;

    // Attach hover listeners to menu buttons that have data-submenu
    document.querySelectorAll('[data-submenu]').forEach(function(btn) {
        const submenuId = btn.getAttribute('data-submenu');

        btn.addEventListener('mouseenter', function(e) {
            const isCollapsed = sidebar.classList.contains('collapsed');
            const isDesktop = window.matchMedia('(min-width: 992px)').matches;
            
            if (isCollapsed && isDesktop) {
                // Clear any existing timer
                if (hoverTimer) {
                    clearTimeout(hoverTimer);
                    hoverTimer = null;
                }
                
                currentTrigger = btn;
                
                // Small delay to prevent flickering
                hoverTimer = setTimeout(function() {
                    if (currentTrigger === btn) {
                        showFloating(submenuId, btn);
                    }
                }, 100);
            }
        });

        btn.addEventListener('mouseleave', function(e) {
            // Clear the show timer if mouse leaves before showing
            if (hoverTimer) {
                clearTimeout(hoverTimer);
                hoverTimer = null;
            }
            
            // Set hide timer with longer delay
            hoverTimer = setTimeout(function() {
                if (!floating.matches(':hover') && !btn.matches(':hover')) {
                    hideFloating();
                    currentTrigger = null;
                }
            }, 300);
        });
    });

    // Attach hover listeners to single navigation links
    document.querySelectorAll('#sidebar .nav-link:not(.nav-toggle)').forEach(function(anchor) {
        // Skip anchors that are inside submenu containers
        if (anchor.closest('.submenu')) return;

        anchor.addEventListener('mouseenter', function() {
            const isCollapsed = sidebar.classList.contains('collapsed');
            const isDesktop = window.matchMedia('(min-width: 992px)').matches;
            if (!isCollapsed || !isDesktop) return;

            // Clear any existing timer
            if (hoverTimer) {
                clearTimeout(hoverTimer);
                hoverTimer = null;
            }

            const textElement = anchor.querySelector('.nav-text');
            if (textElement) {
                currentTrigger = anchor;
                
                // Show tooltip with small delay
                hoverTimer = setTimeout(function() {
                    if (currentTrigger === anchor) {
                        showFloatingTooltip(textElement.textContent.trim(), anchor);
                    }
                }, 100);
            }
        });

        anchor.addEventListener('mouseleave', function() {
            // Clear the show timer if mouse leaves before showing
            if (hoverTimer) {
                clearTimeout(hoverTimer);
                hoverTimer = null;
            }
            
            // Hide with delay
            hoverTimer = setTimeout(function() {
                if (!anchor.matches(':hover')) {
                    hideFloating();
                    currentTrigger = null;
                }
            }, 200);
        });
    });

    // Enhanced floating menu mouse handling
    floating.addEventListener('mouseenter', function() {
        // Clear hide timer when entering floating menu
        if (hoverTimer) {
            clearTimeout(hoverTimer);
            hoverTimer = null;
        }
    });

    floating.addEventListener('mouseleave', function() {
        // Hide immediately when leaving floating menu
        hoverTimer = setTimeout(function() {
            hideFloating();
            currentTrigger = null;
        }, 100);
    });
}

// Initialize main content class
function initializeMainContent() {
    // Ensure the page-wrapper exists and has proper transition
    const pageWrapper = document.querySelector('.page-wrapper');
    if (pageWrapper && !pageWrapper.style.transition) {
        pageWrapper.style.transition = 'margin-left 0.3s ease';
    }
    
    // Also handle other possible content containers
    const mainContent = document.querySelector('.main-content');
    const contentWrapper = document.querySelector('.content-wrapper');
    const header = document.querySelector('.header');
    
    if (mainContent && !mainContent.style.transition) {
        mainContent.style.transition = 'margin-left 0.3s ease';
    }
    
    if (contentWrapper && !contentWrapper.style.transition) {
        contentWrapper.style.transition = 'margin-left 0.3s ease';
    }
    
    if (header && !header.style.transition) {
        header.style.transition = 'margin-left 0.3s ease';
    }
}

// Auto-open active submenus on page load
function initializeActiveSubmenus() {
    // First, check if any submenu links are active and mark their parent toggle buttons
    document.querySelectorAll('.submenu-link.active').forEach(function(activeLink) {
        // Find the parent submenu
        const submenu = activeLink.closest('.submenu');
        if (submenu) {
            const submenuId = submenu.id;
            // Find the toggle button that controls this submenu
            const toggle = document.querySelector(`[data-submenu="${submenuId}"]`);
            if (toggle) {
                toggle.classList.add('active');
                submenu.classList.remove('d-none');
                
                // Rotate the arrow
                const arrow = toggle.querySelector('.nav-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
    
    // Then find all active nav-toggle buttons and open their submenus
    document.querySelectorAll('.nav-toggle.active').forEach(function(toggle) {
        const submenuId = toggle.getAttribute('data-submenu');
        if (submenuId) {
            const submenu = document.getElementById(submenuId);
            if (submenu && submenu.classList.contains('d-none')) {
                submenu.classList.remove('d-none');
                toggle.classList.add('active');
                
                // Also rotate the arrow
                const arrow = toggle.querySelector('.nav-arrow');
                if (arrow) {
                    arrow.style.transform = 'rotate(180deg)';
                }
            }
        }
    });
}

// Initialize - Show sidebar on desktop
document.addEventListener('DOMContentLoaded', function() {
    initializeMainContent();
    
    if (window.innerWidth >= 992) {
        document.getElementById('sidebar').classList.add('show');
    }
    
    // Initialize floating menu functionality
    initializeFloatingMenus();
    
    // Auto-open active submenus
    initializeActiveSubmenus();
    
    // Check if sidebar is collapsed by default and initialize floating menus
    const sidebar = document.getElementById('sidebar');
    if (sidebar.classList.contains('collapsed')) {
        initializeFloatingMenus();
    }
    
    // Clean up old sidebar overlay that conflicts with new sidebar
    const oldOverlay = document.querySelector('.sidebar-overlay');
    if (oldOverlay) {
        oldOverlay.remove(); // Remove it completely
    }
    
    // Prevent old app.js from interfering with our new sidebar
    const html = document.documentElement;
    const wrapper = document.querySelector('.main-wrapper');
    html.classList.remove('menu-opened');
    if (wrapper) {
        wrapper.classList.remove('slide-nav');
    }
});
</script>