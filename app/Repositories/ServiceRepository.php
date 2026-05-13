<?php

namespace App\Repositories;

use App\Models\Service;
use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = Service::query();

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('name', 'like', "%{$filter}%");
            });
        }

        $query->orderBy('name', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Service
    {
        return Service::find($id);
    }

    public function createService(array $data): Service
    {
        return Service::create($data);
    }
    
    public function updateService(Service $service, array $data): void
    {
        $service->update($data);
    }
    
    public function deactivateService(Service $service): void
    {
        $service->delete();
    }
}
