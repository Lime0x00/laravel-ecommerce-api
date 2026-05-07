<?php

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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

    /**
     * Get paginated available products with eager loading.
     */
    public function getPaginatedAvailableProducts(int $perPage = 15): mixed
    {
        return $this->model->with('category')
            ->where('stock', '>', 0)
            ->paginate($perPage);
    }
}
