<?php

namespace App\Http\Requests\ProgressReport;

use Illuminate\Foundation\Http\FormRequest;

class StoreProgressReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'report_period' => ['required', 'in:monthly,quarterly,annual'],
            'report_date' => ['required', 'date'],
            'summary' => ['required', 'string'],
            'metrics' => ['required', 'array'],
            'metrics.*.metric_name' => ['required', 'string', 'max:255'],
            'metrics.*.metric_value' => ['required', 'numeric'],
            'metrics.*.previous_value' => ['nullable', 'numeric'],
        ];
    }
}
