<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// Smoke test — garante que o módulo Auth responde corretamente.
// Testes detalhados estão em tests/Feature/Auth/{Register,Login,Profile,Address}Test.php

it('rejects unauthenticated access to profile', function () {
    $this->getJson('/api/v1/auth/profile')
        ->assertUnauthorized();
});

it('register endpoint is reachable', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Test User',
        'email'                 => 'smoke@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ])->assertCreated();
});

it('login endpoint rejects invalid credentials', function () {
    User::factory()->create(['email' => 'smoke@example.com']);

    $this->postJson('/api/v1/auth/login', [
        'email'    => 'smoke@example.com',
        'password' => 'wrong-password',
    ])->assertStatus(401);
});
