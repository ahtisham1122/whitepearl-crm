<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use Illuminate\Support\Collection;

/**
 * Every alert here is calculated fresh from current data every time it's
 * asked for -- nothing is stored in a "notifications" table. See the
 * class-level note in each query method for why. This is deliberately
 * NOT Laravel's built-in notification system (no Notifiable trait, no
 * notifications table, no ->notify() calls) -- it's a live feed built
 * from the same business data every other module already has.
 */
class NotificationService
{
    private const UPCOMING_WINDOW_DAYS = 7;

    private const RECENT_WINDOW_DAYS = 7;

    /**
     * What the header bell needs on every single page load: a count, and
     * a short preview list. Deliberately limited to the two most urgent
     * alert types (overdue + upcoming installments) so this stays cheap
     * enough to run on every page -- the full 5-category picture lives
     * on the dedicated Notifications page instead.
     */
    public function getHeaderData(): array
    {
        $overdue = $this->overdueInstallments();
        $upcoming = $this->upcomingInstallments();

        // Fetched once here, not inside the map() below -- setting() is
        // cached, but calling it once per row would still mean one cache
        // lookup per row instead of one for the whole request.
        $currencySymbol = setting('currency_symbol');

        $alerts = $overdue->map(fn (Installment $installment) => $this->overdueInstallmentAlert($installment, $currencySymbol))
            ->concat($upcoming->map(fn (Installment $installment) => $this->upcomingInstallmentAlert($installment)))
            ->sortBy('sortDate')
            ->values();

        return [
            'count' => $overdue->count() + $upcoming->count(),
            'alerts' => $alerts->take(8)->values(),
        ];
    }

    /**
     * The full picture for the dedicated Notifications page, grouped by
     * type. Each entry is the real Eloquent collection (not the flattened
     * "Alert" array the header dropdown uses), so the page can show
     * type-specific columns instead of one generic message string.
     */
    public function getGroupedAlerts(): array
    {
        return [
            'overdue_installments' => $this->overdueInstallments(),
            'upcoming_installments' => $this->upcomingInstallments(),
            'new_bookings' => $this->recentBookings(),
            'new_clients' => $this->recentClients(),
            'missing_documents' => $this->clientsMissingDocuments(),
        ];
    }

    /**
     * Same "Overdue" definition used everywhere else in the app (the
     * Installment Plan module, the Dashboard, the Reports module):
     * past its due date and not yet fully paid. One query, eager-loaded,
     * not a query per installment.
     */
    private function overdueInstallments(): Collection
    {
        return Installment::with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->where('due_date', '<', today())
            ->where('status', '!=', 'Paid')
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->orderBy('due_date')
            ->get();
    }

    private function upcomingInstallments(): Collection
    {
        return Installment::with(['installmentPlan.booking.client', 'installmentPlan.booking.plot.block'])
            ->whereBetween('due_date', [today(), today()->addDays(self::UPCOMING_WINDOW_DAYS)])
            ->where('status', '!=', 'Paid')
            ->whereHas('installmentPlan.booking', fn ($query) => $query->where('status', 'Active'))
            ->orderBy('due_date')
            ->get();
    }

    private function recentBookings(): Collection
    {
        return Booking::with(['client', 'plot.block'])
            ->where('created_at', '>=', now()->subDays(self::RECENT_WINDOW_DAYS))
            ->latest()
            ->get();
    }

    private function recentClients(): Collection
    {
        return Client::where('created_at', '>=', now()->subDays(self::RECENT_WINDOW_DAYS))
            ->latest()
            ->get();
    }

    private function clientsMissingDocuments(): Collection
    {
        return Client::whereDoesntHave('documents')
            ->orderBy('full_name')
            ->get();
    }

    private function overdueInstallmentAlert(Installment $installment, string $currencySymbol): array
    {
        $booking = $installment->installmentPlan->booking;
        $owed = max($installment->amount + $installment->late_fee - $installment->paid_amount, 0);
        $daysOverdue = (int) $installment->due_date->diffInDays(today());

        return [
            'type' => 'Overdue Installment',
            'icon' => 'feather-alert-triangle',
            'color' => 'danger',
            'title' => $booking->client->full_name,
            'message' => $booking->plot->block->name.' - '.$booking->plot->plot_number
                .' - '.$currencySymbol.' '.number_format($owed, 2)
                ." ({$daysOverdue} days overdue)",
            'url' => route('bookings.show', $booking),
            'sortDate' => $installment->due_date,
        ];
    }

    private function upcomingInstallmentAlert(Installment $installment): array
    {
        $booking = $installment->installmentPlan->booking;

        return [
            'type' => 'Upcoming Installment',
            'icon' => 'feather-clock',
            'color' => 'warning',
            'title' => $booking->client->full_name,
            'message' => $booking->plot->block->name.' - '.$booking->plot->plot_number
                .' - due '.$installment->due_date->format('d M Y'),
            'url' => route('bookings.show', $booking),
            'sortDate' => $installment->due_date,
        ];
    }
}
