<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns the authenticated user profile', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/profile')
        ->assertOk()
        ->assertJsonStructure([
            'user' => ['id', 'name', 'email', 'created_at'],
        ]);
});

it('rejects unauthenticated access to profile', function () {
    $this->getJson('/api/v1/auth/profile')
        ->assertUnauthorized();
});

it('updates name and email', function () {
    $user = User::factory()->create(['password' => bcrypt('old_password')]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/auth/profile', [
            'name'  => 'Updated Name',
            'email' => 'updated@example.com',
        ])
        ->assertOk()
        ->assertJson([
            'message' => 'Perfil atualizado com sucesso.',
            'user'    => [
                'name'  => 'Updated Name',
                'email' => 'updated@example.com',
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'id'    => $user->id,
        'name'  => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('updates password when current_password is correct', function () {
    $user = User::factory()->create(['password' => bcrypt('old_password')]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/auth/profile', [
            'current_password'      => 'old_password',
            'password'              => 'new_password123',
            'password_confirmation' => 'new_password123',
        ])
        ->assertOk();

    // After password change the user should be able to log in with the new password
    $this->postJson('/api/v1/auth/login', [
        'email'    => $user->email,
        'password' => 'new_password123',
    ])->assertOk();
});

it('rejects password change when current_password is wrong', function () {
    $user = User::factory()->create(['password' => bcrypt('old_password')]);

    $this->actingAs($user, 'sanctum')
        ->putJson('/api/v1/auth/profile', [
            'current_password'      => 'wrong_password',
            'password'              => 'new_password123',
            'password_confirmation' => 'new_password123',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('current_password');
});

it('logs out the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/auth/logout')
        ->assertOk()
        ->assertJson(['message' => 'Logout realizado com sucesso.']);
});
