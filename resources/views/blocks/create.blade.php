@extends('layouts.app')

@section('title', 'Add Block')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Add Block</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blocks.index') }}">Blocks</a></li>
                <li class="breadcrumb-item">Add</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-lg-6">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="POST" action="{{ route('blocks.store') }}">
                            @csrf

                            @include('blocks._form')

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Save Block</button>
                                <a href="{{ route('blocks.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
