@php
    $containerNav = $containerNav ?? 'container-fluid';
    $navbarDetached = $navbarDetached ?? '';
@endphp

<!-- Navbar -->
@if (isset($navbarDetached) && $navbarDetached == 'navbar-detached')
    <nav class="layout-navbar app-header {{ $containerNav }} navbar navbar-expand-xl {{ $navbarDetached }} bg-navbar-theme"
        id="layout-navbar">
@endif
@if (isset($navbarDetached) && $navbarDetached == '')
    <nav class="layout-navbar app-header navbar navbar-expand-xl bg-navbar-theme" id="layout-navbar">
        <div class="{{ $containerNav }}">
@endif


{{-- <!--  Brand demo (display only for navbar-full and hide on below xl) -->\n@if (isset($navbarFull)) --}}
    {{-- <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
            <span class="app-brand-logo demo">
                @include('_partials.macros', ['height' => 20, 'color' => '#B5F23C'])
            </span>
            <span class="app-brand-text demo menu-text fw-semibold ms-1">Squash Pro</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="mdi menu-toggle-icon d-xl-block align-middle mdi-20px"></i>
        </a>
    </div> --}}


<!-- Menu Toggle (Mobile) -->
@if (!isset($navbarHideToggle))
    <div class="layout-menu-toggle navbar-nav d-xl-none">
        <a class="nav-item nav-link px-0 me-3" href="javascript:void(0)">
            <i class="mdi mdi-menu mdi-24px"></i>
        </a>
    </div>
@endif

<!-- Header Content -->
<div class="app-header-content">
    <!-- Left Section: Search -->
    <div class="app-header-left">
        {{-- <div class="app-search">
            <i class="mdi mdi-magnify app-search-icon"></i>
            <input type="text" class="app-search-input" placeholder="Search..." aria-label="Search">
        </div> --}}
    </div>

    <!-- Right Section: Notifications + Profile -->
    <div class="app-header-right">
        <!-- Notification -->
        <div class="app-header-item">
            <a href="javascript:void(0);" class="app-header-icon" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="mdi mdi-bell-outline"></i>
                <span class="app-badge-dot"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end app-dropdown-notifications">
                <li class="dropdown-header">
                    <div class="d-flex align-items-center justify-content-between py-2 px-3">
                        <h6 class="mb-0">Notifications</h6>
                        <a href="javascript:void(0)" class="text-muted small">
                            <i class="mdi mdi-check-all"></i> Mark all read
                        </a>
                    </div>
                </li>
                <li><hr class="dropdown-divider m-0"></li>
                <li class="dropdown-notifications-list">
                    <ul class="list-unstyled mb-0">
                        <!-- Notification Item -->
                        <li class="dropdown-notifications-item">
                            <a href="javascript:void(0)" class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="app-avatar app-avatar-sm">
                                        <img src="{{ asset('assets/img/avatars/5.png') }}" alt="Avatar" class="rounded-circle">
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">New message from Jane</h6>
                                    <p class="mb-1 small text-muted">Hey there, how are you doing?</p>
                                    <small class="text-muted">Today, 10:30 AM</small>
                                </div>
                            </a>
                        </li>
                        <!-- Notification Item -->
                        <li class="dropdown-notifications-item">
                            <a href="javascript:void(0)" class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="app-avatar app-avatar-sm bg-warning">
                                        <i class="mdi mdi-alert-circle-outline"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">System Alert</h6>
                                    <p class="mb-1 small text-muted">Storage is almost full (85%)</p>
                                    <small class="text-muted">Yesterday, 3:45 PM</small>
                                </div>
                            </a>
                        </li>
                        <!-- Notification Item -->
                        <li class="dropdown-notifications-item">
                            <a href="javascript:void(0)" class="d-flex">
                                <div class="flex-shrink-0 me-3">
                                    <div class="app-avatar app-avatar-sm bg-success">
                                        <i class="mdi mdi-cart-outline"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 small">Order Completed</h6>
                                    <p class="mb-1 small text-muted">Order #12345 has been shipped</p>
                                    <small class="text-muted">May 12, 2024</small>
                                </div>
                            </a>
                        </li>
                    </ul>
                </li>
                <li><hr class="dropdown-divider m-0"></li>
                <li class="dropdown-footer">
                    <a href="javascript:void(0)" class="d-block text-center py-2 small">
                        View all notifications
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Profile -->
        <div class="app-header-item">
            <a href="javascript:void(0);" class="app-header-profile" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="app-avatar">
                    <img src="{{ asset('assets/img/avatars/1.png') }}" alt="User" class="rounded-circle">
                    <span class="app-avatar-status"></span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end app-dropdown-profile">
                <li class="dropdown-header">
                    <div class="d-flex align-items-center py-2 px-3">
                        <div class="app-avatar me-3">
                            <img src="{{ asset('assets/img/avatars/1.png') }}" alt="User" class="rounded-circle">
                        </div>
                        <div>
                            <h6 class="mb-0">John Doe</h6>
                            <small class="text-muted">Admin</small>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider m-0"></li>
                <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="mdi mdi-account-outline me-2"></i>
                        <span>My Profile</span>
                    </a>
                </li>
                {{-- <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="mdi mdi-cog-outline me-2"></i>
                        <span>Settings</span>
                    </a>
                </li> --}}
                {{-- <li>
                    <a class="dropdown-item" href="javascript:void(0)">
                        <i class="mdi mdi-credit-card-outline me-2"></i>
                        <span>Billing</span>
                        <span class="badge bg-danger ms-auto">4</span>
                    </a>
                </li> --}}
                <li><hr class="dropdown-divider m-0"></li>
                <li>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="mdi mdi-logout me-2"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

@if (!isset($navbarDetached))
    </div>
@endif
</nav>
<!-- / Navbar -->
