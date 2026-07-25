<form method="GET" action="{{ route($routeBase) }}" class="row g-3 mb-4">
    <div class="col-md-4">
        <select name="status" class="form-control">
            @foreach ($statusOptions as $option)
                <option value="{{ $option }}" @selected(request('status', 'All') === $option)>
                    {{ $option }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
</form>
