<?php

namespace App\Http\Requests\ContactInquiry;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactInquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,responded'],
        ];
    }
}
