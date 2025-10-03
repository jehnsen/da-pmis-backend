<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'email'       => ['required','email','unique:users,email'],
            'full_name'   => ['required','string','max:255'],
            'username'    => ['required','string','max:50','unique:users,username'],
            'password'    => ['required','string','min:8'],
            'school_id'   => ['nullable','exists:schools,id'],
            'role_id'     => ['nullable','exists:roles,id'],
            'department_id' => ['nullable','exists:departments,id'],
        ];
    }
}
