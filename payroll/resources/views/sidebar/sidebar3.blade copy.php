
<!-- Sidebar -->
 
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="menu-title">
                    <span>Main</span>
                </li>
                {{-- Dashboard - Always visible for logged-in users --}}
                <li class="{{ set_active(['home', 'em/dashboard']) ? 'active noti-dot' : '' }}">
                    <a href="{{ route('home') }}">
                        <i class="la la-dashboard"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- User Management Section - Hide for employees with limited permissions --}}
                @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                    <li class="menu-title"> <span>User Management</span> </li>
                    <li class="{{set_active(['search/user/list','userManagement','activity/log','activity/login/logout'])}} submenu">
                        <a href="#" class="{{ set_active(['search/user/list','userManagement','activity/log','activity/login/logout']) ? 'noti-dot' : '' }}">
                            <i class="la la-users"></i> <span> User Control</span> <span class="menu-arrow"></span>
                        </a>
                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            <li><a class="{{set_active(['search/user/list','userManagement'])}} user-sync-menu" href="{{ route('userManagement') }}">
                                </i> All Users</a></li>
                            <li><a class="{{set_active(['users/sync','users/sync/all'])}}" href="{{ route('users.sync') }}">
                                Sync Users</a></li>
                        </ul>
                    </li>
                @endif
                
                {{-- Employees Section --}}
                @if (Auth::user()->hasPermission('employees.index') || Auth::user()->hasPermission('employees.add_create') || Auth::user()->hasPermission('employees.edit_update'))
                    <li class="menu-title"> <span>Employees</span> </li>
                    <li class="{{set_active(['employees','employees/new','form/department/manage','form/designation/manage','form/role/manage','form/employee-status/manage','form/document-type/manage'])}} submenu">
                        <a href="#"  class="{{ request()->is('employees') || request()->is('employees/*') || request()->is('form/department/manage') || request()->is('form/designation/manage') || request()->is('form/role/manage') || request()->is('form/employee-status/manage') || request()->is('form/document-type/manage') ? 'noti-dot' : '' }}">
                            <i class="la la-user"></i> <span> Employees</span> <span class="menu-arrow"></span>
                        </a>
                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            @if (Auth::user()->hasPermission('employees.index'))
                            <li><a class="{{set_active(['employees'])}} {{ request()->is('employees/*') ? 'active' : '' }}" href="{{ route('employees.index') }}">All Employees</a></li>
                            @endif
                            <!-- @if (Auth::user()->hasPermission('employees.add_create'))
                            <li><a class="{{set_active(['employees/new'])}}" href="{{ route('employees.new') }}">Add Employee</a></li>
                            @endif -->
                            {{-- Master data management - Admin only for now --}}
                            @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                            <li><a class="{{set_active(['form/department/manage'])}}" href="{{ route('form/department/manage') }}">Departments</a></li>
                            <li><a class="{{set_active(['form/designation/manage'])}}" href="{{ route('form/designation/manage') }}">Designations</a></li>
                            <li><a class="{{set_active(['form/role/manage'])}}" href="{{ route('form/role/manage') }}"> Roles</a></li>
                            <li><a class="{{set_active(['form/employee-status/manage'])}}" href="{{ route('form/employee-status/manage') }}"> Employee Status</a></li>
                            <li><a class="{{set_active(['form/document-type/manage'])}}" href="{{ route('form/document-type/manage') }}">Document Types</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- HR Section - Admin only for now --}}
                @if (Auth::user()->role_name === 'Super Admin' || Auth::user()->role_name === 'Admin')
                    <li class="menu-title"> <span>HR</span> </li>                
                    <li class="{{set_active(['payslip/employee-list','payroll', 'payroll/attendance/*', 'form/statutory-component/manage','form/salary-component/manage' ])}} {{ request()->is('payroll/*') ? 'active' : '' }} submenu">
                       
                        <a href="#" class="{{ request()->is('payroll') || request()->is('payroll/*') || request()->is('payslip/employee-list') || request()->is('form/statutory-component/manage') || request()->is('form/salary-component/manage') ? 'noti-dot' : '' }}"><i class="fa fa-inr"></i>
                        <span> Payroll </span> <span class="menu-arrow"></span></a>

                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            @if (Auth::user()->hasPermission('payroll.view'))
                            <li><a class="{{set_active(['payroll'])}} {{ request()->is('payroll/*') ? 'active' : '' }}" href="{{ route('payroll.index') }}"> Employee Salary Process</a></li>
                            @endif
                            @if (Auth::user()->hasPermission('payroll.payslips'))
                            <li><a class="{{set_active(['payslip/employee-list'])}} {{ request()->is('payslip/employee-list') ? 'active' : '' }}" href="{{ route('payroll/employee-list') }}"> Payslips </a></li>
                            @endif
                            @if (Auth::user()->hasPermission('statutory_components.view'))
                            <li><a class="{{set_active(['form/statutory-component/manage'])}}" href="{{ route('form/statutory-component/manage') }}"> Statutory Components </a></li>
                            @endif
                            @if (Auth::user()->hasPermission('salary_components.view'))
                            <li><a class="{{set_active(['form/salary-component/manage'])}}" href="{{ route('form/salary-component/manage') }}"> Salary Components </a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- OT and Incentives Section --}}
                @if (Auth::user()->hasPermission('ot_incentive.view'))
                    <li class="{{set_active(['ot-incentive', 'ot-incentive/ot/*', 'ot-incentive/incentive/*'])}} {{ request()->is('ot-incentive/*') ? 'active' : '' }} submenu">
                       
                        <a href="#" class="{{ request()->is('ot-incentive') || request()->is('ot-incentive/ot/*') || request()->is('ot-incentive/incentive/*') ? 'noti-dot' : '' }}"><i class="la la-money"></i>
                        <span> OT and Incentives </span> <span class="menu-arrow"></span></a>

                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            <li><a class="{{set_active(['ot-incentive'])}} {{ request()->is('ot-incentive/*') ? 'active' : '' }}" href="{{ route('ot-incentive.index') }}">Process Employee OT and Incentive</a></li>                        
                        </ul>
                    </li>
                @endif

                {{-- Reports Section --}}
                @if (Auth::user()->hasPermission('reports.view'))
                    <li class="{{set_active(['payroll-reports', 'payroll-reports/*', 'combined-reports', 'combined-reports/*', 'overtime-reports', 'overtime-reports/*','incentive-reports', 'incentive-reports/*', 'comparison-reports' , 'comparison-reports/*'])}} {{ request()->is('payroll-reports/*') ? 'active' : '' }} submenu">
                       
                        <a href="#" class="{{ request()->is('payroll-reports') || request()->is('payroll-reports/*') ||  request()->is('combined-reports') || request()->is('combined-reports/*') ||  request()->is('overtime-reports') || request()->is('overtime-reports/*') || request()->is('incentive-reports/*') ||  request()->is('incentive-reports') || request()->is('comparison-reports') || request()->is('comparison-reports/*')   ? 'noti-dot' : '' }}"><i class="la la-pie-chart"></i>
                        <span> Reports </span> <span class="menu-arrow"></span></a>

                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            <li><a class="{{set_active(['payroll-reports'])}} {{ request()->is('payroll-reports/*') ? 'active' : '' }}" href="{{ route('payroll.reports.index') }}">Payroll Report</a></li>
                            <li><a class="{{set_active(['combined-reports'])}} {{ request()->is('combined-reports/*') ? 'active' : '' }}" href="{{ route('combined.reports.index') }}">Combined OT & Holiday Payout Reports</a></li>                        
                            <li><a class="{{set_active(['overtime-reports'])}} {{ request()->is('overtime-reports/*') ? 'active' : '' }}" href="{{ route('overtime.reports.index') }}">Overtime & Holiday Payout Reports</a></li>
                            <li><a class="{{set_active(['incentive-reports'])}} {{ request()->is('incentive-reports/*') ? 'active' : '' }}" href="{{ route('incentive.reports.index') }}">Incentive Reports</a></li>
                            <li><a class="{{set_active(['comparison-reports'])}} {{ request()->is('comparison-reports/*') ? 'active' : '' }}" href="{{ route('payroll.comparison.index') }}">Comparison Reports</a></li>
                        </ul>
                    </li>

                    {{-- Analytical Reports --}}
                    <li class="{{set_active(['reports/payroll-analytics', 'reports/payroll-comparison'])}} {{ request()->is('payroll-reports/*') ? 'active' : '' }} submenu">
                       
                        <a href="#" class="{{ request()->is('reports/payroll-analytics') || request()->is('reports/payroll-comparison')    ? 'noti-dot' : '' }}"><i class="fa-solid fa-chart-simple"></i>
                        <span> Analytical Reports </span> <span class="menu-arrow"></span></a>

                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            <li><a class="{{ set_active(['reports/payroll-analytics']) }} {{ request()->is('reports/payroll-analytics') ? 'active' : '' }}" href="{{ route('reports.payroll.analytics') }}">Payroll Analytics</a></li>
                            <li><a class="{{ set_active(['reports/payroll-comparison']) }} {{ request()->is('reports/payroll-comparison') ? 'active' : '' }}" href="{{ route('reports.payroll.comparison') }}">Analytical Comparison</a></li>
                        </ul>
                    </li>
                @endif

                {{-- Financial Year Management Section --}}
                @if (Auth::user()->hasPermission('financial_years.view'))
                    <li class="{{set_active(['financial-years', 'financial-years/*'])}} {{ request()->is('financial-years/*') ? 'active' : '' }} submenu">
                       
                        <a href="#" class="{{ request()->is('financial-years') || request()->is('financial-years/*') ? 'noti-dot' : '' }}"><i class="la la-calendar"></i>
                        <span> Financial Year </span> <span class="menu-arrow"></span></a>

                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            <li><a class="{{set_active(['financial-years'])}} {{ request()->is('financial-years') ? 'active' : '' }}" href="{{ route('financial-years.index') }}">Manage Financial Years</a></li>
                            @if (Auth::user()->hasPermission('financial_years.create'))
                            <li><a class="{{set_active(['financial-years/create'])}}" href="{{ route('financial-years.create') }}">Create New FY</a></li>
                            @endif
                            @if (Auth::user()->hasPermission('financial_years.edit'))
                            <li><a class="{{set_active(['financial-years/settings/index'])}}" href="{{ route('financial-years.settings') }}">FY Settings</a></li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Admin Settings Section --}}
                @if (Auth::user()->hasPermission('activity_logs.view') || Auth::user()->hasPermission('company_settings.view') || Auth::user()->hasPermission('settings.view'))
                    <li class="menu-title"> <span>System Settings</span> </li>
                    @if (Auth::user()->hasPermission('activity_logs.view'))
                    <li class="{{set_active(['activity-logs'])}}">
                        <a href="{{ route('activity-logs') }}" class="{{ set_active(['activity-logs']) ? 'noti-dot' : '' }}">
                            <i class="la la-history"></i>
                            <span>Activity Logs</span>
                        </a>
                    </li>
                    @endif
                    
                    @if (Auth::user()->hasPermission('company_settings.view') || Auth::user()->hasPermission('settings.view'))
                    <li class="{{set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page'])}} submenu">
                        <a href="#" class="{{ set_active(['company/settings/page','master-settings','permissions/manage','localization/page','salary/settings/page','email/settings/page']) ? 'noti-dot' : '' }}">
                            <i class="la la-cog"></i> <span> Settings</span> <span class="menu-arrow"></span>
                        </a>
                        <ul style="{{ request()->is('/*') ? 'display: block;' : 'display: none;' }}">
                            @if (Auth::user()->hasPermission('company_settings.view'))
                            <li><a class="{{set_active(['company/settings/page'])}}" href="{{ route('company/settings/page') }}">
                                Company Settings</a></li>
                            @endif
                            @if (Auth::user()->hasPermission('settings.view'))
                            <li><a class="{{set_active(['master-settings'])}}" href="{{ route('settings.index') }}">
                                 Master Settings</a></li>
                            @endif
                            @if (Auth::user()->hasPermission('settings.view'))
                            <li><a class="{{set_active(['permissions/manage'])}}" href="{{ route('permissions.manage') }}">
                                 Permission Management</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif
                @endif

                <li class="menu-title">
                    <span>Work Space</span>
                </li>

                <li>
                    <a href="{{ env('SSO_WORKSPACE_URL') }}">
                        <i class="la la-backward text-danger"></i>
                        <span>Back to Work Space</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->