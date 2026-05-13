<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    protected PermissionService $service;

    public function __construct(PermissionService $service)
    {
        $this->service = $service;
    }

    public function listRolePermissions(int $id): JsonResponse
    {
        try {
            $result = $this->service->listRolePermissions($id);

            return response()->json($result);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function syncRolePermissions(Request $request, Role $role): JsonResponse
    {
        try {
            $payload = $request->validate([
                'permissions' => 'required|array',
                'permissions.*' => 'integer|distinct|exists:permissions,id',
            ]);

            $result = $this->service->syncRolePermissions($role, $payload['permissions']);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }
}
