<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use App\Exceptions\ConflictException;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    protected ProductService $service;

    public function __construct(ProductService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['filter', 'order', 'per_page']);

            $result = $this->service->listProducts($params);

            return response()->json($result);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $product = $this->service->getProduct($id);

            return response()->json($product);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();
            $payload = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
            ]);

            $product = $this->service->createProduct($payload);
            DB::commit();
            return response()->json($product, 201);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        try {
            $payload = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'price' => 'sometimes|required|numeric',
                'stock' => 'sometimes|required|integer',
            ]);

            DB::beginTransaction();
            $updatedProduct = $this->service->updateProduct($product, $payload);
            DB::commit();

            return response()->json($updatedProduct);
        } catch (ConflictException $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error del servidor'], 500);
        }

    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->deleteProduct($id);

            return response()->json(['message' => 'Producto desactivado correctamente']);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'No encontrado'], 404);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }
}
