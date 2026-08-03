<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Plot;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
     *
     * lockForUpdate() "reserves" the plot row the moment we read it, inside
     * this transaction -- any other request trying to read that same row
     * with lockForUpdate() has to wait until this transaction finishes. That
     * closes the gap between "check the plot is Available" and "save the
     * booking": two people can no longer both pass the check at the same
     * instant and both book the same plot.
     */
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $plot = Plot::lockForUpdate()->findOrFail($data['plot_id']);

            if ($plot->status !== 'Available') {
                throw ValidationException::withMessages([
                    'plot_id' => 'This plot was just booked by someone else. Please choose another plot.',
                ]);
            }

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
     * "Delete" a booking (e.g. it was entered by mistake). Booking now uses
     * soft deletes, so this only hides the record -- it stays recoverable.
     * If it was still Active, its plot is freed back to Available too.
     *
     * A booking with any payment or installment plan is refused: hiding
     * the booking would leave those rows pointing at a booking every
     * receipt/report/history page would suddenly find missing.
     *
     * @throws \DomainException
     */
    public function deleteBooking(Booking $booking): void
    {
        if ($booking->payments()->exists() || $booking->installmentPlan()->exists()) {
            throw new \DomainException('This booking has payments or an installment plan and cannot be deleted. Remove those first.');
        }

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
