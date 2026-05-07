<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a user by email.
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }
}
