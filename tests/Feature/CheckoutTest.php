<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('authenticated user can checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 120.00]);

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user_id', $user->id)
        ->assertJsonPath('data.status', 'pending')
        ->assertJsonPath('data.total_price', 240.0);
});

it('prevents unauthenticated user from checkout', function () {
    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'visa',
    ]);

    $response->assertUnauthorized();
});

it('empty cart cannot checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertUnprocessable();
});

it('returns validation error for invalid checkout payload', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '',
        'payment_method' => 'bitcoin',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Validation failed.');
});

it('order is created correctly', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 50.00]);

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 3,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Cairo, Egypt',
        'payment_method' => 'cash',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    expect(Order::query()->count())->toBe(1);
    expect(Order::query()->first()->shipping_address)->toBe('Cairo, Egypt');
    expect(Order::query()->first()->payment_method)->toBe('cash');
});

it('order items are created correctly', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $productOne = Product::factory()->create(['price' => 10.00]);
    $productTwo = Product::factory()->create(['price' => 20.00]);

    $this->postJson("/api/cart/{$productOne->id}", ['quantity' => 2], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();
    $this->postJson("/api/cart/{$productTwo->id}", ['quantity' => 1], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Alexandria, Egypt',
        'payment_method' => 'paypal',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    expect(OrderItem::query()->count())->toBe(2);
});

it('cart clears after checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 99.00]);

    $this->postJson("/api/cart/{$product->id}", ['quantity' => 1], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Giza, Egypt',
        'payment_method' => 'visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $cartResponse = $this->getJson('/api/cart', [
        'Authorization' => "Bearer {$token}",
    ]);

    $cartResponse->assertOk()->assertJsonCount(0, 'data.items');
});

it('authenticated user can list their orders', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    Order::factory()->count(2)->create([
        'user_id' => $user->id,
    ]);

    $response = $this->getJson('/api/orders', [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(2, 'data.data');
});

it('authenticated user can view single order', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 15.00]);

    $this->postJson("/api/cart/{$product->id}", ['quantity' => 2], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $checkoutResponse = $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Dokki, Egypt',
        'payment_method' => 'cash',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $orderId = (int) $checkoutResponse->json('data.id');

    $showResponse = $this->getJson("/api/orders/{$orderId}", [
        'Authorization' => "Bearer {$token}",
    ]);

    $showResponse
        ->assertOk()
        ->assertJsonPath('data.id', $orderId)
        ->assertJsonPath('data.items.0.product.id', $product->id);
});

it('user cannot access another user order', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ownerOrder = Order::factory()->create([
        'user_id' => $owner->id,
    ]);
    $token = JWTAuth::fromUser($otherUser);

    $response = $this->getJson("/api/orders/{$ownerOrder->id}", [
        'Authorization' => "Bearer {$token}",
    ]);

    $response->assertNotFound();
});
