@extends('layouts.client')

@section('title', 'My Account')

@php
    $installmentStatusBadgeClass = [
        'Pending' => 'bg-warning',
        'Partially Paid' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger',
    ];
@endphp

@section('content')
    <div class="mb-4">
        <h5 class="fw-bold mb-1">Welcome, {{ $client->full_name }}</h5>
        <p class="text-muted fs-13 mb-0">Client ID: {{ $client->client_id }}</p>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">My Profile</h6>
                    <p class="mb-1 fs-13"><strong>Name:</strong> {{ $client->full_name }}</p>
                    <p class="mb-1 fs-13"><strong>CNIC:</strong> {{ $client->cnic }}</p>
                    <p class="mb-0 fs-13"><strong>Phone:</strong> {{ $client->phone }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="fw-bold mb-2">Total Outstanding Balance</h6>
                    <h3 class="mb-0 {{ $outstandingBalance > 0 ? 'text-danger' : 'text-success' }}">
                        {{ setting('currency_symbol', 'Rs.') }} {{ number_format($outstandingBalance, 2) }}
                    </h3>
                    <p class="text-muted fs-12 mb-0">Across all your active plots.</p>
                </div>
            </div>
        </div>
    </div>

    @forelse ($bookings as $row)
        @php
            $booking = $row['booking'];
            $plot = $booking->plot;
        @endphp
        <div class="card mb-4">
            <div class="card-body">
                <h6 class="fw-bold mb-3">
                    <i class="feather-map-pin me-1"></i>
                    {{ $plot->block->name }} - Plot {{ $plot->plot_number }}
                </h6>

                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-11">Size</div>
                        <div class="fw-medium">{{ $plot->plot_size }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-11">Total Price</div>
                        <div class="fw-medium">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($booking->final_price, 2) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-11">Total Paid</div>
                        <div class="fw-medium text-success">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($row['total_paid'], 2) }}</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="text-muted fs-11">Outstanding</div>
                        <div class="fw-medium {{ $row['outstanding'] > 0 ? 'text-danger' : 'text-success' }}">
                            {{ setting('currency_symbol', 'Rs.') }} {{ number_format($row['outstanding'], 2) }}
                        </div>
                    </div>
                </div>

                @if ($booking->installmentPlan)
                    <h6 class="fs-13 fw-bold mb-2 mt-4">Installment Schedule</h6>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Late Fee</th>
                                    <th>Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($booking->installmentPlan->installments as $installment)
                                    <tr class="{{ $installment->displayStatus() === 'Overdue' ? 'table-danger' : '' }}">
                                        <td>{{ $installment->installment_number }}</td>
                                        <td>{{ $installment->due_date->format('d M Y') }}</td>
                                        <td>{{ number_format($installment->amount, 2) }}</td>
                                        <td>{{ number_format($installment->late_fee, 2) }}</td>
                                        <td>{{ number_format($installment->paid_amount, 2) }}</td>
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
                    <p class="text-muted fs-12 mb-0">No installment plan has been set up for this plot yet.</p>
                @endif
            </div>
        </div>
    @empty
        <div class="alert alert-light border">You don't have any active plots on record yet.</div>
    @endforelse

    <div class="card">
        <div class="card-body">
            <h6 class="fw-bold mb-3">Payment History</h6>
            @if ($payments->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Receipt #</th>
                                <th>Plot</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $payment)
                                <tr>
                                    <td>{{ $payment->receipt_number }}</td>
                                    <td>{{ $payment->booking->plot->block->name }} - {{ $payment->booking->plot->plot_number }}</td>
                                    <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td>{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('client.receipts.pdf', $payment) }}" class="btn btn-sm btn-light-brand">
                                            <i class="feather-download"></i> Receipt
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
@endsection
