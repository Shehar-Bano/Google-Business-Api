<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
                @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">Laravel Starter</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="mdi menu-toggle-icon d-xl-block align-middle mdi-20px"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}">
            <a href="{{ route('admin.dashboard.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-home-outline"></i>
                <div>Dashboard</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.user-management.*') ? 'active' : '' }}">
            <a href="{{ route('admin.user-management.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-account-group-outline"></i>
                <div>User Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.order-management.*') ? 'active' : '' }}">
            <a href="{{ route('admin.order-management.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-clipboard-list-outline"></i>
                <div>Order Management</div>
            </a>
        </li>

        <li
            class="menu-item {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.users.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-shield-account-outline"></i>
                <div>Access Control</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link">
                        <div>Roles</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link">
                        <div>Permissions</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link">
                        <div>Users</div>
                    </a>
                </li>
            </ul>
        </li>

        <li
            class="menu-item {{ request()->routeIs('admin.support-options.*') || request()->routeIs('admin.privacy-policy.*') || request()->routeIs('admin.notifications.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-lifebuoy"></i>
                <div>Support & Content</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.support-options.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.support-options.index') }}" class="menu-link">
                        <div>Help & Support</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.privacy-policy.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.privacy-policy.edit') }}" class="menu-link">
                        <div>Privacy Policy</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.notifications.index') }}" class="menu-link">
                        <div>Notifications</div>
                    </a>
                </li>
            </ul>
        </li>





        {{-- TODO: Add feature-specific menu groups here in future. --}}
    </ul>
</aside>
