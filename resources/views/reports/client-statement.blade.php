@extends('layouts.app')

@section('title', $title.' - '.$booking->client->full_name)

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $title }} - {{ $booking->client->full_name }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('reports.clients') }}">Reports</a></li>
                <li class="breadcrumb-item">Client Statement</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route('reports.client-statement.export', ['booking' => $booking, 'format' => 'excel']) }}" class="btn btn-outline-success">
                <i class="feather-file-text me-2"></i>Excel
            </a>
            <a href="{{ route('reports.client-statement.export', ['booking' => $booking, 'format' => 'pdf']) }}" class="btn btn-outline-danger">
                <i class="feather-download me-2"></i>PDF
            </a>
            <a href="{{ route('reports.client-statement.print', $booking) }}" class="btn btn-outline-primary" target="_blank">
                <i class="feather-printer me-2"></i>Print
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        @include('reports.partials.client-statement-body')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
