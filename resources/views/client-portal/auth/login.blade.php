@extends('layouts.client-guest')

@section('title', 'Client Login')

@section('content')
    <h5 class="fw-bold mb-1">Login</h5>
    <p class="text-muted fs-12 mb-4">Enter your CNIC and password to view your account.</p>

    @if (session('status'))
        <div class="alert alert-success fs-12">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('client.login.store') }}">
        @csrf

        <div class="mb-3">
            <label for="cnic" class="form-label">CNIC</label>
            <input
                id="cnic"
                type="text"
                name="cnic"
                value="{{ old('cnic') }}"
                class="form-control @error('cnic') is-invalid @enderror"
                placeholder="e.g. 12345-1234567-1"
                required
                autofocus
                autocomplete="username"
            />
            @error('cnic')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                id="password"
                type="password"
                name="password"
                class="form-control @error('password') is-invalid @enderror"
                placeholder="Password"
                required
                autocomplete="current-password"
            />
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label fs-12" for="remember">Remember Me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>

    <div class="text-center mt-4">
        <a href="{{ route('client.password.request') }}" class="fs-12 text-primary">Forgot your password?</a>
    </div>
@endsection
