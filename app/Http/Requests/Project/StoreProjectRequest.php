<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'department_id' => ['required', 'exists:departments,id'],
            'sector_id' => ['nullable', 'exists:lgu_sectors,id'], // RA 7160 sector
            'municipality_id' => ['required', 'exists:municipalities,id'], // RA 7160 territorial jurisdiction
            'province_id' => ['nullable', 'exists:provinces,id'],
            'barangay' => ['nullable', 'string', 'max:100'],
            'project_type_id' => ['required', 'exists:project_types,id'],
            'project_status_id' => ['required', 'exists:project_statuses,id'],
            'budget' => ['required', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_public' => ['required', 'boolean'],
        ];
    }
}
