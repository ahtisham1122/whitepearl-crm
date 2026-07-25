<form method="GET" action="{{ route($routeBase) }}" class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label">From</label>
        <input type="date" name="from" class="form-control" value="{{ request('from') }}" />
    </div>
    <div class="col-md-3">
        <label class="form-label">To</label>
        <input type="date" name="to" class="form-control" value="{{ request('to') }}" />
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <button type="submit" class="btn btn-primary w-100">Filter</button>
    </div>
</form>
