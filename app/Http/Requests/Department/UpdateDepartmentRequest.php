<?php

namespace App\Http\Requests\Department;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDepartmentRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('department');
        return [
            'name'        => ['sometimes','string','max:255', Rule::unique('departments','name')->ignore($id)],
            'description' => ['sometimes','nullable','string','max:255'],
        ];
    }
}
