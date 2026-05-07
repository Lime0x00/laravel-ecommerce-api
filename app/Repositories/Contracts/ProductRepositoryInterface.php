<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retrieve all products that currently have stock greater than zero.
     */
    public function findAvailableProducts(): Collection;

    public function getPaginatedCatalog(
        int $perPage = 15,
        ?string $categorySlug = null,
        ?string $search = null
    ): LengthAwarePaginator;

    public function findWithCategory(int $id): ?Model;
}
