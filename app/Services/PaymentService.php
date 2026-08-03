<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Client;
use App\Models\Installment;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * How many seconds count as "just submitted" for the duplicate-payment
     * check below. Long enough to catch a double-click or a page refresh,
     * short enough that a genuine second payment made a bit later for the
     * same amount is never blocked.
     */
    private const DUPLICATE_WINDOW_SECONDS = 10;

    /**
     * Build the next receipt code, e.g. RCP-0001, RCP-0002.
     */
    public function generateReceiptNumber(): string
    {
        $lastPayment = Payment::orderByDesc('id')->first();

        $nextNumber = $lastPayment
            ? ((int) substr($lastPayment->receipt_number, 4)) + 1
            : 1;

        return 'RCP-'.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Record a payment and apply it to the installment schedule.
     * Everything below happens in one transaction: the payment row, the
     * installment update(s), and the plan status check either all succeed
     * together or all get rolled back together. With real money involved,
     * we never want a payment saved without its installment update (or the
     * other way around) -- that would silently corrupt the client's balance.
     */
    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $booking = Booking::findOrFail($data['booking_id']);
            $lateFee = (float) ($data['late_fee'] ?? 0);

            $this->guardAgainstDuplicate($booking->id, (float) $data['amount'], $data['payment_method']);

            $payment = Payment::create([
                'receipt_number' => $this->generateReceiptNumber(),
                'booking_id' => $booking->id,
                'installment_id' => $data['application_mode'] === 'specific' ? $data['installment_id'] : null,
                'payment_date' => $data['payment_date'],
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'payment_type' => $data['payment_type'],
                'late_fee' => $lateFee,
                'reference_no' => $data['reference_no'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            if ($data['application_mode'] === 'specific') {
                $installment = Installment::findOrFail($data['installment_id']);
                $this->addLateFee($installment, $lateFee);
                $this->applyAmountToInstallment($installment, (float) $data['amount']);
            } else {
                $this->autoApply($booking, (float) $data['amount']);
            }

            $this->completePlanIfFullyPaid($booking);

            return $payment;
        });
    }

    /**
     * Stop the exact same payment from being saved twice in a row -- e.g. a
     * double-click on "Record Payment," or a browser retry after a slow
     * response. If a payment for this booking, with this same amount and
     * payment method, was created moments ago, we treat the new submission
     * as a duplicate and refuse it instead of recording the money twice.
     */
    private function guardAgainstDuplicate(int $bookingId, float $amount, string $paymentMethod): void
    {
        $isDuplicate = Payment::where('booking_id', $bookingId)
            ->where('amount', $amount)
            ->where('payment_method', $paymentMethod)
            ->where('created_at', '>=', now()->subSeconds(self::DUPLICATE_WINDOW_SECONDS))
            ->exists();

        if ($isDuplicate) {
            throw ValidationException::withMessages([
                'amount' => 'This looks like a duplicate: an identical payment for this booking was just recorded a few seconds ago. Please check the payment list before submitting again.',
            ]);
        }
    }

    /**
     * A late fee is always tied to one specific installment (the overdue
     * one), so it's only ever entered together with Mode A. It adds to
     * whatever late fee that installment already carries, since a second
     * late payment could add a second fee on top of the first.
     */
    private function addLateFee(Installment $installment, float $lateFee): void
    {
        if ($lateFee > 0) {
            $installment->increment('late_fee', $lateFee);
        }
    }

    /**
     * Apply as much of $amount as is owed on this one installment, mark it
     * Paid or Partially Paid accordingly, and return whatever is left over
     * (0 if the full amount was absorbed here).
     */
    private function applyAmountToInstallment(Installment $installment, float $amount): float
    {
        $totalOwed = $installment->amount + $installment->late_fee;
        $remainingOwed = max(round($totalOwed - $installment->paid_amount, 2), 0);
        $appliedHere = min($amount, $remainingOwed);

        $installment->paid_amount = round($installment->paid_amount + $appliedHere, 2);

        if ($installment->paid_amount >= $totalOwed) {
            $installment->status = 'Paid';
            $installment->paid_date = now();
        } elseif ($installment->paid_amount > 0) {
            $installment->status = 'Partially Paid';
        }

        $installment->save();

        return round($amount - $appliedHere, 2);
    }

    /**
     * Mode B: walk the booking's unpaid installments oldest-due-date-first,
     * pouring the payment into each one until either the money runs out or
     * every installment is covered.
     */
    private function autoApply(Booking $booking, float $amount): void
    {
        $installments = $booking->installmentPlan
            ->installments()
            ->where('status', '!=', 'Paid')
            ->orderBy('due_date')
            ->orderBy('installment_number')
            ->get();

        $remaining = $amount;

        foreach ($installments as $installment) {
            if ($remaining <= 0) {
                break;
            }

            $remaining = $this->applyAmountToInstallment($installment, $remaining);
        }
    }

    /**
     * Once every installment in a plan is Paid, the plan itself is done.
     */
    private function completePlanIfFullyPaid(Booking $booking): void
    {
        $plan = $booking->installmentPlan;

        if (! $plan) {
            return;
        }

        $stillHasUnpaid = $plan->installments()->where('status', '!=', 'Paid')->exists();

        if (! $stillHasUnpaid) {
            $plan->update(['status' => 'Completed']);
        }
    }

    /**
     * Apply an admin-confirmed lump-sum fine to one booking's account for
     * being behind schedule. Unlike addLateFee() above (entered alongside an
     * actual payment), this charges a fee with no money changing hands --
     * it lands on whichever unpaid installment has been overdue the
     * longest, the same installment ReportService used to work out how many
     * months overdue this booking is. Never called automatically; only from
     * an admin clicking "Apply" on one specific booking's suggested fine on
     * the Late Fee Report.
     */
    public function applyFine(Booking $booking, float $amount): Installment
    {
        return DB::transaction(function () use ($booking, $amount) {
            if ($amount <= 0) {
                throw new \DomainException('There is no overdue amount to apply a fine for.');
            }

            $installment = $booking->installmentPlan
                ?->installments()
                ->where('status', '!=', 'Paid')
                ->where('due_date', '<', today())
                ->orderBy('due_date')
                ->lockForUpdate()
                ->first();

            if (! $installment) {
                throw new \DomainException('This booking has no overdue installment to apply a fine to.');
            }

            $installment->increment('late_fee', round($amount, 2));

            activity('installment')
                ->performedOn($installment)
                ->causedBy(auth()->user())
                ->withProperties(['amount' => round($amount, 2), 'booking_id' => $booking->id])
                ->log('Late fee fine applied');

            return $installment->fresh();
        });
    }

    /**
     * A client's outstanding balance is not stored anywhere -- it's always
     * calculated fresh as the sum of what's still owed (amount + late fee
     * - paid so far) across every installment on their active bookings.
     */
    public function outstandingBalanceForClient(Client $client): float
    {
        return (float) $client->bookings()
            ->where('status', 'Active')
            ->with('installmentPlan.installments')
            ->get()
            ->flatMap(fn (Booking $booking) => $booking->installmentPlan?->installments ?? collect())
            ->sum(fn (Installment $installment) => max($installment->amount + $installment->late_fee - $installment->paid_amount, 0));
    }

    /**
     * How much is still owed on one specific booking's installment plan.
     * This is the single place that math lives -- the booking show page,
     * the "receive payment" form, and receipts all call this instead of
     * each repeating the same sum themselves.
     */
    public function remainingBalanceForBooking(Booking $booking): float
    {
        return (float) ($booking->installmentPlan?->installments
            ->sum(fn (Installment $installment) => max($installment->amount + $installment->late_fee - $installment->paid_amount, 0)) ?? 0);
    }
}
