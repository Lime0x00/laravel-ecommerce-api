<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    /**
     * CartRepository constructor.
     */
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findOrCreateCart(?int $userId, ?string $sessionId): Cart
    {
        $query = $this->model->newQuery();

        if ($userId) {
            $cart = $query->where('user_id', $userId)->first();

            if ($cart instanceof Cart) {
                return $cart;
            }
        }

        if ($sessionId) {
            $cart = $query->where('session_id', $sessionId)->first();

            if ($cart instanceof Cart) {
                return $cart;
            }
        }

        return $this->model->newQuery()->create([
            'user_id' => $userId,
            'session_id' => $userId ? null : $sessionId,
        ]);
    }

    public function getCartWithItems(?int $userId, ?string $sessionId): ?Cart
    {
        $query = $this->model->newQuery()
            ->with(['items.product.category']);

        if ($userId) {
            return $query->where('user_id', $userId)->first();
        }

        if (!$sessionId) {
            return null;
        }

        return $query->where('session_id', $sessionId)->first();
    }

    public function addOrIncrementItem(Cart $cart, int $productId, int $quantity, float $unitPrice): Cart
    {
        $item = $cart->items()
            ->where('product_id', $productId)
            ->first();

        if ($item instanceof CartItem) {
            $item->update([
                'quantity' => $item->quantity + $quantity,
                'unit_price' => $unitPrice,
            ]);
        } else {
            $cart->items()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
            ]);
        }

        return $this->getCartWithItems($cart->user_id, $cart->session_id) ?? $cart;
    }

    public function updateItemQuantity(Cart $cart, int $productId, int $quantity): Cart
    {
        $item = $cart->items()
            ->where('product_id', $productId)
            ->first();

        if ($item instanceof CartItem) {
            $item->update([
                'quantity' => $quantity,
            ]);
        }

        return $this->getCartWithItems($cart->user_id, $cart->session_id) ?? $cart;
    }

    public function removeItem(Cart $cart, int $productId): Cart
    {
        $cart->items()->where('product_id', $productId)->delete();

        return $this->getCartWithItems($cart->user_id, $cart->session_id) ?? $cart;
    }

    public function clearCart(Cart $cart): void
    {
        $cart->items()->delete();
    }

    public function mergeGuestCartIntoUser(string $sessionId, int $userId): void
    {
        DB::transaction(function () use ($sessionId, $userId): void {
            $guestCart = $this->model->newQuery()
                ->with('items')
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->first();

            if (!$guestCart instanceof Cart) {
                return;
            }

            $userCart = $this->findOrCreateCart($userId, null);

            foreach ($guestCart->items as $guestItem) {
                $existingItem = $userCart->items()
                    ->where('product_id', $guestItem->product_id)
                    ->first();

                if ($existingItem instanceof CartItem) {
                    $existingItem->update([
                        'quantity' => $existingItem->quantity + $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                    ]);
                } else {
                    $userCart->items()->create([
                        'product_id' => $guestItem->product_id,
                        'quantity' => $guestItem->quantity,
                        'unit_price' => $guestItem->unit_price,
                    ]);
                }
            }

            $guestCart->delete();
        });
    }

    public function calculateCartTotal(int $userId): float
    {
        $cart = $this->getCartWithItems($userId, null);

        if (!$cart instanceof Cart) {
            return 0.0;
        }

        return (float) $cart->items->sum(function (CartItem $item): float {
            return (float) $item->quantity * (float) $item->unit_price;
        });
    }
}
