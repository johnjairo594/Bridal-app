<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Models\Product;
use App\Repositories\WorkOrderRepository;
use App\Models\WorkOrder;
use App\Models\WorkOrderProduct;
use App\Models\WorkOrderService as ModelsWorkOrderService;
use App\Repositories\PersonRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class WorkOrderService
{
    protected WorkOrderRepository $repository;

    public function __construct(WorkOrderRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listWorkOrders(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $clientId = $params['client_id'] ?? null;
        $vehicleId = $params['vehicle_id'] ?? null;
        $mechanicId = $params['mechanic_id'] ?? null;
        $status = $params['status'] ?? null;
        $entryDateFrom = $params['entry_date_from'] ?? null;
        $entryDateTo = $params['entry_date_to'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $clientId, $vehicleId, $mechanicId, $status, $entryDateFrom, $entryDateTo, $perPage, $order);
    }

    public function getWorkOrder(int $id)
    {
        $workOrder = $this->repository->findById($id);

        if (! $workOrder) {
            throw new ModelNotFoundException('WorkOrder not found');
        }

        $workOrder->total = $workOrder->total;

        return $workOrder;
    }

    public function createWorkOrder(array $data)
    {
        return $this->repository->createWorkOrder([
            'client_id' => $data['client_id'],
            'vehicle_id' => $data['vehicle_id'],
            'status' => 'PROCESO',
            'mechanic_id' => Auth::check() ? Auth::id() : null,
            'diagnosis' => $data['diagnosis'] ?? null,
            'repair_notes' => $data['repair_notes'] ?? null,
            'entry_date' => now(),
        ]);
    }

    public function updateWorkOrder(WorkOrder $workOrder, array $data)
    {
        if($workOrder->status === 'FINALIZADO') {
            throw new ConflictException('No se pueden modificar órdenes de trabajo finalizadas');
        }

        $user = Auth::check() ? Auth::user() : null;
        $roleName = $user?->role->name ?? null;

        if ($roleName !== 'SUPERADMIN' && $user?->id !== $workOrder->mechanic_id) {
            throw new ConflictException('No tienes permisos para modificar esta orden de trabajo');
        }

        if ($data['status'] ?? null === 'FINALIZADO' && $data['repair_notes'] === null) {
            throw new ConflictException('No se puede finalizar una orden de trabajo sin notas de reparación');
        }

        $this->repository->updateWorkOrder($workOrder, $data);

        return $workOrder->fresh();
    }

    public function deleteWorkOrder(int $id)
    {
        $workOrder = $this->repository->findById($id);

        if (! $workOrder) {
            throw new ModelNotFoundException('WorkOrder not found');
        }

        $this->repository->deactivateWorkOrder($workOrder);

        return true;
    }

    public function addProductToWorkOrder(WorkOrder $workOrder, int $productId, int $quantity)
    {
        if($workOrder->status === 'FINALIZADO') {
            throw new ConflictException('No se pueden modificar órdenes de trabajo finalizadas');
        }

        $product = Product::find($productId);

        if (! $product) {
            throw new ModelNotFoundException('Product not found');
        }

        if ($product->stock < $quantity) {
            throw new ConflictException('No hay stock suficiente para agregar el producto. Existen ' . $product->stock . ' unidades disponibles.');
        }

        $workOrder->workOrderProducts()->create([
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);

        $product->decrement('stock', $quantity);

        return $workOrder->fresh();
    }

    public function removeProductFromWorkOrder(WorkOrder $workOrder, WorkOrderProduct $workOrderProduct)
    {
        if($workOrder->status === 'FINALIZADO') {
            throw new ConflictException('No se pueden modificar órdenes de trabajo finalizadas');
        }

        $product = $workOrderProduct->product;

        $product->increment('stock', $workOrderProduct->quantity);

        $workOrderProduct->delete();

        return $workOrder->fresh();
    }

    public function addServiceToWorkOrder(WorkOrder $workOrder, int $serviceId)
    {
        if($workOrder->status === 'FINALIZADO') {
            throw new ConflictException('No se pueden modificar órdenes de trabajo finalizadas');
        }

        $workOrder->workOrderServices()->create([
            'service_id' => $serviceId,
        ]);

        return $workOrder->fresh();
    }

    public function removeServiceFromWorkOrder(WorkOrder $workOrder, ModelsWorkOrderService $workOrderService)
    {
        if($workOrder->status === 'FINALIZADO') {
            throw new ConflictException('No se pueden modificar órdenes de trabajo finalizadas');
        }

        $workOrderService->delete();

        return $workOrder->fresh();
    }
}
