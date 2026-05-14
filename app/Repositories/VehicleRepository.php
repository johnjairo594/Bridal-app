<?php

namespace App\Repositories;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;

class VehicleRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = Vehicle::with('client');

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('model', 'like', "%{$filter}%")
                        ->orWhere('brand', 'like', "%{$filter}%")
                        ->orWhere('plate', 'like', "%{$filter}%")
                        ->orWhere('year', 'like', "%{$filter}%");
            });
        }

        $query->orderBy('model', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Vehicle
    {
        return Vehicle::with('client')->find($id);
    }

    public function createVehicle(array $data): Vehicle
    {
        return Vehicle::create($data);
    }
    
    public function updateVehicle(Vehicle $vehicle, array $data): void
    {
        $vehicle->update($data);
    }
    
    public function deactivateVehicle(Vehicle $vehicle): void
    {
        $vehicle->delete();
    }
}
