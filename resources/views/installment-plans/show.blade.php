@extends('layouts.app')

@section('title', 'Installment Plan')

@php
    $statusBadgeClass = [
        'Pending' => 'bg-warning',
        'Partially Paid' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger',
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Installment Plan - {{ $plan->booking->booking_number }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('installment-plans.index') }}">Installment Plans</a></li>
                <li class="breadcrumb-item">{{ $plan->booking->booking_number }}</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-4">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Plan Summary</h6>
                        <p class="mb-1"><strong>Client:</strong> {{ $plan->booking->client->full_name }}</p>
                        <p class="mb-1"><strong>Plot:</strong> {{ $plan->booking->plot->block->name }} - {{ $plan->booking->plot->plot_number }}</p>
                        <p class="mb-1"><strong>Plan Type:</strong> {{ $plan->plan_type }}</p>
                        <p class="mb-1"><strong>Installments:</strong> {{ $plan->number_of_installments }}</p>
                        <p class="mb-1"><strong>Start Date:</strong> {{ $plan->start_date->format('d M Y') }}</p>
                        <p class="mb-1"><strong>Total Amount:</strong> Rs. {{ number_format($plan->total_amount, 2) }}</p>
                        <p class="mb-0"><strong>Status:</strong> {{ $plan->status }}</p>

                        <div class="d-flex gap-2 mt-3">
                            <a href="{{ route('bookings.show', $plan->booking) }}" class="btn btn-sm btn-light-brand">
                                <i class="feather-eye"></i> View Booking
                            </a>
                            <a href="{{ route('clients.show', $plan->booking->client) }}" class="btn btn-sm btn-light-brand">
                                <i class="feather-eye"></i> View Client
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <h6 class="fw-bold mb-3">Installment Schedule</h6>
                        <div class="table-responsive">
                            <table class="table table-hover">
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
                                    @foreach ($plan->installments as $installment)
                                        <tr class="{{ $installment->displayStatus() === 'Overdue' ? 'table-danger' : '' }}">
                                            <td>{{ $installment->installment_number }}</td>
                                            <td>{{ $installment->due_date->format('d M Y') }}</td>
                                            <td>{{ number_format($installment->amount, 2) }}</td>
                                            <td>{{ number_format($installment->late_fee, 2) }}</td>
                                            <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass[$installment->displayStatus()] ?? 'bg-secondary' }}">
                                                    {{ $installment->displayStatus() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
