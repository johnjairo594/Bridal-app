<?php

namespace App\Repositories;

use App\Models\Product;
use App\Models\Person;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function paginate(?string $filter = null, int $perPage = 15, string $order = 'asc'): LengthAwarePaginator
    {
        $query = Product::query();

        if ($filter) {
            $query->where(function ($builder) use ($filter) {
                $builder->where('name', 'like', "%{$filter}%");
            });
        }

        $query->orderBy('name', $order);

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return Product::find($id);
    }

    public function createProduct(array $data): Product
    {
        return Product::create($data);
    }
    
    public function updateProduct(Product $product, array $data): void
    {
        $product->update($data);
    }
    
    public function deactivateProduct(Product $product): void
    {
        $product->delete();
    }
}
