@extends('layouts.app')

@section('title', 'Installment Plans')

@php
    $statusBadgeClass = [
        'Active' => 'bg-success',
        'Completed' => 'bg-secondary',
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Installment Plans</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Installment Plans</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('installment-plans.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Installment Plan
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking #</th>
                                        <th>Client</th>
                                        <th>Plot</th>
                                        <th>Plan Type</th>
                                        <th>Installments</th>
                                        <th>Total Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plans as $plan)
                                        <tr>
                                            <td>{{ $plan->booking->booking_number }}</td>
                                            <td>{{ $plan->booking->client->full_name }}</td>
                                            <td>{{ $plan->booking->plot->block->name }} - {{ $plan->booking->plot->plot_number }}</td>
                                            <td>{{ $plan->plan_type }}</td>
                                            <td>{{ $plan->number_of_installments }}</td>
                                            <td>{{ number_format($plan->total_amount, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass[$plan->status] ?? 'bg-secondary' }}">
                                                    {{ $plan->status }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('installment-plans.show', $plan) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No installment plans found. Click "Add Installment Plan" to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $plans->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
