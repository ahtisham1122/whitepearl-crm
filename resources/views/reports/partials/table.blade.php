<div class="table-responsive">
    <table class="table table-bordered table-hover">
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
                        No records found for the selected filters.
                    </td>
                </tr>
            @endforelse

            @if ($totalsRow)
                <tr class="table-light fw-bold">
                    @foreach ($totalsRow as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endif
        </tbody>
    </table>
</div>
