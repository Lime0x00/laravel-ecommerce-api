<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;
use App\Models\Order;
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

    public function createFromCart(int $userId, Cart $cart, array $data): Order;

    public function findUserOrderById(int $userId, int $orderId): ?Order;
}
