<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $paymentService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $payments = Payment::with(['booking.client', 'booking.plot.block'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('booking', fn ($q) => $q->where('booking_number', 'like', "%{$search}%"))
                        ->orWhereHas('booking.client', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('booking.plot', fn ($q) => $q->where('plot_number', 'like', "%{$search}%"));
                });
            })
            ->latest('payment_date')
            ->paginate(10)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource. If a booking has already
     * been chosen (via ?booking_id=), also load its installment schedule
     * so the admin can see what's owed before recording the payment.
     */
    public function create(Request $request): View
    {
        $bookings = Booking::where('status', 'Active')
            ->with(['client', 'plot.block'])
            ->orderByDesc('booking_date')
            ->get();

        $selectedBooking = null;

        if ($request->filled('booking_id')) {
            $selectedBooking = Booking::with(['client', 'plot.block', 'installmentPlan.installments', 'payments'])
                ->find($request->integer('booking_id'));
        }

        return view('payments.create', [
            'bookings' => $bookings,
            'selectedBooking' => $selectedBooking,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $payment = $this->paymentService->recordPayment($request->validated());

        return redirect()
            ->route('payments.receipt', $payment)
            ->with('success', 'Payment recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment): View
    {
        $payment->load(['booking.client', 'booking.plot.block', 'installment']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the printable receipt for a payment.
     */
    public function receipt(Payment $payment): View
    {
        $payment->load(['booking.client', 'booking.plot.block', 'installment']);

        $outstandingBalance = $this->paymentService->outstandingBalanceForClient($payment->booking->client);

        return view('payments.receipt', compact('payment', 'outstandingBalance'));
    }
}
