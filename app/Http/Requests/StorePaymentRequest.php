<?php

namespace App\Http\Requests;

use App\Models\Booking;
use App\Models\Installment;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['Cash', 'Bank Transfer', 'Cheque', 'JazzCash', 'EasyPaisa'])],
            'payment_type' => ['required', Rule::in(['Booking Payment', 'Installment Payment', 'Advance', 'Partial'])],
            'application_mode' => ['required', Rule::in(['specific', 'auto'])],
            'installment_id' => ['required_if:application_mode,specific', 'nullable', 'exists:installments,id'],
            'late_fee' => ['nullable', 'numeric', 'min:0'],
            'reference_no' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Cross-field checks that need to look at the booking and installment
     * together, so they can't be plain per-field rules above.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $booking = Booking::find($this->input('booking_id'));

            if (! $booking) {
                return;
            }

            if ($this->input('application_mode') === 'specific') {
                $this->validateSpecificMode($validator, $booking);
            } else {
                $this->validateAutoMode($validator, $booking);
            }
        });
    }

    private function validateSpecificMode(Validator $validator, Booking $booking): void
    {
        $installment = Installment::find($this->input('installment_id'));

        if (! $installment) {
            return;
        }

        if (! $booking->installmentPlan || $installment->installment_plan_id !== $booking->installmentPlan->id) {
            $validator->errors()->add('installment_id', 'This installment does not belong to the selected booking.');

            return;
        }

        $extraLateFee = (float) $this->input('late_fee', 0);
        $totalOwed = $installment->amount + $installment->late_fee + $extraLateFee;
        $remainingOwed = round($totalOwed - $installment->paid_amount, 2);
        $amount = (float) $this->input('amount');

        if ($amount > $remainingOwed + 0.01) {
            $validator->errors()->add(
                'amount',
                'This payment (Rs. '.number_format($amount, 2).') is more than what is owed on this installment (Rs. '.number_format($remainingOwed, 2).').'
            );
        }
    }

    private function validateAutoMode(Validator $validator, Booking $booking): void
    {
        $hasUnpaidInstallments = $booking->installmentPlan
            && $booking->installmentPlan->installments()->where('status', '!=', 'Paid')->exists();

        if (! $hasUnpaidInstallments) {
            $validator->errors()->add('application_mode', 'This booking has no unpaid installments to apply this payment to.');
        }
    }
}
