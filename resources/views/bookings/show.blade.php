@extends('layouts.app')

@section('title', $booking->booking_number)

@php
    $typeBadgeClass = [
        'Reservation' => 'bg-info',
        'Sale' => 'bg-success',
    ];

    $statusBadgeClass = [
        'Active' => 'bg-success',
        'Cancelled' => 'bg-secondary',
    ];

    $installmentStatusBadgeClass = [
        'Pending' => 'bg-warning',
        'Partially Paid' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger',
    ];

    $remainingForBooking = $booking->installmentPlan?->installments
        ->sum(fn ($installment) => max($installment->amount + $installment->late_fee - $installment->paid_amount, 0));
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Booking {{ $booking->booking_number }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item">{{ $booking->booking_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto d-flex gap-2">
            @if ($booking->status === 'Active')
                <form
                    method="POST"
                    action="{{ route('bookings.cancel', $booking) }}"
                    onsubmit="return confirm('Cancel this booking? The plot will become Available again.');"
                >
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="feather-x-circle me-2"></i>Cancel Booking
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-8">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Booking Details</h6>
                            <div class="d-flex gap-2">
                                <span class="badge {{ $typeBadgeClass[$booking->booking_type] ?? 'bg-secondary' }}">
                                    {{ $booking->booking_type }}
                                </span>
                                <span class="badge {{ $statusBadgeClass[$booking->status] ?? 'bg-secondary' }}">
                                    {{ $booking->status }}
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Booking Date:</strong> {{ $booking->booking_date->format('d M Y') }}</div>
                            <div class="col-md-6 mb-2"><strong>Sales Person:</strong> {{ $booking->sales_person ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>Total Price:</strong> Rs. {{ number_format($booking->total_price, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Discount:</strong> Rs. {{ number_format($booking->discount, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Final Price:</strong> Rs. {{ number_format($booking->final_price, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Booking Amount (down payment):</strong> Rs. {{ number_format($booking->booking_amount, 2) }}</div>
                            @if ($booking->installmentPlan)
                                <div class="col-md-6 mb-2"><strong>Remaining Balance:</strong> Rs. {{ number_format($remainingForBooking ?? 0, 2) }}</div>
                            @endif
                            @if ($booking->remarks)
                                <div class="col-12 mb-2"><strong>Remarks:</strong> {{ $booking->remarks }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Payments</h6>
                            @if ($booking->installmentPlan && $booking->status === 'Active')
                                <a href="{{ route('payments.create', ['booking_id' => $booking->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="feather-plus"></i> Receive Payment
                                </a>
                            @endif
                        </div>

                        @if ($booking->payments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Receipt #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($booking->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->receipt_number }}</td>
                                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-light-brand">
                                                        <i class="feather-printer"></i> Receipt
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No payments recorded yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Installment Plan</h6>
                            @if ($booking->installmentPlan)
                                <a href="{{ route('installment-plans.show', $booking->installmentPlan) }}" class="btn btn-sm btn-light-brand">
                                    <i class="feather-eye"></i> View Full Plan
                                </a>
                            @elseif ($booking->status === 'Active')
                                <a href="{{ route('installment-plans.create', ['booking' => $booking->id]) }}" class="btn btn-sm btn-primary">
                                    <i class="feather-plus"></i> Create Installment Plan
                                </a>
                            @endif
                        </div>

                        @if ($booking->installmentPlan)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($booking->installmentPlan->installments as $installment)
                                            <tr class="{{ $installment->displayStatus() === 'Overdue' ? 'table-danger' : '' }}">
                                                <td>{{ $installment->installment_number }}</td>
                                                <td>{{ $installment->due_date->format('d M Y') }}</td>
                                                <td>{{ number_format($installment->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $installmentStatusBadgeClass[$installment->displayStatus()] ?? 'bg-secondary' }}">
                                                        {{ $installment->displayStatus() }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No installment plan yet for this booking.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Client</h6>
                        <p class="mb-1"><strong>{{ $booking->client->full_name }}</strong></p>
                        <p class="text-muted mb-2">{{ $booking->client->client_id }}</p>
                        <p class="mb-1">{{ $booking->client->phone }}</p>
                        <a href="{{ route('clients.show', $booking->client) }}" class="btn btn-sm btn-light-brand mt-2">
                            <i class="feather-eye"></i> View Client Profile
                        </a>
                    </div>
                </div>

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Plot</h6>
                        <p class="mb-1"><strong>{{ $booking->plot->block->name }} - Plot {{ $booking->plot->plot_number }}</strong></p>
                        <p class="text-muted mb-2">{{ $booking->plot->plot_size }} - {{ $booking->plot->category }}</p>
                        <a href="{{ route('plots.show', $booking->plot) }}" class="btn btn-sm btn-light-brand mt-2">
                            <i class="feather-eye"></i> View Plot
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
