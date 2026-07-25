@php
    $contact = collect([setting('company_phone'), setting('company_email')])->filter()->implode(' | ');
    $logoPath = setting('company_logo') ? public_path('storage/'.setting('company_logo')) : null;
@endphp

@if ($logoPath && file_exists($logoPath))
    <img src="{{ $logoPath }}" alt="{{ setting('company_name') }}" style="max-height: 50px; margin-bottom: 6px;" />
@endif
<h1>{{ setting('company_name') }}</h1>
@if (setting('company_address'))
    <p>{{ setting('company_address') }}</p>
@endif
@if ($contact)
    <p>{{ $contact }}</p>
@endif
@isset($subtitle)
    <p>{{ $subtitle }}</p>
@endisset
