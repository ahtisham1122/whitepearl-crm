@extends('layouts.guest')

@section('title', 'Verify Email')

@section('content')
    <h2 class="fs-20 fw-bolder mb-4">Verify Email</h2>
    <p class="fs-12 fw-medium text-muted">Thanks for signing up! Please verify your email address by clicking the link we just emailed you. Didn't get it? We'll gladly send another.</p>

    @if (session('status') == 'verification-link-sent')
        <div class="alert alert-success fs-12 mt-3">A new verification link has been sent to your email address.</div>
    @endif

    <div class="d-flex align-items-center justify-content-between mt-5">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-muted">Log Out</button>
        </form>
    </div>
@endsection
