<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\OrderHistoryRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends BaseApiController
{
    public function __construct(
        private readonly OrderService $orderService
    ) {}

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
        ]);
    }
}
