<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository
{
    public function findUserByIdWithRoles(int $userId): ?User
    {
        return User::with('roles')->find($userId);
    }

    public function getRolesByIds(array $roleIds): Collection
    {
        return Role::whereIn('id', $roleIds)->get();
    }

    public function syncUserRoles(User $user, array $roles): void
    {
        $user->syncRoles($roles);
    }
}
