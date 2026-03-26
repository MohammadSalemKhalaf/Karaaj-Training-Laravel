<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Attendance\AttendanceController;
use App\Http\Controllers\Api\Department\DepartmentController;
use App\Http\Controllers\Api\Employee\EmployeeController;
use App\Http\Controllers\Api\Leave\LeaveController;
use App\Http\Controllers\Api\Salary\SalaryController;
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

Route::middleware(['auth:api', EnsureAdminOrManagerRole::class])->prefix('departments')->group(function (): void {
    Route::get('/', [DepartmentController::class, 'index']);
    Route::get('/{id}', [DepartmentController::class, 'show'])->whereUuid('id');
    Route::post('/', [DepartmentController::class, 'store']);
    Route::put('/{id}', [DepartmentController::class, 'update'])->whereUuid('id');
    Route::delete('/{id}', [DepartmentController::class, 'destroy'])->whereUuid('id');
});

Route::middleware(['auth:api', EnsureAdminOrManagerRole::class])->prefix('salaries')->group(function (): void {
    Route::get('/', [SalaryController::class, 'index']);
    Route::get('/{id}', [SalaryController::class, 'show'])->whereUuid('id');
    Route::post('/', [SalaryController::class, 'store']);
    Route::put('/{id}', [SalaryController::class, 'update'])->whereUuid('id');
    Route::delete('/{id}', [SalaryController::class, 'destroy'])->whereUuid('id');
});

Route::middleware(['auth:api', EnsureAdminOrManagerRole::class])
    ->get('/employees/{id}/salaries', [SalaryController::class, 'employeeSalaries'])
    ->whereUuid('id');

Route::middleware('auth:api')->prefix('leaves')->group(function (): void {
    Route::get('/', [LeaveController::class, 'index']);
    Route::post('/', [LeaveController::class, 'store']);
    Route::get('/{id}', [LeaveController::class, 'show'])->whereUuid('id');
    Route::put('/{id}', [LeaveController::class, 'update'])->whereUuid('id');
    Route::delete('/{id}', [LeaveController::class, 'destroy'])->whereUuid('id');
    Route::post('/{id}/approve', [LeaveController::class, 'approve'])->whereUuid('id');
    Route::post('/{id}/reject', [LeaveController::class, 'reject'])->whereUuid('id');
});

Route::middleware('auth:api')->prefix('attendance')->group(function (): void {
    Route::post('/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/', [AttendanceController::class, 'index']);
    Route::get('/{id}', [AttendanceController::class, 'show'])->whereUuid('id');
});

Route::middleware('auth:api')
    ->get('/employees/{id}/attendance', [AttendanceController::class, 'employeeAttendance'])
    ->whereUuid('id');
