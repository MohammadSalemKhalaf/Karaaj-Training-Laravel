<?php

namespace App\Services\Salary;

use App\Models\Employee;
use App\Models\Salary;
use App\Repositories\Salary\SalaryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SalaryService
{
    public function __construct(private readonly SalaryRepository $salaryRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getSalaries(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->salaryRepository->paginate($filters, $perPage);
    }

    public function getSalaryById(string $id): ?Salary
    {
        return $this->salaryRepository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSalary(array $data): Salary
    {
        $this->ensureEmployeeExists((string) $data['employee_id']);

        $payload = $this->buildSalaryPayload($data);

        return $this->salaryRepository->create($payload);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateSalary(Salary $salary, array $data): Salary
    {
        $this->ensureEmployeeExists((string) $data['employee_id']);

        $payload = $this->buildSalaryPayload($data);

        return $this->salaryRepository->update($salary, $payload);
    }

    public function deleteSalary(Salary $salary): void
    {
        $this->salaryRepository->delete($salary);
    }

    public function getEmployeeSalaries(string $employeeId, int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureEmployeeExists($employeeId);

        return $this->salaryRepository->getByEmployee(
            $employeeId,
            max(1, min(100, $perPage))
        );
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildSalaryPayload(array $data): array
    {
        $amount = (float) $data['amount'];
        $bonuses = (float) ($data['bonuses'] ?? 0);
        $deductions = (float) ($data['deductions'] ?? 0);
        $netSalary = $amount + $bonuses - $deductions;

        if ($amount < 0 || $bonuses < 0 || $deductions < 0 || $netSalary < 0) {
            throw ValidationException::withMessages([
                'salary' => ['Salary values are invalid. Net salary cannot be negative.'],
            ]);
        }

        return [
            'employee_id' => $data['employee_id'],
            'amount' => $amount,
            'bonuses' => $bonuses,
            'deductions' => $deductions,
            'net_salary' => $netSalary,
            'effective_date' => $data['effective_date'],
            'notes' => $data['notes'] ?? null,
        ];
    }

    private function ensureEmployeeExists(string $employeeId): void
    {
        if (! Employee::query()->whereKey($employeeId)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => ['The selected employee is invalid.'],
            ]);
        }
    }
}
