<?php

namespace App\Services;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function addItem(?int $userId, ?string $sessionId, int $productId, int $quantity): array
    {
        $product = $this->findProduct($productId);
        $cart = $this->cartRepository->findOrCreateCart($userId, $sessionId);

        $updatedCart = $this->cartRepository->addOrIncrementItem(
            cart: $cart,
            productId: $productId,
            quantity: $quantity,
            unitPrice: (float) $product->price
        );

        return $this->buildCartResponse($updatedCart, $sessionId);
    }

    public function updateQuantity(?int $userId, ?string $sessionId, int $productId, int $quantity): array
    {
        $cart = $this->cartRepository->findOrCreateCart($userId, $sessionId);
        $updatedCart = $this->cartRepository->updateItemQuantity($cart, $productId, $quantity);

        return $this->buildCartResponse($updatedCart, $sessionId);
    }

    public function removeItem(?int $userId, ?string $sessionId, int $productId): array
    {
        $cart = $this->cartRepository->findOrCreateCart($userId, $sessionId);
        $updatedCart = $this->cartRepository->removeItem($cart, $productId);

        return $this->buildCartResponse($updatedCart, $sessionId);
    }

    public function showCart(?int $userId, ?string $sessionId): array
    {
        $cart = $this->cartRepository->getCartWithItems($userId, $sessionId);

        return $this->buildCartResponse($cart, $sessionId);
    }

    public function clearCart(?int $userId, ?string $sessionId): array
    {
        $cart = $this->cartRepository->getCartWithItems($userId, $sessionId);

        if ($cart instanceof Cart) {
            $this->cartRepository->clearCart($cart);
        }

        return $this->buildCartResponse(
            $this->cartRepository->getCartWithItems($userId, $sessionId),
            $sessionId
        );
    }

    public function mergeGuestCartIntoUser(?string $sessionId, int $userId): void
    {
        if (!$sessionId) {
            return;
        }

        $this->cartRepository->mergeGuestCartIntoUser($sessionId, $userId);
    }

    private function findProduct(int $productId): Model
    {
        $product = $this->productRepository->find($productId);

        if (!$product instanceof Model) {
            abort(404, 'Product not found.');
        }

        return $product;
    }

    private function buildCartResponse(?Cart $cart, ?string $sessionId): array
    {
        if (!$cart instanceof Cart) {
            return [
                'session_id' => $sessionId,
                'items' => [],
                'total' => 0.0,
            ];
        }

        $items = $cart->items->map(function ($item): array {
            $unitPrice = (float) $item->unit_price;
            $subtotal = $unitPrice * (int) $item->quantity;

            return [
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
                'unit_price' => $unitPrice,
                'subtotal' => $subtotal,
                'product' => [
                    'id' => $item->product?->id,
                    'name' => $item->product?->name,
                    'slug' => $item->product?->slug,
                    'price' => (float) ($item->product?->price ?? $unitPrice),
                    'category' => [
                        'id' => $item->product?->category?->id,
                        'name' => $item->product?->category?->name,
                        'slug' => $item->product?->category?->slug,
                    ],
                ],
            ];
        })->values();

        return [
            'cart_id' => $cart->id,
            'user_id' => $cart->user_id,
            'session_id' => $cart->session_id ?? $sessionId,
            'items' => $items,
            'total' => (float) $items->sum('subtotal'),
        ];
    }
}
