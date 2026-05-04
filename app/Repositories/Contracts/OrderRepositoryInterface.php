<?php

namespace App\Repositories\Contracts;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retrieve all orders belonging to a specific user.
     */
    public function findByUserId(int $userId): mixed;

    /**
     * Create an order from a finalized cart.
     */
    public function createFromCart(int $userId, array $data): mixed;
}
