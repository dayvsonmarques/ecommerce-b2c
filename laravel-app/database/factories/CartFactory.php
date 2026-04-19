<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Cart;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'subtotal' => 0.00,
            'items_count' => 0,
            'shipping_cost' => 0.00,
            'coupon_code' => null,
            'discount_amount' => 0.00,
            'total' => 0.00,
        ];
    }
}
