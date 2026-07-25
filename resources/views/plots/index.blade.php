@extends('layouts.app')

@section('title', 'Plots')

@php
    $statusBadgeClass = [
        'Available' => 'bg-success',
        'Reserved' => 'bg-warning',
        'Sold' => 'bg-danger',
        'Cancelled' => 'bg-secondary',
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Plots</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Plots</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('plots.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Plot
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="GET" action="{{ route('plots.index') }}" class="row g-3 mb-4">
                            <div class="col-md-4">
                                <input
                                    type="text"
                                    name="plot_number"
                                    class="form-control"
                                    placeholder="Search by plot number"
                                    value="{{ request('plot_number') }}"
                                />
                            </div>
                            <div class="col-md-3">
                                <select name="block_id" class="form-control">
                                    <option value="">All Blocks</option>
                                    @foreach ($blocks as $blockOption)
                                        <option value="{{ $blockOption->id }}" @selected(request('block_id') == $blockOption->id)>
                                            {{ $blockOption->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-control">
                                    <option value="">All Statuses</option>
                                    @foreach (['Available', 'Reserved', 'Sold', 'Cancelled'] as $statusOption)
                                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>
                                            {{ $statusOption }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                                <a href="{{ route('plots.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Plot Number</th>
                                        <th>Block</th>
                                        <th>Size</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Total Price</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($plots as $plot)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $plot->plot_number }}</td>
                                            <td>{{ $plot->block->name }}</td>
                                            <td>{{ $plot->plot_size }}</td>
                                            <td>{{ $plot->category }}</td>
                                            <td>
                                                <span class="badge {{ $statusBadgeClass[$plot->status] ?? 'bg-secondary' }}">
                                                    {{ $plot->status }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($plot->total_price, 2) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('plots.edit', $plot) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-edit-2"></i> Edit
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('plots.destroy', $plot) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this plot?');"
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
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No plots found. Try changing your filters, or click "Add Plot" to create one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $plots->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
