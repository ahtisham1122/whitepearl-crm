@extends('layouts.app')

@section('title', 'Plot '.$plot->plot_number)

@php
    $statusBadgeClass = [
        'Available' => 'bg-success',
        'Reserved' => 'bg-warning',
        'Sold' => 'bg-danger',
        'Cancelled' => 'bg-secondary',
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Plot {{ $plot->plot_number }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('plots.index') }}">Plots</a></li>
                <li class="breadcrumb-item">{{ $plot->plot_number }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('plots.edit', $plot) }}" class="btn btn-primary">
                <i class="feather-edit-2 me-2"></i>Edit Plot
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-7">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold mb-0">Plot Details</h6>
                            <span class="badge {{ $statusBadgeClass[$plot->status] ?? 'bg-secondary' }}">
                                {{ $plot->status }}
                            </span>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Block:</strong> {{ $plot->block->name }}</div>
                            <div class="col-md-6 mb-2"><strong>Street:</strong> {{ $plot->street ?? '—' }}</div>
                            <div class="col-md-6 mb-2"><strong>Size:</strong> {{ $plot->plot_size }}</div>
                            <div class="col-md-6 mb-2"><strong>Category:</strong> {{ $plot->category }}</div>
                            <div class="col-md-4 mb-2"><strong>Corner Plot:</strong> {{ $plot->corner_plot ? 'Yes' : 'No' }}</div>
                            <div class="col-md-4 mb-2"><strong>Park Facing:</strong> {{ $plot->park_facing ? 'Yes' : 'No' }}</div>
                            <div class="col-md-4 mb-2"><strong>Main Boulevard:</strong> {{ $plot->main_boulevard ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>
                </div>

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Pricing</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2"><strong>Plot Price:</strong> Rs. {{ number_format($plot->plot_price, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Development Charges:</strong> Rs. {{ number_format($plot->development_charges, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Extra Charges:</strong> Rs. {{ number_format($plot->extra_charges, 2) }}</div>
                            <div class="col-md-6 mb-2"><strong>Total Price:</strong> Rs. {{ number_format($plot->total_price, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Current Booking</h6>
                        @if ($plot->activeBooking)
                            <p class="mb-1"><strong>{{ $plot->activeBooking->client->full_name }}</strong></p>
                            <p class="text-muted mb-2">{{ $plot->activeBooking->client->client_id }}</p>
                            <p class="mb-1">Booking #: {{ $plot->activeBooking->booking_number }}</p>
                            <p class="mb-1">Type: {{ $plot->activeBooking->booking_type }}</p>
                            <p class="mb-2">Final Price: Rs. {{ number_format($plot->activeBooking->final_price, 2) }}</p>
                            <a href="{{ route('bookings.show', $plot->activeBooking) }}" class="btn btn-sm btn-light-brand">
                                <i class="feather-eye"></i> View Booking
                            </a>
                        @else
                            <p class="text-muted mb-0">This plot is currently available. No active booking.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
