<!-- Sidebar2 - Bootstrap Version -->
<nav id="sidebar2" class="sidebar-nav bg-dark position-fixed h-100 shadow-sm" style="width: 280px; left: 0; top: 0; z-index: 1040; transition: all 0.3s ease;">
    <div class="d-flex flex-column h-100">
        <!-- Logo Header -->
        <div class="sidebar-header bg-gradient p-3 text-white" style="background: linear-gradient(135deg, #007bff, #6f42c1) !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-money-bill-wave text-primary fs-5"></i>
                    </div>
                    <h5 class="sidebar-brand-text mb-0 fw-bold">Payroll HRMS</h5>
                </div>
                
                <!-- Mobile Close Button -->
                <button type="button" class="btn btn-link text-white p-2 d-lg-none" onclick="toggleMobileSidebar2()">
                    <i class="fas fa-times"></i>
                </button>
                
                <!-- Desktop Collapse Toggle -->
                <button type="button" id="sidebar2-toggle" class="btn btn-link text-white p-2 d-none d-lg-block" onclick="toggleSidebar2()">
                    <i id="sidebar2-toggle-icon" class="fas fa-chevron-left"></i>
                </button>
            </div>
        </div>
        
        <!-- Navigation -->
        <div class="sidebar-body flex-grow-1 overflow-auto p-3">
            <ul class="nav flex-column">
                <!-- Dashboard -->
                <li class="nav-item mb-1">
                    <a href="{{ route('home') }}" class="nav-link sidebar-link {{ set_active(['home', 'em/dashboard']) ? 'active' : '' }} rounded-3 py-2 px-3">
                        <i class="fas fa-tachometer-alt me-3 sidebar-icon"></i>
                        <span class="sidebar-text">Dashboard</span>
                    </a>
                </li>

                {{-- User Management Section - Super Admin & Admin only --}}
                @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                    <!-- Section Divider -->
                    <hr class="sidebar-divider my-3">
                    <li class="nav-item">
                        <h6 class="sidebar-heading text-muted text-uppercase small fw-bold px-3 mb-3">User Management</h6>
                    </li>
                    
                    <!-- User Control Menu -->
                    <li class="nav-item mb-1">
                        <button class="nav-link sidebar-link btn btn-link w-100 text-start rounded-3 py-2 px-3 {{ set_active(['search/user/list','userManagement','activity/log','activity/login/logout']) ? 'active' : '' }}" 
                                data-bs-toggle="collapse" 
                                data-bs-target="#user-control-submenu" 
                                aria-expanded="{{ set_active(['search/user/list','userManagement','activity/log','activity/login/logout']) ? 'true' : 'false' }}">
                            <i class="fas fa-users me-3 sidebar-icon"></i>
                            <span class="sidebar-text">User Control</span>
                            <i class="fas fa-chevron-down ms-auto submenu-arrow"></i>
                        </button>
                        
                        <div id="user-control-submenu" class="collapse {{ set_active(['search/user/list','userManagement','activity/log','activity/login/logout']) ? 'show' : '' }} ms-4">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a href="{{ route('userManagement') }}" class="nav-link sidebar-sublink {{ set_active(['search/user/list','userManagement']) ? 'active' : '' }} rounded-3 py-2 px-3">
                                        <i class="fas fa-circle me-3 small"></i>
                                        <span class="sidebar-text">All Users</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('users.sync') }}" class="nav-link sidebar-sublink {{ set_active(['users/sync','users/sync/all']) ? 'active' : '' }} rounded-3 py-2 px-3">
                                        <i class="fas fa-circle me-3 small"></i>
                                        <span class="sidebar-text">Sync Users</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                @endif

                {{-- Employees Section --}}
                @if (Auth::user()->hasPermission('employees.index') || Auth::user()->hasPermission('employees.add_create') || Auth::user()->hasPermission('employees.edit_update'))
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Employees</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Employees Menu -->
                            <div class="space-y-1">
                                <button data-submenu="employees-submenu" onclick="toggleSubmenu('employees-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['employees','employees/new','form/department/manage','form/designation/manage','form/role/manage','form/employee-status/manage','form/document-type/manage']) ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-user-tie text-lg flex-shrink-0 {{ set_active(['employees','employees/new','form/department/manage','form/designation/manage','form/role/manage','form/employee-status/manage','form/document-type/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Employees</span>
                                    </div>
                                    <i id="employees-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['employees','employees/new','form/department/manage','form/designation/manage','form/role/manage','form/employee-status/manage','form/document-type/manage']) ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="employees-submenu" class="submenu ms-4 space-y-1 {{ set_active(['employees','employees/new','form/department/manage','form/designation/manage','form/role/manage','form/employee-status/manage','form/document-type/manage']) ? '' : 'hidden' }}" data-parent="employees-submenu">
                                    @if (Auth::user()->hasPermission('employees.index'))
                                    <a href="{{ route('employees.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['employees']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['employees']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">All Employees</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('employees.add_create'))
                                    <a href="{{ route('employees.new') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['employees/new']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['employees/new']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Add Employee</span>
                                    </a>
                                    @endif

                                    {{-- Master data management - Admin only --}}
                                    @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                                    <a href="{{ route('form/department/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/department/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/department/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Departments</span>
                                    </a>
                                    
                                    <a href="{{ route('form/designation/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/designation/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/designation/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Designations</span>
                                    </a>
                                    
                                    <a href="{{ route('form/role/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/role/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/role/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Roles</span>
                                    </a>
                                    
                                    <a href="{{ route('form/employee-status/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/employee-status/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/employee-status/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Employee Status</span>
                                    </a>
                                    
                                    <a href="{{ route('form/document-type/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/document-type/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/document-type/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Document Types</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- HR Section --}}
                @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">HR</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Payroll Menu -->
                            <div class="space-y-1">
                                <button data-submenu="payroll-submenu" onclick="toggleSubmenu('payroll-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['payslip/employee-list','payroll', 'payroll/attendance/*', 'form/statutory-component/manage','form/salary-component/manage']) ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-money-bill-wave text-lg flex-shrink-0 {{ set_active(['payslip/employee-list','payroll', 'payroll/attendance/*', 'form/statutory-component/manage','form/salary-component/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Payroll</span>
                                    </div>
                                    <i id="payroll-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['payslip/employee-list','payroll', 'payroll/attendance/*', 'form/statutory-component/manage','form/salary-component/manage']) ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="payroll-submenu" class="submenu ms-4 space-y-1 {{ set_active(['payslip/employee-list','payroll', 'payroll/attendance/*', 'form/statutory-component/manage','form/salary-component/manage']) ? '' : 'hidden' }}" data-parent="payroll-submenu">
                                    @if (Auth::user()->hasPermission('payroll.view'))
                                    <a href="{{ route('payroll.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['payroll']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['payroll']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Employee Salary Process</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('payroll.payslips'))
                                    <a href="{{ route('payroll/employee-list') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['payslip/employee-list']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['payslip/employee-list']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Payslips</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('statutory_components.view'))
                                    <a href="{{ route('form/statutory-component/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/statutory-component/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/statutory-component/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Statutory Components</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('salary_components.view'))
                                    <a href="{{ route('form/salary-component/manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['form/salary-component/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['form/salary-component/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Salary Components</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- OT and Incentives Section --}}
                @if (Auth::user()->hasPermission('ot_incentive.view'))
                    <div class="space-y-1">
                        <button data-submenu="ot-incentives-submenu" onclick="toggleSubmenu('ot-incentives-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['ot-incentive', 'ot-incentive/ot/*', 'ot-incentive/incentive/*']) ? 'bg-blue-50 text-blue-600' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-coins text-lg flex-shrink-0 {{ set_active(['ot-incentive', 'ot-incentive/ot/*', 'ot-incentive/incentive/*']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ms-3 font-medium sidebar-text whitespace-nowrap">OT and Incentives</span>
                            </div>
                            <i id="ot-incentives-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['ot-incentive', 'ot-incentive/ot/*', 'ot-incentive/incentive/*']) ? 'rotate-180' : '' }}"></i>
                        </button>
                        
                        <div id="ot-incentives-submenu" class="submenu ms-4 space-y-1 {{ set_active(['ot-incentive', 'ot-incentive/ot/*', 'ot-incentive/incentive/*']) ? '' : 'hidden' }}" data-parent="ot-incentives-submenu">
                            <a href="{{ route('ot-incentive.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['ot-incentive']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['ot-incentive']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Process Employee OT and Incentive</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Reports Section --}}
                @if (Auth::user()->hasPermission('reports.view'))
                    <div class="space-y-1">
                        <button data-submenu="reports-submenu" onclick="toggleSubmenu('reports-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['payroll-reports', 'payroll-reports/*', 'combined-reports', 'combined-reports/*', 'overtime-reports', 'overtime-reports/*','incentive-reports', 'incentive-reports/*', 'comparison-reports' , 'comparison-reports/*']) ? 'bg-blue-50 text-blue-600' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-chart-pie text-lg flex-shrink-0 {{ set_active(['payroll-reports', 'payroll-reports/*', 'combined-reports', 'combined-reports/*', 'overtime-reports', 'overtime-reports/*','incentive-reports', 'incentive-reports/*', 'comparison-reports' , 'comparison-reports/*']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Reports</span>
                            </div>
                            <i id="reports-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['payroll-reports', 'payroll-reports/*', 'combined-reports', 'combined-reports/*', 'overtime-reports', 'overtime-reports/*','incentive-reports', 'incentive-reports/*', 'comparison-reports' , 'comparison-reports/*']) ? 'rotate-180' : '' }}"></i>
                        </button>
                        
                        <div id="reports-submenu" class="submenu ms-4 space-y-1 {{ set_active(['payroll-reports', 'payroll-reports/*', 'combined-reports', 'combined-reports/*', 'overtime-reports', 'overtime-reports/*','incentive-reports', 'incentive-reports/*', 'comparison-reports' , 'comparison-reports/*']) ? '' : 'hidden' }}" data-parent="reports-submenu">
                            <a href="{{ route('payroll.reports.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['payroll-reports']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['payroll-reports']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Payroll Report</span>
                            </a>
                            
                            <a href="{{ route('combined.reports.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['combined-reports']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['combined-reports']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Combined OT & Holiday Payout Reports</span>
                            </a>
                            
                            <a href="{{ route('overtime.reports.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['overtime-reports']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['overtime-reports']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Overtime & Holiday Payout Reports</span>
                            </a>
                            
                            <a href="{{ route('incentive.reports.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['incentive-reports']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['incentive-reports']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Incentive Reports</span>
                            </a>
                            
                            <a href="{{ route('payroll.comparison.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['comparison-reports']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['comparison-reports']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Comparison Reports</span>
                            </a>
                        </div>
                    </div>

                    {{-- Analytical Reports --}}
                    <div class="space-y-1">
                        <button data-submenu="analytical-reports-submenu" onclick="toggleSubmenu('analytical-reports-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['reports/payroll-analytics', 'reports/payroll-comparison']) ? 'bg-blue-50 text-blue-600' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-chart-line text-lg flex-shrink-0 {{ set_active(['reports/payroll-analytics', 'reports/payroll-comparison']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Analytical Reports</span>
                            </div>
                            <i id="analytical-reports-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['reports/payroll-analytics', 'reports/payroll-comparison']) ? 'rotate-180' : '' }}"></i>
                        </button>
                        
                        <div id="analytical-reports-submenu" class="submenu ms-4 space-y-1 {{ set_active(['reports/payroll-analytics', 'reports/payroll-comparison']) ? '' : 'hidden' }}" data-parent="analytical-reports-submenu">
                            <a href="{{ route('reports.payroll.analytics') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['reports/payroll-analytics']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['reports/payroll-analytics']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Payroll Analytics</span>
                            </a>
                            
                            <a href="{{ route('reports.payroll.comparison') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['reports/payroll-comparison']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['reports/payroll-comparison']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                <span class="font-medium sidebar-text whitespace-nowrap">Analytical Comparison</span>
                            </a>
                        </div>
                    </div>
                @endif

                {{-- Financial Management Section --}}
                @if (Auth::user()->hasPermission('financial_years.view'))
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Financial Management</h3>
                        <div class="mt-2 space-y-1">
                            <!-- Financial Year Menu -->
                            <div class="space-y-1">
                                <button data-submenu="financial-year-submenu" onclick="toggleSubmenu('financial-year-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['financial-years', 'financial-years/*']) ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt text-lg flex-shrink-0 {{ set_active(['financial-years', 'financial-years/*']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Financial Year</span>
                                    </div>
                                    <i id="financial-year-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['financial-years', 'financial-years/*']) ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="financial-year-submenu" class="submenu ms-4 space-y-1 {{ set_active(['financial-years', 'financial-years/*']) ? '' : 'hidden' }}" data-parent="financial-year-submenu">
                                    <a href="{{ route('financial-years.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['financial-years']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['financial-years']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Manage Financial Years</span>
                                    </a>
                                    
                                    @if (Auth::user()->hasPermission('financial_years.create'))
                                    <a href="{{ route('financial-years.create') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['financial-years/create']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['financial-years/create']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Create New FY</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('financial_years.edit'))
                                    <a href="{{ route('financial-years.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['financial-years/settings/index']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['financial-years/settings/index']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">FY Settings</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- System Settings Section --}}
                @if (Auth::user()->hasPermission('activity_logs.view') || Auth::user()->hasPermission('company_settings.view') || Auth::user()->hasPermission('settings.view'))
                    <div class="pt-4 mt-4 border-t border-gray-200">
                        <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">System Settings</h3>
                        <div class="mt-2 space-y-1">
                            @if (Auth::user()->hasPermission('activity_logs.view'))
                            <!-- Activity Logs -->
                            <a href="{{ route('activity-logs') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['activity-logs']) ? 'bg-gradient-to-r from-red-50 to-pink-50 text-red-600 border-r-4 border-red-600' : '' }}">
                                <i class="fas fa-history text-lg flex-shrink-0 {{ set_active(['activity-logs']) ? 'text-red-600' : 'text-gray-400' }} group-hover:text-red-600 transition-colors"></i>
                                <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Activity Logs</span>
                                <span class="ms-auto bg-red-100 text-red-600 text-xs px-2 py-1 rounded-full notification-badge">
                                    <i class="fas fa-shield-alt"></i>
                                </span>
                            </a>
                            @endif
                            
                            @if (Auth::user()->hasPermission('company_settings.view') || Auth::user()->hasPermission('settings.view'))
                            <!-- Settings Menu -->
                            <div class="space-y-1">
                                <button data-submenu="settings-submenu" onclick="toggleSubmenu('settings-submenu')" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page']) ? 'bg-blue-50 text-blue-600' : '' }}">
                                    <div class="flex items-center">
                                        <i class="fas fa-cog text-lg flex-shrink-0 {{ set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors"></i>
                                        <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Settings</span>
                                    </div>
                                    <i id="settings-submenu-icon" class="submenu-chevron fas fa-chevron-down text-xs transition-transform {{ set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page']) ? 'rotate-180' : '' }}"></i>
                                </button>
                                
                                <div id="settings-submenu" class="submenu ms-4 space-y-1 {{ set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page']) ? '' : 'hidden' }}" data-parent="settings-submenu">
                                    @if (Auth::user()->hasPermission('company_settings.view'))
                                    <a href="{{ route('company/settings/page') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['company/settings/page']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['company/settings/page']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Company Settings</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('settings.view'))
                                    <a href="{{ route('settings.index') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['master-settings']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['master-settings']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Master Settings</span>
                                    </a>
                                    @endif
                                    
                                    @if (Auth::user()->hasPermission('settings.view'))
                                    <a href="{{ route('permissions.manage') }}" class="flex items-center px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-100 transition-colors group {{ set_active(['permissions/manage']) ? 'bg-blue-50 text-blue-600 border-l-4 border-blue-600' : '' }}">
                                        <i class="fas fa-circle text-xs flex-shrink-0 {{ set_active(['permissions/manage']) ? 'text-blue-600' : 'text-gray-400' }} group-hover:text-blue-600 transition-colors me-3"></i>
                                        <span class="font-medium sidebar-text whitespace-nowrap">Permission Management</span>
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Work Space Section -->
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Work Space</h3>
                    <div class="mt-2 space-y-1">
                        <!-- Back to Work Space Button -->
                        <a href="{{ env('SSO_WORKSPACE_URL') }}" class="flex items-center px-4 py-3 text-gray-700 rounded-lg hover:bg-yellow-50 hover:text-yellow-600 transition-colors group">
                            <i class="fas fa-arrow-left text-yellow-600 text-lg flex-shrink-0"></i>
                            <span class="ms-3 font-medium sidebar-text whitespace-nowrap">Back to Work Space</span>
                        </a>
                    </div>
                </div>
            </nav>
            
            <!-- User Info -->
            <div class="p-4 border-t border-gray-200">
                <div class="user-info flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</span>
                    </div>
                    <div class="ms-3 flex-1 sidebar-text">
                        <p class="text-sm font-medium text-gray-900 sidebar-text">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 sidebar-text">{{ ucfirst(Auth::user()->role_name) }}</p>
                    </div>
                </div>
        </div>
    </div>
