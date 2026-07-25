@extends('layouts.app')

@section('title', 'Edit Plot')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Edit Plot</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('plots.index') }}">Plots</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-9">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="POST" action="{{ route('plots.update', $plot) }}">
                            @csrf
                            @method('PUT')

                            @include('plots._form')

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Plot</button>
                                <a href="{{ route('plots.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
