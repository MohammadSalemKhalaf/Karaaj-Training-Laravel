<?php

namespace App\Http\Controllers\Api\Attendance;

use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\CheckInAttendanceRequest;
use App\Http\Requests\Attendance\CheckOutAttendanceRequest;
use App\Http\Resources\Attendance\AttendanceResource;
use App\Models\User;
use App\Services\Attendance\AttendanceService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendanceService)
    {
    }

    public function checkIn(CheckInAttendanceRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isEmployee($user)) {
            return $this->forbiddenResponse('Only employee users can check in.');
        }

        if (! $user->employee) {
            return $this->forbiddenResponse('Employee profile is required for this action.');
        }

        $attendance = $this->attendanceService->checkIn(
            $user->employee->id,
            $request->validated()
        );

        return ApiResponse::success(
            'Check-in completed successfully.',
            ['attendance' => AttendanceResource::make($attendance)],
            [],
            201
        );
    }

    public function checkOut(CheckOutAttendanceRequest $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isEmployee($user)) {
            return $this->forbiddenResponse('Only employee users can check out.');
        }

        if (! $user->employee) {
            return $this->forbiddenResponse('Employee profile is required for this action.');
        }

        $attendance = $this->attendanceService->checkOut(
            $user->employee->id,
            $request->validated()
        );

        return ApiResponse::success(
            'Check-out completed successfully.',
            ['attendance' => AttendanceResource::make($attendance)]
        );
    }

    public function index(Request $request): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isManagerOrAdmin($user)) {
            return $this->forbiddenResponse('Only manager/admin users can view attendance records.');
        }

        $attendance = $this->attendanceService->getAttendance($request->only([
            'employee_id',
            'status',
            'from_date',
            'to_date',
            'per_page',
        ]));

        return ApiResponse::success(
            'Attendance records fetched successfully.',
            [
                'attendance' => AttendanceResource::collection($attendance->items()),
            ],
            [
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'per_page' => $attendance->perPage(),
                'total' => $attendance->total(),
            ]
        );
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isManagerOrAdmin($user)) {
            return $this->forbiddenResponse('Only manager/admin users can view attendance records.');
        }

        $attendance = $this->attendanceService->getAttendanceById($id);

        if (! $attendance) {
            return $this->notFoundResponse();
        }

        return ApiResponse::success(
            'Attendance record fetched successfully.',
            ['attendance' => AttendanceResource::make($attendance)]
        );
    }

    public function employeeAttendance(Request $request, string $id): JsonResponse
    {
        $user = $this->authenticatedUser($request);

        if (! $this->isManagerOrAdmin($user)) {
            return $this->forbiddenResponse('Only manager/admin users can view employee attendance records.');
        }

        $attendance = $this->attendanceService->getEmployeeAttendance(
            $id,
            (int) $request->integer('per_page', 10)
        );

        return ApiResponse::success(
            'Employee attendance fetched successfully.',
            [
                'attendance' => AttendanceResource::collection($attendance->items()),
            ],
            [
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'per_page' => $attendance->perPage(),
                'total' => $attendance->total(),
            ]
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

    private function forbiddenResponse(string $message): JsonResponse
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
            'Attendance record not found.',
            ['attendance' => ['The requested attendance record does not exist.']],
            'ATTENDANCE_NOT_FOUND',
            404
        );
    }
}
