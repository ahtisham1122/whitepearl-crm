@extends('layouts.app')

@section('title', 'Add Installment Plan')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Add Installment Plan</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('installment-plans.index') }}">Installment Plans</a></li>
                <li class="breadcrumb-item">Add</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        @if ($bookings->isEmpty())
                            <div class="alert alert-warning mb-0">
                                There are no bookings available for a new plan. Every active booking either
                                already has an installment plan, or there are no active bookings yet.
                            </div>
                        @else
                            <form method="POST" action="{{ route('installment-plans.store') }}">
                                @csrf

                                <div class="row">
                                    <div class="col-md-8 mb-3">
                                        <label for="booking_id" class="form-label">Booking <span class="text-danger">*</span></label>
                                        <select
                                            name="booking_id"
                                            id="booking_id"
                                            class="form-control @error('booking_id') is-invalid @enderror"
                                        >
                                            <option value="">Select a booking</option>
                                            @foreach ($bookings as $bookingOption)
                                                <option
                                                    value="{{ $bookingOption->id }}"
                                                    data-remaining="{{ $bookingOption->final_price - $bookingOption->booking_amount }}"
                                                    @selected((int) old('booking_id', $selectedBookingId) === $bookingOption->id)
                                                >
                                                    {{ $bookingOption->booking_number }} - {{ $bookingOption->client->full_name }}
                                                    - {{ $bookingOption->plot->block->name }} {{ $bookingOption->plot->plot_number }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('booking_id')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Remaining Balance</label>
                                        <input type="text" id="remaining_balance_display" class="form-control" readonly />
                                        <small class="text-muted">final_price - booking_amount</small>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="plan_type" class="form-label">Plan Type <span class="text-danger">*</span></label>
                                        <select
                                            name="plan_type"
                                            id="plan_type"
                                            class="form-control @error('plan_type') is-invalid @enderror"
                                        >
                                            @foreach (['Monthly', 'Quarterly', 'Custom'] as $typeOption)
                                                <option value="{{ $typeOption }}" @selected(old('plan_type') === $typeOption)>
                                                    {{ $typeOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('plan_type')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                                        <input
                                            type="date"
                                            name="start_date"
                                            id="start_date"
                                            class="form-control @error('start_date') is-invalid @enderror"
                                            value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                        />
                                        @error('start_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-md-4 mb-3" id="number_of_installments_field">
                                        <label for="number_of_installments" class="form-label">Number of Installments <span class="text-danger">*</span></label>
                                        <input
                                            type="number"
                                            min="1"
                                            max="120"
                                            name="number_of_installments"
                                            id="number_of_installments"
                                            class="form-control @error('number_of_installments') is-invalid @enderror"
                                            value="{{ old('number_of_installments') }}"
                                        />
                                        <small class="text-muted">Monthly = +1 month per installment. Quarterly = +3 months.</small>
                                        @error('number_of_installments')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div id="custom-installments-section" class="d-none">
                                    <hr class="my-4" />

                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                                        <div>
                                            <h6 class="fw-bold mb-0">Custom Schedule</h6>
                                            <p class="text-muted fs-12 mb-0">
                                                Add one row per installment. The amounts must add up to the remaining balance.
                                            </p>
                                        </div>
                                        <button type="button" id="add-installment-row" class="btn btn-light btn-sm">
                                            <i class="feather-plus me-1"></i>Add Installment
                                        </button>
                                    </div>

                                    @error('installment_due_date')
                                        <div class="alert alert-danger py-2">{{ $message }}</div>
                                    @enderror
                                    @error('installment_amount')
                                        <div class="alert alert-danger py-2">{{ $message }}</div>
                                    @enderror

                                    <div id="installment-rows"></div>

                                    <p class="mb-3">
                                        Rows total: <strong><span id="custom-rows-total">0.00</span></strong>
                                    </p>
                                </div>

                                <template id="installment-row-template">
                                    <div class="row g-2 mb-2 align-items-center installment-row">
                                        <div class="col-md-5">
                                            <input type="date" name="installment_due_date[]" class="form-control" />
                                        </div>
                                        <div class="col-md-5">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="installment_amount[]"
                                                class="form-control installment-amount-input"
                                                placeholder="Amount"
                                            />
                                        </div>
                                        <div class="col-md-2">
                                            <button type="button" class="btn btn-light-danger btn-sm remove-installment-row">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </template>

                                <div class="d-flex gap-2 mt-3">
                                    <button type="submit" class="btn btn-primary">Generate Plan</button>
                                    <a href="{{ route('installment-plans.index') }}" class="btn btn-light">Cancel</a>
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
            const bookingSelect = document.getElementById('booking_id');
            const remainingDisplay = document.getElementById('remaining_balance_display');
            const planTypeSelect = document.getElementById('plan_type');
            const numberField = document.getElementById('number_of_installments_field');
            const customSection = document.getElementById('custom-installments-section');
            const template = document.getElementById('installment-row-template');
            const rowsContainer = document.getElementById('installment-rows');
            const addRowButton = document.getElementById('add-installment-row');
            const customTotalDisplay = document.getElementById('custom-rows-total');

            if (!bookingSelect) {
                return;
            }

            function updateRemainingDisplay() {
                const selectedOption = bookingSelect.options[bookingSelect.selectedIndex];
                const remaining = selectedOption ? selectedOption.getAttribute('data-remaining') : null;
                remainingDisplay.value = remaining ? parseFloat(remaining).toFixed(2) : '';
            }

            function updateCustomTotal() {
                const amounts = rowsContainer.querySelectorAll('.installment-amount-input');
                let total = 0;

                amounts.forEach(function (input) {
                    total += parseFloat(input.value) || 0;
                });

                customTotalDisplay.textContent = total.toFixed(2);
            }

            function togglePlanTypeFields() {
                const isCustom = planTypeSelect.value === 'Custom';
                numberField.classList.toggle('d-none', isCustom);
                customSection.classList.toggle('d-none', !isCustom);
            }

            bookingSelect.addEventListener('change', updateRemainingDisplay);
            planTypeSelect.addEventListener('change', togglePlanTypeFields);

            addRowButton.addEventListener('click', function () {
                rowsContainer.appendChild(template.content.cloneNode(true));
            });

            rowsContainer.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-installment-row');

                if (removeButton) {
                    removeButton.closest('.installment-row').remove();
                    updateCustomTotal();
                }
            });

            rowsContainer.addEventListener('input', function (event) {
                if (event.target.classList.contains('installment-amount-input')) {
                    updateCustomTotal();
                }
            });

            updateRemainingDisplay();
            togglePlanTypeFields();
        })();
    </script>
@endpush
