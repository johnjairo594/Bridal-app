<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Exceptions\ConflictException;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class ProductService
{
    protected ProductRepository $repository;

    public function __construct(ProductRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listProducts(array $params = [])
    {
        $filter = $params['filter'] ?? null;
        $perPage = isset($params['per_page']) ? (int) $params['per_page'] : 15;
        $order = isset($params['order']) && strtolower($params['order']) === 'desc' ? 'desc' : 'asc';

        return $this->repository->paginate($filter, $perPage, $order);
    }

    public function getProduct(int $id)
    {
        $product = $this->repository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException('Product not found');
        }

        return $product;
    }

    public function createProduct(array $data)
    {
        return $this->repository->createProduct($data);
    }

    public function updateProduct(Product $product, array $data)
    {
        $this->repository->updateProduct($product, $data);

        return $product->fresh();
    }

    public function deleteProduct(int $id)
    {
        $product = $this->repository->findById($id);

        if (! $product) {
            throw new ModelNotFoundException('Product not found');
        }

        $this->repository->deactivateProduct($product);

        return true;
    }
}
