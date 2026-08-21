@php
    $user = auth()->user();
    $org = $user?->organization;
    $isAdmin = $user?->canManageIc();
    $isIc = $user?->hasIcAccess();
    $navActive = 'flex items-center px-4 py-3 rounded-lg transition-colors group';
    $navIdle = 'text-gray-700 hover:bg-gray-100';
    $navOn = 'bg-blue-50 text-blue-600 border-r-4 border-blue-600';
    $iconIdle = 'text-gray-400 group-hover:text-blue-600 transition-colors';
    $iconOn = 'text-blue-600';
@endphp
<aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transition-all duration-300 transform -translate-x-full lg:translate-x-0">
    <div class="flex flex-col h-full">
        <div class="logo flex items-center justify-between h-16 px-4 lg:px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 overflow-hidden">
            <div class="sidebar-logo-brand flex items-center min-w-0 flex-1">
                <div class="sidebar-logo-icon w-8 h-8 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-halved text-blue-600 text-lg"></i>
                </div>
                <div class="ml-3 sidebar-text min-w-0 overflow-hidden">
                    <h1 class="text-lg font-bold text-white leading-tight truncate">{{ config('posh.product_short_name') }}</h1>
                    <p class="text-xs text-blue-100 truncate">{{ config('posh.product_tagline') }}</p>
                </div>
            </div>
            <button type="button" onclick="toggleMobileSidebar()" class="lg:hidden p-2 text-white hover:bg-white/20 rounded-md">
                <i class="fas fa-times text-lg"></i>
            </button>
            <button type="button" id="sidebar-toggle" onclick="toggleSidebar()" class="hidden lg:block p-1 text-white hover:text-gray-200">
                <i id="sidebar-toggle-icon" class="fas fa-chevron-left text-sm transition-transform duration-300"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" class="{{ $navActive }} {{ request()->routeIs('dashboard') ? $navOn : $navIdle }}">
                <i class="fas fa-home text-lg flex-shrink-0 {{ request()->routeIs('dashboard') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Dashboard</span>
            </a>

            <a href="{{ route('guide.index') }}" class="{{ $navActive }} {{ request()->routeIs('guide.*') ? $navOn : $navIdle }}">
                <i class="fas fa-book-open text-lg flex-shrink-0 {{ request()->routeIs('guide.*') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">User Guide</span>
            </a>

            <a href="{{ route('employee.portal') }}" class="{{ $navActive }} {{ request()->routeIs('employee.*') ? $navOn : $navIdle }}">
                <i class="fas fa-user-shield text-lg flex-shrink-0 {{ request()->routeIs('employee.*') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Employee Portal</span>
            </a>

            <a href="{{ route('complaints.create') }}" class="{{ $navActive }} {{ request()->routeIs('complaints.create') ? $navOn : $navIdle }}">
                <i class="fas fa-file-circle-plus text-lg flex-shrink-0 {{ request()->routeIs('complaints.create') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">New Complaint</span>
            </a>

            <a href="{{ route('complaints.my') }}" class="{{ $navActive }} {{ request()->routeIs('complaints.my') ? $navOn : $navIdle }}">
                <i class="fas fa-folder text-lg flex-shrink-0 {{ request()->routeIs('complaints.my') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">My Cases</span>
            </a>

            <a href="{{ route('management.index') }}" class="{{ $navActive }} {{ request()->routeIs('management.*') ? $navOn : $navIdle }}">
                <i class="fas fa-briefcase text-lg flex-shrink-0 {{ request()->routeIs('management.*') ? $iconOn : $iconIdle }}"></i>
                <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Management</span>
            </a>

            @if($isIc)
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">IC / HR</h3>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('cases.index') }}" class="{{ $navActive }} {{ request()->routeIs('cases.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-folder-open text-lg flex-shrink-0 {{ request()->routeIs('cases.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">All Cases</span>
                        </a>
                        <a href="{{ route('compliance.index') }}" class="{{ $navActive }} {{ request()->routeIs('compliance.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-clipboard-check text-lg flex-shrink-0 {{ request()->routeIs('compliance.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Compliance</span>
                        </a>
                        <a href="{{ route('reports.annual.index') }}" class="{{ $navActive }} {{ request()->routeIs('reports.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-file-lines text-lg flex-shrink-0 {{ request()->routeIs('reports.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Annual Report</span>
                        </a>
                        <a href="{{ route('audit.index') }}" class="{{ $navActive }} {{ request()->routeIs('audit.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-clock-rotate-left text-lg flex-shrink-0 {{ request()->routeIs('audit.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Audit Log</span>
                        </a>
                    </div>
                </div>
            @endif

            @if($isAdmin)
                <div class="pt-4 mt-4 border-t border-gray-200">
                    <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Administration</h3>
                    <div class="mt-2 space-y-1">
                        <a href="{{ route('employees.index') }}" class="{{ $navActive }} {{ request()->routeIs('employees.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-users text-lg flex-shrink-0 {{ request()->routeIs('employees.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Employees</span>
                        </a>
                        <a href="{{ route('ic-members.index') }}" class="{{ $navActive }} {{ request()->routeIs('ic-members.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-people-group text-lg flex-shrink-0 {{ request()->routeIs('ic-members.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">IC Setup</span>
                        </a>
                        <a href="{{ route('policies.index') }}" class="{{ $navActive }} {{ request()->routeIs('policies.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-file-contract text-lg flex-shrink-0 {{ request()->routeIs('policies.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Policy</span>
                        </a>
                        <a href="{{ route('settings.edit') }}" class="{{ $navActive }} {{ request()->routeIs('settings.*') ? $navOn : $navIdle }}">
                            <i class="fas fa-gear text-lg flex-shrink-0 {{ request()->routeIs('settings.*') ? $iconOn : $iconIdle }}"></i>
                            <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Settings</span>
                        </a>
                    </div>
                </div>
            @endif

            @if($org && ! $org->usesNativeAuth())
            <div class="pt-4 mt-4 border-t border-gray-200">
                <h3 class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider section-title">Workspace</h3>
                <div class="mt-2 space-y-1">
                    <a href="{{ config('posh.workspace_url') }}" target="_blank" rel="noopener" class="{{ $navActive }} {{ $navIdle }}">
                        <i class="fas fa-th-large text-lg flex-shrink-0 text-yellow-600"></i>
                        <span class="ml-3 font-medium sidebar-text whitespace-nowrap">Back to Workspace</span>
                        <i class="fas fa-external-link-alt ml-auto text-xs text-gray-400 sidebar-text"></i>
                    </a>
                </div>
            </div>
            @endif
        </nav>

        <div class="p-4 border-t border-gray-200">
            <div class="user-info flex items-center">
                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user?->name ?? 'U', 0, 2)) }}</span>
                </div>
                <div class="ml-3 flex-1 min-w-0 sidebar-text">
                    <p class="text-sm font-medium text-blue-950 truncate">{{ $user?->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ config('posh.user_roles.' . ($user?->posh_role ?? 'employee'), 'Employee') }}</p>
                </div>
            </div>
        </div>
    </div>
</aside>
