<?php

namespace App\Services\Leave;

use App\Models\LeaveRequest as LeaveRequestModel;
use App\Repositories\Leave\LeaveRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class LeaveService
{
    public function __construct(private readonly LeaveRepository $leaveRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getLeaves(array $filters, ?string $employeeScopeId = null): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->leaveRepository->paginate($filters, $perPage, $employeeScopeId);
    }

    public function getLeaveById(string $id): ?LeaveRequestModel
    {
        return $this->leaveRepository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function applyLeave(array $data): LeaveRequestModel
    {
        $payload = $this->buildCreateOrUpdatePayload($data);
        $this->ensureNoOverlappingLeave(
            (string) $payload['employee_id'],
            (string) $payload['start_date'],
            (string) $payload['end_date']
        );

        $leave = $this->leaveRepository->create($payload);

        Log::channel('ems')->info('Leave applied', [
            'event' => 'leave.applied',
            'leave_id' => $leave->id,
            'employee_id' => $leave->employee_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $leave;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateLeave(LeaveRequestModel $leave, array $data): LeaveRequestModel
    {
        $this->ensurePending($leave, 'updated');

        $payload = $this->buildCreateOrUpdatePayload($data);
        $this->ensureNoOverlappingLeave(
            (string) $payload['employee_id'],
            (string) $payload['start_date'],
            (string) $payload['end_date'],
            $leave->id
        );

        return $this->leaveRepository->update($leave, $payload);
    }

    public function approveLeave(LeaveRequestModel $leave, string $approvedBy): LeaveRequestModel
    {
        $this->ensurePending($leave, 'approved');

        $updated = $this->leaveRepository->update($leave, [
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'rejection_reason' => null,
        ]);

        Log::channel('ems')->info('Leave approved', [
            'event' => 'leave.approved',
            'leave_id' => $updated->id,
            'approved_by' => $approvedBy,
            'employee_id' => $updated->employee_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $updated;
    }

    public function rejectLeave(LeaveRequestModel $leave, string $approvedBy, string $reason): LeaveRequestModel
    {
        $this->ensurePending($leave, 'rejected');

        $updated = $this->leaveRepository->update($leave, [
            'status' => 'rejected',
            'approved_by' => $approvedBy,
            'rejection_reason' => $reason,
        ]);

        Log::channel('ems')->info('Leave rejected', [
            'event' => 'leave.rejected',
            'leave_id' => $updated->id,
            'approved_by' => $approvedBy,
            'employee_id' => $updated->employee_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $updated;
    }

    public function cancelLeave(LeaveRequestModel $leave): LeaveRequestModel
    {
        $this->ensurePending($leave, 'cancelled');

        $updated = $this->leaveRepository->update($leave, [
            'status' => 'cancelled',
        ]);

        Log::channel('ems')->info('Leave cancelled', [
            'event' => 'leave.cancelled',
            'leave_id' => $updated->id,
            'employee_id' => $updated->employee_id,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $updated;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function buildCreateOrUpdatePayload(array $data): array
    {
        $startDate = Carbon::parse((string) $data['start_date'])->startOfDay();
        $endDate = Carbon::parse((string) $data['end_date'])->startOfDay();

        if ($startDate->gt($endDate)) {
            throw ValidationException::withMessages([
                'start_date' => ['The start date must be before or equal to end date.'],
            ]);
        }

        $totalDays = $startDate->diffInDays($endDate) + 1;

        return [
            'employee_id' => (string) $data['employee_id'],
            'description' => (string) $data['description'],
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'total_days' => $totalDays,
            'status' => 'pending',
            'approved_by' => null,
            'rejection_reason' => null,
        ];
    }

    private function ensurePending(LeaveRequestModel $leave, string $action): void
    {
        if ($leave->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ["Only pending leaves can be {$action}."],
            ]);
        }
    }

    private function ensureNoOverlappingLeave(
        string $employeeId,
        string $startDate,
        string $endDate,
        ?string $ignoreLeaveId = null
    ): void {
        if (! $this->leaveRepository->hasOverlappingLeave($employeeId, $startDate, $endDate, $ignoreLeaveId)) {
            return;
        }

        throw ValidationException::withMessages([
            'start_date' => ['The selected period overlaps with another active leave request.'],
        ]);
    }
}
