<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'category_id'                 => ['required', 'integer', 'exists:categories,id'],
            'name'                        => ['required', 'string', 'max:255'],
            'slug'                        => ['nullable', 'string', 'max:255', "unique:products,slug,{$productId}"],
            'description'                 => ['required', 'string'],
            'short_description'           => ['nullable', 'string', 'max:500'],
            'price'                       => ['required', 'numeric', 'min:0'],
            'cost_price'                  => ['nullable', 'numeric', 'min:0'],
            'sku'                         => ['required', 'string', 'max:100', "unique:products,sku,{$productId}"],
            'quantity'                    => ['nullable', 'integer', 'min:0'],
            'min_quantity_alert'          => ['nullable', 'integer', 'min:0'],
            'is_active'                   => ['nullable', 'boolean'],

            'variations'                  => ['nullable', 'array'],
            'variations.*.name'           => ['required_with:variations', 'string', 'max:100'],
            'variations.*.values'         => ['required_with:variations', 'array', 'min:1'],
            'variations.*.values.*.value' => ['required', 'string', 'max:100'],
            'variations.*.values.*.label' => ['nullable', 'string', 'max:100'],

            'skus'                        => ['nullable', 'array'],
            'skus.*.sku'                  => ['required_with:skus', 'string', 'max:100'],
            'skus.*.price'                => ['nullable', 'numeric', 'min:0'],
            'skus.*.quantity'             => ['required_with:skus', 'integer', 'min:0'],
            'skus.*.variation_values'     => ['required_with:skus', 'array'],
            'skus.*.is_active'            => ['nullable', 'boolean'],
        ];
    }
}
