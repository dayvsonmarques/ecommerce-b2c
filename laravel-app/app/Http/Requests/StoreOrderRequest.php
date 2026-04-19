<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Endereço de entrega (obrigatório ao finalizar compra)
            'shipping_address_id' => ['required', 'integer', 'exists:user_addresses,id'],

            // Método de pagamento
            'payment_method' => ['required', 'string', 'in:credit_card,pix,boleto'],

            // Dados específicos de cartão (quando payment_method = credit_card)
            'card_token'         => ['required_if:payment_method,credit_card', 'nullable', 'string'],
            'installments'       => ['nullable', 'integer', 'min:1', 'max:12'],

            // Observações opcionais
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'shipping_address_id.required' => 'O endereço de entrega é obrigatório.',
            'shipping_address_id.exists'   => 'Endereço de entrega não encontrado.',
            'payment_method.required'      => 'Informe o método de pagamento.',
            'payment_method.in'            => 'Método de pagamento inválido.',
            'card_token.required_if'       => 'O token do cartão é obrigatório para pagamento via cartão.',
        ];
    }
}
