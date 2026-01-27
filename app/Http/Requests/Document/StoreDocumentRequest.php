<?php

namespace App\Http\Requests\Document;

use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:10240'], // 10MB max
            'file_path' => ['nullable', 'string', 'max:500'], // For backward compatibility
            'document_type' => [
                'required',
                'string',
                'in:annual_report,quarterly_report,technical_report,policy_document,white_paper,strategic_plan,report,whitepaper,policy,other',
            ],
            'category_id' => ['nullable', 'exists:document_categories,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'fiscal_year' => ['nullable', 'integer', 'min:2020', 'max:2035'],
            'is_featured' => ['nullable', 'boolean'],
            'published_date' => ['nullable', 'date'],
            'categories' => ['nullable', 'array'],
            'categories.*' => ['exists:document_categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A document file is required.',
            'file.file' => 'The uploaded file must be a valid file.',
            'file.mimes' => 'The document must be a PDF, Word document, Excel spreadsheet, or PowerPoint presentation.',
            'file.max' => 'The document file must not exceed 10MB.',
            'document_type.in' => 'Invalid document type. Must be one of: annual_report, quarterly_report, technical_report, policy_document, white_paper, strategic_plan.',
            'category_id.exists' => 'The selected category does not exist.',
            'department_id.exists' => 'The selected department does not exist.',
            'fiscal_year.min' => 'Fiscal year must be 2020 or later.',
            'fiscal_year.max' => 'Fiscal year must be 2035 or earlier.',
        ];
    }
}
