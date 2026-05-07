<?php

namespace App\Services;

use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {
    }

    /**
     * Get authenticated user's paginated order history.
     */
    public function getUserOrderHistory(
        int $userId,
        int $perPage = 10
    ): LengthAwarePaginator {
        return $this->orderRepository->findByUserId($userId, $perPage);
    }
}