<?php

namespace App\Services\Employee;

use App\Models\Department;
use App\Models\Employee;
use App\Repositories\Employee\EmployeeRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    public function __construct(private readonly EmployeeRepository $employeeRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getEmployees(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->employeeRepository->paginate($filters, $perPage);
    }

    public function getEmployeeById(string $id): ?Employee
    {
        return $this->employeeRepository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createEmployee(array $data): Employee
    {
        $payload = $data;
        $payload['employee_code'] = $this->generateNextEmployeeCode();
        $this->validateBusinessRules($payload);

        $employee = $this->employeeRepository->create($payload);

        Log::channel('ems')->info('Employee created', [
            'event' => 'employee.created',
            'employee_id' => $employee->id,
            'user_id' => $employee->user_id,
            'department_id' => $employee->department_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $employee;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        $this->validateBusinessRules($data, $employee->id);

        $updatedEmployee = $this->employeeRepository->update($employee, $data);

        Log::channel('ems')->info('Employee updated', [
            'event' => 'employee.updated',
            'employee_id' => $updatedEmployee->id,
            'user_id' => $updatedEmployee->user_id,
            'department_id' => $updatedEmployee->department_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $updatedEmployee;
    }

    public function deleteEmployee(Employee $employee): void
    {
        $this->employeeRepository->delete($employee);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateBusinessRules(array $data, ?string $ignoreEmployeeId = null): void
    {
        if (! Department::query()->whereKey((string) $data['department_id'])->exists()) {
            throw ValidationException::withMessages([
                'department_id' => ['The selected department is invalid.'],
            ]);
        }

        if ($this->employeeRepository->userAssignedToAnotherEmployee((string) $data['user_id'], $ignoreEmployeeId)) {
            throw ValidationException::withMessages([
                'user_id' => ['This user is already assigned to another employee.'],
            ]);
        }

        if ($this->employeeRepository->employeeCodeExists((string) $data['employee_code'], $ignoreEmployeeId)) {
            throw ValidationException::withMessages([
                'employee_code' => ['The employee code has already been taken.'],
            ]);
        }
    }

    private function generateNextEmployeeCode(): string
    {
        $lastCode = $this->employeeRepository->getLastEmployeeCode();
        $nextNumber = 1;

        if (is_string($lastCode) && preg_match('/^EMP-(\d+)$/', $lastCode, $matches)) {
            $nextNumber = (int) $matches[1] + 1;
        }

        do {
            $candidate = sprintf('EMP-%04d', $nextNumber);
            $nextNumber++;
        } while ($this->employeeRepository->employeeCodeExists($candidate));

        return $candidate;
    }
}
