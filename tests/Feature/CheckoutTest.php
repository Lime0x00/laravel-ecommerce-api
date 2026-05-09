<?php

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Services\Contracts\PaymentGatewayInterface;
use App\Services\StripePaymentGateway;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\App;

beforeEach(function () {
    $this->paymentMock = Mockery::mock(StripePaymentGateway::class);
    $this->paymentMock->shouldReceive('process')->andReturn(true)->byDefault();
    
    // Bind the mock so the Factory picks it up via the container
    App::bind(StripePaymentGateway::class, fn() => $this->paymentMock);
});

it('authenticated user can checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 120.00, 'stock' => 100]);

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.user_id', $user->id);

    expect((float) $response->json('data.total_price'))->toBe(240.0);
});

it('prevents unauthenticated user from checkout', function () {
    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
    ]);

    $response->assertUnauthorized();
});

it('empty cart cannot checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->postJson('/api/orders/checkout', [
        'shipping_address' => '123 Main St',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
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
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
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
    $product = Product::factory()->create(['price' => 50.00, 'stock' => 100]);

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 3,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Cairo, Egypt',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    expect(Order::query()->count())->toBe(1);
    expect(Order::query()->first()->shipping_address)->toBe('Cairo, Egypt');
});

it('order items are created correctly', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $productOne = Product::factory()->create(['price' => 10.00, 'stock' => 100]);
    $productTwo = Product::factory()->create(['price' => 20.00, 'stock' => 100]);

    $this->postJson("/api/cart/{$productOne->id}", ['quantity' => 2], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();
    $this->postJson("/api/cart/{$productTwo->id}", ['quantity' => 1], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Alexandria, Egypt',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    expect(OrderItem::query()->count())->toBe(2);
});

it('cart clears after checkout', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 99.00, 'stock' => 100]);

    $this->postJson("/api/cart/{$product->id}", ['quantity' => 1], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Giza, Egypt',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
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
        ->assertJsonPath('status', 'success')
        ->assertJsonCount(2, 'data.data');
});

it('authenticated user can view single order', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 15.00, 'stock' => 100]);

    $this->postJson("/api/cart/{$product->id}", ['quantity' => 2], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $checkoutResponse = $this->postJson('/api/orders/checkout', [
        'shipping_address' => 'Dokki, Egypt',
        'payment_method' => 'stripe',
        'payment_token' => 'tok_visa',
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
