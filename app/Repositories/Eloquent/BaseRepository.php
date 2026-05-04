<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * The model instance.
     */
    protected Model $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all records.
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID.
     */
    public function find(int|string $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $attributes): Model
    {
        return $this->model->create($attributes);
    }

    /**
     * Update an existing record.
     */
    public function update(int|string $id, array $attributes): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->update($attributes);
    }

    /**
     * Delete a record.
     */
    public function delete(int|string $id): bool
    {
        $record = $this->find($id);

        if (!$record) {
            return false;
        }

        return $record->delete();
    }
}
