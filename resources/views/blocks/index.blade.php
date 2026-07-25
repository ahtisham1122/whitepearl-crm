@extends('layouts.app')

@section('title', 'Blocks')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Blocks</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Blocks</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto">
            <a href="{{ route('blocks.create') }}" class="btn btn-primary">
                <i class="feather-plus me-2"></i>Add Block
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Total Plots</th>
                                        <th>Available</th>
                                        <th>Sold</th>
                                        <th>Reserved</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($blocks as $block)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $block->name }}</td>
                                            <td>{{ $block->description ?? '—' }}</td>
                                            {{-- Plots don't exist yet, so these counts are hardcoded to 0 for now.
                                                 Once the Plots module is built, these will be calculated from
                                                 the block's related plots instead. --}}
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td>0</td>
                                            <td class="text-end">
                                                <a href="{{ route('blocks.edit', $block) }}" class="btn btn-sm btn-light-brand">
                                                    <i class="feather-edit-2"></i> Edit
                                                </a>
                                                <form
                                                    method="POST"
                                                    action="{{ route('blocks.destroy', $block) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Are you sure you want to delete this block?');"
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
                                                No blocks added yet. Click "Add Block" to create the first one.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $blocks->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
