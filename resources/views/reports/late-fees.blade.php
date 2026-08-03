@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">{{ $title }}</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0);">Reports</a></li>
                <li class="breadcrumb-item">{{ $title }}</li>
            </ul>
        </div>
        <div class="page-header-right ms-auto d-flex gap-2">
            <a href="{{ route('reports.late-fees.export', ['format' => 'excel']) }}" class="btn btn-outline-success">
                <i class="feather-file-text me-2"></i>Excel
            </a>
            <a href="{{ route('reports.late-fees.export', ['format' => 'pdf']) }}" class="btn btn-outline-danger">
                <i class="feather-download me-2"></i>PDF
            </a>
            <a href="{{ route('reports.late-fees.print') }}" class="btn btn-outline-primary" target="_blank">
                <i class="feather-printer me-2"></i>Print
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            <div class="col-12">
                <div class="card stretch stretch-full">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <strong>How this works:</strong> the fine shown is only a suggestion --
                            1.5% of the overdue amount, for every month it has been overdue. Nothing
                            here is charged automatically. Review a row and click <strong>Apply Fine</strong>
                            only when you want to actually add it to that client's account; it affects
                            that one booking only.
                            <br>
                            Rows marked <span class="badge bg-light-warning text-warning">Imported</span>
                            came from the Block A spreadsheet import, which used the import date as
                            every installment's due date instead of its real original due date -- so
                            "Months Overdue" for those rows is measured from the import date, not true
                            history. Double-check those before applying a fine.
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>CNIC</th>
                                        <th>Plot</th>
                                        <th>Overdue Amount</th>
                                        <th>Months Overdue</th>
                                        <th>Suggested Fine (1.5%/month)</th>
                                        <th>Data Source</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($defaulters as $d)
                                        <tr>
                                            <td>{{ $d['booking']->client->full_name }}</td>
                                            <td>{{ $d['booking']->client->cnic }}</td>
                                            <td>{{ $d['booking']->plot->block->name }} - {{ $d['booking']->plot->plot_number }}</td>
                                            <td>{{ setting('currency_symbol', 'Rs.') }} {{ number_format($d['overdue_amount'], 2) }}</td>
                                            <td>{{ $d['months_overdue'] }}</td>
                                            <td class="fw-bold">{{ setting('currency_symbol', 'Rs.') }} {{ number_format($d['suggested_fine'], 2) }}</td>
                                            <td>
                                                @if ($d['is_legacy'])
                                                    <span class="badge bg-light-warning text-warning">Imported</span>
                                                @else
                                                    <span class="badge bg-light-success text-success">Live</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form
                                                    method="POST"
                                                    action="{{ route('reports.late-fees.apply', $d['booking']) }}"
                                                    onsubmit="return confirm('Apply a fine of {{ setting('currency_symbol', 'Rs.') }} {{ number_format($d['suggested_fine'], 2) }} to {{ addslashes($d['booking']->client->full_name) }}\'s account? This will be added to their installment balance and cannot be auto-reversed.');"
                                                >
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-danger">Apply Fine</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">
                                                No clients are currently overdue.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
