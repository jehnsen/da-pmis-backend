<?php

namespace App\Http\Requests\LivestockStatistic;

use Illuminate\Foundation\Http\FormRequest;

class StoreLivestockStatisticRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'region_id' => ['nullable', 'exists:regions,id'],
            'livestock_type' => ['required', 'string', 'max:255'],
            'population' => ['required', 'integer', 'min:0'],
            'unit' => ['required', 'string', 'max:50'],
            'recorded_date' => ['required', 'date'],
            'fiscal_year' => ['required', 'integer', 'min:2000', 'max:2100'],
        ];
    }
}
