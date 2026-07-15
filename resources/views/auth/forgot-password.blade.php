@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
    <h2 class="fs-20 fw-bolder mb-4">Forgot Password</h2>
    <p class="fs-12 fw-medium text-muted">No problem. Enter your email address and we'll email you a password reset link.</p>

    @if (session('status'))
        <div class="alert alert-success fs-12 mt-3">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="w-100 mt-4 pt-2">
        @csrf

        <div class="mb-3">
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required autofocus />
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-5">
            <button type="submit" class="btn btn-lg btn-primary w-100">Email Password Reset Link</button>
        </div>
    </form>

    <div class="mt-5 text-muted">
        <a href="{{ route('login') }}" class="fw-bold">Back to Login</a>
    </div>
@endsection
