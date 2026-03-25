<?php

namespace App\Http\Controllers\Api\Leave;

use App\Http\Controllers\Controller;
use App\Http\Requests\Leave\ApproveLeaveRequest;
use App\Http\Requests\Leave\RejectLeaveRequest;
use App\Http\Requests\Leave\StoreLeaveRequest;
use App\Http\Requests\Leave\UpdateLeaveRequest;
use App\Http\Resources\Leave\LeaveResource;
use App\Models\LeaveRequest as LeaveRequestModel;
use App\Models\User;
use App\Services\Leave\LeaveService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function __construct(private readonly LeaveService $leaveService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $role = (string) $user->role?->name;

        if (! in_array($role, ['admin', 'manager', 'employee'], true)) {
            return $this->forbiddenResponse();
        }

        $employeeScopeId = null;

        if ($role === 'employee') {
            if (! $user->employee) {
                return $this->forbiddenResponse('Employee profile is required for this action.');
            }

            $employeeScopeId = $user->employee->id;
        }

        $leaves = $this->leaveService->getLeaves(
            $request->only(['employee_id', 'status', 'from_date', 'to_date', 'per_page']),
            $employeeScopeId
        );

        return ApiResponse::success(
            'Leaves fetched successfully.',
            [
                'leaves' => LeaveResource::collection($leaves->items()),
            ],
            [
                'current_page' => $leaves->currentPage(),
                'last_page' => $leaves->lastPage(),
                'per_page' => $leaves->perPage(),
                'total' => $leaves->total(),
            ]
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);
        $leave = $this->leaveService->getLeaveById($id);

        if (! $leave) {
            return $this->notFoundResponse();
        }

        if (! $this->canViewLeave($user, $leave)) {
            return $this->forbiddenResponse();
        }

        return ApiResponse::success(
            'Leave fetched successfully.',
            ['leave' => LeaveResource::make($leave)]
        );
    }

    public function store(StoreLeaveRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isEmployee($user)) {
            return $this->forbiddenResponse('Only employee users can apply for leave.');
        }

        if (! $user->employee) {
            return $this->forbiddenResponse('Employee profile is required for this action.');
        }

        $payload = $request->validated();

        if ((string) $payload['employee_id'] !== (string) $user->employee->id) {
            return $this->forbiddenResponse('Employees can only apply leave for themselves.');
        }

        $leave = $this->leaveService->applyLeave($payload);

        return ApiResponse::success(
            'Leave applied successfully.',
            ['leave' => LeaveResource::make($leave)],
            [],
            201
        );
    }

    public function update(UpdateLeaveRequest $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isEmployee($user) || ! $user->employee) {
            return $this->forbiddenResponse('Only employee users can update their leave requests.');
        }

        $leave = $this->leaveService->getLeaveById($id);

        if (! $leave) {
            return $this->notFoundResponse();
        }

        if ((string) $leave->employee_id !== (string) $user->employee->id) {
            return $this->forbiddenResponse();
        }

        $payload = $request->validated();

        if ((string) $payload['employee_id'] !== (string) $user->employee->id) {
            return $this->forbiddenResponse('Employees can only update their own leave requests.');
        }

        $updatedLeave = $this->leaveService->updateLeave($leave, $payload);

        return ApiResponse::success(
            'Leave updated successfully.',
            ['leave' => LeaveResource::make($updatedLeave)]
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isEmployee($user) || ! $user->employee) {
            return $this->forbiddenResponse('Only employee users can cancel their leave requests.');
        }

        $leave = $this->leaveService->getLeaveById($id);

        if (! $leave) {
            return $this->notFoundResponse();
        }

        if ((string) $leave->employee_id !== (string) $user->employee->id) {
            return $this->forbiddenResponse();
        }

        $cancelled = $this->leaveService->cancelLeave($leave);

        return ApiResponse::success(
            'Leave cancelled successfully.',
            ['leave' => LeaveResource::make($cancelled)]
        );
    }

    public function approve(ApproveLeaveRequest $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isManagerOrAdmin($user)) {
            return $this->forbiddenResponse('Only manager/admin users can approve leaves.');
        }

        $leave = $this->leaveService->getLeaveById($id);

        if (! $leave) {
            return $this->notFoundResponse();
        }

        $approved = $this->leaveService->approveLeave($leave, $user->id);

        return ApiResponse::success(
            'Leave approved successfully.',
            ['leave' => LeaveResource::make($approved)]
        );
    }

    public function reject(RejectLeaveRequest $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isManagerOrAdmin($user)) {
            return $this->forbiddenResponse('Only manager/admin users can reject leaves.');
        }

        $leave = $this->leaveService->getLeaveById($id);

        if (! $leave) {
            return $this->notFoundResponse();
        }

        $rejected = $this->leaveService->rejectLeave(
            $leave,
            $user->id,
            (string) $request->validated('rejection_reason')
        );

        return ApiResponse::success(
            'Leave rejected successfully.',
            ['leave' => LeaveResource::make($rejected)]
        );
    }

    private function authenticatedUser(Request $request): User
    {
        /** @var User $user */
        $user = $request->user('api');
        $user->loadMissing(['role', 'employee']);

        return $user;
    }

    private function isEmployee(User $user): bool
    {
        return $user->role?->name === 'employee';
    }

    private function isManagerOrAdmin(User $user): bool
    {
        return in_array($user->role?->name, ['manager', 'admin'], true);
    }

    private function canViewLeave(User $user, LeaveRequestModel $leave): bool
    {
        if ($this->isManagerOrAdmin($user)) {
            return true;
        }

        return $this->isEmployee($user)
            && $user->employee
            && (string) $user->employee->id === (string) $leave->employee_id;
    }

    private function forbiddenResponse(string $message = 'You are not allowed to perform this action.'): JsonResponse
    {
        return ApiResponse::error(
            'Forbidden.',
            ['authorization' => [$message]],
            'AUTH_FORBIDDEN',
            403
        );
    }

    private function notFoundResponse(): JsonResponse
    {
        return ApiResponse::error(
            'Leave request not found.',
            ['leave' => ['The requested leave record does not exist.']],
            'LEAVE_NOT_FOUND',
            404
        );
    }
}
