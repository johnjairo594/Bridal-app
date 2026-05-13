<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;

class UserRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = User::with('person');

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('name', 'like', "%{$filter}%")
                    ->orWhereHas('person', function ($queryBuilder) use ($filter) {
                        $queryBuilder->where('full_name', 'like', "%{$filter}%");
                    });
            });
        }

        $query->orderBy('name', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?User
    {
        return User::with('person')->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    public function findPersonByIdentification(string $identification): ?Person
    {
        return Person::where('identification', $identification)->first();
    }

    public function createPerson(array $data): Person
    {
        return Person::create($data);
    }

    public function createUser(array $data): User
    {
        return User::create($data);
    }
    
    public function updateUser(User $user, array $data): void
    {
        $user->update($data);
    }
    
    public function deactivateUser(User $user): void
    {
        $user->delete();
    }
}
