<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function () {
    Queue::fake();
    $this->user  = User::factory()->create();
    $this->order = Order::factory()->create(['user_id' => $this->user->id]);
});

it('initiates pix payment for an order', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$this->order->id}/pay", ['method' => 'pix'])
        ->assertCreated()
        ->assertJsonStructure([
            'data' => ['id', 'method', 'status', 'pix_qr_code', 'pix_qr_code_text', 'pix_expires_at'],
        ])
        ->assertJsonPath('data.method', 'pix')
        ->assertJsonPath('data.status', 'pending');

    $this->assertDatabaseHas('payments', [
        'order_id' => $this->order->id,
        'method'   => 'pix',
    ]);
});

it('initiates boleto payment', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$this->order->id}/pay", ['method' => 'boleto'])
        ->assertCreated()
        ->assertJsonPath('data.method', 'boleto')
        ->assertJsonStructure(['data' => ['boleto_url', 'boleto_barcode']]);
});

it('initiates credit card payment', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$this->order->id}/pay", [
            'method'     => 'credit_card',
            'card_token' => 'tok_visa_test',
        ])
        ->assertCreated()
        ->assertJsonPath('data.method', 'credit_card')
        ->assertJsonPath('data.status', 'processing');
});

it('rejects payment for another user order', function () {
    $other = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $other->id]);

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$order->id}/pay", ['method' => 'pix'])
        ->assertNotFound();
});

it('rejects duplicate payment', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$this->order->id}/pay", ['method' => 'pix'])
        ->assertCreated();

    $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/v1/orders/{$this->order->id}/pay", ['method' => 'pix'])
        ->assertStatus(422);
});

it('dispatches webhook job on webhook event', function () {
    $payload = [
        'type' => 'payment.succeeded',
        'data' => ['object' => ['id' => 'pix_test123']],
    ];

    $this->postJson('/api/v1/webhooks/payment', $payload)
        ->assertOk()
        ->assertJson(['message' => 'Webhook recebido.']);

    Queue::assertPushedOn('webhooks', \App\Jobs\ProcessPaymentWebhookJob::class);
});
