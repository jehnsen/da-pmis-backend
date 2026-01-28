<?php

namespace App\Http\Requests\ProjectTeamMember;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'User is required',
            'user_id.exists' => 'Selected user does not exist',
        ];
    }
}
