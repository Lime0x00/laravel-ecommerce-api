<?php

namespace App\Services;

use App\Events\OrderPlaced;
use App\Factories\PaymentGatewayFactory;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Repositories\Contracts\CartRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly CartRepositoryInterface $cartRepository
    ) {}

    /**
     * Get authenticated user's paginated order history.
     */
    public function getUserOrderHistory(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->orderRepository->findByUserId($userId, $perPage);
    }

    public function checkout(int $userId, array $payload): Order
    {
        $cart = $this->cartRepository->getCartWithItems($userId, null);

        if (!$cart instanceof Cart || $cart->items->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => ['Cart is empty.'],
            ]);
        }

        $totalPrice = (float) $cart->items->sum(function (CartItem $item): float {
            return (float) $item->unit_price * (int) $item->quantity;
        });

        // Process payment using the configured payment gateway
        $paymentMethod = $payload['payment_method'] ?? 'stripe';
        $paymentGateway = PaymentGatewayFactory::make($paymentMethod);

        $paymentSuccessful = $paymentGateway->process(
            amount: $totalPrice * 100, // Convert to cents
            params: [
                'token' => $payload['payment_token'] ?? null,
                'email' => $payload['payment_email'] ?? null,
                'description' => "Order payment for user {$userId}",
                'order_id' => null, // Will be set after order creation
            ]
        );

        if (!$paymentSuccessful) {
            throw ValidationException::withMessages([
                'payment' => ['Payment processing failed. Please try again.'],
            ]);
        }

        // Create order after successful payment
        $order = $this->orderRepository->createFromCart($userId, $cart, [
            'shipping_address' => $payload['shipping_address'],
            'payment_method' => $paymentMethod,
            'total_price' => $totalPrice,
            'payment_status' => 'completed',
        ]);

        // Clear the cart
        $this->cartRepository->clearCart($cart);

        // Dispatch event to trigger listeners (e.g., send confirmation email)
        OrderPlaced::dispatch($order);

        return $order;
    }

    public function getUserOrderById(int $userId, int $orderId): Order
    {
        $order = $this->orderRepository->findUserOrderById($userId, $orderId);

        if (!$order instanceof Order) {
            abort(404, 'Order not found.');
        }

        return $order;
    }

    /**
     * Retrieve all orders for admin dashboard.
     */
    public function getAllOrders(): mixed
    {
        return Order::query()
            ->with(['user', 'items.product'])
            ->latest()
            ->paginate(15);
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
