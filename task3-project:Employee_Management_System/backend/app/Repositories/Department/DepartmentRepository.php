<?php

namespace App\Repositories\Department;

use App\Models\Department;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DepartmentRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $status = (string) ($filters['status'] ?? '');

        return Department::query()
            ->with('manager')
            ->withCount('employees')
            ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Department
    {
        return Department::query()
            ->with('manager')
            ->withCount('employees')
            ->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Department
    {
        /** @var Department $department */
        $department = Department::query()->create($data);

        return $department->load('manager')->loadCount('employees');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Department $department, array $data): Department
    {
        $department->fill($data)->save();

        return $department->load('manager')->loadCount('employees');
    }

    public function delete(Department $department): void
    {
        $department->delete();
    }

    public function codeExists(string $code, ?string $ignoreDepartmentId = null): bool
    {
        return Department::query()
            ->where('code', $code)
            ->when($ignoreDepartmentId, fn ($query) => $query->where('id', '!=', $ignoreDepartmentId))
            ->exists();
    }

    public function paginateEmployees(Department $department, int $perPage): LengthAwarePaginator
    {
        return $department->employees()
            ->with(['user', 'department'])
            ->orderBy('first_name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
