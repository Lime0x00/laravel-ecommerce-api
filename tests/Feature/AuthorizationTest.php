<?php

use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('allows admin user to access admin orders', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);

    $response = $this->getJson('/api/admin/orders', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertOk();
});

it('prevents customer from accessing admin orders', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $token = JWTAuth::fromUser($customer);

    $response = $this->getJson('/api/admin/orders', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertForbidden();
});

it('prevents unauthenticated user from accessing admin routes', function () {
    $response = $this->getJson('/api/admin/orders');

    $response->assertUnauthorized();
});

it('allows customer to access their own orders', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $token = JWTAuth::fromUser($customer);

    $response = $this->getJson('/api/orders', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertOk();
});
