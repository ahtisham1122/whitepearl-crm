@extends('layouts.app')

@section('title', 'Receipts')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Receipts</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Receipts</li>
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
                        <form method="GET" action="{{ route('receipts.index') }}" class="row g-3 mb-4">
                            <div class="col-md-8">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by receipt number, client name, or CNIC"
                                    value="{{ request('search') }}"
                                />
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Search</button>
                                <a href="{{ route('receipts.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Receipt #</th>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Plot</th>
                                        <th>Amount</th>
                                        <th>Method</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($receipts as $receipt)
                                        <tr>
                                            <td>{{ $receipt->receipt_number }}</td>
                                            <td>{{ $receipt->payment_date->format('d M Y') }}</td>
                                            <td>{{ $receipt->booking->client->full_name }}</td>
                                            <td>{{ $receipt->booking->plot->block->name }} - {{ $receipt->booking->plot->plot_number }}</td>
                                            <td>{{ number_format($receipt->amount, 2) }}</td>
                                            <td>{{ $receipt->payment_method }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('payments.show', $receipt) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-eye"></i> View
                                                </a>
                                                <a href="{{ route('payments.receipt', $receipt) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-printer"></i> Print
                                                </a>
                                                <a href="{{ route('payments.receipt.pdf', $receipt) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-download"></i> PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No receipts yet. Receipts are created automatically whenever a payment is recorded.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $receipts->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
