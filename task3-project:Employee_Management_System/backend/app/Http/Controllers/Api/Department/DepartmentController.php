<?php

namespace App\Http\Controllers\Api\Department;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\Department\DepartmentResource;
use App\Http\Resources\Employee\EmployeeResource;
use App\Services\Department\DepartmentService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function __construct(private readonly DepartmentService $departmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $departments = $this->departmentService->getDepartments($request->only([
            'search',
            'status',
            'per_page',
        ]));

        return ApiResponse::success(
            'Departments fetched successfully.',
            [
                'departments' => DepartmentResource::collection($departments->items()),
            ],
            [
                'current_page' => $departments->currentPage(),
                'last_page' => $departments->lastPage(),
                'per_page' => $departments->perPage(),
                'total' => $departments->total(),
            ]
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $departmentPayload = $this->departmentService->getDepartmentById(
            $id,
            (int) $request->integer('employees_per_page', 10)
        );

        if (! $departmentPayload) {
            return ApiResponse::error(
                'Department not found.',
                ['department' => ['The requested department does not exist.']],
                'DEPARTMENT_NOT_FOUND',
                404
            );
        }

        $department = $departmentPayload['department'];
        $employees = $departmentPayload['employees'];

        return ApiResponse::success(
            'Department fetched successfully.',
            [
                'department' => DepartmentResource::make($department),
                'employees' => EmployeeResource::collection($employees->items()),
            ],
            [
                'employees_current_page' => $employees->currentPage(),
                'employees_last_page' => $employees->lastPage(),
                'employees_per_page' => $employees->perPage(),
                'employees_total' => $employees->total(),
            ]
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $department = $this->departmentService->createDepartment($request->validated());

        return ApiResponse::success(
            'Department created successfully.',
            ['department' => DepartmentResource::make($department)],
            [],
            201
        );
    }

    public function update(UpdateDepartmentRequest $request, string $id): JsonResponse
    {
        $departmentPayload = $this->departmentService->getDepartmentById($id);

        if (! $departmentPayload) {
            return ApiResponse::error(
                'Department not found.',
                ['department' => ['The requested department does not exist.']],
                'DEPARTMENT_NOT_FOUND',
                404
            );
        }

        $updatedDepartment = $this->departmentService->updateDepartment(
            $departmentPayload['department'],
            $request->validated()
        );

        return ApiResponse::success(
            'Department updated successfully.',
            ['department' => DepartmentResource::make($updatedDepartment)]
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $departmentPayload = $this->departmentService->getDepartmentById($id);

        if (! $departmentPayload) {
            return ApiResponse::error(
                'Department not found.',
                ['department' => ['The requested department does not exist.']],
                'DEPARTMENT_NOT_FOUND',
                404
            );
        }

        $this->departmentService->deleteDepartment($departmentPayload['department']);

        return ApiResponse::success('Department deleted successfully.');
    }
}
