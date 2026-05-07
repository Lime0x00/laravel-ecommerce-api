<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

it('lists products with pagination', function () {
    Product::factory()->count(20)->create(['stock' => 5]);

    $response = $this->getJson('/api/products?per_page=10');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(10, 'data.data');
});

it('filters products by category slug', function () {
    $electronics = Category::factory()->create([
        'name' => 'Electronics',
        'slug' => 'electronics',
    ]);
    $books = Category::factory()->create([
        'name' => 'Books',
        'slug' => 'books',
    ]);

    Product::factory()->count(3)->create([
        'category_id' => $electronics->id,
        'stock' => 10,
    ]);
    Product::factory()->count(2)->create([
        'category_id' => $books->id,
        'stock' => 10,
    ]);

    $response = $this->getJson('/api/products?category=electronics');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(3, 'data.data');
});

it('searches products by name', function () {
    $category = Category::factory()->create();

    Product::factory()->create([
        'name' => 'Gaming Laptop',
        'category_id' => $category->id,
        'stock' => 10,
    ]);
    Product::factory()->create([
        'name' => 'Office Chair',
        'category_id' => $category->id,
        'stock' => 10,
    ]);

    $response = $this->getJson('/api/products?search=laptop');

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.name', 'Gaming Laptop');
});

it('shows a single product', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $response = $this->getJson("/api/products/{$product->id}");

    $response
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.id', $product->id);
});

it('returns not found for missing product id', function () {
    $response = $this->getJson('/api/products/999999');

    $response->assertNotFound();
});

it('prevents customer from creating, updating, and deleting products', function () {
    $customer = User::factory()->create(['role' => 'customer']);
    $token = JWTAuth::fromUser($customer);
    $category = Category::factory()->create();
    $product = Product::factory()->create();

    $createResponse = $this->postJson('/api/products', [
        'name' => 'New Product',
        'slug' => 'new-product',
        'description' => 'Description',
        'price' => 100,
        'stock' => 10,
        'category_id' => $category->id,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $updateResponse = $this->putJson("/api/products/{$product->id}", [
        'name' => 'Updated Name',
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $deleteResponse = $this->deleteJson("/api/products/{$product->id}", [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $createResponse->assertForbidden();
    $updateResponse->assertForbidden();
    $deleteResponse->assertForbidden();
});

it('allows admin to create, update, and delete products', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);
    $category = Category::factory()->create();

    $createResponse = $this->postJson('/api/products', [
        'name' => 'Pro Keyboard',
        'slug' => 'pro-keyboard',
        'description' => 'Mechanical keyboard',
        'price' => 120.50,
        'stock' => 15,
        'category_id' => $category->id,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $createResponse
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Pro Keyboard');

    $productId = (int) $createResponse->json('data.id');

    $updateResponse = $this->putJson("/api/products/{$productId}", [
        'name' => 'Pro Keyboard X',
        'stock' => 20,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $updateResponse
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.name', 'Pro Keyboard X');

    $deleteResponse = $this->deleteJson("/api/products/{$productId}", [], [
        'Authorization' => "Bearer {$token}",
    ]);

    $deleteResponse
        ->assertOk()
        ->assertJsonPath('success', true);

    expect(Product::query()->find($productId))->toBeNull();
});

it('returns validation error when admin creates product with invalid payload', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $token = JWTAuth::fromUser($admin);

    $response = $this->postJson('/api/products', [
        'name' => '',
        'price' => -1,
        'stock' => -1,
    ], [
        'Authorization' => "Bearer {$token}",
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Validation failed.');
});
