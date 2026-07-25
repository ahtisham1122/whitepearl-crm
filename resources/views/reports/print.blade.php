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
        <div class="text-center mb-4">
            <h4 class="fw-bold mb-1">White Pearl City Gwadar</h4>
            <p class="text-muted mb-0">{{ $title }}</p>
            <p class="text-muted fs-12 mb-0">Generated {{ now()->format('d M Y, h:i A') }}</p>
        </div>

        @include('reports.partials.table')
    </div>
@endsection
