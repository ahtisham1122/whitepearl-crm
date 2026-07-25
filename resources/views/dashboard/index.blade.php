@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $plotStats = [
        ['label' => 'Total Plots', 'value' => number_format($totalPlots), 'icon' => 'feather-grid', 'color' => 'primary'],
        ['label' => 'Available', 'value' => number_format($plotStatusCounts['Available']), 'icon' => 'feather-check-circle', 'color' => 'success'],
        ['label' => 'Reserved', 'value' => number_format($plotStatusCounts['Reserved']), 'icon' => 'feather-clock', 'color' => 'warning'],
        ['label' => 'Sold', 'value' => number_format($plotStatusCounts['Sold']), 'icon' => 'feather-check-square', 'color' => 'danger'],
        ['label' => 'Cancelled', 'value' => number_format($plotStatusCounts['Cancelled']), 'icon' => 'feather-x-circle', 'color' => 'secondary'],
    ];

    $businessStats = [
        ['label' => 'Total Clients', 'value' => number_format($totalClients), 'icon' => 'feather-users', 'color' => 'info'],
        ['label' => 'Total Revenue', 'value' => 'Rs. '.number_format($revenue['total'], 2), 'icon' => 'feather-dollar-sign', 'color' => 'success'],
        ['label' => 'Outstanding Amount', 'value' => 'Rs. '.number_format($outstandingAmount, 2), 'icon' => 'feather-alert-circle', 'color' => 'danger'],
        ['label' => "Today's Collection", 'value' => 'Rs. '.number_format($revenue['today'], 2), 'icon' => 'feather-calendar', 'color' => 'primary'],
        ['label' => "This Month's Collection", 'value' => 'Rs. '.number_format($revenue['this_month'], 2), 'icon' => 'feather-trending-up', 'color' => 'primary'],
        ['label' => 'Active Installment Plans', 'value' => number_format($activeInstallmentPlansCount), 'icon' => 'feather-file-text', 'color' => 'info'],
        ['label' => 'Overdue Installments', 'value' => number_format($overdueInstallmentsCount), 'icon' => 'feather-alert-triangle', 'color' => 'danger'],
    ];
@endphp

