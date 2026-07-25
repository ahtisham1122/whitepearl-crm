@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $title }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                <li class="breadcrumb-item">{{ $title }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route($routeBase.'.export', array_merge(request()->query(), ['format' => 'excel'])) }}" class="btn btn-outline-success">
                <i class="feather-file-text me-2"></i>Excel
            </a>
            <a href="{{ route($routeBase.'.export', array_merge(request()->query(), ['format' => 'pdf'])) }}" class="btn btn-outline-danger">
                <i class="feather-download me-2"></i>PDF
            </a>
            <a href="{{ route($routeBase.'.print', request()->query()) }}" class="btn btn-outline-primary" target="_blank">
                <i class="feather-printer me-2"></i>Print
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        @if ($filterType === 'status')
                            @include('reports.partials.filter-status')
                        @elseif ($filterType === 'date-range')
                            @include('reports.partials.filter-date-range')
                        @endif

                        @include('reports.partials.table')
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
