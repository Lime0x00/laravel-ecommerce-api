<?php

namespace App\Repositories\Contracts;

use App\Models\Cart;

interface CartRepositoryInterface extends BaseRepositoryInterface
{
    public function findOrCreateCart(?int $userId, ?string $sessionId): Cart;

    public function getCartWithItems(?int $userId, ?string $sessionId): ?Cart;

    public function addOrIncrementItem(Cart $cart, int $productId, int $quantity, float $unitPrice): Cart;

    public function updateItemQuantity(Cart $cart, int $productId, int $quantity): Cart;

    public function removeItem(Cart $cart, int $productId): Cart;

    public function clearCart(Cart $cart): void;

    public function mergeGuestCartIntoUser(string $sessionId, int $userId): void;

    public function calculateCartTotal(int $userId): float;
}
