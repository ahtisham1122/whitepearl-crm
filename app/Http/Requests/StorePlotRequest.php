<?php

namespace App\Http\Requests;

use App\Models\Plot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePlotRequest extends FormRequest
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
            'block_id' => ['required', 'exists:blocks,id'],
            'plot_number' => [
                'required', 'string', 'max:255',
                Rule::unique('plots', 'plot_number')->where(fn ($query) => $query->where('block_id', $this->block_id)),
            ],
            'street' => ['nullable', 'string', 'max:255'],
            'plot_size' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['Residential', 'Commercial'])],
            'corner_plot' => ['sometimes', 'boolean'],
            'park_facing' => ['sometimes', 'boolean'],
            'main_boulevard' => ['sometimes', 'boolean'],
            'plot_price' => ['required', 'numeric', 'min:0'],
            'development_charges' => ['nullable', 'numeric', 'min:0'],
            'extra_charges' => ['nullable', 'numeric', 'min:0'],
            // A brand new plot has no booking (or import) behind it yet, so
            // only the manually-settable statuses make sense here.
            'status' => ['required', Rule::in(Plot::MANUAL_STATUSES)],
        ];
    }
}
