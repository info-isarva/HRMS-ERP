{{-- Sidebar Reports Section --}}
<li class="nav-item mb-2">
    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->is('reports*') ? 'active' : '' }}" href="#reports-submenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('reports*') ? 'true' : 'false' }}" aria-controls="reports-submenu">
        <span class="d-flex align-items-center"><i class="bi bi-bar-chart-fill me-2"></i> <span class="nav-label">Reports</span></span>
        <i class="bi bi-chevron-down"></i>
    </a>
    <div class="collapse{{ request()->is('reports*') ? ' show' : '' }} mt-2" id="reports-submenu">
        <ul class="nav flex-column ms-3">
            <!-- Leads Submenu under Reports -->
            <li class="nav-item mb-2">
                <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('reports.today_leads') ? 'active' : (request()->routeIs('reports.leads_by_status') ? 'active' : (request()->routeIs('reports.leads_by_source_custom') ? 'active' : (request()->routeIs('reports.converted_leads') ? 'active' : ''))) }}" href="#leads-submenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('leads*') ? 'true' : 'false' }}" aria-controls="leads-submenu">
                    <span class="d-flex align-items-center"><span class="nav-label">Leads Reports</span></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.today_leads') ? 'show ' : (request()->routeIs('reports.leads_by_status') ? 'show ' : (request()->routeIs('reports.leads_by_source_custom') ? 'show ' : (request()->routeIs('reports.converted_leads') ? 'show ' : ''))) }} mt-2" id="leads-submenu">
                    <ul class="nav flex-column ms-3">
                        @if(auth()->user()->hasCrmPermission('todays_leads_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.today_leads') ? 'active' : '' }}" href="{{ route('reports.today_leads') }}">Today's Leads</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('leads_by_status_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.leads_by_status') ? 'active' : '' }}" href="{{ route('reports.leads_by_status') }}">Leads by Status</a></li>
                        
                        @endif
                        @if(auth()->user()->hasCrmPermission('leads_by_source_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.leads_by_source_custom') ? 'active' : '' }}" href="{{ route('reports.leads_by_source_custom') }}">Leads by Source</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('converted_leads_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.converted_leads') ? 'active' : '' }}" href="{{ route('reports.converted_leads') }}">Converted Leads</a></li>
                        @endif
                    </ul>
                </div>
            </li>

            <!-- Deals Submenu under Reports -->
            <li class="nav-item mb-2">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('reports.today_closed_won_deals') ? 'active' : (request()->routeIs('reports.deals_by_source_custom') ? 'active' : (request()->routeIs('reports.open_deals') ? 'active' : (request()->routeIs('reports.lost_deals') ? 'active' : (request()->routeIs('reports.deals_this_month') ? 'active' : '')))) }}" href="#deals-submenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('deals*') ? 'true' : 'false' }}" aria-controls="deals-submenu">
                    <span class="d-flex align-items-center"><span class="nav-label">Deals Reports</span></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.today_closed_won_deals') ? 'show ' : (request()->routeIs('reports.deals_by_source_custom') ? 'show ' : (request()->routeIs('reports.open_deals') ? 'show ' : (request()->routeIs('reports.lost_deals') ? 'show ' : (request()->routeIs('reports.deals_this_month') ? 'show ' : '')))) }} mt-2" id="deals-submenu">
                    <ul class="nav flex-column ms-3">
                       
                        @if(auth()->user()->hasCrmPermission('today_deals_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.today_closed_won_deals') ? 'active' : '' }}" href="{{ route('reports.today_closed_won_deals') }}">Today's Sales</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('deal_by_source_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.deals_by_source_custom') ? 'active' : '' }}" href="{{ route('reports.deals_by_source_custom') }}">Sales by Source</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('open_deals_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.open_deals') ? 'active' : '' }}" href="{{ route('reports.open_deals') }}">Open Sales</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('lost_deals_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.lost_deals') ? 'active' : '' }}" href="{{ route('reports.lost_deals') }}">Lost Sales</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('deals_closing_this_month_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.deals_this_month') ? 'active' : '' }}" href="{{ route('reports.deals_this_month') }}">Sales Closing This Month</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            
            <!-- User Reports -->
            @if(auth()->user()->hasCrmPermission('user_report_guard'))
            <li class="nav-item mb-2">
                <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('reports.user_daily_report') ? 'active' : (request()->routeIs('reports.monthly_user_report') ? 'active' : (request()->routeIs('reports.monthly_user_performance') ? 'active' : (request()->routeIs('task-reports.index') ? 'active' : (request()->routeIs('reports.task') ? 'active' : '')))) }}" href="#user-submenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('user*') ? 'true' : 'false' }}" aria-controls="user-submenu">
                    <span class="d-flex align-items-center"><span class="nav-label">User Reports</span></span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.user_daily_report') ? ' show' : (request()->routeIs('reports.monthly_user_report') ? ' show' : (request()->routeIs('reports.monthly_user_performance') ? ' show' : (request()->routeIs('task-reports.index') ? ' show' :  (request()->routeIs('reports.task') ? 'show' : '')))) }} mt-2" id="user-submenu">
                    <ul class="nav flex-column ms-3">
                        <!-- <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.user_reports') ? 'active' : '' }}" href="{{ route('reports.user_reports') }}">User Report</a></li> -->
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.user_daily_report') ? 'active' : '' }}" href="{{ route('reports.user_daily_report') }}">Daily User Report</a></li>
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.monthly_user_report') ? 'active' : '' }}" href="{{ route('reports.monthly_user_report') }}">Monthly User Report</a></li>
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.monthly_user_performance') ? 'active' : '' }}" href="{{ route('reports.monthly_user_performance') }}">User Performance Report</a></li>
                        @if(auth()->user()->hasCrmPermission('task_reminder_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('task-reports.index') ? 'active' : '' }}" href="{{ route('task-reports.index') }}">Reminder Report</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('user_task_report_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.task') ? 'active' : '' }}" href="{{ route('reports.task') }}">User Task Report</a></li>
                        @endif
                         @if (auth()->user()->hasCrmPermission(permission: 'product_wise_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.product_category_user_report') ? 'active' : '' }}" href="{{ route('reports.product_category_user_report') }}">Product Wise Reports</a></li>
                        @endif
                    </ul>
                </div>
            </li>
            @endif
           
            <!-- Analytics Submenu under Reports -->
            @if(auth()->user()->hasCrmPermission('leads_analytics_report_guard') || auth()->user()->hasCrmPermission('deals_analytics_reports_guard') || auth()->user()->hasCrmPermission('revenue_analytics_reports_guard'))
            <li class="nav-item mb-2">
                <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('reports.leads') ? 'active' : (request()->routeIs('reports.deals') ? 'active' : (request()->routeIs('reports.revenue_by_month') ? 'active' : '')) }}" href="#analytics-submenu" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->is('analytics*') ? 'true' : 'false' }}" aria-controls="analytics-submenu">
                    <span>Analytics Reports</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
                <div class="collapse {{ request()->routeIs('reports.leads') ? 'show' : (request()->routeIs('reports.deals') ? 'show' : (request()->routeIs('reports.revenue_by_month') ? 'show' : '')) }} mt-2" id="analytics-submenu">
                    <ul class="nav flex-column ms-3">
                        @if(auth()->user()->hasCrmPermission('leads_analytics_report_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.leads') ? 'active' : '' }}" href="{{ route('reports.leads') }}">Leads Reports</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('deals_analytics_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.deals') ? 'active' : '' }}" href="{{ route('reports.deals') }}">Deals Reports</a></li>
                        @endif
                        @if(auth()->user()->hasCrmPermission('revenue_analytics_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('reports.revenue_by_month') ? 'active' : '' }}" href="{{ route('reports.revenue_by_month') }}">Revenue Reports</a></li>
                        @endif
                        @if (auth()->user()->hasCrmPermission(permission: 'user_performance_analytics_reports_guard'))
                        <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('analytics.reports') ? 'active' : '' }}" href="{{ route('analytics.reports') }}">User Performance Analytics Reports</a></li>
                        @endif
                       
                        
                    </ul>
                </div>
            </li>
            @endif
        </ul>
    </div>
</li>
