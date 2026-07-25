@extends('layouts.app')

@section('title', 'Edit Client')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Edit Client</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="{{ route('clients.index') }}">Clients</a></li>
                <li class="breadcrumb-item">Edit</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                @if ($client->documents->isNotEmpty())
                    <div class="card stretch stretch-full mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3">Existing Documents</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>File</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($client->documents as $document)
                                            <tr>
                                                <td>{{ $document->document_type }}</td>
                                                <td>
                                                    <a href="{{ asset('storage/'.$document->file_path) }}" target="_blank">
                                                        {{ $document->original_name ?? 'View File' }}
                                                    </a>
                                                </td>
                                                <td class="text-end">
                                                    <form
                                                        method="POST"
                                                        action="{{ route('clients.documents.destroy', [$client, $document]) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Delete this document?');"
                                                    >
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-light-danger">
                                                            <i class="feather-trash-2"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="POST" action="{{ route('clients.update', $client) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            @include('clients._form')

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">Update Client</button>
                                <a href="{{ route('clients.index') }}" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
