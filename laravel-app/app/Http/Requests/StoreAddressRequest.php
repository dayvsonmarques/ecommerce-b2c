<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:billing,shipping',
            'street' => 'required|string|max:255',
            'number' => 'required|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|size:2',
            'postal_code' => 'required|string|max:10',
            'country' => 'nullable|string|size:2',
            'is_default' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'O tipo de endereço é obrigatório.',
            'street.required' => 'A rua é obrigatória.',
            'state.size' => 'O estado deve ter 2 caracteres.',
        ];
    }
}
