<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\ProductStoreRequest;
use App\Http\Requests\ProductUpdateRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends BaseApiController
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->integer('per_page', 15);
        $category = $request->string('category')->toString() ?: null;
        $search = $request->string('search')->toString() ?: null;

        $products = $this->productService->getCatalog(
            perPage: $perPage,
            categorySlug: $category,
            search: $search
        );

        return $this->success(
            data: $products,
            message: 'Products retrieved successfully.'
        );
    }

    public function show(int $id): JsonResponse
    {
        $product = $this->productService->findById($id);

        return $this->success(
            data: $product,
            message: 'Product retrieved successfully.'
        );
    }

    public function store(ProductStoreRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());

        return $this->success(
            data: $product->load('category'),
            message: 'Product created successfully.',
            code: 201
        );
    }

    public function update(ProductUpdateRequest $request, int $id): JsonResponse
    {
        $product = $this->productService->update($id, $request->validated());

        return $this->success(
            data: $product,
            message: 'Product updated successfully.'
        );
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productService->delete($id);

        return $this->success(
            data: null,
            message: 'Product deleted successfully.'
        );
    }
}
