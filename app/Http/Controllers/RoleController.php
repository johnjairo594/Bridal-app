<?php

namespace App\Http\Controllers;

use App\Exceptions\ConflictException;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    protected RoleService $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
    }

    public function listUserRoles(int $id): JsonResponse
    {
        try {
            $result = $this->service->listUserRoles($id);

            return response()->json($result);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function syncUserRoles(Request $request, User $user): JsonResponse
    {
        try {
            $payload = $request->validate([
                'roles' => 'required|array',
                'roles.*' => 'integer|distinct|exists:roles,id',
            ]);

            $result = $this->service->syncUserRoles($user, $payload['roles']);

            return response()->json($result);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }
}