@section('content')
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Dashboard</h5>
            </div>
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Dashboard</li>
            </ul>
        </div>
    </div>

    <div class="main-content">
        <div class="row">
            @foreach ($plotStats as $stat)
                <div class="col-xl-2 col-md-4 col-6 mb-4">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg bg-light-{{ $stat['color'] }} text-{{ $stat['color'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-muted fs-12 mb-1">{{ $stat['label'] }}</p>
                                <h5 class="mb-0">{{ $stat['value'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            @foreach ($businessStats as $stat)
                <div class="col-xl-3 col-md-4 col-6 mb-4">
                    <div class="card stretch stretch-full">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="avatar-text avatar-lg bg-light-{{ $stat['color'] }} text-{{ $stat['color'] }}">
                                <i class="{{ $stat['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-muted fs-12 mb-1">{{ $stat['label'] }}</p>
                                <h5 class="mb-0">{{ $stat['value'] }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Monthly Collection (Last 12 Months)</h6>
                    </div>
                    <div class="card-body">
                        <div id="monthlyCollectionChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Plot Status Distribution</h6>
                    </div>
                    <div class="card-body">
                        <div id="plotStatusChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Block-wise Sales (Sold Plots)</h6>
                    </div>
                    <div class="card-body">
                        <div id="blockSalesChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Revenue Trend (Cumulative)</h6>
                    </div>
                    <div class="card-body">
                        <div id="revenueTrendChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Recent Payments</h6>
                    </div>
                    <div class="card-body">
                        @if ($recentPayments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentPayments as $payment)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('payments.show', $payment) }}">
                                                        {{ $payment->booking->client->full_name }}
                                                    </a>
                                                </td>
                                                <td>{{ number_format($payment->amount, 2) }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td>{{ $payment->payment_date->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No payments recorded yet.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Upcoming Installments</h6>
                    </div>
                    <div class="card-body">
                        @if ($upcomingInstallments->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Plot</th>
                                            <th>Due Date</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($upcomingInstallments as $installment)
                                            <tr>
                                                <td>{{ $installment->installmentPlan->booking->client->full_name }}</td>
                                                <td>
                                                    {{ $installment->installmentPlan->booking->plot->block->name }}
                                                    - {{ $installment->installmentPlan->booking->plot->plot_number }}
                                                </td>
                                                <td>{{ $installment->due_date->format('d M Y') }}</td>
                                                <td>{{ number_format($installment->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No upcoming installments.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Defaulters (Overdue Installments)</h6>
                    </div>
                    <div class="card-body">
                        @if ($defaulters->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Client</th>
                                            <th>Overdue Count</th>
                                            <th>Overdue Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($defaulters as $row)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('clients.show', $row['client']) }}">
                                                        {{ $row['client']->full_name }}
                                                    </a>
                                                </td>
                                                <td>{{ $row['overdue_count'] }}</td>
                                                <td class="text-danger">{{ number_format($row['overdue_amount'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No defaulters. Everyone is up to date.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h6 class="card-title mb-0">Recent Bookings</h6>
                    </div>
                    <div class="card-body">
                        @if ($recentBookings->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Booking #</th>
                                            <th>Client</th>
                                            <th>Plot</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentBookings as $booking)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('bookings.show', $booking) }}">{{ $booking->booking_number }}</a>
                                                </td>
                                                <td>{{ $booking->client->full_name }}</td>
                                                <td>{{ $booking->plot->block->name }} - {{ $booking->plot->plot_number }}</td>
                                                <td>{{ $booking->booking_date->format('d M Y') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted mb-0">No bookings yet.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/vendors/js/apexcharts.min.js') }}"></script>
    <script>
        (function () {
            const monthlyCollection = @json($monthlyCollection);
            const revenueTrend = @json($revenueTrend);
            const blockWiseSales = @json($blockWiseSales);
            const plotStatusCounts = @json($plotStatusCounts);

            new ApexCharts(document.querySelector('#monthlyCollectionChart'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Collection', data: monthlyCollection.data }],
                xaxis: { categories: monthlyCollection.labels, labels: { style: { fontSize: '10px' } } },
                colors: ['#2a78d6'],
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                yaxis: { labels: { formatter: function (val) { return Math.round(val).toLocaleString(); } } },
                tooltip: { y: { formatter: function (val) { return 'Rs. ' + Number(val).toLocaleString(); } } },
            }).render();

            new ApexCharts(document.querySelector('#plotStatusChart'), {
                chart: { type: 'donut', height: 320 },
                series: [
                    plotStatusCounts.Available,
                    plotStatusCounts.Reserved,
                    plotStatusCounts.Sold,
                    plotStatusCounts.Cancelled,
                ],
                labels: ['Available', 'Reserved', 'Sold', 'Cancelled'],
                colors: ['#0ca30c', '#fab219', '#d03b3b', '#898781'],
                legend: { position: 'bottom' },
                dataLabels: { enabled: true, formatter: function (val) { return val.toFixed(1) + '%'; } },
            }).render();

            new ApexCharts(document.querySelector('#blockSalesChart'), {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: 'Sold Plots', data: blockWiseSales.data }],
                xaxis: { categories: blockWiseSales.labels, labels: { style: { fontSize: '10px' } } },
                colors: ['#2a78d6'],
                plotOptions: { bar: { columnWidth: '45%', borderRadius: 4 } },
                dataLabels: { enabled: false },
                yaxis: { labels: { formatter: function (val) { return Math.round(val); } } },
                tooltip: { y: { formatter: function (val) { return Math.round(val) + ' plots'; } } },
            }).render();

            new ApexCharts(document.querySelector('#revenueTrendChart'), {
                chart: { type: 'line', height: 320, toolbar: { show: false } },
                series: [{ name: 'Cumulative Revenue', data: revenueTrend.data }],
                xaxis: { categories: revenueTrend.labels, labels: { style: { fontSize: '10px' } } },
                colors: ['#2a78d6'],
                stroke: { width: 2, curve: 'smooth' },
                markers: { size: 4 },
                dataLabels: { enabled: false },
                yaxis: { labels: { formatter: function (val) { return Math.round(val).toLocaleString(); } } },
                tooltip: { y: { formatter: function (val) { return 'Rs. ' + Number(val).toLocaleString(); } } },
            }).render();
        })();
    </script>
@endpush
