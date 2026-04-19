<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id'     => ['required', 'integer', 'exists:products,id'],
            'product_sku_id' => ['nullable', 'integer', 'exists:product_skus,id'],
            'quantity'       => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'O produto é obrigatório.',
            'product_id.exists'   => 'Produto não encontrado.',
            'quantity.min'        => 'A quantidade mínima é 1.',
            'quantity.max'        => 'A quantidade máxima por item é 999.',
        ];
    }
}
