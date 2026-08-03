@extends('layouts.app')

@section('title', 'Choose a Plot')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $client->full_name }} owns more than one plot</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reports.clients') }}">Reports</a></li>
                <li class="breadcrumb-item">Choose a Plot</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <p class="text-muted">
                            The statement is a single-plot document, so pick which one to open.
                        </p>
                        <div class="list-group">
                            @foreach ($bookings as $booking)
                                <a
                                    href="{{ route('reports.client-statement', $booking) }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                >
                                    <span>
                                        <strong>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</strong>
                                        <span class="text-muted ms-2">Booking #{{ $booking->booking_number }}</span>
                                        @if ($booking->status !== 'Active')
                                            <span class="badge bg-light-warning text-warning ms-2">{{ $booking->status }}</span>
                                        @endif
                                    </span>
                                    <i class="feather-chevron-right"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
