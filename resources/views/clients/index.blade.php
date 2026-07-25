@extends('layouts.app')

@section('title', 'Clients')

@php
    $statusBadgeClass = [
        'Active' => 'bg-success',
        'Inactive' => 'bg-secondary',
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Clients</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Clients</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('clients.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Client
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="GET" action="{{ route('clients.index') }}" class="row g-3 mb-4">
                            <div class="col-md-8">
                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search by name, client ID, CNIC, or phone"
                                    value="{{ request('search') }}"
                                />
                            </div>
                            <div class="col-md-4 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Search</button>
                                <a href="{{ route('clients.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client ID</th>
                                        <th>Name</th>
                                        <th>CNIC</th>
                                        <th>Phone</th>
                                        <th>City</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($clients as $client)
                                        <tr>
                                            <td>{{ $client->client_id }}</td>
                                            <td>{{ $client->full_name }}</td>
                                            <td>{{ $client->cnic }}</td>
                                            <td>{{ $client->phone }}</td>
                                            <td>{{ $client->city ?? '—' }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass[$client->status] ?? 'bg-secondary' }}">
                                                    {{ $client->status }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-eye"></i> View
                                                </a>
                                                <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-edit-2"></i> Edit
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('clients.destroy', $client) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this client?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger">
                                                        <i class="feather-trash-2"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No clients found. Try a different search, or click "Add Client" to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $clients->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
