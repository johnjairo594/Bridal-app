<?php

namespace App\Repositories;

use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;

class PersonRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = Person::query();

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('full_name', 'like', "%{$filter}%")
                        ->orWhere('identification', 'like', "%{$filter}%")
                        ->orWhere('phone', 'like', "%{$filter}%")
                        ->orWhere('address', 'like', "%{$filter}%");
            });
        }

        $query->orderBy('full_name', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Person
    {
        return Person::find($id);
    }

    public function findByIdentification(string $identification): ?Person
    {
        return Person::where('identification', $identification)->first();
    }

    public function createPerson(array $data): Person
    {
        return Person::create($data);
    }
    
    public function updatePerson(Person $person, array $data): void
    {
        $person->update($data);
    }
    
    public function deactivatePerson(Person $person): void
    {
        $person->delete();
    }
}
