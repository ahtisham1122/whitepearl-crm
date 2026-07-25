@extends('layouts.app')

@section('title', 'Settings')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Settings</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-8">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="company_name"
                                        id="company_name"
                                        class="form-control @error('company_name') is-invalid @enderror"
                                        value="{{ old('company_name', $setting->company_name) }}"
                                    />
                                    @error('company_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="currency_symbol" class="form-label">Currency Symbol <span class="text-danger">*</span></label>
                                    <input
                                        type="text"
                                        name="currency_symbol"
                                        id="currency_symbol"
                                        class="form-control @error('currency_symbol') is-invalid @enderror"
                                        value="{{ old('currency_symbol', $setting->currency_symbol) }}"
                                        placeholder="Rs."
                                    />
                                    @error('currency_symbol')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_logo" class="form-label">Company Logo</label>
                                    <input
                                        type="file"
                                        name="company_logo"
                                        id="company_logo"
                                        class="form-control @error('company_logo') is-invalid @enderror"
                                        accept="image/*"
                                    />
                                    @error('company_logo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    @if ($setting->company_logo)
                                        <div class="mt-2 d-flex align-items-center gap-2">
                                            <img
                                                src="{{ asset('storage/'.$setting->company_logo) }}"
                                                alt="{{ $setting->company_name }}"
                                                style="max-height: 60px; object-fit: contain;"
                                            />
                                            <small class="text-muted">Uploading a new logo will replace this one.</small>
                                        </div>
                                    @endif
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_phone" class="form-label">Company Phone</label>
                                    <input
                                        type="text"
                                        name="company_phone"
                                        id="company_phone"
                                        class="form-control @error('company_phone') is-invalid @enderror"
                                        value="{{ old('company_phone', $setting->company_phone) }}"
                                    />
                                    @error('company_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_email" class="form-label">Company Email</label>
                                    <input
                                        type="email"
                                        name="company_email"
                                        id="company_email"
                                        class="form-control @error('company_email') is-invalid @enderror"
                                        value="{{ old('company_email', $setting->company_email) }}"
                                    />
                                    @error('company_email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="company_address" class="form-label">Company Address</label>
                                    <textarea
                                        name="company_address"
                                        id="company_address"
                                        rows="3"
                                        class="form-control @error('company_address') is-invalid @enderror"
                                    >{{ old('company_address', $setting->company_address) }}</textarea>
                                    @error('company_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-12 mb-3">
                                    <label for="receipt_footer_text" class="form-label">Receipt Footer Text</label>
                                    <input
                                        type="text"
                                        name="receipt_footer_text"
                                        id="receipt_footer_text"
                                        class="form-control @error('receipt_footer_text') is-invalid @enderror"
                                        value="{{ old('receipt_footer_text', $setting->receipt_footer_text) }}"
                                        placeholder="Thank you for your payment"
                                    />
                                    <small class="text-muted">Shown at the bottom of every printed and PDF receipt.</small>
                                    @error('receipt_footer_text')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Save Settings</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
