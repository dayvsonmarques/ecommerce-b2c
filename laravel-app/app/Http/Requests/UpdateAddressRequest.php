<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|in:billing,shipping',
            'street' => 'sometimes|string|max:255',
            'number' => 'sometimes|string|max:20',
            'complement' => 'nullable|string|max:255',
            'neighborhood' => 'sometimes|string|max:255',
            'city' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|size:2',
            'postal_code' => 'sometimes|string|max:10',
            'country' => 'nullable|string|size:2',
            'is_default' => 'nullable|boolean',
        ];
    }
}
