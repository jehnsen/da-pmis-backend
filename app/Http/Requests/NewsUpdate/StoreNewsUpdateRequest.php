<?php

namespace App\Http\Requests\NewsUpdate;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'image_url' => ['nullable', 'url', 'max:500'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['required', 'boolean'],
        ];
    }
}
