<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * ProductRepository constructor.
     */
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    /**
     * Retrieve all products that currently have stock greater than zero.
     */
    public function findAvailableProducts(): Collection
    {
        return $this->model->where('stock', '>', 0)->get();
    }

    public function getPaginatedCatalog(
        int $perPage = 15,
        ?string $categorySlug = null,
        ?string $search = null
    ): LengthAwarePaginator {
        return $this->model->newQuery()
            ->with('category')
            ->where('stock', '>', 0)
            ->when($categorySlug, function ($query) use ($categorySlug) {
                $query->whereHas('category', function ($categoryQuery) use ($categorySlug) {
                    $categoryQuery->where('slug', $categorySlug);
                });
            })
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->latest()
            ->paginate($perPage);
    }

    public function findWithCategory(int $id): ?Model
    {
        return $this->model->newQuery()
            ->with('category')
            ->find($id);
    }
}
