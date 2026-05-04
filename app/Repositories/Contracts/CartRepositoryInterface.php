<?php

namespace App\Repositories\Contracts;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Calculate and return the total price for a user's active cart.
     */
    public function calculateCartTotal(int $userId): float;
}
