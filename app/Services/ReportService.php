<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\Payment;
use App\Models\Plot;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Every report method below returns the exact same shape:
 *
 *   [
 *       'title'     => string,               // shown on screen, print, and PDF
 *       'filename'  => string,                // kebab-case, used for exports
 *       'headings'  => array<string>,         // column names, in order
 *       'rows'      => Collection<array>,     // one associative array per row, same keys as headings
 *       'totalsRow' => ?array,                // optional bottom row (Collection Report, Sales Report)
 *   ]
 *
 * That one shape is what lets the exact same data feed the on-screen
 * table, the printable page, the PDF, and the Excel file -- nothing is
 * built four times.
 */
class ReportService
{
    public function clientsReport(?string $status): array
    {
        $status = in_array($status, ['Active', 'Inactive'], true) ? $status : null;

        $clients = Client::query()
            ->when($status, fn ($query) => $query->where('status', $status))
            ->withCount(['bookings as owned_plots_count' => fn ($query) => $query->where('status', 'Active')])
            ->orderBy('full_name')
            ->get();

        $outstandingByClient = $this->bulkOutstandingBalances();

        $rows = $clients->map(fn (Client $client) => [
            'Client ID' => $client->client_id,
            'Name' => $client->full_name,
            'CNIC' => $client->cnic,
            'Phone' => $client->phone,
            'City' => $client->city ?? '—',
            'Status' => $client->status,
            'Owned Plots' => $client->owned_plots_count,
            'Outstanding Balance' => number_format($outstandingByClient[$client->id] ?? 0, 2),
        ]);

        return [
            'title' => 'All Clients',
            'filename' => 'clients-report',
            'headings' => ['Client ID', 'Name', 'CNIC', 'Phone', 'City', 'Status', 'Owned Plots', 'Outstanding Balance'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function plotsReport(?string $status): array
    {
        $status = in_array($status, ['Available', 'Reserved', 'Sold', 'Cancelled'], true) ? $status : null;

        $plots = Plot::with(['block', 'activeBooking.client'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('plot_number')
            ->get();

        $rows = $plots->map(fn (Plot $plot) => [
            'Plot Number' => $plot->plot_number,
            'Block' => $plot->block->name,
            'Size' => $plot->plot_size,
            'Category' => $plot->category,
            'Total Price' => number_format($plot->total_price, 2),
            'Status' => $plot->status,
            'Owner' => $plot->activeBooking?->client->full_name ?? '—',
        ]);

        return [
            'title' => 'Plots'.($status ? " ({$status})" : ''),
            'filename' => 'plots-report',
            'headings' => ['Plot Number', 'Block', 'Size', 'Category', 'Total Price', 'Status', 'Owner'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function blockSummaryReport(): array
    {
        $blocks = Block::withCount([
            'plots',
            'plots as available_plots_count' => fn ($query) => $query->where('status', 'Available'),
            'plots as sold_plots_count' => fn ($query) => $query->where('status', 'Sold'),
            'plots as reserved_plots_count' => fn ($query) => $query->where('status', 'Reserved'),
        ])->orderBy('name')->get();

        $revenueByBlock = Payment::join('bookings', 'payments.booking_id', '=', 'bookings.id')
            ->join('plots', 'bookings.plot_id', '=', 'plots.id')
            ->selectRaw('plots.block_id, SUM(payments.amount) as total')
            ->groupBy('plots.block_id')
            ->pluck('total', 'block_id');

        $rows = $blocks->map(fn (Block $block) => [
            'Block' => $block->name,
            'Total Plots' => $block->plots_count,
            'Available' => $block->available_plots_count,
            'Sold' => $block->sold_plots_count,
            'Reserved' => $block->reserved_plots_count,
            'Revenue' => number_format($revenueByBlock[$block->id] ?? 0, 2),
        ]);

        return [
            'title' => 'Block-wise Summary',
            'filename' => 'block-summary-report',
            'headings' => ['Block', 'Total Plots', 'Available', 'Sold', 'Reserved', 'Revenue'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function collectionReport(?string $from, ?string $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to, now()->startOfMonth(), now());

        $payments = Payment::with(['booking.client', 'booking.plot.block'])
            ->whereBetween('payment_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('payment_date')
            ->get();

        $rows = $payments->map(fn (Payment $payment) => [
            'Date' => $payment->payment_date->format('d M Y'),
            'Receipt #' => $payment->receipt_number,
            'Client' => $payment->booking->client->full_name,
            'Plot' => $payment->booking->plot->block->name.' - '.$payment->booking->plot->plot_number,
            'Amount' => number_format($payment->amount, 2),
            'Method' => $payment->payment_method,
        ]);

        return [
            'title' => 'Collection Report ('.$from->format('d M Y').' - '.$to->format('d M Y').')',
            'filename' => 'collection-report',
            'headings' => ['Date', 'Receipt #', 'Client', 'Plot', 'Amount', 'Method'],
            'rows' => $rows,
            'totalsRow' => [
                'Date' => '', 'Receipt #' => '', 'Client' => '', 'Plot' => 'TOTAL',
                'Amount' => number_format($payments->sum('amount'), 2), 'Method' => '',
            ],
        ];
    }

    public function outstandingBalancesReport(): array
    {
        $outstandingByClient = $this->bulkOutstandingBalances()->filter(fn ($amount) => $amount > 0);

        $clients = Client::whereIn('id', $outstandingByClient->keys())->get()->keyBy('id');

        $rows = $outstandingByClient
            ->sortDesc()
            ->map(fn ($amount, $clientId) => [
                'Client ID' => $clients[$clientId]->client_id,
                'Name' => $clients[$clientId]->full_name,
                'Phone' => $clients[$clientId]->phone,
                'Outstanding Balance' => number_format($amount, 2),
            ])
            ->values();

        return [
            'title' => 'Outstanding Balances',
            'filename' => 'outstanding-balances-report',
            'headings' => ['Client ID', 'Name', 'Phone', 'Outstanding Balance'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function dueInstallmentsReport(?string $from, ?string $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to, today(), today()->addDays(30));

        $installments = Installment::with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->whereBetween('due_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->where('status', '!=', 'Paid')
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->orderBy('due_date')
            ->get();

        $rows = $installments->map(fn (Installment $installment) => [
            'Due Date' => $installment->due_date->format('d M Y'),
            'Installment #' => $installment->installment_number,
            'Client' => $installment->installmentPlan->booking->client->full_name,
            'Plot' => $installment->installmentPlan->booking->plot->block->name.' - '.$installment->installmentPlan->booking->plot->plot_number,
            'Amount Due' => number_format(max($installment->amount + $installment->late_fee - $installment->paid_amount, 0), 2),
            'Status' => $installment->displayStatus(),
        ]);

        return [
            'title' => 'Due Installments ('.$from->format('d M Y').' - '.$to->format('d M Y').')',
            'filename' => 'due-installments-report',
            'headings' => ['Due Date', 'Installment #', 'Client', 'Plot', 'Amount Due', 'Status'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function defaultersReport(): array
    {
        $installments = Installment::with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->where('due_date', '<', today())
            ->where('status', '!=', 'Paid')
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->orderBy('due_date')
            ->get();

        $rows = $installments->map(fn (Installment $installment) => [
            'Client' => $installment->installmentPlan->booking->client->full_name,
            'Phone' => $installment->installmentPlan->booking->client->phone,
            'Plot' => $installment->installmentPlan->booking->plot->block->name.' - '.$installment->installmentPlan->booking->plot->plot_number,
            'Overdue Amount' => number_format(max($installment->amount + $installment->late_fee - $installment->paid_amount, 0), 2),
            'Days Overdue' => (int) $installment->due_date->diffInDays(today()),
        ]);

        return [
            'title' => 'Overdue Installments (Defaulters)',
            'filename' => 'defaulters-report',
            'headings' => ['Client', 'Phone', 'Plot', 'Overdue Amount', 'Days Overdue'],
            'rows' => $rows,
            'totalsRow' => null,
        ];
    }

    public function salesReport(?string $from, ?string $to): array
    {
        [$from, $to] = $this->normalizeDateRange($from, $to, now()->startOfMonth(), now());

        $bookings = Booking::with(['client', 'plot.block'])
            ->where('booking_type', 'Sale')
            ->whereBetween('booking_date', [$from->format('Y-m-d'), $to->format('Y-m-d')])
            ->orderBy('booking_date')
            ->get();

        $rows = $bookings->map(fn (Booking $booking) => [
            'Date' => $booking->booking_date->format('d M Y'),
            'Client' => $booking->client->full_name,
            'Plot' => $booking->plot->plot_number,
            'Block' => $booking->plot->block->name,
            'Final Price' => number_format($booking->final_price, 2),
            'Sales Person' => $booking->sales_person ?? '—',
        ]);

        return [
            'title' => 'Sales Report ('.$from->format('d M Y').' - '.$to->format('d M Y').')',
            'filename' => 'sales-report',
            'headings' => ['Date', 'Client', 'Plot', 'Block', 'Final Price', 'Sales Person'],
            'rows' => $rows,
            'totalsRow' => [
                'Date' => '', 'Client' => '', 'Plot' => '', 'Block' => 'TOTAL',
                'Final Price' => number_format($bookings->sum('final_price'), 2), 'Sales Person' => '',
            ],
        ];
    }

    /**
     * Outstanding balance for every client who has at least one active
     * booking, keyed by client_id. One query (with eager loading) plus an
     * in-memory group-and-sum -- not one query per client.
     */
    private function bulkOutstandingBalances(): Collection
    {
        return Booking::where('status', 'Active')
            ->with('installmentPlan.installments')
            ->get()
            ->groupBy('client_id')
            ->map(function (Collection $bookings) {
                return $bookings
                    ->flatMap(fn (Booking $booking) => $booking->installmentPlan?->installments ?? collect())
                    ->sum(fn (Installment $installment) => max($installment->amount + $installment->late_fee - $installment->paid_amount, 0));
            });
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function normalizeDateRange(?string $from, ?string $to, Carbon $defaultFrom, Carbon $defaultTo): array
    {
        $fromDate = $from ? Carbon::parse($from)->startOfDay() : $defaultFrom->copy()->startOfDay();
        $toDate = $to ? Carbon::parse($to)->endOfDay() : $defaultTo->copy()->endOfDay();

        return [$fromDate, $toDate];
    }
}
