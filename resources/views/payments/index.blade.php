@extends('layouts.app')

@section('title', 'Payments')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Payments</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Payments</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Receive Payment
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="GET" action="{{ route('payments.index') }}" class="row g-3 mb-4">
                            <div class="col-md-8">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by receipt number, booking number, client, or plot number"
                                    value="{{ request('search') }}"
                                />
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Search</button>
                                <a href="{{ route('payments.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Client</th>
                                        <th>Plot</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th>Date</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payments as $payment)
                                        <tr>
                                            <td>{{ $payment->receipt_number }}</td>
                                            <td>{{ $payment->booking->client->full_name }}</td>
                                            <td>{{ $payment->booking->plot->block->name }} - {{ $payment->booking->plot->plot_number }}</td>
                                            <td>{{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->payment_method }}</td>
                                            <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-eye"></i> View
                                                </a>
                                                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-printer"></i> Print
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No payments recorded yet. Click "Receive Payment" to record the first one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $payments->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
