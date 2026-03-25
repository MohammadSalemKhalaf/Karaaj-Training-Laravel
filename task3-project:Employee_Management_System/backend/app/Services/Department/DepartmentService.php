<?php

namespace App\Services\Department;

use App\Models\Department;
use App\Models\User;
use App\Repositories\Department\DepartmentRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class DepartmentService
{
    public function __construct(private readonly DepartmentRepository $departmentRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getDepartments(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->departmentRepository->paginate($filters, $perPage);
    }

    /**
     * @return array{department: Department, employees: LengthAwarePaginator}|null
     */
    public function getDepartmentById(string $id, int $employeesPerPage = 10): ?array
    {
        $department = $this->departmentRepository->findById($id);

        if (! $department) {
            return null;
        }

        $employees = $this->departmentRepository->paginateEmployees(
            $department,
            max(1, min(100, $employeesPerPage))
        );

        return [
            'department' => $department,
            'employees' => $employees,
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createDepartment(array $data): Department
    {
        $this->validateBusinessRules($data);

        return $this->departmentRepository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateDepartment(Department $department, array $data): Department
    {
        $this->validateBusinessRules($data, $department->id);

        return $this->departmentRepository->update($department, $data);
    }

    public function deleteDepartment(Department $department): void
    {
        $this->departmentRepository->delete($department);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateBusinessRules(array $data, ?string $ignoreDepartmentId = null): void
    {
        if ($this->departmentRepository->codeExists((string) $data['code'], $ignoreDepartmentId)) {
            throw ValidationException::withMessages([
                'code' => ['The department code has already been taken.'],
            ]);
        }

        $managerUserId = $data['manager_user_id'] ?? null;

        if (! $managerUserId) {
            return;
        }

        $manager = User::query()
            ->with('role')
            ->find((string) $managerUserId);

        if (! $manager) {
            throw ValidationException::withMessages([
                'manager_user_id' => ['The selected manager is invalid.'],
            ]);
        }

        if ($manager->role?->name !== 'manager') {
            throw ValidationException::withMessages([
                'manager_user_id' => ['The selected user must have manager role.'],
            ]);
        }
    }
}
