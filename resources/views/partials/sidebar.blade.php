<!--! ================================================================ !-->
<!--! [Start] Navigation Menu !-->
<!--! ================================================================ !-->
<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header">
            <a href="{{ route('dashboard') }}" class="b-brand">
                <img src="{{ asset('assets/images/logo-full.png') }}" alt="{{ config('app.name') }}" class="logo logo-lg" />
                <img src="{{ asset('assets/images/logo-abbr.png') }}" alt="{{ config('app.name') }}" class="logo logo-sm" />
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
            </ul>
        </div>
    </div>
</nav>
<!--! ================================================================ !-->
<!--! [End] Navigation Menu !-->
<!--! ================================================================ !-->
