<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $title }} - {{ $booking->client->full_name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        table.schedule {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 50px;
        }

        table.schedule th,
        table.schedule td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            text-align: left;
        }

        table.schedule th {
            background: #f3f4f6;
        }

        table.signatures {
            width: 100%;
            margin-top: 40px;
        }

        table.signatures td {
            width: 50%;
            border-top: 1px solid #1f2937;
            padding-top: 6px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>WHITE PEARL CITY</h1>
        <p>(Sale Installment Schedule)</p>
        <p>Page 1</p>
    </div>

    @if ($booking->status !== 'Active')
        <p style="text-align: center; font-weight: bold; border: 1px solid #92400e; background: #fef3c7; color: #92400e; padding: 6px;">
            Status: {{ $booking->status }} -- this is historical data for a booking that is no longer active.
        </p>
    @endif

    @include('reports.partials.client-statement-summary')

    <table class="schedule">
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
                    <td colspan="{{ count($headings) }}">This booking has no installment plan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>Signature</td>
            <td style="text-align: right;">White Pearl City</td>
        </tr>
    </table>
</body>

</html>
