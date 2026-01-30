<?php

namespace App\Http\Requests\LguSector;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLguSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Add authorization logic later if needed
    }

    public function rules(): array
    {
        $sectorId = $this->route('sector');

        return [
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'code' => ['sometimes', 'required', 'string', 'max:10', Rule::unique('lgu_sectors')->ignore($sectorId)],
            'description' => ['nullable', 'string'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color_code' => ['nullable', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['sometimes', 'required', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
