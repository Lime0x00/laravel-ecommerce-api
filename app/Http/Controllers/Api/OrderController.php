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

        return $this->success(
            data: $order,
            message: 'Checkout completed successfully.',
            code: 201
        );
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

        return $this->success(
            data: $orders,
            message: 'Orders retrieved successfully.'
        );
    }

    public function show(OrderHistoryRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->getUserOrderById(
            userId: (int) $request->user()->id,
            orderId: $id
        );

        return $this->success(
            data: $order,
            message: 'Order retrieved successfully.'
        );
    }
}
