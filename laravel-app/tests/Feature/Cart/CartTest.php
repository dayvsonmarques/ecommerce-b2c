<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user     = User::factory()->create();
    $category       = Category::factory()->create();
    $this->product  = Product::factory()->create([
        'category_id' => $category->id,
        'quantity'    => 10,
        'price'       => 25.00,
    ]);
});

it('blocks unauthenticated cart access', function () {
    $this->getJson('/api/v1/cart')->assertUnauthorized();
});

it('adds a product to cart', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity'   => 2,
        ])
        ->assertCreated()
        ->assertJsonPath('data.quantity', 2);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $this->product->id,
        'quantity'   => 2,
    ]);
});

it('rejects quantity exceeding available stock', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', [
            'product_id' => $this->product->id,
            'quantity'   => 50,
        ])
        ->assertStatus(422);
});

it('updates cart item quantity', function () {
    $add    = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 2]);
    $itemId = $add->json('data.id');

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/cart/items/{$itemId}", ['quantity' => 5])
        ->assertOk()
        ->assertJsonPath('data.quantity', 5);
});

it('removes an item from cart', function () {
    $add    = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 1]);
    $itemId = $add->json('data.id');

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/cart/items/{$itemId}")
        ->assertOk()
        ->assertJson(['message' => 'Item removido do carrinho.']);

    $this->assertDatabaseMissing('cart_items', ['id' => $itemId]);
});

it('shows cart summary with correct totals', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 3]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/cart')
        ->assertOk()
        ->assertJsonPath('data.items_count', 3)
        ->assertJsonPath('data.subtotal', '75.00');
});

it('clears the cart', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 2]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/clear')
        ->assertOk()
        ->assertJson(['message' => 'Carrinho limpo com sucesso.']);

    $this->assertDatabaseMissing('cart_items', ['product_id' => $this->product->id]);
});

it('estimates shipping cost', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 1]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/shipping', ['postal_code' => '01310-100'])
        ->assertOk()
        ->assertJsonStructure(['data' => ['shipping_cost']]);
});

it('rejects an invalid coupon', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/items', ['product_id' => $this->product->id, 'quantity' => 1]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/cart/coupon', ['coupon_code' => 'INVALID999'])
        ->assertStatus(422);
});
