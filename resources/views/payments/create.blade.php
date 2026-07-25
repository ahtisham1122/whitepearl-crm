@extends('layouts.app')

@section('title', 'Receive Payment')

@php
    $installmentStatusBadgeClass = [
        'Pending' => 'bg-warning',
        'Partially Paid' => 'bg-info',
        'Paid' => 'bg-success',
        'Overdue' => 'bg-danger',
    ];

    $unpaidInstallments = $selectedBooking?->installmentPlan?->installments
        ->filter(fn ($installment) => $installment->status !== 'Paid');
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Receive Payment</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">Payments</a></li>
                <li class="breadcrumb-item">Receive Payment</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('payments.create') }}" class="row g-3">
                            <div class="col-md-9">
                                <label for="booking_id_search" class="form-label">Booking</label>
                                <select name="booking_id" id="booking_id_search" class="form-control">
                                    <option value="">Select a booking</option>
                                    @foreach ($bookings as $bookingOption)
                                        <option value="{{ $bookingOption->id }}" @selected($selectedBooking?->id === $bookingOption->id)>
                                            {{ $bookingOption->booking_number }} - {{ $bookingOption->client->full_name }}
                                            - {{ $bookingOption->plot->block->name }} {{ $bookingOption->plot->plot_number }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Search by client, plot, or booking number.</small>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">Load Booking</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if ($selectedBooking)
                    <div class="card stretch stretch-full mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Booking Summary</h6>
                            <div class="row">
                                <div class="col-md-4 mb-2"><strong>Client:</strong> {{ $selectedBooking->client->full_name }}</div>
                                <div class="col-md-4 mb-2">
                                    <strong>Plot:</strong> {{ $selectedBooking->plot->block->name }} - {{ $selectedBooking->plot->plot_number }}
                                </div>
                                <div class="col-md-4 mb-2"><strong>Final Price:</strong> Rs. {{ number_format($selectedBooking->final_price, 2) }}</div>
                                <div class="col-md-4 mb-2"><strong>Booking Amount (down payment):</strong> Rs. {{ number_format($selectedBooking->booking_amount, 2) }}</div>
                                <div class="col-md-4 mb-2">
                                    <strong>Remaining Balance:</strong>
                                    Rs. {{ number_format($remainingForBooking ?? 0, 2) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (! $selectedBooking->installmentPlan)
                        <div class="alert alert-warning">
                            This booking doesn't have an installment plan yet.
                            <a href="{{ route('installment-plans.create', ['booking' => $selectedBooking->id]) }}">Create one first</a>
                            before recording payments.
                        </div>
                    @elseif ($unpaidInstallments->isEmpty())
                        <div class="alert alert-success mb-0">
                            All installments on this booking are already fully paid.
                        </div>
                    @else
                        <div class="card stretch stretch-full mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Installment Schedule</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
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
                                            @foreach ($selectedBooking->installmentPlan->installments as $installment)
                                                <tr class="{{ $installment->displayStatus() === 'Overdue' ? 'table-danger' : '' }}">
                                                    <td>{{ $installment->installment_number }}</td>
                                                    <td>{{ $installment->due_date->format('d M Y') }}</td>
                                                    <td>{{ number_format($installment->amount, 2) }}</td>
                                                    <td>{{ number_format($installment->late_fee, 2) }}</td>
                                                    <td>{{ number_format($installment->paid_amount, 2) }}</td>
                                                    <td>
                                                        <span class="badge {{ $installmentStatusBadgeClass[$installment->displayStatus()] ?? 'bg-secondary' }}">
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

                        <div class="card stretch stretch-full">
                            <div class="card-body">
                                <h6 class="fw-bold mb-3">Apply Payment</h6>

                                <form method="POST" action="{{ route('payments.store') }}">
                                    @csrf
                                    <input type="hidden" name="booking_id" value="{{ $selectedBooking->id }}" />

                                    <div class="mb-3">
                                        <label class="form-label d-block">How should this payment be applied? <span class="text-danger">*</span></label>

                                        <div class="form-check form-check-inline">
                                            <input
                                                type="radio"
                                                name="application_mode"
                                                id="mode_specific"
                                                value="specific"
                                                class="form-check-input"
                                                checked
                                            />
                                            <label for="mode_specific" class="form-check-label">Pay a specific installment</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input
                                                type="radio"
                                                name="application_mode"
                                                id="mode_auto"
                                                value="auto"
                                                class="form-check-input"
                                            />
                                            <label for="mode_auto" class="form-check-label">Auto-apply to oldest unpaid first</label>
                                        </div>
                                        @error('application_mode')
                                            <div class="text-danger fs-12 mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3" id="installment_id_field">
                                            <label for="installment_id" class="form-label">Installment <span class="text-danger">*</span></label>
                                            <select
                                                name="installment_id"
                                                id="installment_id"
                                                class="form-control @error('installment_id') is-invalid @enderror"
                                            >
                                                <option value="">Select an installment</option>
                                                @foreach ($unpaidInstallments as $installment)
                                                    @php
                                                        $owed = max($installment->amount + $installment->late_fee - $installment->paid_amount, 0);
                                                    @endphp
                                                    <option
                                                        value="{{ $installment->id }}"
                                                        data-remaining="{{ $owed }}"
                                                        @selected((int) old('installment_id') === $installment->id)
                                                    >
                                                        #{{ $installment->installment_number }} - Due {{ $installment->due_date->format('d M Y') }}
                                                        - Owed Rs. {{ number_format($owed, 2) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('installment_id')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6 mb-3" id="late_fee_field">
                                            <label for="late_fee" class="form-label">Late Fee (optional)</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="late_fee"
                                                id="late_fee"
                                                class="form-control @error('late_fee') is-invalid @enderror"
                                                value="{{ old('late_fee', 0) }}"
                                            />
                                            <small class="text-muted">Added to this installment's amount owed.</small>
                                            @error('late_fee')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="amount" class="form-label">Amount Received <span class="text-danger">*</span></label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                name="amount"
                                                id="amount"
                                                class="form-control @error('amount') is-invalid @enderror"
                                                value="{{ old('amount') }}"
                                            />
                                            @error('amount')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                                            <input
                                                type="date"
                                                name="payment_date"
                                                id="payment_date"
                                                class="form-control @error('payment_date') is-invalid @enderror"
                                                value="{{ old('payment_date', now()->format('Y-m-d')) }}"
                                            />
                                            @error('payment_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                                            <select
                                                name="payment_method"
                                                id="payment_method"
                                                class="form-control @error('payment_method') is-invalid @enderror"
                                            >
                                                @foreach (['Cash', 'Bank Transfer', 'Cheque', 'JazzCash', 'EasyPaisa'] as $methodOption)
                                                    <option value="{{ $methodOption }}" @selected(old('payment_method') === $methodOption)>
                                                        {{ $methodOption }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('payment_method')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                                            <select
                                                name="payment_type"
                                                id="payment_type"
                                                class="form-control @error('payment_type') is-invalid @enderror"
                                            >
                                                @foreach (['Booking Payment', 'Installment Payment', 'Advance', 'Partial'] as $typeOption)
                                                    <option value="{{ $typeOption }}" @selected(old('payment_type') === $typeOption)>
                                                        {{ $typeOption }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('payment_type')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label for="reference_no" class="form-label">Reference No.</label>
                                            <input
                                                type="text"
                                                name="reference_no"
                                                id="reference_no"
                                                class="form-control @error('reference_no') is-invalid @enderror"
                                                value="{{ old('reference_no') }}"
                                                placeholder="Cheque number, transaction ID, etc."
                                            />
                                            @error('reference_no')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12 mb-3">
                                            <label for="remarks" class="form-label">Remarks</label>
                                            <textarea
                                                name="remarks"
                                                id="remarks"
                                                rows="2"
                                                class="form-control @error('remarks') is-invalid @enderror"
                                            >{{ old('remarks') }}</textarea>
                                            @error('remarks')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-primary">Record Payment</button>
                                        <a href="{{ route('payments.index') }}" class="btn btn-light">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            const bookingSelect = document.getElementById('booking_id_search');

            if (bookingSelect) {
                bookingSelect.addEventListener('change', function () {
                    const bookingId = bookingSelect.value;
                    window.location.href = '{{ route('payments.create') }}' + (bookingId ? '?booking_id=' + bookingId : '');
                });
            }

            const modeSpecific = document.getElementById('mode_specific');
            const modeAuto = document.getElementById('mode_auto');
            const installmentField = document.getElementById('installment_id_field');
            const lateFeeField = document.getElementById('late_fee_field');
            const installmentSelect = document.getElementById('installment_id');
            const amountInput = document.getElementById('amount');

            if (!modeSpecific) {
                return;
            }

            function toggleModeFields() {
                const isSpecific = modeSpecific.checked;
                installmentField.classList.toggle('d-none', !isSpecific);
                lateFeeField.classList.toggle('d-none', !isSpecific);
            }

            modeSpecific.addEventListener('change', toggleModeFields);
            modeAuto.addEventListener('change', toggleModeFields);

            installmentSelect.addEventListener('change', function () {
                const selectedOption = installmentSelect.options[installmentSelect.selectedIndex];
                const remaining = selectedOption ? selectedOption.getAttribute('data-remaining') : null;

                if (remaining) {
                    amountInput.value = parseFloat(remaining).toFixed(2);
                }
            });

            toggleModeFields();
        })();
    </script>
@endpush
