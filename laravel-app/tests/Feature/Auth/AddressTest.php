<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Shared user fixture
beforeEach(function () {
    $this->user = User::factory()->create();
});

it('creates an address for the authenticated user', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/addresses', [
            'type'         => 'billing',
            'street'       => 'Rua Principal',
            'number'       => '123',
            'neighborhood' => 'Centro',
            'city'         => 'São Paulo',
            'state'        => 'SP',
            'postal_code'  => '01234-567',
            'is_default'   => true,
        ])
        ->assertCreated()
        ->assertJsonStructure([
            'message',
            'data' => ['id', 'type', 'street', 'created_at'],
        ]);

    $this->assertDatabaseHas('user_addresses', [
        'user_id' => $this->user->id,
        'street'  => 'Rua Principal',
    ]);
});

it('lists all addresses of the authenticated user', function () {
    UserAddress::factory()->count(3)->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/v1/addresses')
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'type', 'street', 'city'],
            ],
        ])
        ->assertJsonCount(3, 'data');
});

it('updates an address owned by the user', function () {
    $address = UserAddress::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/addresses/{$address->id}", [
            'street' => 'Rua Atualizada',
            'city'   => 'Rio de Janeiro',
        ])
        ->assertOk()
        ->assertJson([
            'message' => 'Endereço atualizado com sucesso.',
            'data'    => [
                'street' => 'Rua Atualizada',
                'city'   => 'Rio de Janeiro',
            ],
        ]);
});

it('deletes an address owned by the user', function () {
    $address = UserAddress::factory()->create(['user_id' => $this->user->id]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertOk()
        ->assertJson(['message' => 'Endereço deletado com sucesso.']);

    $this->assertDatabaseMissing('user_addresses', ['id' => $address->id]);
});

it('returns 404 when trying to delete another user address', function () {
    $otherUser = User::factory()->create();
    $address   = UserAddress::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/v1/addresses/{$address->id}")
        ->assertNotFound();
});

it('returns 404 when trying to update another user address', function () {
    $otherUser = User::factory()->create();
    $address   = UserAddress::factory()->create(['user_id' => $otherUser->id]);

    $this->actingAs($this->user, 'sanctum')
        ->putJson("/api/v1/addresses/{$address->id}", ['street' => 'Hack'])
        ->assertNotFound();
});

it('blocks unauthenticated access to addresses', function () {
    $this->postJson('/api/v1/addresses', ['type' => 'billing', 'street' => 'Rua Principal'])
        ->assertUnauthorized();
});

it('blocks validation of required address fields', function () {
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/v1/addresses', []) // empty payload
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type', 'street', 'number', 'neighborhood', 'city', 'state', 'postal_code']);
});
