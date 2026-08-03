@extends('layouts.print')

@section('title', $title.' - '.$booking->client->full_name)

@section('content')
    <div class="no-print d-flex justify-content-between align-items-center">
        <span></span>
        <button type="button" class="btn btn-primary" onclick="window.print();">
            <i class="feather-printer me-2"></i>Print
        </button>
    </div>

    <div class="print-sheet">
        @include('reports.partials.client-statement-body')
    </div>
@endsection
