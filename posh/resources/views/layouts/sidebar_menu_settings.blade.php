{{-- Sidebar Settings Section --}}
<li class="nav-item mb-2">
    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('profile.edit') ? 'active' : (request()->routeIs('company.edit') ? 'active' : (request()->routeIs('users.*') ? 'active' : (request()->routeIs('backup.*') ? 'active' : (request()->routeIs('permissions.*') ? 'active' : (request()->routeIs('company.close_year.page') ? 'active' : (request()->routeIs('roles.*') ? 'active' : (request()->routeIs('product_categories.*') ? 'active' : (request()->routeIs('password.*') ? 'active' : '')))))))) }}" href="#settings-submenu-1" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('settings.*') ? 'true' : 'false' }}" aria-controls="settings-submenu-1">
        <span class="d-flex align-items-center"><i class="bi bi-gear-fill me-2"></i> <span class="nav-label">Settings</span></span>
        <i class="bi bi-chevron-down"></i>
    </a>
    <div class="collapse {{ request()->routeIs('profile.edit') ? 'show' : (request()->routeIs('company.edit') ? 'show' : (request()->routeIs('users.*') ? 'show' : (request()->routeIs('backup.*') ? 'show' : (request()->routeIs('permissions.*') ? 'show' : (request()->routeIs('company.close_year.page') ? 'show' : (request()->routeIs('roles.*') ? 'show' : (request()->routeIs('product_categories.*') ? 'show' : (request()->routeIs('password.*') ? 'show' : '')))))))) }} mt-2" id="settings-submenu-1">
        <ul class="nav flex-column ms-3">
            <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                    <i class="bi bi-person-circle me-2"></i> <span class="nav-label">Personal Settings</span>
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('company.edit') ? 'active' : '' }}" href="{{ route('company.edit') }}">
                    <i class="bi bi-building  me-2"></i> <span class="nav-label">Company Settings</span>
                </a>
            </li>

            
            @if(( auth()->user()->hasCrmPermission('create_crm_user_guard') || auth()->user()->hasCrmPermission('edit_crm_user_guard') || auth()->user()->hasCrmPermission('delete_crm_user_guard') || auth()->user()->hasCrmPermission('manage_crm_user_guard')) && auth()->user()->hasCrmPermission('manage_crm_user_guard'))
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{route('users.index')}}"><i class="bi bi-people me-2"></i> <span class="nav-label">User Settings</span></a></li>
            @endif
            @if(auth()->user()->hasCrmPermission('crm_backup_guard'))
            <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('backup.*') ? 'active' : '' }}" href="{{ route('backup') }}">
                    <i class="bi bi-database me-2"></i><span class="nav-label">Backup Data</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->hasCrmPermission('crm_close_financial_year_guard'))            
            <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('company.close_year.page') ? 'active' : '' }}" href="{{ route('company.close_year.page') }}">
                    <i class="bi bi-calendar-event me-2"></i><span class="nav-label">Close Financial Year</span>
                </a>
            </li>
            @endif
            @if(auth()->user()->hasCrmPermission('manage_crm_permission_guard'))
            <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('permissions.*') ? 'active' : '' }}" href="{{ route('permissions.index') }}"><i class="bi bi-shield-lock me-2"></i> Permissions</a></li>
            @endif
            @if(auth()->user()->hasCrmPermission('manage_crm_role_guard'))
            <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-person-badge me-2"></i> Roles</a></li>
            @endif
            <!-- @if(auth()->user()->hasCrmPermission('manage_crm_tax_guard'))
            <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('tax_rates.*') ? 'active' : '' }}" href="{{ route('tax_rates.index') }}"><i class="bi bi-percent me-2"></i> Tax Rates</a></li>
            @endif -->
            @if(auth()->user()->hasCrmPermission('manage_crm_product_category_guard'))
            <li class="nav-item mb-1"><a class="nav-link {{ request()->routeIs('product_categories.*') ? 'active' : '' }}" href="{{ route('product_categories.index') }}"><i class="bi bi-tags me-2"></i> Product Categories</a></li>
            @endif
            
            <li class="nav-item mb-2">
                <a class="nav-link {{ request()->routeIs('password.*') ? 'active' : '' }}" href="{{ route('password.edit') }}">
                    <i class="bi bi-lock-fill me-2"></i> <span class="nav-label">Change Password</span>
                </a>
            </li>
        </ul>
    </div>
</li>
