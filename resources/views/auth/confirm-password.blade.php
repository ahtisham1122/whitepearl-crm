@extends('layouts.guest')

@section('title', 'Confirm Password')

@section('content')
    <h2 class="fs-20 fw-bolder mb-4">Confirm Password</h2>
    <p class="fs-12 fw-medium text-muted">This is a secure area. Please confirm your password before continuing.</p>

    <form method="POST" action="{{ route('password.confirm') }}" class="w-100 mt-4 pt-2">
        @csrf

        <div class="mb-3">
            <input id="password" type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Password" required autocomplete="current-password" autofocus />
            @error('password')
                <span class="invalid-feedback d-block">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-5">
            <button type="submit" class="btn btn-lg btn-primary w-100">Confirm</button>
        </div>
    </form>
@endsection
