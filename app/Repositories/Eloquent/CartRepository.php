<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    /**
     * CartRepository constructor.
     */
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    /**
     * Calculate and return the total price for a user's active cart.
     */
    public function calculateCartTotal(int $userId): float
    {
        return 0.00;
    }
}
