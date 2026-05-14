<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\VehicleController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/whoami', [AuthController::class, 'whoami']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/users', [UserController::class, 'index'])->middleware('permission:list-users');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create-user');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:update-user');
    Route::put('/users/{user}/reset-password', [UserController::class, 'resetPasswordToUser'])->middleware('permission:reset-password-user');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:view-user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-user');
    

    Route::get('/users/{user}/roles', [RoleController::class, 'listUserRoles'])->middleware('permission:view-user-roles');
    Route::put('/users/{user}/roles', [RoleController::class, 'syncUserRoles'])->middleware('permission:assign-user-roles');

    Route::get('/roles/{role}/permissions', [PermissionController::class, 'listRolePermissions'])->middleware('permission:view-role-permissions');
    Route::put('/roles/{role}/permissions', [PermissionController::class, 'syncRolePermissions'])->middleware('permission:assign-role-permissions');

    Route::get('/products', [ProductController::class, 'index'])->middleware('permission:list-products');
    Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('permission:view-product');
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:create-product');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:update-product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:delete-product');
    
    Route::get('/services', [ServiceController::class, 'index'])->middleware('permission:list-service');
    Route::get('/services/{service}', [ServiceController::class, 'show'])->middleware('permission:view-service');
    Route::post('/services', [ServiceController::class, 'store'])->middleware('permission:create-service');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->middleware('permission:update-service');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->middleware('permission:delete-service');

    Route::get('/vehicles', [VehicleController::class, 'index'])->middleware('permission:list-vehicle');
    Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->middleware('permission:view-vehicle');
    Route::post('/vehicles', [VehicleController::class, 'store'])->middleware('permission:create-vehicle');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->middleware('permission:update-vehicle');
    Route::delete('/vehicles/{vehicle}', [VehicleController::class, 'destroy'])->middleware('permission:delete-vehicle');

    Route::get('/clients', [ClientController::class, 'index'])->middleware('permission:list-client');
    Route::get('/clients/{client}', [ClientController::class, 'show'])->middleware('permission:view-client');
    Route::post('/clients', [ClientController::class, 'store'])->middleware('permission:create-client');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->middleware('permission:update-client');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->middleware('permission:delete-client');

    Route::get('/people', [PersonController::class, 'index'])->middleware('permission:list-person');
    Route::get('/people/{person}', [PersonController::class, 'show'])->middleware('permission:view-person');
    Route::post('/people', [PersonController::class, 'store'])->middleware('permission:create-person');
    Route::put('/people/{person}', [PersonController::class, 'update'])->middleware('permission:update-person');
    Route::delete('/people/{person}', [PersonController::class, 'destroy'])->middleware('permission:delete-person');
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello, API!']);
});

