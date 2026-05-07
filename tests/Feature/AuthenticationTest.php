<?php

use App\Models\User;

it('allows user registration with valid data', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.email', 'john@example.com')
        ->assertJsonPath('data.role', 'customer');

    expect($this->getJson('/api/auth/profile')->json('status'))->toBe('error');
});

it('prevents registration with duplicate email', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/api/auth/register', [
        'name' => 'Jane Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'Validation failed.');
});

it('prevents registration with invalid email', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'not-an-email',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');
});

it('prevents registration with weak password', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');
});

it('prevents registration without confirmation password', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonPath('status', 'error');
});
