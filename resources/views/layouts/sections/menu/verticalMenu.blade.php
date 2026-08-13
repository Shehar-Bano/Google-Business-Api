<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/') }}" class="app-brand-link">
            <span class="app-brand-logo demo me-1">
                @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-2">Google Business's</span>
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
        <li class="menu-item {{ request()->routeIs('admin.business-management.*') ? 'active' : '' }}">
            <a href="{{ route('admin.business-management.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-briefcase-outline"></i>
                <div>Business Management</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.estimated-scores.*') ? 'active' : '' }}">
            <a href="{{ route('admin.estimated-scores.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-star-box-outline"></i>
                <div>Estimated Scores</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.google-business-connections.*') ? 'active' : '' }}">
            <a href="{{ route('admin.google-business-connections.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-google"></i>
                <div>Google Business Integration</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.whatsapp-review-requests.*') ? 'active' : '' }}">
            <a href="{{ route('admin.whatsapp-review-requests.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-whatsapp"></i>
                <div>WhatsApp Review Requests</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.posters.*') ? 'active' : '' }}">
            <a href="{{ route('admin.posters.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-image-multiple-outline"></i>
                <div>Poster Templates</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.ai-generated-posters.*') ? 'active' : '' }}">
            <a href="{{ route('admin.ai-generated-posters.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-creation-outline"></i>
                <div>AI Generated Posters</div>
            </a>
        </li>
        <li class="menu-item {{ request()->routeIs('admin.videos.*') ? 'active' : '' }}">
            <a href="{{ route('admin.videos.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-video-outline"></i>
                <div>Video Shorts</div>
            </a>
        </li>

        {{-- <li class="menu-item {{ request()->routeIs('admin.order-management.*') ? 'active' : '' }}">
            <a href="{{ route('admin.order-management.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-clipboard-list-outline"></i>
                <div>Order Management</div>
            </a>
        </li> --}}

        <li class="menu-item {{ request()->routeIs('admin.plan-features.*') ? 'active' : '' }}">
            <a href="{{ route('admin.plan-features.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-format-list-checks"></i>
                <div>Plan Features</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('admin.subscription-plans.*') ? 'active' : '' }}">
            <a href="{{ route('admin.subscription-plans.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-credit-card-outline"></i>
                <div>Subscriptions</div>
            </a>
        </li>

        <li
            class="menu-item {{ request()->routeIs('admin.roles.*') || request()->routeIs('admin.permissions.*') || request()->routeIs('admin.users.*') || request()->routeIs('admin.audit-logs.*') ? 'open' : '' }}">
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
                <li class="menu-item {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.audit-logs.index') }}" class="menu-link">
                        <div>Audit Logs</div>
                    </a>
                </li>
            </ul>
        </li>

        <li
            class="menu-item {{ request()->routeIs('admin.support-options.*') || request()->routeIs('admin.privacy-policy.*') || request()->routeIs('admin.terms-conditions.*') || request()->routeIs('admin.notifications.*') ? 'open' : '' }}">
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
                <li class="menu-item {{ request()->routeIs('admin.terms-conditions.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.terms-conditions.index') }}" class="menu-link">
                        <div>Terms & Conditions</div>
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
