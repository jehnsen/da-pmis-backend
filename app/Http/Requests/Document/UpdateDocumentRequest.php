<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file_path' => ['sometimes', 'string', 'max:500'],
            'document_type' => ['sometimes', 'in:report,whitepaper,policy,other'],
            'published_date' => ['nullable', 'date'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:document_categories,id'],
        ];
    }
}
