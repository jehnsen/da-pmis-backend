<?php

namespace App\Http\Requests\CropProduction;

use Illuminate\Foundation\Http\FormRequest;

class StoreCropProductionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => ['nullable', 'exists:regions,id'],
            'crop_name' => ['required', 'string', 'max:255'],
            'production_volume' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'harvest_date' => ['required', 'date'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
