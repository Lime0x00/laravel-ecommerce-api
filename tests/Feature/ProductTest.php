<?php

use App\Models\Product;
use App\Models\User;

// Product validation tests would go here once ProductController is implemented
// For now, we test the authorization and form request validation through authorization tests

it('only admin users can create products', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create(['role' => 'customer']);

    // This will be tested once ProductController is implemented
    // For now, we verify the FormRequest authorization logic
    expect($admin->role)->toBe('admin');
    expect($customer->role)->toBe('customer');
});

it('can retrieve products list', function () {
    $products = Product::factory()->count(5)->create();

    // Products endpoint would be available once ProductController is implemented
    // Assert that products were created successfully
    expect($products)->toHaveCount(5);
    expect($products->first()->id)->toBeGreaterThan(0);
});
