<?php

namespace App\Http\Controllers;

use App\Services\VehicleService;
use App\Exceptions\ConflictException;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    protected VehicleService $service;

    public function __construct(VehicleService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['filter', 'order', 'per_page']);

            $result = $this->service->listVehicles($params);

            return response()->json($result);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $vehicle = $this->service->getVehicle($id);

            return response()->json($vehicle);
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
                'client_id' => 'required|integer|exists:clients,id',
                'brand' => 'required|string|max:255',
                'model' => 'required|string|max:255',
                'year' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'plate' => 'required|string|max:20|unique:vehicles,plate',
                'mileage' => 'required|integer|min:0'
            ]);

            $vehicle = $this->service->createVehicle($payload);
            DB::commit();
            return response()->json($vehicle, 201);
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

    public function update(Request $request, Vehicle $vehicle): JsonResponse
    {
        try {
            $payload = $request->validate([
                'client_id' => 'sometimes|required|integer|exists:clients,id',
                'brand' => 'sometimes|required|string|max:255',
                'model' => 'sometimes|required|string|max:255',
                'year' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'plate' => 'sometimes|required|string|max:20|unique:vehicles,plate,' . $vehicle->id,
                'mileage' => 'sometimes|required|integer|min:0'
            ]);

            DB::beginTransaction();
            $updatedVehicle = $this->service->updateVehicle($vehicle, $payload);
            DB::commit();

            return response()->json($updatedVehicle);
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
            $this->service->deleteVehicle($id);

            return response()->json(['message' => 'Vehículo desactivado correctamente']);
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
