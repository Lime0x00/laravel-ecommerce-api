<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {}

    public function getCatalog(
        int $perPage = 15,
        ?string $categorySlug = null,
        ?string $search = null
    ): LengthAwarePaginator {
        return $this->productRepository->getPaginatedCatalog(
            perPage: $perPage,
            categorySlug: $categorySlug,
            search: $search
        );
    }

    public function findById(int $id): Product
    {
        $product = $this->productRepository->findWithCategory($id);

        if (!$product instanceof Product) {
            abort(404, 'Product not found.');
        }

        return $product;
    }

    public function create(array $payload): Product
    {
        return $this->productRepository->create($payload);
    }

    public function update(int $id, array $payload): Product
    {
        $updated = $this->productRepository->update($id, $payload);

        if (!$updated) {
            abort(404, 'Product not found.');
        }

        return $this->findById($id);
    }

    public function delete(int $id): void
    {
        $deleted = $this->productRepository->delete($id);

        if (!$deleted) {
            abort(404, 'Product not found.');
        }
    }
}
