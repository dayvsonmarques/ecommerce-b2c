<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('logs in with valid credentials and returns token', function () {
    User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'password123',
    ])
        ->assertOk()
        ->assertJsonStructure([
            'message',
            'user'  => ['id', 'name', 'email'],
            'token',
        ]);
});

it('rejects login with wrong password', function () {
    User::factory()->create([
        'email'    => 'test@example.com',
        'password' => bcrypt('password123'),
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'wrong_password',
    ])
        ->assertStatus(401)
        ->assertJsonPath('message', 'Credenciais inválidas.');
});

it('rejects login for a non-existent email', function () {
    $this->postJson('/api/v1/auth/login', [
        'email'    => 'ghost@example.com',
        'password' => 'password123',
    ])
        ->assertStatus(401);
});

it('rejects login for an inactive user', function () {
    User::factory()->create([
        'email'     => 'inactive@example.com',
        'password'  => bcrypt('password123'),
        'is_active' => false,
    ]);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'inactive@example.com',
        'password' => 'password123',
    ])
        ->assertStatus(403);
});
