<?php

namespace App\Services;

use App\Repositories\ServiceRepository;
use App\Exceptions\ConflictException;
use App\Models\Service;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ServiceService
{
    protected ServiceRepository $repository;

    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listServices(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $perPage, $order);
    }

    public function getService(int $id)
    {
        $service = $this->repository->findById($id);

        if (! $service) {
            throw new ModelNotFoundException('Service not found');
        }

        return $service;
    }

    public function createService(array $data)
    {
        return $this->repository->createService($data);
    }

    public function updateService(Service $service, array $data)
    {
        $this->repository->updateService($service, $data);

        return $service->fresh();
    }

    public function deleteService(int $id)
    {
        $service = $this->repository->findById($id);

        if (! $service) {
            throw new ModelNotFoundException('Service not found');
        }

        $this->repository->deactivateService($service);

        return true;
    }
}
