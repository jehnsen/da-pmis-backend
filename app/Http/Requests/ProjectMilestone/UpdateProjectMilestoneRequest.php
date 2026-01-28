<?php

namespace App\Http\Requests\ProjectMilestone;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectMilestoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'completion_date' => ['nullable', 'date'],
            'status' => ['nullable', 'string', 'in:pending,in_progress,completed,cancelled'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Milestone title is required',
            'status.in' => 'Invalid status. Must be: pending, in_progress, completed, or cancelled',
        ];
    }
}
