<?php

namespace App\Http\Controllers;

use App\Services\ServiceService;
use App\Exceptions\ConflictException;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    protected ServiceService $service;

    public function __construct(ServiceService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['filter', 'order', 'per_page']);

            $result = $this->service->listServices($params);

            return response()->json($result);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $service = $this->service->getService($id);

            return response()->json($service);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $payload = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric'
            ]);

            $service = $this->service->createService($payload);
            DB::commit();
            return response()->json($service, 201);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        try {
            $payload = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric'
            ]);

            DB::beginTransaction();
            $updatedService = $this->service->updateService($service, $payload);
            DB::commit();

            return response()->json($updatedService);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }

    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteService($id);

            return response()->json(['message' => 'Servicio desactivado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }
}
