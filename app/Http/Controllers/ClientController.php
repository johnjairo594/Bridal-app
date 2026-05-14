<?php

namespace App\Http\Controllers;

use App\Services\ClientService;
use App\Exceptions\ConflictException;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    protected ClientService $service;

    public function __construct(ClientService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['filter', 'order', 'per_page']);

            $result = $this->service->listClients($params);

            return response()->json($result);
        } catch (ConflictException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\Exception $e) {
            dd($e);
            return response()->json(['message' => 'Error del servidor'], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $client = $this->service->getClient($id);

            return response()->json($client);
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
                'person_id' => 'nullable|exists:people,id',

                'person.full_name' => 'required_without:person_id|string|max:150',
                'person.identification' => 'required_without:person_id|string|unique:people,identification|max:20',
                'person.phone' => 'string|max:20',
                'person.address' => 'string|max:150',
                'person.birth_date' => 'date',
            ]);

            $client = $this->service->createClient($payload);
            DB::commit();
            return response()->json($client, 201);
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

    public function update(Request $request, Client $client): JsonResponse
    {
        try {
            $payload = $request->validate([
                'person_id' => 'sometimes|required|integer|exists:people,id',
            ]);

            DB::beginTransaction();
            $updatedClient = $this->service->updateClient($client, $payload);
            DB::commit();

            return response()->json($updatedClient);
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
            $this->service->deleteClient($id);

            return response()->json(['message' => 'Vehículo desactivado correctamente']);
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
