<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement([
                'pending',
                'processing',
                'completed',
                'cancelled',
            ]),
            'total_amount' => $this->faker->randomFloat(2, 50, 5000),
            'shipping_address' => $this->faker->address(),
            'payment_method' => $this->faker->randomElement([
                'cash',
                'visa',
                'paypal',
            ]),
        ];
    }
}
