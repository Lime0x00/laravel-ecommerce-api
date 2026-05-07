<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retrieve paginated orders belonging to a specific user.
     */
    public function findByUserId(
        int $userId,
        int $perPage = 10
    ): LengthAwarePaginator;

    /**
     * Create an order from a finalized cart.
     */
    public function createFromCart(int $userId, array $data): mixed;
}