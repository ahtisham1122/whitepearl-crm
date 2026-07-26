@extends('layouts.app')

@section('title', 'Notifications')

@php
    $currencySymbol = setting('currency_symbol');
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Notifications</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Notifications</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-alert-triangle text-danger me-2"></i>
                            Overdue Installments ({{ $overdue_installments->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($overdue_installments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Plot</th>
                                            <th>Overdue Amount</th>
                                            <th>Days Overdue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($overdue_installments as $installment)
                                            @php
                                                $booking = $installment->installmentPlan->booking;
                                                $owed = max($installment->amount + $installment->late_fee - $installment->paid_amount, 0);
                                            @endphp
                                            <tr>
                                                <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->client->full_name }}</a></td>
                                                <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                                <td class="text-danger">{{ $currencySymbol }} {{ number_format($owed, 2) }}</td>
                                                <td>{{ (int) $installment->due_date->diffInDays(today()) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No overdue installments.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-clock text-warning me-2"></i>
                            Upcoming Installments ({{ $upcoming_installments->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($upcoming_installments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Plot</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($upcoming_installments as $installment)
                                            @php $booking = $installment->installmentPlan->booking; @endphp
                                            <tr>
                                                <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->client->full_name }}</a></td>
                                                <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                                <td>{{ $installment->due_date->format('d M Y') }}</td>
                                                <td>{{ $currencySymbol }} {{ number_format($installment->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No installments due in the next 7 days.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-file-text text-info me-2"></i>
                            New Bookings ({{ $new_bookings->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($new_bookings->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Booking #</th>
                                            <th>Client</th>
                                            <th>Plot</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($new_bookings as $booking)
                                            <tr>
                                                <td><a href="{{ route('bookings.show', $booking) }}">{{ $booking->booking_number }}</a></td>
                                                <td>{{ $booking->client->full_name }}</td>
                                                <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No new bookings in the last 7 days.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-user-plus text-success me-2"></i>
                            New Clients ({{ $new_clients->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($new_clients->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th>Added</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($new_clients as $client)
                                            <tr>
                                                <td><a href="{{ route('clients.show', $client) }}">{{ $client->client_id }}</a></td>
                                                <td>{{ $client->full_name }}</td>
                                                <td>{{ $client->phone }}</td>
                                                <td>{{ $client->created_at->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No new clients in the last 7 days.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">
                            <i class="feather-file-minus text-secondary me-2"></i>
                            Missing Documents ({{ $missing_documents->count() }})
                        </h6>
                    </div>
                    <div class="card-body">
                        @if ($missing_documents->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client ID</th>
                                            <th>Name</th>
                                            <th>Phone</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($missing_documents as $client)
                                            <tr>
                                                <td>{{ $client->client_id }}</td>
                                                <td>{{ $client->full_name }}</td>
                                                <td>{{ $client->phone }}</td>
                                                <td class="text-end">
                                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-light-brand">
                                                        <i class="feather-upload"></i> Upload
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">Every client has at least one document uploaded.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
