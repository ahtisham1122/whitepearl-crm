<!--! ================================================================ !-->
<!--! [Start] Header !-->
<!--! ================================================================ !-->
<header class="nxl-header">
    <div class="header-wrapper">
        <!--! [Start] Header Left !-->
        <div class="header-left d-flex align-items-center gap-4">
            <a href="javascript:void(0);" class="nxl-head-mobile-toggler" id="mobile-collapse">
                <div class="hamburger hamburger--arrowturn">
                    <div class="hamburger-box">
                        <div class="hamburger-inner"></div>
                    </div>
                </div>
            </a>
            <div class="nxl-navigation-toggle">
                <a href="javascript:void(0);" id="menu-mini-button">
                    <i class="feather-align-left"></i>
                </a>
                <a href="javascript:void(0);" id="menu-expend-button" style="display: none">
                    <i class="feather-arrow-right"></i>
                </a>
            </div>
        </div>
        <!--! [End] Header Left !-->
        <!--! [Start] Header Right !-->
        <div class="header-right ms-auto">
            <div class="d-flex align-items-center">
                <div class="dropdown nxl-h-item nxl-header-search">
                    <a href="javascript:void(0);" class="nxl-head-link me-0" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="feather-search"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-search-dropdown">
                        <div class="input-group search-form">
                            <span class="input-group-text">
                                <i class="feather-search fs-6 text-muted"></i>
                            </span>
                            <input type="text" class="form-control search-input-field" placeholder="Search...." />
                            <span class="input-group-text">
                                <button type="button" class="btn-close"></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="nxl-h-item d-none d-sm-flex">
                    <div class="full-screen-switcher">
                        <a href="javascript:void(0);" class="nxl-head-link me-0" onclick="$('body').fullScreenHelper('toggle');">
                            <i class="feather-maximize maximize"></i>
                            <i class="feather-minimize minimize"></i>
                        </a>
                    </div>
                </div>
                <div class="nxl-h-item dark-light-theme">
                    <a href="javascript:void(0);" class="nxl-head-link me-0 dark-button">
                        <i class="feather-moon"></i>
                    </a>
                    <a href="javascript:void(0);" class="nxl-head-link me-0 light-button" style="display: none">
                        <i class="feather-sun"></i>
                    </a>
                </div>
                <div class="dropdown nxl-h-item">
                    <a class="nxl-head-link me-3" data-bs-toggle="dropdown" href="javascript:void(0);" role="button" data-bs-auto-close="outside">
                        <i class="feather-bell"></i>
                        @if ($notificationCount > 0)
                            <span class="nxl-h-badge badge bg-danger rounded-pill">
                                {{ $notificationCount > 9 ? '9+' : $notificationCount }}
                            </span>
                        @endif
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-notifications-menu">
                        <div class="d-flex justify-content-between align-items-center notifications-head">
                            <h6 class="fw-bold text-dark mb-0">Notifications</h6>
                        </div>
                        @if ($notificationAlerts->isNotEmpty())
                            <div style="max-height: 320px; overflow-y: auto;">
                                @foreach ($notificationAlerts as $alert)
                                    <a href="{{ $alert['url'] }}" class="dropdown-item border-bottom py-2 white-space-normal">
                                        <div class="d-flex align-items-start gap-2">
                                            <i class="{{ $alert['icon'] }} text-{{ $alert['color'] }} mt-1"></i>
                                            <div>
                                                <p class="mb-0 fw-medium fs-13">{{ $alert['type'] }}: {{ $alert['title'] }}</p>
                                                <p class="mb-0 text-muted fs-12">{{ $alert['message'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="text-center py-2 border-top">
                                <a href="{{ route('notifications.index') }}" class="fw-medium">View All Notifications</a>
                            </div>
                        @else
                            <div class="d-flex justify-content-between align-items-center flex-column py-5">
                                <i class="feather-bell fs-1 mb-4"></i>
                                <p class="text-muted mb-0">No notifications yet</p>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="dropdown nxl-h-item">
                    <a href="javascript:void(0);" data-bs-toggle="dropdown" role="button" data-bs-auto-close="outside">
                        <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image" class="img-fluid user-avtar me-0" />
                    </a>
                    <div class="dropdown-menu dropdown-menu-end nxl-h-dropdown nxl-user-dropdown">
                        <div class="dropdown-header">
                            <div class="d-flex align-items-center">
                                <img src="{{ asset('assets/images/avatar/1.png') }}" alt="user-image" class="img-fluid user-avtar" />
                                <div>
                                    <h6 class="text-dark mb-0">{{ auth()->user()->name }}</h6>
                                    <span class="fs-12 fw-medium text-muted">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="feather-log-out"></i>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--! [End] Header Right !-->
    </div>
</header>
<!--! ================================================================ !-->
<!--! [End] Header !-->
<!--! ================================================================ !-->
