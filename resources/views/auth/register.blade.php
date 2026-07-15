@extends('layouts.guest')

@section('title', 'Register')

@section('content')
    <h2 class="fs-20 fw-bolder mb-4">Register</h2>
    <h4 class="fs-13 fw-bold mb-2">Create a new account</h4>
    <p class="fs-12 fw-medium text-muted">Please fill in the details below to get started.</p>

    <form method="POST" action="{{ route('register') }}" class="w-100 mt-4 pt-2">
        @csrf

        <div class="mb-4">
            <input id="name" type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" placeholder="Full Name" required autofocus autocomplete="name" />
            @error('name')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <input id="email" type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" placeholder="Email Address" required autocomplete="username" />
            @error('email')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="new-password" />
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <input id="password_confirmation" type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" placeholder="Confirm Password" required autocomplete="new-password" />
            @error('password_confirmation')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-5">
            <button type="submit" class="btn btn-lg btn-primary w-100">Register</button>
        </div>
    </form>

    <div class="mt-5 text-muted">
        <span>Already registered?</span>
        <a href="{{ route('login') }}" class="fw-bold">Login</a>
    </div>
@endsection
