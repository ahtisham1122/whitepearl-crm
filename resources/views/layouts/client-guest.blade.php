<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="x-ua-compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>@yield('title', 'Client Login') | {{ setting('company_name', config('app.name')) }}</title>

    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon.ico') }}" />

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/vendors/css/vendors.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/theme.min.css') }}" />

    @stack('styles')
</head>

<body>
    <main class="d-flex align-items-center justify-content-center" style="min-height: 100vh; padding: 1.5rem; background-color: var(--bs-tertiary-bg, #f5f6fa);">
        <div class="w-100" style="max-width: 400px;">
            <div class="text-center mb-4">
                <h4 class="fw-bold mb-1">{{ setting('company_name', config('app.name')) }}</h4>
                <p class="text-muted fs-13 mb-0">Client Portal</p>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    @yield('content')
                </div>
            </div>
        </div>
    </main>

    <script src="{{ asset('assets/vendors/js/vendors.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
