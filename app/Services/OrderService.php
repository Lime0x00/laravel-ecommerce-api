<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    /**
     * Get authenticated user's paginated order history.
     */
    public function getUserOrderHistory(
        int $userId,
        int $perPage = 10
    ): LengthAwarePaginator {
        return $this->orderRepository->findByUserId($userId, $perPage);
    }

    /**
     * Retrieve all orders for admin dashboard.
     */
    public function getAllOrders(): mixed
    {
        return Order::query()
            ->with(['user', 'items'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Update order status.
     */
    public function updateOrderStatus(
        int $orderId,
        string $status
    ): Order {
        $order = Order::findOrFail($orderId);

        $order->update([
            'status' => $status,
        ]);

        return $order;
    }
}
