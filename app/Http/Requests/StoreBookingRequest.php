<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
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
            'client_id' => ['required', 'exists:clients,id'],
            // The plot must exist AND still be Available -- this is a second
            // safety net behind the dropdown only listing available plots,
            // in case two people try to book the same plot at nearly the
            // same time.
            'plot_id' => ['required', Rule::exists('plots', 'id')->where('status', 'Available')],
            'booking_date' => ['required', 'date'],
            'booking_type' => ['required', Rule::in(['Reservation', 'Sale'])],
            'total_price' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'booking_amount' => ['nullable', 'numeric', 'min:0'],
            'sales_person' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'plot_id.exists' => 'This plot is no longer available. Please choose another plot.',
        ];
    }
}
