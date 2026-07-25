<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Client;
use App\Models\Plot;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private readonly BookingService $bookingService,
        private readonly PaymentService $paymentService,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $bookings = Booking::with(['client', 'plot.block'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('booking_number', 'like', "%{$search}%")
                        ->orWhereHas('client', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                        ->orWhereHas('plot', fn ($q) => $q->where('plot_number', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $clients = Client::orderBy('full_name')->get();
        $plots = Plot::with('block')->where('status', 'Available')->orderBy('plot_number')->get();

        return view('bookings.create', compact('clients', 'plots'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookingRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->createBooking($request->validated());

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Booking $booking): View
    {
        $booking->load(['client', 'plot.block', 'installmentPlan.installments', 'payments' => function ($query) {
            $query->latest('payment_date');
        }]);

        $remainingForBooking = $this->paymentService->remainingBalanceForBooking($booking);

        return view('bookings.show', compact('booking', 'remainingForBooking'));
    }

    /**
     * Cancel a booking and free its plot back to Available.
     */
    public function cancel(Booking $booking): RedirectResponse
    {
        $this->bookingService->cancelBooking($booking);

        return redirect()
            ->route('bookings.show', $booking)
            ->with('success', 'Booking cancelled. The plot is Available again.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->bookingService->deleteBooking($booking);

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }
}
