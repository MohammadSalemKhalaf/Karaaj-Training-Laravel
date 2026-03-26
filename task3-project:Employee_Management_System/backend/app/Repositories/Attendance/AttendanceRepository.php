<?php

namespace App\Repositories\Attendance;

use App\Models\AttendanceRecord;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceRepository
{
    public function findTodayByEmployee(string $employeeId): ?AttendanceRecord
    {
        $today = Carbon::today()->toDateString();

        return AttendanceRecord::query()
            ->with('employee')
            ->where('employee_id', $employeeId)
            ->whereDate('attendance_date', $today)
            ->first();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): AttendanceRecord
    {
        /** @var AttendanceRecord $attendance */
        $attendance = AttendanceRecord::query()->create($data);

        return $attendance->load('employee');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(AttendanceRecord $attendance, array $data): AttendanceRecord
    {
        $attendance->fill($data)->save();

        return $attendance->load('employee');
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $employeeId = (string) ($filters['employee_id'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $fromDate = (string) ($filters['from_date'] ?? '');
        $toDate = (string) ($filters['to_date'] ?? '');

        return AttendanceRecord::query()
            ->with('employee')
            ->when($employeeId !== '', fn ($query) => $query->where('employee_id', $employeeId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($fromDate !== '', fn ($query) => $query->whereDate('attendance_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('attendance_date', '<=', $toDate))
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?AttendanceRecord
    {
        return AttendanceRecord::query()
            ->with('employee')
            ->find($id);
    }

    public function paginateByEmployee(string $employeeId, int $perPage): LengthAwarePaginator
    {
        return AttendanceRecord::query()
            ->with('employee')
            ->where('employee_id', $employeeId)
            ->orderByDesc('attendance_date')
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
