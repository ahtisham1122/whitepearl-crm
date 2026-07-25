<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Build the next booking code, e.g. BK-0001, BK-0002.
     */
    public function generateBookingNumber(): string
    {
        $lastBooking = Booking::orderByDesc('id')->first();

        $nextNumber = $lastBooking
            ? ((int) substr($lastBooking->booking_number, 3)) + 1
            : 1;

        return 'BK-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create a booking and move the plot out of the Available pool.
     * Both changes happen in one transaction: if either fails, neither is saved.
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $plot = Plot::findOrFail($data['plot_id']);

            $totalPrice = $data['total_price'];
            $discount = $data['discount'] ?? 0;

            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'client_id' => $data['client_id'],
                'plot_id' => $plot->id,
                'booking_date' => $data['booking_date'],
                'booking_type' => $data['booking_type'],
                'total_price' => $totalPrice,
                'discount' => $discount,
                'final_price' => $totalPrice - $discount,
                'booking_amount' => $data['booking_amount'] ?? 0,
                'sales_person' => $data['sales_person'] ?? null,
                'remarks' => $data['remarks'] ?? null,
                'status' => 'Active',
            ]);

            $plot->update([
                'status' => $data['booking_type'] === 'Sale' ? 'Sold' : 'Reserved',
            ]);

            return $booking;
        });
    }

    /**
     * Cancel a booking: mark it Cancelled (the row stays, for history) and
     * free its plot back to Available.
     */
    public function cancelBooking(Booking $booking): void
    {
        if ($booking->status === 'Cancelled') {
            return;
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'Cancelled']);
            $booking->plot->update(['status' => 'Available']);
        });
    }

    /**
     * Permanently delete a booking (e.g. it was entered by mistake).
     * If it was still Active, its plot is freed back to Available too.
     */
    public function deleteBooking(Booking $booking): void
    {
        DB::transaction(function () use ($booking) {
            $wasActive = $booking->status === 'Active';
            $plot = $booking->plot;

            $booking->delete();

            if ($wasActive) {
                $plot->update(['status' => 'Available']);
            }
        });
    }
}
