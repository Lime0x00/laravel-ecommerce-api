<?php

use App\Models\Order;
use App\Models\User;

it('allows admin to update order status', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $order = Order::factory()->create([
        'status' => 'pending',
    ]);

    $response = $this
        ->actingAs($admin, 'api')
        ->putJson("/api/admin/orders/{$order->id}/status", [
            'status' => 'completed',
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.status', 'completed');

    expect($order->fresh()->status)->toBe('completed');
});

it('prevents customers from accessing admin routes', function () {
    $customer = User::factory()->create([
        'role' => 'customer',
    ]);

    $response = $this
        ->actingAs($customer, 'api')
        ->getJson('/api/admin/orders');

    $response->assertForbidden();
});
