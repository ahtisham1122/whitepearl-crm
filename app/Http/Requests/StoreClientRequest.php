<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClientRequest extends FormRequest
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
            // Client fields
            'full_name' => ['required', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'cnic' => ['required', 'string', 'max:255', 'unique:clients,cnic'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['required', 'string', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],

            // Nominee fields (all optional -- a client may add the nominee later)
            'nominee_name' => ['nullable', 'string', 'max:255'],
            'nominee_relationship' => ['nullable', 'string', 'max:255'],
            'nominee_cnic' => ['nullable', 'string', 'max:255'],
            'nominee_phone' => ['nullable', 'string', 'max:255'],
            'nominee_email' => ['nullable', 'email', 'max:255'],
            'nominee_address' => ['nullable', 'string'],

            // Document upload rows (parallel arrays: document_type[0] goes with document_file[0], etc.)
            'document_type' => ['nullable', 'array'],
            'document_type.*' => ['nullable', Rule::in(['CNIC Front', 'CNIC Back', 'Passport', 'Photograph', 'Application Form', 'Other'])],
            'document_file' => ['nullable', 'array'],
            'document_file.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }
}
