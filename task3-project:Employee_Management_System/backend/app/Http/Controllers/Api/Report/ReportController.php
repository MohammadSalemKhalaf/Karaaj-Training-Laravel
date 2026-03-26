<?php

namespace App\Http\Controllers\Api\Report;

use App\Http\Controllers\Controller;
use App\Http\Requests\Report\AttendanceSummaryReportRequest;
use App\Http\Requests\Report\LeaveStatisticsReportRequest;
use App\Http\Requests\Report\SalaryDistributionReportRequest;
use App\Services\Report\ReportService;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService)
    {
    }

    public function employeeSummary(): JsonResponse
    {
        return $this->reportResponse(
            'Employee summary report fetched successfully.',
            $this->reportService->employeeSummary(),
            []
        );
    }

    public function departmentDistribution(): JsonResponse
    {
        return $this->reportResponse(
            'Department distribution report fetched successfully.',
            $this->reportService->departmentDistribution(),
            []
        );
    }

    public function attendanceSummary(AttendanceSummaryReportRequest $request): JsonResponse
    {
        $filters = $request->validated();

        return $this->reportResponse(
            'Attendance summary report fetched successfully.',
            $this->reportService->attendanceSummary($filters),
            $filters
        );
    }

    public function salaryDistribution(SalaryDistributionReportRequest $request): JsonResponse
    {
        $filters = $request->validated();

        return $this->reportResponse(
            'Salary distribution report fetched successfully.',
            $this->reportService->salaryDistribution($filters),
            $filters
        );
    }

    public function leaveStatistics(LeaveStatisticsReportRequest $request): JsonResponse
    {
        $filters = $request->validated();

        return $this->reportResponse(
            'Leave statistics report fetched successfully.',
            $this->reportService->leaveStatistics($filters),
            $filters
        );
    }

    public function dashboardOverview(): JsonResponse
    {
        return $this->reportResponse(
            'Dashboard overview report fetched successfully.',
            $this->reportService->dashboardOverview(),
            []
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $filters
     */
    private function reportResponse(string $message, array $data, array $filters): JsonResponse
    {
        return ApiResponse::success(
            $message,
            $data,
            [
                'filters' => (object) $filters,
                'generated_at' => Carbon::now()->toIso8601String(),
            ]
        );
    }
}
