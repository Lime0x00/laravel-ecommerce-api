<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('guest can add item to cart', function () {
    $product = Product::factory()->create(['price' => 100.00]);
    $sessionId = 'guest-session-1';

    $response = $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
        'session_id' => $sessionId,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2);

    expect((float) $response->json('data.items.0.subtotal'))->toBe(200.0);
    expect((float) $response->json('data.total'))->toBe(200.0);
});

it('authenticated user can add item to cart', function () {
    $user = User::factory()->create();
    $token = JWTAuth::fromUser($user);
    $product = Product::factory()->create(['price' => 50.00]);

    $response = $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 3,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.items.0.quantity', 3);

    expect((float) $response->json('data.total'))->toBe(150.0);
});

it('updating quantity works', function () {
    $product = Product::factory()->create(['price' => 25.00]);
    $sessionId = 'guest-session-2';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 1,
        'session_id' => $sessionId,
    ])->assertCreated();

    $response = $this->putJson("/api/cart/{$product->id}", [
        'quantity' => 4,
        'session_id' => $sessionId,
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('data.items.0.quantity', 4);

    expect((float) $response->json('data.items.0.subtotal'))->toBe(100.0);
    expect((float) $response->json('data.total'))->toBe(100.0);
});

it('removing item works', function () {
    $product = Product::factory()->create(['price' => 40.00]);
    $sessionId = 'guest-session-3';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
        'session_id' => $sessionId,
    ])->assertCreated();

    $response = $this->deleteJson("/api/cart/{$product->id}", [
        'session_id' => $sessionId,
    ]);

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data.items');

    expect((float) $response->json('data.total'))->toBe(0.0);
});

it('showing cart returns product details and subtotal', function () {
    $category = Category::factory()->create([
        'name' => 'Books',
        'slug' => 'books',
    ]);
    $product = Product::factory()->create([
        'name' => 'Clean Code',
        'slug' => 'clean-code',
        'price' => 30.00,
        'category_id' => $category->id,
    ]);
    $sessionId = 'guest-session-4';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
        'session_id' => $sessionId,
    ])->assertCreated();

    $response = $this->getJson("/api/cart?session_id={$sessionId}");

    $response
        ->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.items.0.product.name', 'Clean Code')
        ->assertJsonPath('data.items.0.product.category.slug', 'books');

    expect((float) $response->json('data.items.0.subtotal'))->toBe(60.0);
    expect((float) $response->json('data.total'))->toBe(60.0);
});

it('clearing cart works', function () {
    $product = Product::factory()->create(['price' => 70.00]);
    $sessionId = 'guest-session-5';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 3,
        'session_id' => $sessionId,
    ])->assertCreated();

    $response = $this->deleteJson('/api/cart', [
        'session_id' => $sessionId,
    ]);

    $response
        ->assertOk()
        ->assertJsonCount(0, 'data.items');

    expect((float) $response->json('data.total'))->toBe(0.0);
});

it('guest cart merges into authenticated user cart on login', function () {
    $product = Product::factory()->create(['price' => 90.00]);
    $sessionId = 'guest-session-6';
    $password = 'password123';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
        'session_id' => $sessionId,
    ])->assertCreated();

    $user = User::factory()->create([
        'email' => 'cart-merge@example.com',
        'password' => bcrypt($password),
    ]);

    $loginResponse = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => $password,
        'session_id' => $sessionId,
    ]);

    $token = $loginResponse->json('data.access_token');

    $cartResponse = $this->getJson('/api/cart', [
        'Authorization' => "Bearer {$token}",
    ]);

    $cartResponse
        ->assertOk()
        ->assertJsonPath('data.items.0.product_id', $product->id)
        ->assertJsonPath('data.items.0.quantity', 2);

    expect((float) $cartResponse->json('data.total'))->toBe(180.0);
});

it('same product merge increases quantity instead of duplicating rows', function () {
    $product = Product::factory()->create(['price' => 20.00]);
    $sessionId = 'guest-session-7';
    $password = 'password123';

    $user = User::factory()->create([
        'email' => 'cart-merge-dup@example.com',
        'password' => bcrypt($password),
    ]);
    $token = JWTAuth::fromUser($user);

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 1,
    ], [
        'Authorization' => "Bearer {$token}",
    ])->assertCreated();

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 3,
        'session_id' => $sessionId,
    ])->assertCreated();

    $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => $password,
        'session_id' => $sessionId,
    ])->assertOk();

    $cartResponse = $this->getJson('/api/cart', [
        'Authorization' => "Bearer {$token}",
    ]);

    $cartResponse
        ->assertOk()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.quantity', 4);

    expect((float) $cartResponse->json('data.total'))->toBe(80.0);
});

it('adding same product twice in same cart increments quantity', function () {
    $product = Product::factory()->create(['price' => 15.00]);
    $sessionId = 'guest-session-8';

    $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 1,
        'session_id' => $sessionId,
    ])->assertCreated();

    $response = $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 2,
        'session_id' => $sessionId,
    ]);

    $response
        ->assertCreated()
        ->assertJsonCount(1, 'data.items')
        ->assertJsonPath('data.items.0.quantity', 3);

    expect((float) $response->json('data.total'))->toBe(45.0);
});

it('returns validation error for invalid cart quantity', function () {
    $product = Product::factory()->create();

    $response = $this->postJson("/api/cart/{$product->id}", [
        'quantity' => 0,
        'session_id' => 'guest-session-9',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Validation failed.');
});

it('returns validation error for invalid cart product id', function () {
    $response = $this->postJson('/api/cart/999999', [
        'quantity' => 1,
        'session_id' => 'guest-session-10',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Validation failed.');
});
