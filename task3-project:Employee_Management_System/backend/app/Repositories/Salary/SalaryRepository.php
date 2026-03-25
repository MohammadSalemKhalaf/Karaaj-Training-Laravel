<?php

namespace App\Repositories\Salary;

use App\Models\Salary;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SalaryRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $employeeId = (string) ($filters['employee_id'] ?? '');
        $fromDate = (string) ($filters['from_date'] ?? '');
        $toDate = (string) ($filters['to_date'] ?? '');

        return Salary::query()
            ->with('employee')
            ->when($employeeId !== '', fn ($query) => $query->where('employee_id', $employeeId))
            ->when($fromDate !== '', fn ($query) => $query->whereDate('effective_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('effective_date', '<=', $toDate))
            ->orderByDesc('effective_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?Salary
    {
        return Salary::query()->with('employee')->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Salary
    {
        /** @var Salary $salary */
        $salary = Salary::query()->create($data);

        return $salary->load('employee');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Salary $salary, array $data): Salary
    {
        $salary->fill($data)->save();

        return $salary->load('employee');
    }

    public function delete(Salary $salary): void
    {
        $salary->delete();
    }

    public function getByEmployee(string $employeeId, int $perPage): LengthAwarePaginator
    {
        return Salary::query()
            ->with('employee')
            ->where('employee_id', $employeeId)
            ->orderByDesc('effective_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
