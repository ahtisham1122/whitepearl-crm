@extends('layouts.print')

@section('title', $payment->receipt_number)

@section('content')
    <div class="no-print d-flex justify-content-between align-items-center">
        <a href="{{ route('payments.show', $payment) }}" class="btn btn-light">
            <i class="feather-arrow-left me-2"></i>Back
        </a>
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="feather-printer me-2"></i>Print Receipt
        </button>
    </div>

    <div class="print-sheet">
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">White Pearl City Gwadar</h4>
            <p class="text-muted mb-0">Payment Receipt</p>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <p class="mb-1"><strong>Receipt #:</strong> {{ $payment->receipt_number }}</p>
                <p class="mb-1"><strong>Payment Date:</strong> {{ $payment->payment_date->format('d M Y') }}</p>
                <p class="mb-0"><strong>Booking #:</strong> {{ $payment->booking->booking_number }}</p>
            </div>
            <div class="col-6 text-md-end">
                <p class="mb-1"><strong>Client:</strong> {{ $payment->booking->client->full_name }}</p>
                <p class="mb-1"><strong>Client ID:</strong> {{ $payment->booking->client->client_id }}</p>
                <p class="mb-0">
                    <strong>Plot:</strong>
                    {{ $payment->booking->plot->block->name }} - {{ $payment->booking->plot->plot_number }}
                </p>
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <tbody>
                <tr>
                    <th class="w-50">Amount Received</th>
                    <td>Rs. {{ number_format($payment->amount, 2) }}</td>
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
                <tr class="table-light">
                    <th>Client's Total Outstanding Balance</th>
                    <td><strong>Rs. {{ number_format($outstandingBalance, 2) }}</strong></td>
                </tr>
            </tbody>
        </table>

        <p class="text-muted text-center mb-0 fs-12">This is a system-generated receipt. Thank you.</p>
    </div>
@endsection
