<?php

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    /**
     * OrderRepository constructor.
     */
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all orders belonging to a specific user.
     */
    public function findByUserId(int $userId): mixed
    {
        return null;
    }

    /**
     * Create an order from a finalized cart.
     */
    public function createFromCart(int $userId, array $data): mixed
    {
        return null;
    }
}
