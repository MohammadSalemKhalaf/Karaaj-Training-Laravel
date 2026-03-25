<?php

namespace App\Http\Controllers\Api\Salary;

use App\Http\Controllers\Controller;
use App\Http\Requests\Salary\StoreSalaryRequest;
use App\Http\Requests\Salary\UpdateSalaryRequest;
use App\Http\Resources\Salary\SalaryResource;
use App\Services\Salary\SalaryService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function __construct(private readonly SalaryService $salaryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $salaries = $this->salaryService->getSalaries($request->only([
            'employee_id',
            'from_date',
            'to_date',
            'per_page',
        ]));

        return ApiResponse::success(
            'Salaries fetched successfully.',
            [
                'salaries' => SalaryResource::collection($salaries->items()),
            ],
            [
                'current_page' => $salaries->currentPage(),
                'last_page' => $salaries->lastPage(),
                'per_page' => $salaries->perPage(),
                'total' => $salaries->total(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $salary = $this->salaryService->getSalaryById($id);

        if (! $salary) {
            return ApiResponse::error(
                'Salary record not found.',
                ['salary' => ['The requested salary record does not exist.']],
                'SALARY_NOT_FOUND',
                404
            );
        }

        return ApiResponse::success(
            'Salary record fetched successfully.',
            ['salary' => SalaryResource::make($salary)]
        );
    }

    public function store(StoreSalaryRequest $request): JsonResponse
    {
        $salary = $this->salaryService->createSalary($request->validated());

        return ApiResponse::success(
            'Salary record created successfully.',
            ['salary' => SalaryResource::make($salary)],
            [],
            201
        );
    }

    public function update(UpdateSalaryRequest $request, string $id): JsonResponse
    {
        $salary = $this->salaryService->getSalaryById($id);

        if (! $salary) {
            return ApiResponse::error(
                'Salary record not found.',
                ['salary' => ['The requested salary record does not exist.']],
                'SALARY_NOT_FOUND',
                404
            );
        }

        $updatedSalary = $this->salaryService->updateSalary($salary, $request->validated());

        return ApiResponse::success(
            'Salary record updated successfully.',
            ['salary' => SalaryResource::make($updatedSalary)]
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $salary = $this->salaryService->getSalaryById($id);

        if (! $salary) {
            return ApiResponse::error(
                'Salary record not found.',
                ['salary' => ['The requested salary record does not exist.']],
                'SALARY_NOT_FOUND',
                404
            );
        }

        $this->salaryService->deleteSalary($salary);

        return ApiResponse::success('Salary record deleted successfully.');
    }

    public function employeeSalaries(Request $request, string $id): JsonResponse
    {
        $salaries = $this->salaryService->getEmployeeSalaries(
            $id,
            (int) $request->integer('per_page', 10)
        );

        return ApiResponse::success(
            'Employee salary history fetched successfully.',
            [
                'salaries' => SalaryResource::collection($salaries->items()),
            ],
            [
                'current_page' => $salaries->currentPage(),
                'last_page' => $salaries->lastPage(),
                'per_page' => $salaries->perPage(),
                'total' => $salaries->total(),
            ]
        );
    }
}
