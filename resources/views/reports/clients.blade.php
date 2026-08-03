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
                        @include('reports.partials.filter-status')

                        <p class="text-muted fs-12">Click a client's name to open their individual statement.</p>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        @foreach ($headings as $heading)
                                            <th>{{ $heading }}</th>
                                        @endforeach
                                        <th>Statement</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($rows as $i => $row)
                                        <tr>
                                            @foreach ($row as $key => $value)
                                                <td>
                                                    @if ($key === 'Name')
                                                        <a href="{{ route('reports.client-statement.picker', $clients[$i]) }}">{{ $value }}</a>
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td>
                                                <a href="{{ route('reports.client-statement.picker', $clients[$i]) }}" class="btn btn-sm btn-light-primary">
                                                    View Statement
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ count($headings) + 1 }}" class="text-center text-muted py-4">
                                                No records found for the selected filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
