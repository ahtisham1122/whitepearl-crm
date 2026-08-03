@extends('layouts.print')

@section('title', $title)

@section('content')
    <div class="no-print d-flex justify-content-between align-items-center">
        <span></span>
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="feather-printer me-2"></i>Print
        </button>
    </div>

    <div class="print-sheet">
        @include('partials.company-header', ['subtitle' => $title])
        <p class="text-muted fs-12 text-center mb-4">Generated {{ now()->format('d M Y, h:i A') }}</p>

        @isset($note)
            <p class="text-muted fs-12 text-center mb-4"><em>{{ $note }}</em></p>
        @endisset

        @isset($summary)
            <table class="table table-bordered mb-4">
                <thead>
                    <tr>
                        @foreach ($summary as $group)
                            <th>{{ $group['label'] }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach ($summary as $group)
                            <td>{{ $group['count'] }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        @endisset

        @include('reports.partials.table')
    </div>
@endsection
