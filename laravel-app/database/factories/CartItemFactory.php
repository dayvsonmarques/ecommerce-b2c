<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();

        return [
            'cart_id' => Cart::factory(),
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => $product->price,
        ];
    }
}
