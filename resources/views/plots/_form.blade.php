    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="block_id" class="form-label">Block <span class="text-danger">*</span></label>
            <select
                name="block_id"
                id="block_id"
                class="form-control @error('block_id') is-invalid @enderror"
            >
                <option value="">Select a block</option>
                @foreach ($blocks as $blockOption)
                    <option value="{{ $blockOption->id }}" @selected((int) old('block_id', $plot->block_id) === $blockOption->id)>
                        {{ $blockOption->name }}
                    </option>
                @endforeach
            </select>
            @error('block_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="plot_number" class="form-label">Plot Number <span class="text-danger">*</span></label>
            <input
                type="text"
                name="plot_number"
                id="plot_number"
                class="form-control @error('plot_number') is-invalid @enderror"
                value="{{ old('plot_number', $plot->plot_number) }}"
                placeholder="e.g. 123"
            />
            @error('plot_number')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="street" class="form-label">Street</label>
            <input
                type="text"
                name="street"
                id="street"
                class="form-control @error('street') is-invalid @enderror"
                value="{{ old('street', $plot->street) }}"
                placeholder="Optional"
            />
            @error('street')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="plot_size" class="form-label">Plot Size <span class="text-danger">*</span></label>
            <input
                type="text"
                name="plot_size"
                id="plot_size"
                class="form-control @error('plot_size') is-invalid @enderror"
                value="{{ old('plot_size', $plot->plot_size) }}"
                placeholder="e.g. 5 Marla"
            />
            @error('plot_size')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-6 mb-3">
            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
            <select
                name="category"
                id="category"
                class="form-control @error('category') is-invalid @enderror"
            >
                <option value="">Select category</option>
                @foreach (['Residential', 'Commercial'] as $categoryOption)
                    <option value="{{ $categoryOption }}" @selected(old('category', $plot->category) === $categoryOption)>
                        {{ $categoryOption }}
                    </option>
                @endforeach
            </select>
            @error('category')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @php
            // Reserved/Sold means a real booking is behind this plot -- that
            // can only change by creating/cancelling/deleting the booking,
            // never by hand here, so the field is shown read-only instead
            // of a dropdown.
            $statusLocked = $plot->exists && in_array($plot->status, ['Reserved', 'Sold']);
        @endphp
        <div class="col-md-6 mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            @if ($statusLocked)
                <input type="text" class="form-control" value="{{ $plot->status }}" disabled />
                <small class="text-muted">Set automatically by the booking system. Cancel or delete the booking to free this plot.</small>
            @else
                <select
                    name="status"
                    id="status"
                    class="form-control @error('status') is-invalid @enderror"
                >
                    @foreach (['Available', 'Cancelled'] as $statusOption)
                        <option value="{{ $statusOption }}" @selected(old('status', $plot->status ?? 'Available') === $statusOption)>
                            {{ $statusOption }}
                        </option>
                    @endforeach
                </select>
                @error('status')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>

        <div class="col-12 mb-3">
            <div class="d-flex gap-4 flex-wrap">
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="corner_plot"
                        id="corner_plot"
                        class="form-check-input"
                        value="1"
                        @checked(old('corner_plot', $plot->corner_plot))
                    />
                    <label for="corner_plot" class="form-check-label">Corner Plot</label>
                </div>
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="park_facing"
                        id="park_facing"
                        class="form-check-input"
                        value="1"
                        @checked(old('park_facing', $plot->park_facing))
                    />
                    <label for="park_facing" class="form-check-label">Park Facing</label>
                </div>
                <div class="form-check">
                    <input
                        type="checkbox"
                        name="main_boulevard"
                        id="main_boulevard"
                        class="form-check-input"
                        value="1"
                        @checked(old('main_boulevard', $plot->main_boulevard))
                    />
                    <label for="main_boulevard" class="form-check-label">Main Boulevard</label>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-3">
            <label for="plot_price" class="form-label">Plot Price (PKR) <span class="text-danger">*</span></label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="plot_price"
                id="plot_price"
                class="form-control @error('plot_price') is-invalid @enderror"
                value="{{ old('plot_price', $plot->plot_price) }}"
            />
            @error('plot_price')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="development_charges" class="form-label">Development Charges (PKR)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="development_charges"
                id="development_charges"
                class="form-control @error('development_charges') is-invalid @enderror"
                value="{{ old('development_charges', $plot->development_charges ?? 0) }}"
            />
            @error('development_charges')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="extra_charges" class="form-label">Extra Charges (PKR)</label>
            <input
                type="number"
                step="0.01"
                min="0"
                name="extra_charges"
                id="extra_charges"
                class="form-control @error('extra_charges') is-invalid @enderror"
                value="{{ old('extra_charges', $plot->extra_charges ?? 0) }}"
            />
            @error('extra_charges')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="total_price_preview" class="form-label">Total Price (auto-calculated)</label>
            <input type="text" id="total_price_preview" class="form-control" readonly />
            <small class="text-muted">Plot Price + Development Charges + Extra Charges. Calculated on save.</small>
        </div>
    </div>

@push('scripts')
    <script>
        (function () {
            const plotPrice = document.getElementById('plot_price');
            const developmentCharges = document.getElementById('development_charges');
            const extraCharges = document.getElementById('extra_charges');
            const preview = document.getElementById('total_price_preview');

            function updatePreview() {
                const total = (parseFloat(plotPrice.value) || 0)
                    + (parseFloat(developmentCharges.value) || 0)
                    + (parseFloat(extraCharges.value) || 0);

                preview.value = total.toFixed(2);
            }

            [plotPrice, developmentCharges, extraCharges].forEach(function (field) {
                field.addEventListener('input', updatePreview);
            });

            updatePreview();
        })();
    </script>
@endpush
