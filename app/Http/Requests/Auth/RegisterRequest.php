<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseApiRequest;

class RegisterRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['nullable', 'string', 'in:customer,admin'],
        ];
    }
}
