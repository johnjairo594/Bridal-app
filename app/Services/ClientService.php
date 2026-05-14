<?php

namespace App\Services;

use App\Repositories\ClientRepository;
use App\Models\Client;
use App\Repositories\PersonRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClientService
{
    protected ClientRepository $repository;
    protected PersonService $personService;

    public function __construct(ClientRepository $repository, PersonService $personService)
    {
        $this->repository = $repository;
        $this->personService = $personService;
    }

    public function listClients(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $perPage, $order);
    }

    public function getClient(int $id)
    {
        $client = $this->repository->findById($id);

        if (! $client) {
            throw new ModelNotFoundException('Client not found');
        }

        return $client;
    }

    public function createClient(array $data)
    {
        if (is_null($data['person_id'])) {
            return $this->repository->createClient($data);
        } else {
            $person = $this->personService->createPerson($data['person']);
            $data['person_id'] = $person->id;
            return $this->repository->createClient($data);
        }
    }

    public function updateClient(Client $client, array $data)
    {
        $this->repository->updateClient($client, $data);

        return $client->fresh();
    }

    public function deleteClient(int $id)
    {
        $client = $this->repository->findById($id);

        if (! $client) {
            throw new ModelNotFoundException('Client not found');
        }

        $this->repository->deactivateClient($client);

        return true;
    }
}
