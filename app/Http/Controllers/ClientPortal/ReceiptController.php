<?php

namespace App\Http\Controllers\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\PaymentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class ReceiptController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * Download one of the logged-in client's own receipts as a PDF.
     *
     * Security note (the most important line in this whole controller):
     * $payment is fetched through $client->payments() -- a
     * hasManyThrough(Payment::class, Booking::class) relationship that
     * only ever reaches payments belonging to this client's own bookings.
     * Laravel adds that constraint to the SQL query itself, so passing in
     * another client's payment id doesn't return "forbidden" -- it returns
     * "not found," because as far as this query is concerned it simply
     * isn't there. There is no separate "does this belong to me?" check to
     * forget, because the query can't produce someone else's row at all.
     */
    public function pdf(int $payment): Response
    {
        /** @var Client $client */
        $client = Auth::guard('client')->user();

        $payment = $client->payments()
            ->with(['booking.client', 'booking.plot.block', 'installment'])
            ->findOrFail($payment);

        $remainingBalance = $this->paymentService->remainingBalanceForBooking($payment->booking);

        $pdf = Pdf::loadView('payments.receipt-pdf', [
            'payment' => $payment,
            'remainingBalance' => $remainingBalance,
        ]);

        return $pdf->download($payment->receipt_number.'.pdf');
    }
}
