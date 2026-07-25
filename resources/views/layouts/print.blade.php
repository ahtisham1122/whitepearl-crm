<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>@yield('title', 'Receipt') | {{ config('app.name') }}</title>

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}" />

    <style>
        body {
            background: #f3f4f6;
            padding: 24px;
        }

        .print-sheet {
            max-width: 720px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #dee2e6;
            padding: 32px;
        }

        .no-print {
            max-width: 720px;
            margin: 0 auto 16px auto;
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .print-sheet {
                border: none;
                max-width: 100%;
                padding: 0;
            }
        }
    </style>
</head>

<body>
    @yield('content')
</body>

</html>
