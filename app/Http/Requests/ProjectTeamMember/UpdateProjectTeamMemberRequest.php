<?php

namespace App\Http\Requests\ProjectTeamMember;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.required' => 'Role is required',
        ];
    }
}
