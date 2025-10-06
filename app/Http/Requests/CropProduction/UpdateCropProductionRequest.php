<?php

namespace App\Http\Requests\CropProduction;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCropProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => ['sometimes', 'nullable', 'exists:regions,id'],
            'crop_name' => ['sometimes', 'string', 'max:255'],
            'production_volume' => ['sometimes', 'numeric', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'harvest_date' => ['sometimes', 'date'],
            'fiscal_year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
