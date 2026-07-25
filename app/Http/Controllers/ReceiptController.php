<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    /**
     * Display a listing of every receipt (every payment doubles as a
     * receipt -- see Payment::receipt_number). This is a separate page
     * from Payments so record-keeping ("find and reprint a receipt") is
     * kept apart from the day-to-day action of receiving a payment.
     */
    public function index(Request $request): View
    {
        $receipts = Payment::with(['booking.client', 'booking.plot.block'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($query) use ($search) {
                    $query->where('receipt_number', 'like', "%{$search}%")
                        ->orWhereHas('booking.client', function ($query) use ($search) {
                            $query->where('full_name', 'like', "%{$search}%")
                                ->orWhere('cnic', 'like', "%{$search}%");
                        });
                });
            })
            ->latest('payment_date')
            ->paginate(10)
            ->withQueryString();

        return view('receipts.index', compact('receipts'));
    }
}
