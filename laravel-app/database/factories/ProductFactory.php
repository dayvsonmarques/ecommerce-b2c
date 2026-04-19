<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'name' => $this->faker->word(),
            'slug' => $this->faker->unique()->slug(),
            'description' => $this->faker->paragraph(3),
            'short_description' => $this->faker->sentence(),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'cost_price' => $this->faker->randomFloat(2, 5, 500),
            'sku' => $this->faker->unique()->bothify('SKU-####'),
            'quantity' => $this->faker->numberBetween(1, 100),
            'min_quantity_alert' => 10,
            'is_active' => true,
        ];
    }
}
