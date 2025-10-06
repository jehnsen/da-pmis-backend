<?php

namespace App\Http\Requests\ProgressReport;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProgressReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'department_id' => ['sometimes', 'exists:departments,id'],
            'report_period' => ['sometimes', 'in:monthly,quarterly,annual'],
            'report_date' => ['sometimes', 'date'],
            'summary' => ['sometimes', 'string'],
        ];
    }
}
