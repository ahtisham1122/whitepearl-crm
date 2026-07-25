@extends('layouts.app')

@section('title', 'Activity Logs')

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Activity Logs</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Activity Logs</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <form method="GET" action="{{ route('activity-logs.index') }}" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <select name="user_id" class="form-control">
                                    <option value="">All Users</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" @selected((int) request('user_id') === $user->id)>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="description" class="form-control">
                                    <option value="">All Actions</option>
                                    @foreach ($descriptions as $description)
                                        <option value="{{ $description }}" @selected(request('description') === $description)>
                                            {{ $description }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="from" class="form-control" value="{{ request('from') }}" placeholder="From" />
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="to" class="form-control" value="{{ request('to') }}" placeholder="To" />
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                                <a href="{{ route('activity-logs.index') }}" class="btn btn-light">Reset</a>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>User</th>
                                        <th>Action</th>
                                        <th>Affected Record</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($activities as $activity)
                                        <tr>
                                            <td>{{ $activity->created_at->format('d M Y, h:i A') }}</td>
                                            <td>{{ $activity->causer?->name ?? 'System' }}</td>
                                            <td>{{ $activity->description }}</td>
                                            <td>{{ \App\Http\Controllers\ActivityLogController::describeSubject($activity) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">
                                                No activity found for the selected filters.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $activities->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
