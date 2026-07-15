@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
    <h2 class="fs-20 fw-bolder mb-4">Reset Password</h2>
    <p class="fs-12 fw-medium text-muted">Enter your new password below.</p>

    <form method="POST" action="{{ route('password.store') }}" class="w-100 mt-4 pt-2">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-4">
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required autofocus autocomplete="username" />
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="New Password" required autocomplete="new-password" />
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Confirm New Password" required autocomplete="new-password" />
            @error('password_confirmation')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-5">
            <button type="submit" class="btn btn-lg btn-primary w-100">Reset Password</button>
        </div>
    </form>
@endsection
