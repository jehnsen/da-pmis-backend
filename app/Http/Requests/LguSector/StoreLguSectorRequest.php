<?php

namespace App\Http\Requests\LguSector;

use Illuminate\Foundation\Http\FormRequest;

class StoreLguSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic later if needed
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => ['required', 'string', 'max:10', 'unique:lgu_sectors,code'],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['required', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
