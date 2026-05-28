<?php

namespace App\Http\Controllers;

use App\Services\WorkOrderService;
use App\Exceptions\ConflictException;
use App\Models\WorkOrder;
use App\Models\WorkOrderProduct;
use App\Models\WorkOrderService as ModelsWorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    protected WorkOrderService $service;

    public function __construct(WorkOrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $result = $this->service->listWorkOrders($request->all());

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
            $workOrder = $this->service->getWorkOrder($id);

            return response()->json($workOrder);
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
                'client_id' => 'nullable|exists:clients,id',
                'vehicle_id' => 'required|exists:vehicles,id',
                'diagnosis' => 'nullable|string',
                'repair_notes' => 'nullable|string',
            ]);

            $workOrder = $this->service->createWorkOrder($payload);
            DB::commit();
            return response()->json($workOrder, 201);
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

    public function update(Request $request, WorkOrder $workOrder): JsonResponse
    {
        try {
            $payload = $request->validate([
                'client_id' => 'sometimes|required|integer|exists:clients,id',
                'vehicle_id' => 'sometimes|required|integer|exists:vehicles,id',
                'repair_notes' => 'sometimes|nullable|string',
                'diagnosis' => 'sometimes|nullable|string',
                'status' => 'sometimes|required|in:PROCESO,FINALIZADO',
            ]);

            DB::beginTransaction();
            $updatedWorkOrder = $this->service->updateWorkOrder($workOrder, $payload);
            DB::commit();

            return response()->json($updatedWorkOrder);
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
            $this->service->deleteWorkOrder($id);

            return response()->json(['message' => 'Orden de trabajo desactivado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function storeProduct(Request $request, WorkOrder $workOrder): JsonResponse
    {
        try {
            $payload = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();
            $this->service->addProductToWorkOrder($workOrder, $payload['product_id'], $payload['quantity']);
            DB::commit();

            return response()->json(['message' => 'Producto agregado a la orden de trabajo']);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function destroyProduct(Request $request, WorkOrder $workOrder, WorkOrderProduct $workOrderProduct): JsonResponse
    {
        try {
            DB::beginTransaction();
            $this->service->removeProductFromWorkOrder($workOrder, $workOrderProduct);
            DB::commit();

            return response()->json(['message' => 'Producto eliminado de la orden de trabajo']);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function storeService(Request $request, WorkOrder $workOrder): JsonResponse
    {
        try {
            $payload = $request->validate([
                'service_id' => 'required|exists:services,id',
                'quantity' => 'required|integer|min:1',
            ]);

            DB::beginTransaction();
            $this->service->addServiceToWorkOrder($workOrder, $payload['service_id']);
            DB::commit();

            return response()->json(['message' => 'Servicio agregado a la orden de trabajo']);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function destroyService(Request $request, WorkOrder $workOrder, ModelsWorkOrderService $workOrderService): JsonResponse
    {
        try {
            DB::beginTransaction();
            $this->service->removeServiceFromWorkOrder($workOrder, $workOrderService);
            DB::commit();

            return response()->json(['message' => 'Servicio eliminado de la orden de trabajo']);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

}
