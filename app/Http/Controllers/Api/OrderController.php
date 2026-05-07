<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderHistoryRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function checkout(CheckoutRequest $request): JsonResponse
    {
        $order = $this->orderService->checkout(
            userId: (int) $request->user()->id,
            payload: $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Checkout completed successfully.',
            'data' => $order,
        ], 201, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * Display authenticated user's order history.
     */
    public function index(OrderHistoryRequest $request): JsonResponse
    {
        $orders = $this->orderService->getUserOrderHistory(
            userId: (int) $request->user()->id,
            perPage: $request->perPage()
        );

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => $orders,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }

    public function show(OrderHistoryRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->getUserOrderById(
            userId: (int) $request->user()->id,
            orderId: $id
        );

        return response()->json([
            'success' => true,
            'message' => 'Order retrieved successfully.',
            'data' => $order,
        ], 200, [], JSON_PRESERVE_ZERO_FRACTION);
    }
}
