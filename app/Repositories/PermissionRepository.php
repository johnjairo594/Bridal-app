<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionRepository
{
    public function findRoleByIdWithPermissions(int $roleId): ?Role
    {
        return Role::with('permissions')->find($roleId);
    }

    public function syncRolePermissions(Role $role, array $permissions): void
    {
        $role->syncPermissions($permissions);
    }
}
