<?php

namespace App\Repositories\Employee;

use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EmployeeRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $departmentId = (string) ($filters['department_id'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $employmentType = (string) ($filters['employment_type'] ?? '');

        return Employee::query()
            ->with(['department', 'user'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($departmentId !== '', fn ($query) => $query->where('department_id', $departmentId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($employmentType !== '', fn ($query) => $query->where('employment_type', $employmentType))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Employee
    {
        return Employee::query()->with(['department', 'user'])->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Employee
    {
        /** @var Employee $employee */
        $employee = Employee::query()->create($data);

        return $employee->load(['department', 'user']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Employee $employee, array $data): Employee
    {
        $employee->fill($data)->save();

        return $employee->load(['department', 'user']);
    }

    public function delete(Employee $employee): void
    {
        $employee->delete();
    }

    public function userAssignedToAnotherEmployee(string $userId, ?string $ignoreEmployeeId = null): bool
    {
        return Employee::query()
            ->where('user_id', $userId)
            ->when($ignoreEmployeeId, fn ($query) => $query->where('id', '!=', $ignoreEmployeeId))
            ->exists();
    }

    public function employeeCodeExists(string $employeeCode, ?string $ignoreEmployeeId = null): bool
    {
        return Employee::query()
            ->where('employee_code', $employeeCode)
            ->when($ignoreEmployeeId, fn ($query) => $query->where('id', '!=', $ignoreEmployeeId))
            ->exists();
    }

    public function getLastEmployeeCode(): ?string
    {
        /** @var string|null $code */
        $code = Employee::query()
            ->where('employee_code', 'like', 'EMP-%')
            ->orderByRaw('CAST(SUBSTRING(employee_code, 5) AS UNSIGNED) DESC')
            ->value('employee_code');

        return $code;
    }
}
