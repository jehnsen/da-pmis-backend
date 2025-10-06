<?php

namespace App\Http\Requests\LivestockStatistic;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLivestockStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => ['sometimes', 'nullable', 'exists:regions,id'],
            'livestock_type' => ['sometimes', 'string', 'max:255'],
            'population' => ['sometimes', 'integer', 'min:0'],
            'unit' => ['sometimes', 'string', 'max:50'],
            'recorded_date' => ['sometimes', 'date'],
            'fiscal_year' => ['sometimes', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
