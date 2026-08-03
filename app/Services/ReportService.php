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
    /**
     * The late fee business rule: 1.5% of the overdue amount, per month
     * overdue. See calculateFine() below for exactly how it's applied.
     */
    private const FINE_RATE_PER_MONTH = 0.015;

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
            'Outstanding Balance' => $this->money($outstandingByClient[$client->id] ?? 0),
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
        $status = in_array($status, Plot::STATUSES, true) ? $status : null;

        $plots = Plot::with(['block', 'activeBooking.client'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('plot_number')
            ->get();

        $rows = $plots->map(fn (Plot $plot) => [
            'Plot Number' => $plot->plot_number,
            'Block' => $plot->block->name,
            'Size' => $plot->plot_size,
            'Category' => $plot->category,
            'Total Price' => $this->money($plot->total_price),
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

    /**
     * The full detail report: every plot together with its owner and its
     * entire installment schedule. A plot can have many installments, but
     * "rows" (see the class docblock) can only ever be a flat table -- so
     * each installment gets its own row, with the plot's own details
     * repeated on every one of its rows. A plot with no booking yet (no
     * owner, no installments) still gets exactly one row, with those
     * columns shown as "—".
     *
     * Eager-loads block, the active booking, its client, and the whole
     * installment plan in one go, so this is 3 queries total no matter how
     * many plots or installments exist -- not one query per plot.
     */
    public function plotDetailReport(?string $blockId, ?string $status): array
    {
        $status = in_array($status, Plot::STATUSES, true) ? $status : null;

        $plots = Plot::with(['block', 'activeBooking.client', 'activeBooking.installmentPlan.installments'])
            ->when($blockId, fn ($query) => $query->where('block_id', $blockId))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderBy('plot_number')
            ->get();

        $rows = $plots->flatMap(function (Plot $plot) {
            $booking = $plot->activeBooking;
            $installments = $booking?->installmentPlan?->installments ?? collect();

            $totalPaid = $installments->sum(fn (Installment $i) => (float) $i->paid_amount);
            $totalRemaining = $installments->sum(fn (Installment $i) => max($i->amount + $i->late_fee - $i->paid_amount, 0));

            $plotColumns = [
                'Plot Number' => $plot->plot_number,
                'Block' => $plot->block->name,
                'Size' => $plot->plot_size,
                'Category' => $plot->category,
                'Status' => $plot->status,
                'Total Price' => $this->money($plot->total_price),
                'Owner' => $booking?->client->full_name ?? '—',
                'Owner CNIC' => $booking?->client->cnic ?? '—',
            ];

            if ($installments->isEmpty()) {
                return [[
                    ...$plotColumns,
                    'Installment #' => '—',
                    'Due Date' => '—',
                    'Installment Amount' => '—',
                    'Paid Amount' => '—',
                    'Installment Status' => '—',
                    'Total Paid (Plot)' => $this->money($totalPaid),
                    'Total Remaining (Plot)' => $this->money($totalRemaining),
                ]];
            }

            return $installments->map(fn (Installment $installment) => [
                ...$plotColumns,
                'Installment #' => $installment->installment_number,
                'Due Date' => $installment->due_date->format('d M Y'),
                'Installment Amount' => $this->money($installment->amount),
                'Paid Amount' => $this->money($installment->paid_amount),
                'Installment Status' => $installment->displayStatus(),
                'Total Paid (Plot)' => $this->money($totalPaid),
                'Total Remaining (Plot)' => $this->money($totalRemaining),
            ])->all();
        });

        return [
            'title' => 'Plot Report'.($status ? " ({$status})" : ''),
            'filename' => 'plot-report',
            'headings' => [
                'Plot Number', 'Block', 'Size', 'Category', 'Status', 'Total Price', 'Owner', 'Owner CNIC',
                'Installment #', 'Due Date', 'Installment Amount', 'Paid Amount', 'Installment Status',
                'Total Paid (Plot)', 'Total Remaining (Plot)',
            ],
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
            'Revenue' => $this->money($revenueByBlock[$block->id] ?? 0),
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
            'Amount' => $this->money($payment->amount),
            'Method' => $payment->payment_method,
        ]);

        return [
            'title' => 'Collection Report ('.$from->format('d M Y').' - '.$to->format('d M Y').')',
            'filename' => 'collection-report',
            'headings' => ['Date', 'Receipt #', 'Client', 'Plot', 'Amount', 'Method'],
            'rows' => $rows,
            'totalsRow' => [
                'Date' => '', 'Receipt #' => '', 'Client' => '', 'Plot' => 'TOTAL',
                'Amount' => $this->money($payments->sum('amount')), 'Method' => '',
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
                'Outstanding Balance' => $this->money($amount),
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
            'Amount Due' => $this->money(max($installment->amount + $installment->late_fee - $installment->paid_amount, 0)),
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
            'Overdue Amount' => $this->money(max($installment->amount + $installment->late_fee - $installment->paid_amount, 0)),
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
            'Final Price' => $this->money($booking->final_price),
            'Sales Person' => $booking->sales_person ?? '—',
        ]);

        return [
            'title' => 'Sales Report ('.$from->format('d M Y').' - '.$to->format('d M Y').')',
            'filename' => 'sales-report',
            'headings' => ['Date', 'Client', 'Plot', 'Block', 'Final Price', 'Sales Person'],
            'rows' => $rows,
            'totalsRow' => [
                'Date' => '', 'Client' => '', 'Plot' => '', 'Block' => 'TOTAL',
                'Final Price' => $this->money($bookings->sum('final_price')), 'Sales Person' => '',
            ],
        ];
    }

    /**
     * How many installments a client has paid tells us how far along they
     * are, so this groups every active, installment-based booking by its
     * count of Paid installments: 0 (down payment only) up to 8, with
     * "8 or more" as the last bucket so a long payment plan never just
     * falls off the report.
     *
     * One row per booking, not per client -- if a client owns two plots,
     * each one has its own schedule and its own progress, so each gets its
     * own row and can land in a different group.
     */
    public function installmentsPaidGroupingReport(): array
    {
        $bookings = Booking::where('status', 'Active')
            ->whereHas('installmentPlan')
            ->with(['client', 'plot.block', 'installmentPlan.installments'])
            ->get();

        $maxGroup = 8;

        $withGroup = $bookings->map(function (Booking $booking) use ($maxGroup) {
            $installments = $booking->installmentPlan->installments;
            $paidCount = $installments->where('status', 'Paid')->count();
            $amountPaid = (float) $booking->booking_amount + $installments->sum(fn (Installment $i) => (float) $i->paid_amount);
            $remaining = $installments->sum(fn (Installment $i) => max($i->amount + $i->late_fee - $i->paid_amount, 0));

            return [
                'group' => min($paidCount, $maxGroup),
                'row' => [
                    'Group' => min($paidCount, $maxGroup) === $maxGroup ? "{$maxGroup} or more installments paid" : ($paidCount === 0 ? 'Down payment only (0 paid)' : "{$paidCount} installment(s) paid"),
                    'Client' => $booking->client->full_name,
                    'Plot' => $booking->plot->block->name.' - '.$booking->plot->plot_number,
                    'Amount Paid' => $this->money($amountPaid),
                    'Remaining' => $this->money($remaining),
                ],
            ];
        })->sortBy('group');

        $summary = $withGroup
            ->groupBy('group')
            ->map(fn ($group, $key) => [
                'label' => $key === $maxGroup ? "{$maxGroup} or more installments paid" : ($key === 0 ? 'Down payment only (0 paid)' : "{$key} installment(s) paid"),
                'count' => $group->count(),
            ])
            ->values();

        return [
            'title' => 'Clients Grouped by Installments Paid',
            'filename' => 'installments-progress-report',
            'headings' => ['Group', 'Client', 'Plot', 'Amount Paid', 'Remaining'],
            'rows' => $withGroup->pluck('row')->values(),
            'totalsRow' => null,
            'summary' => $summary,
        ];
    }

    /**
     * How much 1.5%-per-month-overdue would suggest charging every client
     * currently behind schedule, listed one row per booking. This report
     * only ever displays the number -- see PaymentService::applyFine() for
     * the one place it can actually be added to an installment, and only
     * ever for one booking at a time, only when an admin clicks to confirm it.
     */
    public function lateFeeReport(): array
    {
        $legacyBookingIds = Payment::where('is_legacy', true)->pluck('booking_id')->unique();

        $bookings = Booking::where('status', 'Active')
            ->whereHas('installmentPlan.installments', fn ($query) => $query->where('due_date', '<', today())->where('status', '!=', 'Paid'))
            ->with(['client', 'plot.block', 'installmentPlan.installments'])
            ->get();

        $defaulters = $bookings->map(function (Booking $booking) use ($legacyBookingIds) {
            $fine = $this->calculateFine($booking);

            return [
                'booking' => $booking,
                'is_legacy' => $legacyBookingIds->contains($booking->id),
                ...$fine,
            ];
        })->sortByDesc('suggested_fine')->values();

        $rows = $defaulters->map(fn (array $d) => [
            'Client' => $d['booking']->client->full_name,
            'CNIC' => $d['booking']->client->cnic,
            'Plot' => $d['booking']->plot->block->name.' - '.$d['booking']->plot->plot_number,
            'Overdue Amount' => $this->money($d['overdue_amount']),
            'Months Overdue' => $d['months_overdue'],
            'Suggested Fine (1.5%/month)' => $this->money($d['suggested_fine']),
            'Data Source' => $d['is_legacy'] ? 'Imported (approx. dates)' : 'Live',
        ]);

        return [
            'title' => 'Late Fee / Fine Suggestions',
            'filename' => 'late-fee-report',
            'headings' => ['Client', 'CNIC', 'Plot', 'Overdue Amount', 'Months Overdue', 'Suggested Fine (1.5%/month)', 'Data Source'],
            'rows' => $rows,
            'totalsRow' => null,
            'defaulters' => $defaulters,
        ];
    }

    /**
     * Re-runs the fine calculation for one booking, fresh, at the moment an
     * admin clicks "Apply" -- never trust a number sitting in a page that
     * might have been open a while.
     */
    public function suggestedFineForBooking(Booking $booking): float
    {
        $booking->loadMissing('installmentPlan.installments');

        return $this->calculateFine($booking)['suggested_fine'];
    }

    /**
     * The 1.5%-per-month-overdue fine rule from the business requirements.
     * "Overdue amount" is the original amount still owed on every
     * installment that is past due and not fully paid -- late fees already
     * sitting on an installment are deliberately excluded, otherwise a fine
     * applied one month would itself get a fine calculated on top of it the
     * next month, snowballing far past a flat 1.5%.
     *
     * "Months overdue" is counted from the earliest overdue installment's
     * due date: being late at all counts as 1 month, then one more for
     * every full 30 days after that. There is no partial-month discount.
     *
     * Imported (legacy) installments were given the import date as their
     * due date rather than their real original due date (see
     * BlockAImportService), so for those bookings "months overdue" is
     * measured from the import date, not history -- the Late Fee Report
     * flags this per row so nobody mistakes it for a real timeline.
     *
     * @return array{overdue_amount: float, months_overdue: int, suggested_fine: float}
     */
    private function calculateFine(Booking $booking): array
    {
        $overdueInstallments = ($booking->installmentPlan?->installments ?? collect())
            ->filter(fn (Installment $installment) => $installment->status !== 'Paid' && $installment->due_date->lt(today()));

        if ($overdueInstallments->isEmpty()) {
            return ['overdue_amount' => 0.0, 'months_overdue' => 0, 'suggested_fine' => 0.0];
        }

        $overdueAmount = round($overdueInstallments->sum(fn (Installment $i) => max($i->amount - $i->paid_amount, 0)), 2);
        $earliestDueDate = $overdueInstallments->min('due_date');
        $monthsOverdue = max((int) floor($earliestDueDate->diffInDays(today()) / 30), 1);
        $suggestedFine = round($overdueAmount * self::FINE_RATE_PER_MONTH * $monthsOverdue, 2);

        return [
            'overdue_amount' => $overdueAmount,
            'months_overdue' => $monthsOverdue,
            'suggested_fine' => $suggestedFine,
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

    /**
     * Format an amount with the configured currency symbol, e.g. "Rs. 1,234.00".
     * One place to change if the symbol setting is ever renamed or the
     * format ever needs to change, instead of every report doing its own thing.
     */
    public function money(float $amount): string
    {
        return setting('currency_symbol', 'Rs.').' '.number_format($amount, 2);
    }
}
