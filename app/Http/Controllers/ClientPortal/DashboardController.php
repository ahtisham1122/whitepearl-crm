<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * The client's whole portal, on one page: profile, plot(s), payment
     * summary, installment schedule, and payment history.
     *
     * Security note: $client comes ONLY from the 'client' guard's own
     * session (Auth::guard('client')->user()) -- never from a route
     * parameter, query string, or form field. Every query below is chained
     * off $client's own relationships (bookings(), payments()), so there is
     * no id anywhere in this method that a client could tamper with to see
     * someone else's data.
     */
    public function index(): View
    {
        /** @var Client $client */
        $client = Auth::guard('client')->user();

        $client->load([
            'bookings' => fn ($query) => $query->where('status', 'Active')
                ->with(['plot.block', 'installmentPlan.installments'])
                ->latest('booking_date'),
        ]);

        $bookings = $client->bookings->map(fn ($booking) => [
            'booking' => $booking,
            'total_paid' => (float) $booking->booking_amount + (float) $booking->payments()->sum('amount'),
            'outstanding' => $this->paymentService->remainingBalanceForBooking($booking),
        ]);

        $payments = $client->payments()
            ->with(['booking.plot.block'])
            ->latest('payment_date')
            ->get();

        $outstandingBalance = $this->paymentService->outstandingBalanceForClient($client);

        return view('client-portal.dashboard', compact('client', 'bookings', 'payments', 'outstandingBalance'));
    }
}