</aside>

<!-- Backdrop for Mobile Menu -->
<div id="sidebar2-backdrop" class="fixed inset-0 bg-black bg-opacity-50 hidden z-30" onclick="toggleMobileSidebar2()"></div>  
</div> <!-- End tailwind-scope -->  

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
        #sidebar2.collapsed nav::-webkit-scrollbar {
            width: 0px;
        }
        
        #sidebar2.collapsed nav {
            scrollbar-width: none;
        }
        
        /* Fixed logo section */
        #sidebar2 .logo {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        /* Mobile sidebar animation */
        #sidebar2.open {
            transform: translateX(0) !important;
        }

        #sidebar2.collapsed {
            width: 4rem;
        }
        #sidebar2.collapsed .sidebar-text,
        #sidebar2.collapsed .section-title,
        #sidebar2.collapsed .notification-badge,
        #sidebar2.collapsed .submenu {
            display: none !important;
        }
        #sidebar2.collapsed .logo {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar2.collapsed .logo .ms-3 {
            margin-left: 0;
        }
        /* When collapsed, hide only the mobile close button (which uses the Tailwind class `lg:hidden`).
           Keep the desktop toggle button (#sidebar2-toggle) visible so users can reopen the sidebar on desktop. */
        #sidebar2.collapsed .logo button.lg\:hidden {
            display: none;
        }

        /* Ensure the desktop toggle remains visible and uses inline-flex for proper alignment. Use !important to
           override the generic rules safely here (specific to collapsed state). */
        #sidebar2.collapsed .logo #sidebar2-toggle {
            display: inline-flex !important;
        }

        /* Hide submenu chevrons in collapsed state on desktop to reduce visual clutter */
        #sidebar2.collapsed .submenu-chevron {
            display: none !important;
        }
        #sidebar2.collapsed nav a,
        #sidebar2.collapsed nav button {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar2.collapsed form button {
            justify-content: center;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        #sidebar2.collapsed .user-info {
            justify-content: center;
        }
        #sidebar2.collapsed .user-info .ms-3 {
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

        function toggleSidebar2() {
            const sidebar = document.getElementById('sidebar2');
            const toggleIcon = document.getElementById('sidebar2-toggle-icon');
            const body = document.body;
            
            sidebar.classList.toggle('collapsed');
            body.classList.toggle('sidebar2-collapsed');
            
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

        function toggleMobileSidebar2() {
            const sidebar = document.getElementById('sidebar2');
            const backdrop = document.getElementById('sidebar2-backdrop');
            const mobileMenuIcon = document.getElementById('mobile-menu-icon');
            
            if (sidebar && backdrop) {
                sidebar.classList.toggle('open');
                backdrop.classList.toggle('hidden');
                
                if (sidebar.classList.contains('open')) {
                    if (mobileMenuIcon) {
                        mobileMenuIcon.classList.replace('fa-bars', 'fa-times');
                    }
                } else {
                    if (mobileMenuIcon) {
                        mobileMenuIcon.classList.replace('fa-times', 'fa-bars');
                    }
                }
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

        // Make functions globally accessible
        window.toggleMobileSidebar2 = toggleMobileSidebar2;
        window.toggleSidebar2 = toggleSidebar2;
        window.toggleSubmenu = toggleSubmenu;
    </script>