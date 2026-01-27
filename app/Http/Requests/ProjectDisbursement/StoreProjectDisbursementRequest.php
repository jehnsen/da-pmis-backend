<?php

namespace App\Http\Requests\ProjectDisbursement;

use App\Models\ProjectDisbursement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'category' => ['required', 'string', Rule::in(array_keys(ProjectDisbursement::CATEGORIES))],
            'description' => ['required', 'string', 'max:1000'],
            'disbursement_date' => ['required', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The disbursement amount is required.',
            'amount.min' => 'The disbursement amount must be at least 0.01.',
            'category.required' => 'The disbursement category is required.',
            'category.in' => 'The selected category is invalid. Valid options are: '.implode(', ', array_keys(ProjectDisbursement::CATEGORIES)),
            'description.required' => 'A description of the disbursement is required.',
            'disbursement_date.required' => 'The disbursement date is required.',
            'disbursement_date.before_or_equal' => 'The disbursement date cannot be in the future.',
        ];
    }
}
