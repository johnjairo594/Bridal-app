<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Repositories\PermissionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Spatie\Permission\Models\Role;

class PermissionService
{
    protected PermissionRepository $repository;

    public function __construct(PermissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listRolePermissions(int $roleId)
    {
        $role = $this->repository->findRoleByIdWithPermissions($roleId);

        if (! $role) {
            throw new ModelNotFoundException('Role not found');
        }

        return [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'permissions' => $role->permissions,
        ];
    }

    public function syncRolePermissions(Role $role, array $permissionNames)
    {
        $this->repository->syncRolePermissions($role, $permissionNames);

        return $this->listRolePermissions($role->id);
    }
}
