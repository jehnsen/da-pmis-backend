<?php

namespace App\Http\Requests\ProjectDisbursement;

use App\Models\ProjectDisbursement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectDisbursementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['sometimes', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'category' => ['sometimes', 'string', Rule::in(array_keys(ProjectDisbursement::CATEGORIES))],
            'description' => ['sometimes', 'string', 'max:1000'],
            'disbursement_date' => ['sometimes', 'date', 'before_or_equal:today'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'vendor_name' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'status' => ['sometimes', 'string', Rule::in(['pending', 'completed', 'cancelled'])],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'The disbursement amount must be at least 0.01.',
            'category.in' => 'The selected category is invalid. Valid options are: '.implode(', ', array_keys(ProjectDisbursement::CATEGORIES)),
            'disbursement_date.before_or_equal' => 'The disbursement date cannot be in the future.',
            'status.in' => 'The status must be one of: pending, completed, cancelled.',
        ];
    }
}
