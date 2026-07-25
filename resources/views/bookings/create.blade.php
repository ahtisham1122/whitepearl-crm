@extends('layouts.app')

@section('title', 'Add Booking')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Add Booking</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('bookings.index') }}">Bookings</a></li>
                <li class="breadcrumb-item">Add</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        @if ($plots->isEmpty())
                            <div class="alert alert-warning mb-0">
                                There are no Available plots right now, so a booking can't be created.
                                Every plot is either Reserved, Sold, or Cancelled.
                            </div>
                        @else
                            <form method="POST" action="{{ route('bookings.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                        <select
                                            name="client_id"
                                            id="client_id"
                                            class="form-control @error('client_id') is-invalid @enderror"
                                        >
                                            <option value="">Select a client</option>
                                            @foreach ($clients as $clientOption)
                                                <option value="{{ $clientOption->id }}" @selected((int) old('client_id') === $clientOption->id)>
                                                    {{ $clientOption->client_id }} - {{ $clientOption->full_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Start typing after clicking to jump to a client.</small>
                                        @error('client_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="plot_id" class="form-label">Plot (Available only) <span class="text-danger">*</span></label>
                                        <select
                                            name="plot_id"
                                            id="plot_id"
                                            class="form-control @error('plot_id') is-invalid @enderror"
                                        >
                                            <option value="">Select a plot</option>
                                            @foreach ($plots as $plotOption)
                                                <option
                                                    value="{{ $plotOption->id }}"
                                                    data-price="{{ $plotOption->total_price }}"
                                                    @selected((int) old('plot_id') === $plotOption->id)
                                                >
                                                    {{ $plotOption->block->name }} - Plot {{ $plotOption->plot_number }}
                                                    ({{ $plotOption->plot_size }}) - Rs. {{ number_format($plotOption->total_price, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plot_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="booking_date" class="form-label">Booking Date <span class="text-danger">*</span></label>
                                        <input
                                            type="date"
                                            name="booking_date"
                                            id="booking_date"
                                            class="form-control @error('booking_date') is-invalid @enderror"
                                            value="{{ old('booking_date', now()->format('Y-m-d')) }}"
                                        />
                                        @error('booking_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="booking_type" class="form-label">Booking Type <span class="text-danger">*</span></label>
                                        <select
                                            name="booking_type"
                                            id="booking_type"
                                            class="form-control @error('booking_type') is-invalid @enderror"
                                        >
                                            @foreach (['Reservation', 'Sale'] as $typeOption)
                                                <option value="{{ $typeOption }}" @selected(old('booking_type') === $typeOption)>
                                                    {{ $typeOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="text-muted">Reservation marks the plot Reserved; Sale marks it Sold.</small>
                                        @error('booking_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="sales_person" class="form-label">Sales Person</label>
                                        <input
                                            type="text"
                                            name="sales_person"
                                            id="sales_person"
                                            class="form-control @error('sales_person') is-invalid @enderror"
                                            value="{{ old('sales_person') }}"
                                        />
                                        @error('sales_person')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="total_price" class="form-label">Total Price <span class="text-danger">*</span></label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="total_price"
                                            id="total_price"
                                            class="form-control @error('total_price') is-invalid @enderror"
                                            value="{{ old('total_price') }}"
                                        />
                                        <small class="text-muted">Auto-filled from the plot. Editable for negotiation.</small>
                                        @error('total_price')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="discount" class="form-label">Discount</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="discount"
                                            id="discount"
                                            class="form-control @error('discount') is-invalid @enderror"
                                            value="{{ old('discount', 0) }}"
                                        />
                                        @error('discount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="final_price_preview" class="form-label">Final Price (auto-calculated)</label>
                                        <input type="text" id="final_price_preview" class="form-control" readonly />
                                        <small class="text-muted">Total Price - Discount. Calculated on save.</small>
                                    </div>

                                    <div class="col-md-3 mb-3">
                                        <label for="booking_amount" class="form-label">Booking Amount (down payment)</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="booking_amount"
                                            id="booking_amount"
                                            class="form-control @error('booking_amount') is-invalid @enderror"
                                            value="{{ old('booking_amount', 0) }}"
                                        />
                                        @error('booking_amount')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 mb-3">
                                        <label for="remarks" class="form-label">Remarks</label>
                                        <textarea
                                            name="remarks"
                                            id="remarks"
                                            rows="3"
                                            class="form-control @error('remarks') is-invalid @enderror"
                                        >{{ old('remarks') }}</textarea>
                                        @error('remarks')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">Save Booking</button>
                                    <a href="{{ route('bookings.index') }}" class="btn btn-light">Cancel</a>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const plotSelect = document.getElementById('plot_id');
            const totalPriceInput = document.getElementById('total_price');
            const discountInput = document.getElementById('discount');
            const finalPricePreview = document.getElementById('final_price_preview');

            if (!plotSelect) {
                return;
            }

            function updateFinalPricePreview() {
                const total = parseFloat(totalPriceInput.value) || 0;
                const discount = parseFloat(discountInput.value) || 0;
                finalPricePreview.value = (total - discount).toFixed(2);
            }

            plotSelect.addEventListener('change', function () {
                const selectedOption = plotSelect.options[plotSelect.selectedIndex];
                const price = selectedOption.getAttribute('data-price');

                if (price) {
                    totalPriceInput.value = parseFloat(price).toFixed(2);
                    updateFinalPricePreview();
                }
            });

            [totalPriceInput, discountInput].forEach(function (field) {
                field.addEventListener('input', updateFinalPricePreview);
            });

            updateFinalPricePreview();
        })();
    </script>
@endpush
