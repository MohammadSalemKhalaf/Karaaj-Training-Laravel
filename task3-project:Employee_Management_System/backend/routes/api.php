<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\User\UserController;
use App\Http\Middleware\EnsureAdminRole;
use App\Http\Middleware\EnsureAdminOrManagerRole;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:auth-login');

    Route::middleware('auth:api')->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware(['auth:api', EnsureAdminRole::class])->prefix('users')->group(function (): void {
    Route::get('/', [UserController::class, 'index']);
    Route::get('/{id}', [UserController::class, 'show'])->whereUuid('id');
    Route::post('/', [UserController::class, 'store']);
    Route::put('/{id}', [UserController::class, 'update'])->whereUuid('id');
    Route::delete('/{id}', [UserController::class, 'destroy'])->whereUuid('id');
});

Route::middleware(['auth:api', EnsureAdminOrManagerRole::class])
    ->get('/users/available-for-employee', [UserController::class, 'availableForEmployee']);

Route::middleware(['auth:api', EnsureAdminOrManagerRole::class])->prefix('employees')->group(function (): void {
    Route::get('/', [EmployeeController::class, 'index']);
    Route::get('/{id}', [EmployeeController::class, 'show'])->whereUuid('id');
    Route::post('/', [EmployeeController::class, 'store']);
    Route::put('/{id}', [EmployeeController::class, 'update'])->whereUuid('id');
    Route::delete('/{id}', [EmployeeController::class, 'destroy'])->whereUuid('id');
});
