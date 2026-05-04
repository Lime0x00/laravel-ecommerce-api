<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     * Get all records.
     */
    public function all(): Collection;

    /**
     * Find a record by ID.
     */
    public function find(int|string $id): ?Model;

    /**
     * Create a new record.
     */
    public function create(array $attributes): Model;

    /**
     * Update an existing record.
     */
    public function update(int|string $id, array $attributes): bool;

    /**
     * Delete a record.
     */
    public function delete(int|string $id): bool;
}
