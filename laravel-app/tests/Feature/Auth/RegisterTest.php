<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user and returns token', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertCreated()
        ->assertJsonStructure([
            'message',
            'user'  => ['id', 'name', 'email', 'created_at'],
            'token',
        ]);

    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
});

it('rejects registration with an already taken email', function () {
    User::factory()->create(['email' => 'existing@example.com']);

    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'existing@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('email');
});

it('rejects registration with a weak password', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'weak',
        'password_confirmation' => 'weak',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('rejects registration when passwords do not match', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'different_password',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('registers user with optional phone and cpf', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
        'phone'                 => '11999998888',
        'cpf'                   => '123.456.789-00',
    ])
        ->assertCreated();

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'phone' => '11999998888',
    ]);
});
