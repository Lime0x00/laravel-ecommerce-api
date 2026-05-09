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

        return $this->success(
            data: $orders,
            message: 'Orders retrieved successfully.'
        );
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $order = $this->orderService->updateOrderStatus(
            orderId: $id,
            status: $request->validated('status')
        );

        return $this->success(
            data: $order,
            message: 'Order status updated successfully.'
        );
    }
}
