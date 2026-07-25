@extends('layouts.app')

@section('title', 'Bookings')

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
                <h5 class="m-b-10">Bookings</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Bookings</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('bookings.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Booking
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="GET" action="{{ route('bookings.index') }}" class="row g-3 mb-4">
                            <div class="col-md-8">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by booking number, client, or plot number"
                                    value="{{ request('search') }}"
                                />
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Search</button>
                                <a href="{{ route('bookings.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Client</th>
                                        <th>Plot</th>
                                        <th>Type</th>
                                        <th>Final Price</th>
                                        <th>Booking Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bookings as $booking)
                                        <tr>
                                            <td>{{ $booking->booking_number }}</td>
                                            <td>{{ $booking->client->full_name }}</td>
                                            <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                            <td>
                                                <span class="badge {{ $typeBadgeClass[$booking->booking_type] ?? 'bg-secondary' }}">
                                                    {{ $booking->booking_type }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($booking->final_price, 2) }}</td>
                                            <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass[$booking->status] ?? 'bg-secondary' }}">
                                                    {{ $booking->status }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('bookings.show', $booking) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-eye"></i> View
                                                </a>
                                                @if ($booking->status === 'Active')
                                                    <form
                                                        method="POST"
                                                        action="{{ route('bookings.cancel', $booking) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Cancel this booking? The plot will become Available again.');"
                                                    >
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-light-danger">
                                                            <i class="feather-x-circle"></i> Cancel
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No bookings found. Click "Add Booking" to create the first one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $bookings->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
