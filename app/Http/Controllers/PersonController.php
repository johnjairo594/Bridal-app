<?php

namespace App\Http\Controllers;

use App\Services\PersonService;
use App\Exceptions\ConflictException;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
{
    protected PersonService $service;

    public function __construct(PersonService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['filter', 'order', 'per_page']);

            $result = $this->service->listPeople($params);

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
            $person = $this->service->getPerson($id);

            return response()->json($person);
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
                'full_name' => 'required:string|max:150',
                'identification' => 'required:string|unique:people,identification|max:20',
                'phone' => 'string|max:20',
                'address' => 'string|max:150',
                'birth_date' => 'date',
            ]);

            $person = $this->service->createPerson($payload);
            DB::commit();
            return response()->json($person, 201);
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

    public function update(Request $request, Person $person): JsonResponse
    {
        try {
            $payload = $request->validate([
                'full_name' => 'sometimes|required|string|max:150',
                'identification' => 'sometimes|required|string|unique:people,identification|max:20',
                'phone' => 'sometimes|string|max:20',
                'address' => 'sometimes|string|max:150',
                'birth_date' => 'sometimes|date',
            ]);

            DB::beginTransaction();
            $updatedPerson = $this->service->updatePerson($person, $payload);
            DB::commit();

            return response()->json($updatedPerson);
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
            $this->service->deletePerson($id);

            return response()->json(['message' => 'Persona desactivada correctamente']);
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
