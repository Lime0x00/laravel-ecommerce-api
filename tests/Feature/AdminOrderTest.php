<?php

use App\Models\Order;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('allows admin to update order status', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $order = Order::factory()->create([
        'status' => 'pending',
    ]);

    $token = JWTAuth::fromUser($admin);

    $response = $this
        ->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'completed',
        ], [
            'Authorization' => "Bearer {$token}",
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.status', 'completed');

    expect($order->fresh()->status)->toBe('completed');
});

it('prevents customers from accessing admin routes', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
    ]);

    $token = JWTAuth::fromUser($customer);

    $response = $this
        ->getJson('/api/admin/orders', [
            'Authorization' => "Bearer {$token}",
        ]);

    $response->assertForbidden();
});

it('prevents customer from updating order status', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
    ]);
    $order = Order::factory()->create([
        'status' => 'pending',
    ]);
    $token = JWTAuth::fromUser($customer);

    $response = $this->putJson("/api/admin/orders/{$order->id}/status", [
        'status' => 'shipped',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertForbidden();
});
