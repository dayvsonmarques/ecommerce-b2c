<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    Event::fake();

    $this->user    = User::factory()->create();
    $category      = Category::factory()->create();
    $this->product = Product::factory()->create([
        'category_id' => $category->id,
        'quantity'    => 10,
        'price'       => 100.00,
    ]);
    $this->address = UserAddress::factory()->create(['user_id' => $this->user->id]);

    // Montar carrinho
    $cart = Cart::factory()->create([
        'user_id'    => $this->user->id,
        'subtotal'   => 100.00,
        'items_count'=> 1,
        'total'      => 115.00,
        'shipping_cost' => 15.00,
    ]);
    CartItem::create([
        'cart_id'    => $cart->id,
        'product_id' => $this->product->id,
        'quantity'   => 1,
        'unit_price' => 100.00,
        'total_price'=> 100.00,
    ]);
});

it('blocks unauthenticated access to orders', function () {
    $this->getJson('/api/v1/orders')->assertUnauthorized();
});

it('creates an order from cart', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/orders', [
            'shipping_address_id' => $this->address->id,
            'payment_method'      => 'pix',
        ])
        ->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'number', 'status', 'total', 'items'],
        ]);

    $this->assertDatabaseHas('orders', ['user_id' => $this->user->id]);
    $this->assertDatabaseHas('order_items', ['product_id' => $this->product->id]);
    // Cart must be empty after checkout
    $this->assertDatabaseMissing('cart_items', ['product_id' => $this->product->id]);
});

it('rejects order with empty cart', function () {
    // Clear cart first
    CartItem::query()->delete();

    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/orders', [
            'shipping_address_id' => $this->address->id,
            'payment_method'      => 'pix',
        ])
        ->assertStatus(422);
});

it('lists orders for authenticated user', function () {
    Order::factory()->count(3)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/orders')
        ->assertOk()
        ->assertJsonStructure([
            'data'       => ['*' => ['id', 'number', 'status', 'total']],
            'pagination' => ['total', 'current_page'],
        ])
        ->assertJsonCount(3, 'data');
});

it('shows a specific order', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertOk()
        ->assertJsonPath('data.id', $order->id);
});

it('returns 404 for another user order', function () {
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson("/api/v1/orders/{$order->id}")
        ->assertNotFound();
});

it('cancels a pending order', function () {
    $order = Order::factory()->create(['user_id' => $this->user->id]);
    $order->items()->create([
        'product_id'   => $this->product->id,
        'product_name' => $this->product->name,
        'product_sku'  => $this->product->sku,
        'quantity'     => 1,
        'unit_price'   => 100.00,
        'total_price'  => 100.00,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/cancel", ['reason' => 'Desisti da compra'])
        ->assertOk()
        ->assertJsonPath('data.status', OrderStatus::Cancelled->value);
});

it('cannot cancel a delivered order', function () {
    $order = Order::factory()->create([
        'user_id' => $this->user->id,
        'status'  => OrderStatus::Delivered,
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/cancel")
        ->assertStatus(422);
});
