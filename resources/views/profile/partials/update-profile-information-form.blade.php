<div>
    <h5 class="mb-1">Profile Information</h5>
    <p class="fs-12 text-muted">Update your account's profile information and email address.</p>
</div>

<form id="send-verification" method="post" action="{{ route('verification.send') }}">
    @csrf
</form>

<form method="post" action="{{ route('profile.update') }}" class="mt-4">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label">Name</label>
        <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
        @error('name')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
        @error('email')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <p class="fs-12 mt-2">
                Your email address is unverified.
                <button form="send-verification" class="btn btn-link p-0 fs-12 align-baseline">Click here to re-send the verification email.</button>
            </p>

            @if (session('status') === 'verification-link-sent')
                <p class="fs-12 text-success">A new verification link has been sent to your email address.</p>
            @endif
        @endif
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Save</button>

        @if (session('status') === 'profile-updated')
            <p class="fs-12 text-muted mb-0">Saved.</p>
        @endif
    </div>
</form>
