<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id'               => User::factory(),
            'number'                => 'ORD-' . now()->format('Ymd') . '-' . str_pad((string) $this->faker->unique()->numberBetween(1, 99999), 5, '0', STR_PAD_LEFT),
            'status'                => OrderStatus::Pending,
            'subtotal'              => $this->faker->randomFloat(2, 50, 5000),
            'shipping_cost'         => 15.00,
            'discount_amount'       => 0.00,
            'total'                 => fn (array $a) => $a['subtotal'] + $a['shipping_cost'],
            'coupon_code'           => null,
            'shipping_street'       => $this->faker->streetName(),
            'shipping_number'       => $this->faker->buildingNumber(),
            'shipping_neighborhood' => $this->faker->word(),
            'shipping_city'         => $this->faker->city(),
            'shipping_state'        => 'SP',
            'shipping_postal_code'  => $this->faker->postcode(),
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status'  => OrderStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn () => [
            'status'           => OrderStatus::Shipped,
            'paid_at'          => now()->subHours(2),
            'shipped_at'       => now(),
            'tracking_code'    => $this->faker->bothify('BR##########BR'),
            'shipping_carrier' => 'Correios',
        ]);
    }
}
