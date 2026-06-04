<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
                @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">Squash Pro</span>
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

        <li class="menu-item {{ request()->routeIs('admin.clubs.*') || request()->routeIs('admin.courts.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-domain"></i>
                <div>Club Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.clubs.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.clubs.index') }}" class="menu-link"><div>Clubs</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.courts.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.courts.index') }}" class="menu-link"><div>Courts</div></a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.players.*') ? 'active' : '' }}">
            <a href="{{ route('admin.players.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-account-group-outline"></i>
                <div>Player Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.bookings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-calendar-check-outline"></i>
                <div>Booking Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.tournaments.*') ? 'active' : '' }}">
            <a href="{{ route('admin.tournaments.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-trophy-outline"></i>
                <div>Tournament Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.payments.*') || request()->routeIs('admin.revenue.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-cash-multiple"></i>
                <div>Finance</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.payments.index') }}" class="menu-link"><div>Payments</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.revenue.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.revenue.index') }}" class="menu-link"><div>Revenue Reports</div></a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <a href="{{ route('admin.notifications.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-bell-outline"></i>
                <div>Notifications</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
            <a href="{{ route('admin.reports.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-chart-box-outline"></i>
                <div>Reports</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.support-options.*') || request()->routeIs('admin.privacy-policy.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-file-document-edit-outline"></i>
                <div>Content Management</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.support-options.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.support-options.index') }}" class="menu-link"><div>Help & Support</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.privacy-policy.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.privacy-policy.edit') }}" class="menu-link"><div>Privacy Policy</div></a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.users.*') ? 'open' : '' }}">
            <a href="#" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-shield-account-outline"></i>
                <div>Access Control</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.roles.index') }}" class="menu-link"><div>Roles</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.permissions.index') }}" class="menu-link"><div>Permissions</div></a>
                </li>
                <li class="menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="menu-link"><div>Users</div></a>
                </li>
            </ul>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
            <a href="{{ route('admin.settings.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-cog-outline"></i>
                <div>Settings</div>
            </a>
        </li>
    </ul>
</aside>
