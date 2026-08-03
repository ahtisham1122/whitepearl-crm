{{--
    Shared by the on-screen statement page and the printable page, so the
    two never drift apart. The header text below is deliberately hardcoded
    (not pulled from company settings) -- this document is meant to match
    the client's old paper "Sale Installment Schedule" form word for word.
--}}
<div class="text-center mb-4">
    <h3 class="fw-bold mb-0">WHITE PEARL CITY</h3>
    <p class="mb-1">(Sale Installment Schedule)</p>
    <p class="text-muted fs-12 mb-0">Page 1</p>
</div>

@if ($booking->status !== 'Active')
    <div class="alert alert-warning text-center fw-bold">
        Status: {{ $booking->status }} -- this is historical data for a booking that is no longer active.
    </div>
@endif

@include('reports.partials.client-statement-summary')

<div class="table-responsive mb-5">
    <table class="table table-bordered">
        <thead>
            <tr>
                @foreach ($headings as $heading)
                    <th>{{ $heading }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($headings) }}" class="text-center text-muted py-4">
                        This booking has no installment plan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="row mt-5 pt-5">
    <div class="col-6">
        <p class="mb-0 border-top pt-2">Signature</p>
    </div>
    <div class="col-6 text-end">
        <p class="mb-0 fw-bold">White Pearl City</p>
    </div>
</div>
