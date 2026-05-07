<?php

use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('allows user login with valid credentials', function () {
    $user = User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonStructure(['data' => ['access_token', 'token_type', 'expires_in']]);
});

it('prevents login with invalid email', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error');
});

it('prevents login with wrong password', function () {
    User::factory()->create([
        'email' => 'john@example.com',
        'password' => bcrypt('password123'),
    ]);

    $response = $this->postJson('/api/auth/login', [
        'email' => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJsonPath('status', 'error');
});

it('allows authenticated user to get profile', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->getJson('/api/auth/profile', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('data.role', 'customer');
});

it('prevents unauthenticated user from accessing profile', function () {
    $response = $this->getJson('/api/auth/profile');

    $response->assertUnauthorized();
});

it('allows authenticated user to refresh token', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->postJson('/api/auth/refresh', [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure(['data' => ['access_token', 'token_type', 'expires_in']]);
});

it('prevents unauthenticated user from refreshing token', function () {
    $response = $this->postJson('/api/auth/refresh');

    $response->assertUnauthorized();
});
