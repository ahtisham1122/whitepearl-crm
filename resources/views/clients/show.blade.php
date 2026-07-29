@extends('layouts.app')

@section('title', $client->full_name)

@php
    $statusBadgeClass = [
        'Active' => 'bg-success',
        'Inactive' => 'bg-secondary',
    ];

    $bookingTypeBadgeClass = [
        'Reservation' => 'bg-info',
        'Sale' => 'bg-success',
    ];

    $installmentStatusBadgeClass = [
        'Pending' => 'bg-warning',
        'Partially Paid' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger',
    ];

    $upcomingInstallments = $client->bookings
        ->filter(fn ($booking) => $booking->installmentPlan)
        ->flatMap(function ($booking) {
            return $booking->installmentPlan->installments
                ->filter(fn ($installment) => $installment->status !== 'Paid')
                ->map(fn ($installment) => ['booking' => $booking, 'installment' => $installment]);
        })
        ->sortBy(fn ($row) => $row['installment']->due_date);
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $client->full_name }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                <li class="breadcrumb-item">{{ $client->client_id }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary">
                <i class="feather-edit-2 me-2"></i>Edit Client
            </a>
        </div>
    </div>

    <div class="main-content">
        @if (session('generated_password'))
            <div class="alert alert-warning">
                <h6 class="fw-bold mb-2"><i class="feather-key me-2"></i>Client Portal Login -- Save This Now</h6>
                <p class="mb-2">Share these with the client. <strong>The password will not be shown again</strong> after you leave this page.</p>
                <div class="row g-2 align-items-center">
                    <div class="col-auto"><strong>CNIC (username):</strong></div>
                    <div class="col-auto"><code>{{ $client->cnic }}</code></div>
                </div>
                <div class="row g-2 align-items-center mt-1">
                    <div class="col-auto"><strong>Password:</strong></div>
                    <div class="col-auto">
                        <code id="generated_password_text">{{ session('generated_password') }}</code>
                    </div>
                    <div class="col-auto">
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-dark"
                            onclick="navigator.clipboard.writeText(document.getElementById('generated_password_text').textContent)"
                        >
                            <i class="feather-copy"></i> Copy
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-4">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body text-center">
                        @if ($client->profile_photo)
                            <img
                                src="{{ asset('storage/'.$client->profile_photo) }}"
                                alt="{{ $client->full_name }}"
                                class="rounded-circle mb-3"
                                style="height: 120px; width: 120px; object-fit: cover;"
                            />
                        @else
                            <div
                                class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3"
                                style="height: 120px; width: 120px;"
                            >
                                <i class="feather-user fs-1 text-muted"></i>
                            </div>
                        @endif
                        <h5 class="mb-1">{{ $client->full_name }}</h5>
                        <p class="text-muted mb-2">{{ $client->client_id }}</p>
                        <span class="badge {{ $statusBadgeClass[$client->status] ?? 'bg-secondary' }}">
                            {{ $client->status }}
                        </span>
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Client Portal Login</h6>
                        @if ($client->password)
                            <p class="text-success mb-3"><i class="feather-check-circle me-1"></i>This client has portal access.</p>
                        @else
                            <p class="text-muted mb-3"><i class="feather-x-circle me-1"></i>No portal login yet.</p>
                        @endif

                        <form method="POST" action="{{ route('clients.generate-login', $client) }}" onsubmit="return confirm('{{ $client->password ? 'This resets their password -- the old one stops working immediately. Continue?' : 'Create a Client Portal login for this client?' }}');">
                            @csrf
                            <button type="submit" class="btn btn-sm {{ $client->password ? 'btn-outline-warning' : 'btn-primary' }} w-100">
                                {{ $client->password ? 'Reset Password' : 'Generate Login' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Father Name:</strong> {{ $client->father_name ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>CNIC:</strong> {{ $client->cnic }}</div>
                            <div class="col-md-6 mb-2">
                                <strong>Date of Birth:</strong>
                                {{ $client->date_of_birth?->format('d M Y') ?? '—' }}
                            </div>
                            <div class="col-md-6 mb-2"><strong>Phone:</strong> {{ $client->phone }}</div>
                            <div class="col-md-6 mb-2"><strong>WhatsApp:</strong> {{ $client->whatsapp ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $client->email ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>Occupation:</strong> {{ $client->occupation ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>City:</strong> {{ $client->city ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>Country:</strong> {{ $client->country }}</div>
                            <div class="col-12 mb-2"><strong>Address:</strong> {{ $client->address ?? '—' }}</div>
                            @if ($client->notes)
                                <div class="col-12 mb-2"><strong>Notes:</strong> {{ $client->notes }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Nominee Information</h6>
                        @if ($client->nominee)
                            <div class="row">
                                <div class="col-md-6 mb-2"><strong>Name:</strong> {{ $client->nominee->name ?? '—' }}</div>
                                <div class="col-md-6 mb-2"><strong>Relationship:</strong> {{ $client->nominee->relationship ?? '—' }}</div>
                                <div class="col-md-6 mb-2"><strong>CNIC:</strong> {{ $client->nominee->cnic ?? '—' }}</div>
                                <div class="col-md-6 mb-2"><strong>Phone:</strong> {{ $client->nominee->phone ?? '—' }}</div>
                                <div class="col-md-6 mb-2"><strong>Email:</strong> {{ $client->nominee->email ?? '—' }}</div>
                                <div class="col-12 mb-2"><strong>Address:</strong> {{ $client->nominee->address ?? '—' }}</div>
                            </div>
                        @else
                            <p class="text-muted mb-0">No nominee added yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Documents</h6>
                        @if ($client->documents->isNotEmpty())
                            <ul class="list-unstyled mb-0">
                                @foreach ($client->documents as $document)
                                    <li class="mb-2">
                                        <i class="feather-file me-2"></i>
                                        <strong>{{ $document->document_type }}:</strong>
                                        <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank">
                                            {{ $document->original_name ?? 'View File' }}
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">No documents uploaded yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Owned Plots</h6>
                        @if ($client->bookings->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Plot</th>
                                            <th>Type</th>
                                            <th>Final Price</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($client->bookings as $booking)
                                            <tr>
                                                <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                                <td>
                                                    <span class="badge {{ $bookingTypeBadgeClass[$booking->booking_type] ?? 'bg-secondary' }}">
                                                        {{ $booking->booking_type }}
                                                    </span>
                                                </td>
                                                <td>{{ number_format($booking->final_price, 2) }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-light-brand">
                                                        <i class="feather-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">This client has not booked any plots yet.</p>
                        @endif
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Upcoming Installments</h6>
                        @if ($upcomingInstallments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Plot</th>
                                            <th>#</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($upcomingInstallments as $row)
                                            <tr class="{{ $row['installment']->displayStatus() === 'Overdue' ? 'table-danger' : '' }}">
                                                <td>
                                                    <a href="{{ route('bookings.show', $row['booking']) }}">
                                                        {{ $row['booking']->plot->block->name }} - {{ $row['booking']->plot->plot_number }}
                                                    </a>
                                                </td>
                                                <td>{{ $row['installment']->installment_number }}</td>
                                                <td>{{ $row['installment']->due_date->format('d M Y') }}</td>
                                                <td>{{ number_format($row['installment']->amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $installmentStatusBadgeClass[$row['installment']->displayStatus()] ?? 'bg-secondary' }}">
                                                        {{ $row['installment']->displayStatus() }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No upcoming installments.</p>
                        @endif
                    </div>
                </div>

                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Outstanding Balance</h6>
                        </div>
                        <h4 class="mb-0 {{ $outstandingBalance > 0 ? 'text-danger' : 'text-success' }}">
                            Rs. {{ number_format($outstandingBalance, 2) }}
                        </h4>
                        <p class="text-muted fs-12 mb-0">Sum of everything still owed across all active bookings.</p>
                    </div>
                </div>

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Payment History</h6>
                        @if ($payments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
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
            </div>
        </div>
    </div>
@endsection
