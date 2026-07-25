<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInstallmentPlanRequest;
use App\Models\Booking;
use App\Models\InstallmentPlan;
use App\Services\InstallmentPlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallmentPlanController extends Controller
{
    public function __construct(private readonly InstallmentPlanService $installmentPlanService)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $plans = InstallmentPlan::with(['booking.client', 'booking.plot.block'])
            ->latest()
            ->paginate(10);

        return view('installment-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $bookings = Booking::where('status', 'Active')
            ->whereDoesntHave('installmentPlan')
            ->with(['client', 'plot.block'])
            ->orderBy('booking_date')
            ->get();

        $selectedBookingId = $request->integer('booking');

        return view('installment-plans.create', compact('bookings', 'selectedBookingId'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreInstallmentPlanRequest $request): RedirectResponse
    {
        $plan = $this->installmentPlanService->generatePlan($request->validated());

        return redirect()
            ->route('installment-plans.show', $plan)
            ->with('success', 'Installment plan created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(InstallmentPlan $installmentPlan): View
    {
        $installmentPlan->load(['booking.client', 'booking.plot.block', 'installments']);

        return view('installment-plans.show', ['plan' => $installmentPlan]);
    }
}
