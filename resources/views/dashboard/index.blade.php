@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Dashboard</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Dashboard</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body text-center py-5">
                        <h4 class="mb-2">Welcome, {{ auth()->user()->name }}</h4>
                        <p class="text-muted mb-0">This is your Dashboard. Modules will appear here as they are built.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
