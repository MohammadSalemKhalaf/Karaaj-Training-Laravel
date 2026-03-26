<?php

namespace App\Services\Attendance;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Repositories\Attendance\AttendanceRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(private readonly AttendanceRepository $attendanceRepository)
    {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function checkIn(string $employeeId, array $data = []): AttendanceRecord
    {
        $this->ensureEmployeeExists($employeeId);

        if ($this->attendanceRepository->findTodayByEmployee($employeeId)) {
            throw ValidationException::withMessages([
                'attendance' => ['Employee has already checked in today.'],
            ]);
        }

        $now = Carbon::now();

        return $this->attendanceRepository->create([
            'employee_id' => $employeeId,
            'attendance_date' => $now->toDateString(),
            'check_in_time' => $now,
            'status' => $this->resolveInitialStatus($now),
            'notes' => $data['notes'] ?? null,
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function checkOut(string $employeeId, array $data = []): AttendanceRecord
    {
        $this->ensureEmployeeExists($employeeId);

        $attendance = $this->attendanceRepository->findTodayByEmployee($employeeId);

        if (! $attendance || ! $attendance->check_in_time) {
            throw ValidationException::withMessages([
                'attendance' => ['Check-in is required before check-out.'],
            ]);
        }

        if ($attendance->check_out_time) {
            throw ValidationException::withMessages([
                'attendance' => ['Employee has already checked out today.'],
            ]);
        }

        $payload = [
            'check_out_time' => Carbon::now(),
        ];

        if (array_key_exists('notes', $data)) {
            $payload['notes'] = $data['notes'];
        }

        if (array_key_exists('status', $data) && in_array($data['status'], ['present', 'absent', 'late', 'half_day'], true)) {
            $payload['status'] = $data['status'];
        }

        return $this->attendanceRepository->update($attendance, $payload);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getAttendance(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->attendanceRepository->paginate($filters, $perPage);
    }

    public function getAttendanceById(string $id): ?AttendanceRecord
    {
        return $this->attendanceRepository->findById($id);
    }

    public function getEmployeeAttendance(string $employeeId, int $perPage = 10): LengthAwarePaginator
    {
        $this->ensureEmployeeExists($employeeId);

        return $this->attendanceRepository->paginateByEmployee(
            $employeeId,
            max(1, min(100, $perPage))
        );
    }

    private function ensureEmployeeExists(string $employeeId): void
    {
        if (! Employee::query()->whereKey($employeeId)->exists()) {
            throw ValidationException::withMessages([
                'employee_id' => ['The selected employee is invalid.'],
            ]);
        }
    }

    private function resolveInitialStatus(Carbon $checkedInAt): string
    {
        $lateThreshold = Carbon::today()->setTime(9, 15, 0);

        return $checkedInAt->gt($lateThreshold) ? 'late' : 'present';
    }
}
