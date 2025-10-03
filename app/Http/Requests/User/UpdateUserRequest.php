<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('user');
        return [
            'email'       => ['sometimes','email',"unique:users,email,{$id}"],
            'full_name'   => ['sometimes','string','max:255'],
            'username'    => ['sometimes','string','max:50',"unique:users,username,{$id}"],
            'password'    => ['sometimes','string','min:8','nullable'],
            'school_id'   => ['sometimes','exists:schools,id','nullable'],
            'role_id'     => ['sometimes','exists:roles,id','nullable'],
            'department_id' => ['sometimes','exists:departments,id','nullable'],
        ];
    }
}
