<?php

namespace App\Repositories;

use App\Models\Client;
use Illuminate\Pagination\LengthAwarePaginator;

class ClientRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = Client::join('people as person', 'clients.person_id', '=', 'person.id')
                        ->select('clients.*')
                        ->with('person');

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('person.full_name', 'like', "%{$filter}%")
                        ->orWhere('person.identification', 'like', "%{$filter}%")
                        ->orWhere('person.phone', 'like', "%{$filter}%")
                        ->orWhere('person.address', 'like', "%{$filter}%");
            });
        }

        $query->orderBy('person.full_name', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Client
    {
        return Client::with('person','vehicles')->find($id);
    }

    public function findByIdentification(string $identification): ?Client
    {
        return Client::with('person','vehicles')->whereHas('person', function ($query) use ($identification) {
            $query->where('identification', $identification);
        })->first();
    }

    public function createClient(array $data): Client
    {
        return Client::create($data);
    }
    
    public function updateClient(Client $client, array $data): void
    {
        $client->update($data);
    }
    
    public function deactivateClient(Client $client): void
    {
        $client->delete();
    }
}
