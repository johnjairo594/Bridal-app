<?php

namespace App\Repositories;

use App\Models\WorkOrder;
use Illuminate\Pagination\LengthAwarePaginator;

class WorkOrderRepository
{
    public function paginate(?string $filter = null, ?int $clientId = null, ?int $vehicleId = null, ?int $mechanicId = null, ?string $status = null, ?string $entryDateFrom = null, ?string $entryDateTo = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = WorkOrder::with('client', 'vehicle', 'mechanic', 'workOrderProducts', 'workOrderServices');

        if($clientId) {
            $query->where('client_id', $clientId);
        }

        if($vehicleId) {
            $query->where('vehicle_id', $vehicleId);
        }

        if($mechanicId) {
            $query->where('mechanic_id', $mechanicId);
        }

        if($status) {
            $query->where('status', $status);
        }

        if($entryDateFrom) {
            $query->where('entry_date', '>=', $entryDateFrom);
        }

        if($entryDateTo) {
            $query->where('entry_date', '<=', $entryDateTo);
        }

        if($filter) {
            $query->where(function ($q) use ($filter) {
                $q->where('diagnosis', 'like', "%$filter%")
                    ->orWhere('repair_notes', 'like', "%$filter%");
            });
        }

        $query->orderBy('work_orders.id', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?WorkOrder
    {
        return WorkOrder::with('client', 'vehicle', 'mechanic', 'workOrderProducts', 'workOrderServices')->find($id);
    }

    public function createWorkOrder(array $data): WorkOrder
    {
        return WorkOrder::create($data);
    }
    
    public function updateWorkOrder(WorkOrder $workOrder, array $data): void
    {
        $workOrder->update($data);
    }
    
    public function deactivateWorkOrder(WorkOrder $workOrder): void
    {
        $workOrder->delete();
    }
}
