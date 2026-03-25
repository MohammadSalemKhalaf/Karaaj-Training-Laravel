<?php

namespace App\Http\Controllers\Api\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\Employee\EmployeeResource;
use App\Services\Employee\EmployeeService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function __construct(private readonly EmployeeService $employeeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $employees = $this->employeeService->getEmployees($request->only([
            'search',
            'department_id',
            'status',
            'employment_type',
            'per_page',
        ]));

        return ApiResponse::success(
            'Employees fetched successfully.',
            [
                'employees' => EmployeeResource::collection($employees->items()),
            ],
            [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeById($id);

        if (! $employee) {
            return ApiResponse::error(
                'Employee not found.',
                ['employee' => ['The requested employee does not exist.']],
                'EMPLOYEE_NOT_FOUND',
                404
            );
        }

        return ApiResponse::success(
            'Employee fetched successfully.',
            ['employee' => EmployeeResource::make($employee)]
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $employee = $this->employeeService->createEmployee($request->validated());

        return ApiResponse::success(
            'Employee created successfully.',
            ['employee' => EmployeeResource::make($employee)],
            [],
            201
        );
    }

    public function update(UpdateEmployeeRequest $request, string $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeById($id);

        if (! $employee) {
            return ApiResponse::error(
                'Employee not found.',
                ['employee' => ['The requested employee does not exist.']],
                'EMPLOYEE_NOT_FOUND',
                404
            );
        }

        $updatedEmployee = $this->employeeService->updateEmployee($employee, $request->validated());

        return ApiResponse::success(
            'Employee updated successfully.',
            ['employee' => EmployeeResource::make($updatedEmployee)]
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $employee = $this->employeeService->getEmployeeById($id);

        if (! $employee) {
            return ApiResponse::error(
                'Employee not found.',
                ['employee' => ['The requested employee does not exist.']],
                'EMPLOYEE_NOT_FOUND',
                404
            );
        }

        $this->employeeService->deleteEmployee($employee);

        return ApiResponse::success('Employee deleted successfully.');
    }
}
