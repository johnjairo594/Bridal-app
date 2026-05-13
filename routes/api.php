<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;

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
});

Route::get('/hello', function () {
    return response()->json(['message' => 'Hello, API!']);
});

