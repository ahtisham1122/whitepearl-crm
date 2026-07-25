@extends('layouts.app')

@section('title', $payment->receipt_number)

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Payment {{ $payment->receipt_number }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item">{{ $payment->receipt_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-primary">
                <i class="feather-printer me-2"></i>View / Print Receipt
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-8">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Payment Details</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Payment Date:</strong> {{ $payment->payment_date->format('d M Y') }}</div>
                            <div class="col-md-6 mb-2"><strong>Amount:</strong> Rs. {{ number_format($payment->amount, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Payment Method:</strong> {{ $payment->payment_method }}</div>
                            <div class="col-md-6 mb-2"><strong>Payment Type:</strong> {{ $payment->payment_type }}</div>
                            <div class="col-md-6 mb-2">
                                <strong>Applied To:</strong>
                                @if ($payment->installment)
                                    Installment #{{ $payment->installment->installment_number }}
                                    (Due {{ $payment->installment->due_date->format('d M Y') }})
                                @else
                                    Auto-applied to oldest unpaid installment(s)
                                @endif
                            </div>
                            @if ($payment->late_fee > 0)
                                <div class="col-md-6 mb-2"><strong>Late Fee Charged:</strong> Rs. {{ number_format($payment->late_fee, 2) }}</div>
                            @endif
                            @if ($payment->reference_no)
                                <div class="col-md-6 mb-2"><strong>Reference No.:</strong> {{ $payment->reference_no }}</div>
                            @endif
                            @if ($payment->remarks)
                                <div class="col-12 mb-2"><strong>Remarks:</strong> {{ $payment->remarks }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Client</h6>
                        <p class="mb-1"><strong>{{ $payment->booking->client->full_name }}</strong></p>
                        <p class="text-muted mb-2">{{ $payment->booking->client->client_id }}</p>
                        <a href="{{ route('clients.show', $payment->booking->client) }}" class="btn btn-sm btn-light-brand">
                            <i class="feather-eye"></i> View Client Profile
                        </a>
                    </div>
                </div>

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Booking &amp; Plot</h6>
                        <p class="mb-1"><strong>{{ $payment->booking->booking_number }}</strong></p>
                        <p class="text-muted mb-2">
                            {{ $payment->booking->plot->block->name }} - {{ $payment->booking->plot->plot_number }}
                        </p>
                        <a href="{{ route('bookings.show', $payment->booking) }}" class="btn btn-sm btn-light-brand">
                            <i class="feather-eye"></i> View Booking
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
