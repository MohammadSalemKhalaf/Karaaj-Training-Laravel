<?php

namespace App\Services\Report;

use App\Repositories\Report\ReportRepository;

class ReportService
{
    public function __construct(private readonly ReportRepository $reportRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function employeeSummary(): array
    {
        return $this->reportRepository->employeeSummary();
    }

    /**
     * @return array<string, mixed>
     */
    public function departmentDistribution(): array
    {
        return $this->reportRepository->departmentDistribution();
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function attendanceSummary(array $filters): array
    {
        return $this->reportRepository->attendanceSummary($filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function salaryDistribution(array $filters): array
    {
        return $this->reportRepository->salaryDistribution($filters);
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function leaveStatistics(array $filters): array
    {
        return $this->reportRepository->leaveStatistics($filters);
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardOverview(): array
    {
        return $this->reportRepository->dashboardOverview();
    }
}
