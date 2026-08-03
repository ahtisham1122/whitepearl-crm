<!--! ================================================================ !-->
<!--! [Start] Navigation Menu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        {{--
            The White Pearl City logo is gold/white, designed to sit on a
            dark background -- so this header strip is forced to black
            here (inline style beats the theme's light/dark-mode CSS either
            way, so it stays correct no matter which theme the user picks).
            "filter:none" on the image blocks the theme's dark-mode
            invert-color effect, which is meant for the old Duralux mark
            and would otherwise wreck our logo's colors.
        --}}
        <div class="m-header" style="background: #000; justify-content: center; padding: 8px 16px;">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img
                    src="{{ asset('images/logo.png') }}"
                    alt="White Pearl City Gwadar"
                    class="logo logo-lg"
                    style="height: 60px; width: auto; max-width: 100%; object-fit: contain; filter: none; display: block;"
                />
            </a>
        </div>
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
                <li class="nxl-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home"></i></span>
                        <span class="nxl-mtext">Dashboard</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
                    <a href="{{ route('notifications.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bell"></i></span>
                        <span class="nxl-mtext">Notifications</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('blocks.*') ? 'active' : '' }}">
                    <a href="{{ route('blocks.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-grid"></i></span>
                        <span class="nxl-mtext">Blocks</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('plots.*') ? 'active' : '' }}">
                    <a href="{{ route('plots.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-map"></i></span>
                        <span class="nxl-mtext">Plots</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <a href="{{ route('clients.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-users"></i></span>
                        <span class="nxl-mtext">Clients</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('bookings.*') ? 'active' : '' }}">
                    <a href="{{ route('bookings.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file-text"></i></span>
                        <span class="nxl-mtext">Bookings</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('installment-plans.*') ? 'active' : '' }}">
                    <a href="{{ route('installment-plans.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-calendar"></i></span>
                        <span class="nxl-mtext">Installment Plans</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('payments.*') ? 'active' : '' }}">
                    <a href="{{ route('payments.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign"></i></span>
                        <span class="nxl-mtext">Payments</span>
                    </a>
                </li>
                <li class="nxl-item {{ request()->routeIs('receipts.*') ? 'active' : '' }}">
                    <a href="{{ route('receipts.index') }}" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-file"></i></span>
                        <span class="nxl-mtext">Receipts</span>
                    </a>
                </li>
                <li class="nxl-item nxl-hasmenu {{ request()->routeIs('reports.*') ? 'active nxl-trigger' : '' }}">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-bar-chart-2"></i></span>
                        <span class="nxl-mtext">Reports</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="{{ request()->routeIs('reports.clients*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.clients') }}">All Clients</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.plots*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.plots') }}">Plots by Status</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.blocks*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.blocks') }}">Block-wise Summary</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.collection*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.collection') }}">Collection Report</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.outstanding*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.outstanding') }}">Outstanding Balances</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.due-installments*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.due-installments') }}">Due Installments</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.defaulters*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.defaulters') }}">Defaulters</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.sales*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.sales') }}">Sales Report</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.plot-detail*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.plot-detail') }}">Plot Report</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.installments-progress*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.installments-progress') }}">Installments Paid Progress</a>
                        </li>
                        <li class="{{ request()->routeIs('reports.late-fees*') ? 'active' : '' }}">
                            <a class="nxl-link" href="{{ route('reports.late-fees') }}">Late Fee / Fine</a>
                        </li>
                    </ul>
                </li>
                @hasanyrole('Super Admin|Admin')
                    <li class="nxl-item {{ request()->routeIs('activity-logs.*') ? 'active' : '' }}">
                        <a href="{{ route('activity-logs.index') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-activity"></i></span>
                            <span class="nxl-mtext">Activity Logs</span>
                        </a>
                    </li>
                    <li class="nxl-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <a href="{{ route('settings.edit') }}" class="nxl-link">
                            <span class="nxl-micon"><i class="feather-settings"></i></span>
                            <span class="nxl-mtext">Settings</span>
                        </a>
                    </li>
                @endhasanyrole
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End] Navigation Menu !-->
<!--! ================================================================ !-->
