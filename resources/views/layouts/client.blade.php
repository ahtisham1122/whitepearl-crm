<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'My Account') | {{ setting('company_name', config('app.name')) }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />

    @stack('styles')
</head>

<body style="background-color: var(--bs-tertiary-bg, #f5f6fa);">
    <nav class="navbar navbar-expand bg-white border-bottom">
        <div class="container">
            <span class="navbar-brand fw-bold">{{ setting('company_name', config('app.name')) }}</span>
            <span class="ms-2 text-muted fs-12 d-none d-sm-inline">Client Portal</span>

            <div class="ms-auto d-flex align-items-center gap-3">
                <span class="fs-13 text-muted d-none d-md-inline">{{ auth('client')->user()->full_name }}</span>
                <form method="POST" action="{{ route('client.logout') }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="feather-log-out me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <main class="container py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
