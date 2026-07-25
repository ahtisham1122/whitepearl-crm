<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 16px;
        }

        .header h1 {
            font-size: 16px;
            margin: 0 0 2px 0;
        }

        .header p {
            margin: 0;
            color: #6b7280;
            font-size: 10px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            text-align: left;
        }

        table.data th {
            background: #f3f4f6;
        }

        tr.totals td {
            font-weight: bold;
            background: #f9fafb;
        }
    </style>
</head>

<body>
    <div class="header">
        @include('partials.company-header-pdf', ['subtitle' => $title])
        <p>Generated {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <table class="data">
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}">No records found for the selected filters.</td>
                </tr>
            @endforelse

            @if ($totalsRow)
                <tr class="totals">
                    @foreach ($totalsRow as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>
</body>

</html>
