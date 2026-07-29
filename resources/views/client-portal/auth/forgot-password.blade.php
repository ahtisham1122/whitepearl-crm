@extends('layouts.client-guest')

@section('title', 'Forgot Password')

@section('content')
    <h5 class="fw-bold mb-1">Forgot Your Password?</h5>
    <p class="text-muted fs-13 mb-4">
        For your security, passwords can only be reset by our office -- there is no automatic reset link.
        Please contact us and we will issue you a new password.
    </p>

    <ul class="list-unstyled fs-13 mb-4">
        @if (setting('company_phone'))
            <li class="mb-2"><i class="feather-phone me-2"></i>{{ setting('company_phone') }}</li>
        @endif
        @if (setting('company_email'))
            <li class="mb-2"><i class="feather-mail me-2"></i>{{ setting('company_email') }}</li>
        @endif
        @if (setting('company_address'))
            <li class="mb-2"><i class="feather-map-pin me-2"></i>{{ setting('company_address') }}</li>
        @endif
    </ul>

    <a href="{{ route('client.login') }}" class="btn btn-light w-100">
        <i class="feather-arrow-left me-2"></i>Back to Login
    </a>
@endsection
