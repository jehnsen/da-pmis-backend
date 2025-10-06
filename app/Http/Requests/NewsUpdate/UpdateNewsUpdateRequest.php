<?php

namespace App\Http\Requests\NewsUpdate;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
