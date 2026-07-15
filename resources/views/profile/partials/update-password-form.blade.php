<div>
    <h5 class="mb-1">Update Password</h5>
    <p class="fs-12 text-muted">Ensure your account is using a long, random password to stay secure.</p>
</div>

<form method="post" action="{{ route('password.update') }}" class="mt-4">
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="update_password_current_password" class="form-label">Current Password</label>
        <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" />
        @error('current_password', 'updatePassword')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password" class="form-label">New Password</label>
        <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
        @error('password', 'updatePassword')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="mb-3">
        <label for="update_password_password_confirmation" class="form-label">Confirm Password</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
        @error('password_confirmation', 'updatePassword')
            <span class="invalid-feedback d-block">{{ $message }}</span>
        @enderror
    </div>

    <div class="d-flex align-items-center gap-3">
        <button type="submit" class="btn btn-primary">Save</button>

        @if (session('status') === 'password-updated')
            <p class="fs-12 text-muted mb-0">Saved.</p>
        @endif
    </div>
</form>
