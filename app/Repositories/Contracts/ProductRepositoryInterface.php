<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Retrieve all products that currently have stock greater than zero.
     */
    public function findAvailableProducts(): Collection;
}
