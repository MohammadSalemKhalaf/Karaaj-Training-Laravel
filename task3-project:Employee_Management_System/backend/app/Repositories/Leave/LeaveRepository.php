<?php

namespace App\Repositories\Leave;

use App\Models\LeaveRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LeaveRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage, ?string $employeeScopeId = null): LengthAwarePaginator
    {
        $employeeId = (string) ($filters['employee_id'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $fromDate = (string) ($filters['from_date'] ?? '');
        $toDate = (string) ($filters['to_date'] ?? '');

        return LeaveRequest::query()
            ->with(['employee', 'approver.role'])
            ->when($employeeScopeId !== null, fn ($query) => $query->where('employee_id', $employeeScopeId))
            ->when($employeeScopeId === null && $employeeId !== '', fn ($query) => $query->where('employee_id', $employeeId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($fromDate !== '', fn ($query) => $query->whereDate('start_date', '>=', $fromDate))
            ->when($toDate !== '', fn ($query) => $query->whereDate('end_date', '<=', $toDate))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?LeaveRequest
    {
        return LeaveRequest::query()
            ->with(['employee', 'approver.role'])
            ->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): LeaveRequest
    {
        /** @var LeaveRequest $leave */
        $leave = LeaveRequest::query()->create($data);

        return $leave->load(['employee', 'approver.role']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(LeaveRequest $leave, array $data): LeaveRequest
    {
        $leave->fill($data)->save();

        return $leave->load(['employee', 'approver.role']);
    }

    public function hasOverlappingLeave(
        string $employeeId,
        string $startDate,
        string $endDate,
        ?string $ignoreLeaveId = null
    ): bool {
        return LeaveRequest::query()
            ->where('employee_id', $employeeId)
            ->whereNotIn('status', [
                'rejected',
                'cancelled',
            ])
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->when($ignoreLeaveId, fn ($query) => $query->where('id', '!=', $ignoreLeaveId))
            ->exists();
    }
}
