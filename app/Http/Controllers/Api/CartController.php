<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CartRequest;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class CartController extends BaseApiController
{
    public function __construct(
        private readonly CartService $cartService
    ) {}

    public function addItem(CartRequest $request, int $productId): JsonResponse
    {
        [$userId, $sessionId] = $this->resolveOwner($request);

        $cart = $this->cartService->addItem(
            userId: $userId,
            sessionId: $sessionId,
            productId: $productId,
            quantity: (int) $request->validated('quantity')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item added successfully.',
            'data' => $cart,
        ], 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function updateQuantity(CartRequest $request, int $productId): JsonResponse
    {
        [$userId, $sessionId] = $this->resolveOwner($request);

        $cart = $this->cartService->updateQuantity(
            userId: $userId,
            sessionId: $sessionId,
            productId: $productId,
            quantity: (int) $request->validated('quantity')
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated successfully.',
            'data' => $cart,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function removeItem(Request $request, int $productId): JsonResponse
    {
        [$userId, $sessionId] = $this->resolveOwner($request);

        $cart = $this->cartService->removeItem(
            userId: $userId,
            sessionId: $sessionId,
            productId: $productId
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart item removed successfully.',
            'data' => $cart,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function showCart(Request $request): JsonResponse
    {
        [$userId, $sessionId] = $this->resolveOwner($request);

        $cart = $this->cartService->showCart(
            userId: $userId,
            sessionId: $sessionId
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart retrieved successfully.',
            'data' => $cart,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function clearCart(Request $request): JsonResponse
    {
        [$userId, $sessionId] = $this->resolveOwner($request);

        $cart = $this->cartService->clearCart(
            userId: $userId,
            sessionId: $sessionId
        );

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'data' => $cart,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    private function resolveOwner(Request $request): array
    {
        $userId = null;

        try {
            $userId = $request->user('api')?->id;
        } catch (Throwable) {
            $userId = null;
        }

        $sessionId = $request->header('X-Session-Id')
            ?? $request->input('session_id');

        if (!$userId && !$sessionId) {
            $sessionId = (string) str()->uuid();
        }

        return [$userId, $sessionId];
    }
}
