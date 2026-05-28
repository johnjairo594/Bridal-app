<?php

namespace App\Services;

use App\Repositories\UserRepository;
use App\Exceptions\ConflictException;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService
{
    protected UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listUsers(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $role = $params['role'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $role, $perPage, $order);
    }

    public function getUser(int $id)
    {
        $user = $this->repository->findById($id);

        if (! $user) {
            throw new ModelNotFoundException('User not found');
        }

        return $user;
    }

    public function createUser(array $data)
    {
        if (isset($data['email']) && $this->repository->findByEmail($data['email'])) {
            throw new ConflictException('El correo ya existe');
        }

        $personData = $data['person'] ?? null;

        if (! $personData || empty($personData['identification'])) {
            throw new ConflictException('La identificación de la persona es requerida');
        }

        return DB::transaction(function () use ($data, $personData) {
            $person = $this->repository->findPersonByIdentification($personData['identification']);

            if (! $person) {
                $person = $this->repository->createPerson($personData);
            }

            $userPayload = [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'person_id' => $person->id,
            ];

            return $this->repository->createUser($userPayload);
        });
    }

    public function updateUser(User $user, array $data)
    {
        $this->repository->updateUser($user, $data);

        return $user->fresh();
    }

    public function resetPasswordToUser(User $user)
    {
        $identification = $user->person?->identification;

        if (! $identification) {
            throw new ConflictException('El usuario no tiene una cédula asociada');
        }

        $this->repository->updateUser($user, [
            'password' => Hash::make($identification),
        ]);

        return $user->fresh();
    }

    public function deleteUser(int $id)
    {
        $user = $this->repository->findById($id);

        if (! $user) {
            throw new ModelNotFoundException('User not found');
        }

        $this->repository->deactivateUser($user);

        return true;
    }
}
