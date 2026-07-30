<?php

namespace App\Services;

use App\Models\Block;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\InstallmentPlan;
use App\Models\Payment;
use App\Models\Plot;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Gather every number, chart series, and list the dashboard needs, in
     * one place. Each piece below is either a single aggregate query
     * (COUNT/SUM/GROUP BY done by the database) or one query with eager
     * loading, never a query repeated inside a PHP loop.
     */
    public function getDashboardData(): array
    {
        $plotStatusCounts = $this->getPlotStatusCounts();
        $monthlyCollection = $this->getMonthlyCollectionSeries();
        $overdueInstallments = $this->getOverdueInstallments();

        return [
            'totalPlots' => array_sum($plotStatusCounts),
            'plotStatusCounts' => $plotStatusCounts,
            'totalClients' => Client::count(),
            'revenue' => $this->getRevenueStats(),
            'outstandingAmount' => $this->getOutstandingAmount(),
            'activeInstallmentPlansCount' => InstallmentPlan::where('status', 'Active')->count(),
            'overdueInstallmentsCount' => $overdueInstallments->count(),
            'monthlyCollection' => $monthlyCollection,
            'revenueTrend' => $this->getRevenueTrendSeries($monthlyCollection),
            'blockWiseSales' => $this->getBlockWiseSales(),
            'recentPayments' => $this->getRecentPayments(),
            'upcomingInstallments' => $this->getUpcomingInstallments(),
            'defaulters' => $this->getDefaulters($overdueInstallments),
            'recentBookings' => $this->getRecentBookings(),
        ];
    }

    /**
     * One GROUP BY query gives every plot status count in a single trip to
     * the database, instead of four separate ->where(...)->count() calls.
     */
    private function getPlotStatusCounts(): array
    {
        $counts = Plot::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return collect(Plot::STATUSES)
            ->mapWithKeys(fn (string $status) => [$status => (int) ($counts[$status] ?? 0)])
            ->all();
    }

    private function getRevenueStats(): array
    {
        return [
            'total' => (float) Payment::sum('amount'),
            'today' => (float) Payment::whereDate('payment_date', today())->sum('amount'),
            'this_month' => (float) Payment::whereYear('payment_date', now()->year)
                ->whereMonth('payment_date', now()->month)
                ->sum('amount'),
        ];
    }

    /**
     * What's still owed across every installment on active bookings.
     * GREATEST(...) clamps a row at 0 in the database itself, so we never
     * pull every installment into PHP just to sum them.
     */
    private function getOutstandingAmount(): float
    {
        return (float) Installment::whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->selectRaw('COALESCE(SUM(GREATEST(amount + late_fee - paid_amount, 0)), 0) as total')
            ->value('total');
    }

    /**
     * Every installment that is overdue right now, fetched ONCE with its
     * client/plot chain eager-loaded. Both the "Overdue Installments" count
     * and the Defaulters list are built from this same collection, so
     * loading it twice never happens.
     */
    private function getOverdueInstallments(): Collection
    {
        return Installment::where('due_date', '<', today())
            ->where('status', '!=', 'Paid')
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->get();
    }

    /**
     * Group the already-fetched overdue installments by client in memory
     * (no extra queries) to build a "who owes the most" list.
     */
    private function getDefaulters(Collection $overdueInstallments, int $limit = 10): Collection
    {
        return $overdueInstallments
            ->groupBy(fn (Installment $installment) => $installment->installmentPlan->booking->client_id)
            ->map(function (Collection $installments) {
                $client = $installments->first()->installmentPlan->booking->client;

                return [
                    'client' => $client,
                    'overdue_count' => $installments->count(),
                    'overdue_amount' => $installments->sum(
                        fn (Installment $installment) => max($installment->amount + $installment->late_fee - $installment->paid_amount, 0)
                    ),
                ];
            })
            ->sortByDesc('overdue_amount')
            ->take($limit)
            ->values();
    }

    /**
     * Total collected per month for the last $months months, with every
     * month present even if it had zero payments (GROUP BY alone would
     * silently skip empty months).
     */
    private function getMonthlyCollectionSeries(int $months = 12): array
    {
        $monthStarts = collect(range($months - 1, 0))
            ->map(fn (int $i) => now()->subMonths($i)->startOfMonth());

        $totals = Payment::selectRaw("DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount) as total")
            ->where('payment_date', '>=', $monthStarts->first())
            ->groupBy('month')
            ->pluck('total', 'month');

        $labels = [];
        $data = [];

        foreach ($monthStarts as $monthStart) {
            $labels[] = $monthStart->format('M Y');
            $data[] = (float) ($totals[$monthStart->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'data' => $data];
    }

    /**
     * Running total of the monthly collection series above -- reuses that
     * data instead of running a second query for "trend."
     */
    private function getRevenueTrendSeries(array $monthlyCollection): array
    {
        $cumulative = 0;
        $data = [];

        foreach ($monthlyCollection['data'] as $amount) {
            $cumulative += $amount;
            $data[] = $cumulative;
        }

        return ['labels' => $monthlyCollection['labels'], 'data' => $data];
    }

    /**
     * Sold-plot count per block in one query (withCount), instead of
     * looping over blocks and counting each one's plots separately.
     */
    private function getBlockWiseSales(): array
    {
        $blocks = Block::withCount(['plots as sold_plots_count' => fn ($query) => $query->where('status', 'Sold')])
            ->orderBy('name')
            ->get();

        return [
            'labels' => $blocks->pluck('name')->all(),
            'data' => $blocks->pluck('sold_plots_count')->all(),
        ];
    }

    private function getRecentPayments(int $limit = 8): Collection
    {
        return Payment::with(['booking.client', 'booking.plot.block'])
            ->latest('payment_date')
            ->limit($limit)
            ->get();
    }

    private function getUpcomingInstallments(int $limit = 8): Collection
    {
        return Installment::where('status', '!=', 'Paid')
            ->where('due_date', '>=', today())
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    private function getRecentBookings(int $limit = 5): Collection
    {
        return Booking::with(['client', 'plot.block'])
            ->latest('booking_date')
            ->limit($limit)
            ->get();
    }
}
