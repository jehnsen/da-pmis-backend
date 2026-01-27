<?php

namespace App\Http\Requests\ProgressReport;

use App\Models\ProgressReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProgressReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Either project_id or department_id must be provided
            'project_id' => ['nullable', 'required_without:department_id', 'exists:projects,id'],
            'department_id' => ['nullable', 'required_without:project_id', 'exists:departments,id'],

            'report_period' => ['required', 'in:monthly,quarterly,annual'],
            'reporting_date' => ['required', 'date', 'before_or_equal:today'],

            // Progress percentages
            'physical_progress' => ['required', 'integer', 'min:0', 'max:100'],
            'financial_progress' => ['required', 'integer', 'min:0', 'max:100'],

            // Narrative fields
            'summary' => ['nullable', 'string', 'max:5000'],
            'accomplishments' => ['nullable', 'string', 'max:5000'],
            'issues' => ['nullable', 'string', 'max:5000'],
            'risks' => ['nullable', 'string', 'max:5000'],
            'next_steps' => ['nullable', 'string', 'max:5000'],

            // Milestones completed (array of milestone IDs)
            'milestones_completed' => ['nullable', 'array'],
            'milestones_completed.*' => ['integer', 'exists:project_milestones,id'],

            // Progress status
            'progress_status' => ['nullable', Rule::in(array_keys(ProgressReport::PROGRESS_STATUSES))],

            // Metrics (for compatibility with existing system)
            'metrics' => ['nullable', 'array'],
            'metrics.*.metric_name' => ['required_with:metrics', 'string', 'max:255'],
            'metrics.*.metric_value' => ['required_with:metrics', 'numeric'],
            'metrics.*.previous_value' => ['nullable', 'numeric'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required_without' => 'Either a project or department must be specified.',
            'department_id.required_without' => 'Either a project or department must be specified.',
            'physical_progress.required' => 'Physical progress percentage is required.',
            'physical_progress.min' => 'Physical progress must be between 0 and 100.',
            'physical_progress.max' => 'Physical progress must be between 0 and 100.',
            'financial_progress.required' => 'Financial progress percentage is required.',
            'financial_progress.min' => 'Financial progress must be between 0 and 100.',
            'financial_progress.max' => 'Financial progress must be between 0 and 100.',
            'reporting_date.before_or_equal' => 'Reporting date cannot be in the future.',
            'milestones_completed.*.exists' => 'One or more milestone IDs are invalid.',
        ];
    }
}
