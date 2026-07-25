@php
    $contact = collect([setting('company_phone'), setting('company_email')])->filter()->implode(' | ');
@endphp

<div class="text-center mb-4">
    @if (setting('company_logo'))
        <img
            src="{{ asset('storage/'.setting('company_logo')) }}"
            alt="{{ setting('company_name') }}"
            style="max-height: 60px; object-fit: contain; margin-bottom: 8px;"
        />
    @endif
    <h4 class="fw-bold mb-1">{{ setting('company_name') }}</h4>
    @if (setting('company_address'))
        <p class="text-muted mb-0 fs-12">{{ setting('company_address') }}</p>
    @endif
    @if ($contact)
        <p class="text-muted mb-0 fs-12">{{ $contact }}</p>
    @endif
    @isset($subtitle)
        <p class="text-muted mb-0 mt-1">{{ $subtitle }}</p>
    @endisset
</div>
