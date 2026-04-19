<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\ProductSku;
use App\Models\ProductVariation;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Cria um produto com suas variações e SKUs.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        $product = Product::create([
            'category_id'        => $data['category_id'],
            'name'               => $data['name'],
            'slug'               => $data['slug'],
            'description'        => $data['description'],
            'short_description'  => $data['short_description'] ?? null,
            'price'              => $data['price'],
            'cost_price'         => $data['cost_price'] ?? null,
            'sku'                => $data['sku'],
            'quantity'           => $data['quantity'] ?? 0,
            'min_quantity_alert' => $data['min_quantity_alert'] ?? 10,
            'is_active'          => $data['is_active'] ?? true,
        ]);

        if (! empty($data['variations'])) {
            $this->syncVariations($product, $data['variations']);
        }

        if (! empty($data['skus'])) {
            $this->syncSkus($product, $data['skus']);
        }

        return $product->load(['category', 'variations.values', 'skus']);
    }

    /**
     * Atualiza um produto existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $product->update(array_filter([
            'category_id'        => $data['category_id'] ?? null,
            'name'               => $data['name'] ?? null,
            'slug'               => $data['slug'] ?? null,
            'description'        => $data['description'] ?? null,
            'short_description'  => $data['short_description'] ?? null,
            'price'              => $data['price'] ?? null,
            'cost_price'         => $data['cost_price'] ?? null,
            'sku'                => $data['sku'] ?? null,
            'quantity'           => $data['quantity'] ?? null,
            'min_quantity_alert' => $data['min_quantity_alert'] ?? null,
            'is_active'          => $data['is_active'] ?? null,
        ], fn ($v) => $v !== null));

        if (array_key_exists('variations', $data)) {
            $this->syncVariations($product, $data['variations']);
        }

        if (array_key_exists('skus', $data)) {
            $this->syncSkus($product, $data['skus']);
        }

        return $product->refresh()->load(['category', 'variations.values', 'skus']);
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * @param  array<int, array{name: string, values: array<int, array{value: string, label?: string}>}>  $variations
     */
    private function syncVariations(Product $product, array $variations): void
    {
        $product->variations()->delete();

        foreach ($variations as $variationData) {
            /** @var ProductVariation $variation */
            $variation = $product->variations()->create(['name' => $variationData['name']]);

            foreach ($variationData['values'] ?? [] as $valueData) {
                $variation->values()->create([
                    'value' => $valueData['value'],
                    'label' => $valueData['label'] ?? $valueData['value'],
                ]);
            }
        }
    }

    /**
     * @param  array<int, array{sku: string, price?: float|null, quantity: int, variation_values: array<int, int>, is_active?: bool}>  $skus
     */
    private function syncSkus(Product $product, array $skus): void
    {
        $product->skus()->delete();

        foreach ($skus as $skuData) {
            $product->skus()->create([
                'sku'              => $skuData['sku'],
                'price'            => $skuData['price'] ?? null,
                'quantity'         => $skuData['quantity'] ?? 0,
                'variation_values' => $skuData['variation_values'] ?? [],
                'is_active'        => $skuData['is_active'] ?? true,
            ]);
        }
    }
}
