<?php

namespace App\Services;

use App\Repositories\VehicleRepository;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class VehicleService
{
    protected VehicleRepository $repository;

    public function __construct(VehicleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listVehicles(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $perPage, $order);
    }

    public function getVehicle(int $id)
    {
        $vehicle = $this->repository->findById($id);

        if (! $vehicle) {
            throw new ModelNotFoundException('Vehicle not found');
        }

        return $vehicle;
    }

    public function createVehicle(array $data)
    {
        return $this->repository->createVehicle($data);
    }

    public function updateVehicle(Vehicle $vehicle, array $data)
    {
        $this->repository->updateVehicle($vehicle, $data);

        return $vehicle->fresh();
    }

    public function deleteVehicle(int $id)
    {
        $vehicle = $this->repository->findById($id);

        if (! $vehicle) {
            throw new ModelNotFoundException('Vehicle not found');
        }

        $this->repository->deactivateVehicle($vehicle);

        return true;
    }
}
