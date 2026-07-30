<?php

namespace App\Http\Requests;

use App\Models\Plot;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePlotRequest extends FormRequest
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
                Rule::unique('plots', 'plot_number')
                    ->where(fn ($query) => $query->where('block_id', $this->block_id))
                    ->ignore($this->route('plot')),
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
            // Only the manually-settable statuses are ever submitted from
            // the form. When a plot is currently locked (see Plot::MANUAL_STATUSES),
            // the form doesn't send a status field at all ('sometimes' means
            // it's simply skipped, and the plot keeps whatever status the
            // booking process or an import gave it).
            'status' => ['sometimes', 'required', Rule::in(Plot::MANUAL_STATUSES)],
        ];
    }
}
