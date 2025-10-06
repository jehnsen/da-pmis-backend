<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'string'],
            'department_id' => ['sometimes', 'exists:departments,id'],
            'project_type_id' => ['sometimes', 'exists:project_types,id'],
            'project_status_id' => ['sometimes', 'exists:project_statuses,id'],
            'budget' => ['sometimes', 'numeric', 'min:0'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after:start_date'],
            'location_lat' => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng' => ['nullable', 'numeric', 'between:-180,180'],
            'is_public' => ['sometimes', 'boolean'],
        ];
    }
}
