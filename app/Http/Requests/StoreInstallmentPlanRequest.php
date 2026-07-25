<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInstallmentPlanRequest extends FormRequest
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
            'plan_type' => ['required', Rule::in(['Monthly', 'Quarterly', 'Custom'])],
            'start_date' => ['required', 'date'],
            'number_of_installments' => ['required_unless:plan_type,Custom', 'nullable', 'integer', 'min:1', 'max:120'],
            'installment_due_date' => ['required_if:plan_type,Custom', 'array', 'min:1'],
            'installment_due_date.*' => ['required', 'date'],
            'installment_amount' => ['required_if:plan_type,Custom', 'array', 'min:1'],
            'installment_amount.*' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    /**
     * Extra checks that need to look at more than one field at once --
     * these can't be expressed as simple per-field rules above, so Laravel
     * lets a Form Request add them here after the normal rules pass.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $booking = Booking::find($this->input('booking_id'));

            if (! $booking) {
                return;
            }

            if ($booking->installmentPlan()->exists()) {
                $validator->errors()->add('booking_id', 'This booking already has an installment plan.');
            }

            if ($this->input('plan_type') === 'Custom') {
                $remaining = round($booking->final_price - $booking->booking_amount, 2);
                $sum = round(collect($this->input('installment_amount', []))->sum(fn ($amount) => (float) $amount), 2);

                if (abs($remaining - $sum) > 0.01) {
                    $validator->errors()->add(
                        'installment_amount',
                        'Installment amounts must add up to the remaining balance of Rs. '.number_format($remaining, 2)
                            .'. They currently add up to Rs. '.number_format($sum, 2).'.'
                    );
                }
            }
        });
    }
}
