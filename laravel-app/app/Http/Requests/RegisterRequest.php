<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|unique:users',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Este email já está registrado.',
            'cpf.unique' => 'Este CPF já está registrado.',
            'password.confirmed' => 'As senhas não correspondem.',
        ];
    }
}
