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
                            @if ($booking->remarks)
                                <div class="col-12 mb-2"><strong>Remarks:</strong> {{ $booking->remarks }}</div>
                            @endif
                        </div>
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
