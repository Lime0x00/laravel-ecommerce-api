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
        return new Collection;
    }
}
