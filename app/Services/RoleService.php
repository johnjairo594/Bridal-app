<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\User;
use App\Repositories\RoleRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleService
{
    protected RoleRepository $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listUserRoles(int $userId)
    {
        $user = $this->repository->findUserByIdWithRoles($userId);

        if (! $user) {
            throw new ModelNotFoundException('User not found');
        }

        return [
            'user_id' => $user->id,
            'roles' => $user->roles,
        ];
    }

    public function syncUserRoles(User $user, array $roles)
    {
        $this->repository->syncUserRoles($user, $roles);

        return $this->listUserRoles($user->id);
    }
}
