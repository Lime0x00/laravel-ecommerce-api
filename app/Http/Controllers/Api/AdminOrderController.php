<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

    public function index(): JsonResponse
    {
        $orders = $this->orderService->getAllOrders();

        return response()->json([
            'success' => true,
            'message' => 'Orders retrieved successfully.',
            'data' => $orders,
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus(
            orderId: $id,
            status: $request->validated('status')
        );

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully.',
            'data' => $order,
        ]);
    }
}
