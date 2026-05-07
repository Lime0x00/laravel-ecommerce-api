<?php

namespace App\Services;

use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    /**
     * Get paginated available products.
     */
    public function getAvailableProducts(int $perPage = 15): LengthAwarePaginator
    {
        $cacheKey = "products.available.{$perPage}";

        return Cache::remember($cacheKey, 300, function () use ($perPage) {
            return $this->productRepository->getPaginatedAvailableProducts($perPage);
        });
    }
}
