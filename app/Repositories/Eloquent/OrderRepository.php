<?php

namespace App\Repositories\Eloquent;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

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
    public function findByUserId(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->model
            ->where('user_id', $userId)
            ->with(['items.product'])
            ->latest()
            ->paginate($perPage);
    }

    public function createFromCart(int $userId, Cart $cart, array $data): Order
    {
        return DB::transaction(function () use ($userId, $cart, $data): Order {
            $order = $this->model->newQuery()->create([
                'user_id' => $userId,
                'total_price' => $data['total_price'],
                'status' => 'pending',
                'shipping_address' => $data['shipping_address'],
                'payment_method' => $data['payment_method'],
            ]);

            $cart->loadMissing('items');

            /** @var CartItem $cartItem */
            foreach ($cart->items as $cartItem) {
                $order->items()->create([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->unit_price,
                ]);
            }

            return $order->load(['items.product.category', 'user']);
        });
    }

    public function findUserOrderById(int $userId, int $orderId): ?Order
    {
        return $this->model->newQuery()
            ->where('user_id', $userId)
            ->where('id', $orderId)
            ->with(['items.product.category', 'user'])
            ->first();
    }
}
