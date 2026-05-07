<?php

namespace App\Http\Controllers\Api;

use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    /**
     * Display a listing of available products.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);

        $products = $this->productService->getAvailableProducts($perPage);

        return response()->json([
            'success' => true,
            'message' => 'Products retrieved successfully.',
            'data' => $products,
        ]);
    }
}
