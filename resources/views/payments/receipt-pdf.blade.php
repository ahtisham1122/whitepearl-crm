<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>{{ $payment->receipt_number }}</title>
    <style>
        /*
            dompdf only understands a subset of CSS (no flexbox, no grid),
            so this view is deliberately plain HTML tables and inline-style
            friendly CSS instead of the Bootstrap classes the rest of the
            app uses.
        */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #1f2937;
        }

        .header {
            text-align: center;
            margin-bottom: 24px;
        }

        .header h1 {
            font-size: 20px;
            margin: 0 0 4px 0;
        }

        .header p {
            margin: 0;
            color: #6b7280;
        }

        table.meta {
            width: 100%;
            margin-bottom: 20px;
        }

        table.meta td {
            vertical-align: top;
            width: 50%;
        }

        table.details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        table.details th,
        table.details td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }

        table.details th {
            width: 40%;
            background: #f3f4f6;
        }

        table.signatures {
            width: 100%;
            margin-top: 60px;
        }

        table.signatures td {
            width: 50%;
            border-top: 1px solid #1f2937;
            padding-top: 6px;
        }

        .footer {
            text-align: center;
            color: #6b7280;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>White Pearl City Gwadar</h1>
        <p>Payment Receipt</p>
    </div>

    <table class="meta">
        <tr>
            <td>
                <strong>Receipt #:</strong> {{ $payment->receipt_number }}<br />
                <strong>Payment Date:</strong> {{ $payment->payment_date->format('d M Y') }}<br />
                <strong>Booking #:</strong> {{ $payment->booking->booking_number }}
            </td>
            <td>
                <strong>Client:</strong> {{ $payment->booking->client->full_name }}<br />
                <strong>Father Name:</strong> {{ $payment->booking->client->father_name ?? '—' }}<br />
                <strong>CNIC:</strong> {{ $payment->booking->client->cnic }}<br />
                <strong>Phone:</strong> {{ $payment->booking->client->phone }}<br />
                <strong>Plot:</strong> {{ $payment->booking->plot->block->name }} - {{ $payment->booking->plot->plot_number }}
            </td>
        </tr>
    </table>

    <table class="details">
        <tr>
            <th>Amount Received</th>
            <td>Rs. {{ number_format($payment->amount, 2) }}</td>
        </tr>
        <tr>
            <th>Amount in Words</th>
            <td>{{ \App\Support\NumberToWords::convertRupees((float) $payment->amount) }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ $payment->payment_method }}</td>
        </tr>
        <tr>
            <th>Payment Type</th>
            <td>{{ $payment->payment_type }}</td>
        </tr>
        @if ($payment->reference_no)
            <tr>
                <th>Reference No.</th>
                <td>{{ $payment->reference_no }}</td>
            </tr>
        @endif
        <tr>
            <th>Applied To</th>
            <td>
                @if ($payment->installment)
                    Installment #{{ $payment->installment->installment_number }}
                    (Due {{ $payment->installment->due_date->format('d M Y') }})
                @else
                    Auto-applied to oldest unpaid installment(s)
                @endif
            </td>
        </tr>
        @if ($payment->late_fee > 0)
            <tr>
                <th>Late Fee Charged</th>
                <td>Rs. {{ number_format($payment->late_fee, 2) }}</td>
            </tr>
        @endif
        @if ($payment->remarks)
            <tr>
                <th>Remarks</th>
                <td>{{ $payment->remarks }}</td>
            </tr>
        @endif
        <tr>
            <th>Remaining Balance (this booking)</th>
            <td><strong>Rs. {{ number_format($remainingBalance, 2) }}</strong></td>
        </tr>
    </table>

    <table class="signatures">
        <tr>
            <td>Received By</td>
            <td style="text-align: right;">Authorized Signature</td>
        </tr>
    </table>

    <p class="footer">This is a system-generated receipt. Thank you.</p>
</body>

</html>
