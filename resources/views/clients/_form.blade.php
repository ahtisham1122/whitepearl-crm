    <div class="row">
        <div class="col-12 mb-3">
            <h6 class="fw-bold mb-0">Personal Information</h6>
        </div>

        <div class="col-md-4 mb-3">
            <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
            <input
                type="text"
                name="full_name"
                id="full_name"
                class="form-control @error('full_name') is-invalid @enderror"
                value="{{ old('full_name', $client->full_name) }}"
            />
            @error('full_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="father_name" class="form-label">Father Name</label>
            <input
                type="text"
                name="father_name"
                id="father_name"
                class="form-control @error('father_name') is-invalid @enderror"
                value="{{ old('father_name', $client->father_name) }}"
            />
            @error('father_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="cnic" class="form-label">CNIC <span class="text-danger">*</span></label>
            <input
                type="text"
                name="cnic"
                id="cnic"
                class="form-control @error('cnic') is-invalid @enderror"
                value="{{ old('cnic', $client->cnic) }}"
                placeholder="XXXXX-XXXXXXX-X"
            />
            @error('cnic')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input
                type="date"
                name="date_of_birth"
                id="date_of_birth"
                class="form-control @error('date_of_birth') is-invalid @enderror"
                value="{{ old('date_of_birth', $client->date_of_birth?->format('Y-m-d')) }}"
            />
            @error('date_of_birth')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
            <input
                type="text"
                name="phone"
                id="phone"
                class="form-control @error('phone') is-invalid @enderror"
                value="{{ old('phone', $client->phone) }}"
            />
            @error('phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="whatsapp" class="form-label">WhatsApp</label>
            <input
                type="text"
                name="whatsapp"
                id="whatsapp"
                class="form-control @error('whatsapp') is-invalid @enderror"
                value="{{ old('whatsapp', $client->whatsapp) }}"
            />
            @error('whatsapp')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                name="email"
                id="email"
                class="form-control @error('email') is-invalid @enderror"
                value="{{ old('email', $client->email) }}"
            />
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="occupation" class="form-label">Occupation</label>
            <input
                type="text"
                name="occupation"
                id="occupation"
                class="form-control @error('occupation') is-invalid @enderror"
                value="{{ old('occupation', $client->occupation) }}"
            />
            @error('occupation')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="city" class="form-label">City</label>
            <input
                type="text"
                name="city"
                id="city"
                class="form-control @error('city') is-invalid @enderror"
                value="{{ old('city', $client->city) }}"
            />
            @error('city')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="country" class="form-label">Country</label>
            <input
                type="text"
                name="country"
                id="country"
                class="form-control @error('country') is-invalid @enderror"
                value="{{ old('country', $client->country ?? 'Pakistan') }}"
            />
            @error('country')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                @foreach (['Active', 'Inactive'] as $statusOption)
                    <option value="{{ $statusOption }}" @selected(old('status', $client->status ?? 'Active') === $statusOption)>
                        {{ $statusOption }}
                    </option>
                @endforeach
            </select>
            @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="profile_photo" class="form-label">Profile Photo</label>
            <input
                type="file"
                name="profile_photo"
                id="profile_photo"
                class="form-control @error('profile_photo') is-invalid @enderror"
                accept="image/*"
            />
            @error('profile_photo')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
            @if ($client->profile_photo)
                <div class="mt-2 d-flex align-items-center gap-2">
                    <img
                        src="{{ asset('storage/'.$client->profile_photo) }}"
                        alt="{{ $client->full_name }}"
                        style="height: 48px; width: 48px; object-fit: cover; border-radius: 50%;"
                    />
                    <small class="text-muted">Uploading a new photo will replace this one.</small>
                </div>
            @endif
        </div>

        <div class="col-md-8 mb-3">
            <label for="address" class="form-label">Address</label>
            <textarea
                name="address"
                id="address"
                rows="2"
                class="form-control @error('address') is-invalid @enderror"
            >{{ old('address', $client->address) }}</textarea>
            @error('address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-12 mb-3">
            <label for="notes" class="form-label">Notes</label>
            <textarea
                name="notes"
                id="notes"
                rows="3"
                class="form-control @error('notes') is-invalid @enderror"
            >{{ old('notes', $client->notes) }}</textarea>
            @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr class="my-4" />

    <div class="row">
        <div class="col-12 mb-3">
            <h6 class="fw-bold mb-0">Nominee Information</h6>
            <p class="text-muted fs-12 mb-0">The person who will inherit the client's plots, if needed. Optional.</p>
        </div>

        <div class="col-md-4 mb-3">
            <label for="nominee_name" class="form-label">Nominee Name</label>
            <input
                type="text"
                name="nominee_name"
                id="nominee_name"
                class="form-control @error('nominee_name') is-invalid @enderror"
                value="{{ old('nominee_name', $client->nominee?->name) }}"
            />
            @error('nominee_name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="nominee_relationship" class="form-label">Relationship</label>
            <input
                type="text"
                name="nominee_relationship"
                id="nominee_relationship"
                class="form-control @error('nominee_relationship') is-invalid @enderror"
                value="{{ old('nominee_relationship', $client->nominee?->relationship) }}"
                placeholder="e.g. Son, Spouse"
            />
            @error('nominee_relationship')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="nominee_cnic" class="form-label">Nominee CNIC</label>
            <input
                type="text"
                name="nominee_cnic"
                id="nominee_cnic"
                class="form-control @error('nominee_cnic') is-invalid @enderror"
                value="{{ old('nominee_cnic', $client->nominee?->cnic) }}"
            />
            @error('nominee_cnic')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="nominee_phone" class="form-label">Nominee Phone</label>
            <input
                type="text"
                name="nominee_phone"
                id="nominee_phone"
                class="form-control @error('nominee_phone') is-invalid @enderror"
                value="{{ old('nominee_phone', $client->nominee?->phone) }}"
            />
            @error('nominee_phone')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-4 mb-3">
            <label for="nominee_email" class="form-label">Nominee Email</label>
            <input
                type="email"
                name="nominee_email"
                id="nominee_email"
                class="form-control @error('nominee_email') is-invalid @enderror"
                value="{{ old('nominee_email', $client->nominee?->email) }}"
            />
            @error('nominee_email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="col-md-8 mb-3">
            <label for="nominee_address" class="form-label">Nominee Address</label>
            <textarea
                name="nominee_address"
                id="nominee_address"
                rows="2"
                class="form-control @error('nominee_address') is-invalid @enderror"
            >{{ old('nominee_address', $client->nominee?->address) }}</textarea>
            @error('nominee_address')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
    </div>

    <hr class="my-4" />

    <div class="row">
        <div class="col-12 mb-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h6 class="fw-bold mb-0">Documents</h6>
                <p class="text-muted fs-12 mb-0">Upload CNIC copies, photographs, or other paperwork. Optional.</p>
            </div>
            <button type="button" id="add-document-row" class="btn btn-light btn-sm">
                <i class="feather-plus me-1"></i>Add Document
            </button>
        </div>

        <div class="col-12" id="document-rows"></div>
    </div>

    <template id="document-row-template">
        <div class="row g-2 mb-2 align-items-center document-row">
            <div class="col-md-5">
                <select name="document_type[]" class="form-control">
                    <option value="">Select document type</option>
                    @foreach (['CNIC Front', 'CNIC Back', 'Passport', 'Photograph', 'Application Form', 'Other'] as $docType)
                        <option value="{{ $docType }}">{{ $docType }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-5">
                <input type="file" name="document_file[]" class="form-control" />
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-light-danger btn-sm remove-document-row">
                    <i class="feather-trash-2"></i>
                </button>
            </div>
        </div>
    </template>

@push('scripts')
    <script>
        (function () {
            const template = document.getElementById('document-row-template');
            const container = document.getElementById('document-rows');
            const addButton = document.getElementById('add-document-row');

            addButton.addEventListener('click', function () {
                container.appendChild(template.content.cloneNode(true));
            });

            container.addEventListener('click', function (event) {
                const removeButton = event.target.closest('.remove-document-row');

                if (removeButton) {
                    removeButton.closest('.document-row').remove();
                }
            });
        })();
    </script>
@endpush
