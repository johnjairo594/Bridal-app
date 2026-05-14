<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Repositories\PersonRepository;
use App\Models\Person;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PersonService
{
    protected PersonRepository $repository;

    public function __construct(PersonRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listPeople(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $perPage, $order);
    }

    public function getPerson(int $id)
    {
        $person = $this->repository->findById($id);

        if (! $person) {
            throw new ModelNotFoundException('Person not found');
        }

        return $person;
    }

    public function createPerson(array $data)
    {
        if(isset($data['identification']) && $this->repository->findByIdentification($data['identification'])) {
            throw new ConflictException('Ya existe una persona con esta identificación');
        }
        return $this->repository->createPerson($data);
    }

    public function updatePerson(Person $person, array $data)
    {
        $this->repository->updatePerson($person, $data);

        return $person->fresh();
    }

    public function deletePerson(int $id)
    {
        $person = $this->repository->findById($id);

        if (! $person) {
            throw new ModelNotFoundException('Person not found');
        }

        $this->repository->deactivatePerson($person);

        return true;
    }
}
