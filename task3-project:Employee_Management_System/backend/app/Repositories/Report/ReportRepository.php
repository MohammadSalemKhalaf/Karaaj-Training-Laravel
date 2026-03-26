<?php

namespace App\Repositories\Report;

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Salary;
use App\Models\User;
use Carbon\Carbon;

class ReportRepository
{
    /**
     * @return array<string, mixed>
     */
    public function employeeSummary(): array
    {
        $totalEmployees = Employee::query()->count();

        $statusCounts = Employee::query()
            ->selectRaw("status, COUNT(*) as aggregate")
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $today = Carbon::today()->toDateString();

        $onLeaveEmployees = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->distinct('employee_id')
            ->count('employee_id');

        $employmentTypeDistribution = Employee::query()
            ->selectRaw('employment_type, COUNT(*) as total')
            ->groupBy('employment_type')
            ->orderByDesc('total')
            ->get();

        return [
            'total_employees' => $totalEmployees,
            'active_employees' => (int) ($statusCounts['active'] ?? 0),
            'inactive_employees' => (int) ($statusCounts['inactive'] ?? 0),
            'on_leave_employees' => $onLeaveEmployees,
            'terminated_employees' => (int) ($statusCounts['terminated'] ?? 0),
            'employment_type_distribution' => $employmentTypeDistribution,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function departmentDistribution(): array
    {
        $departments = Department::query()
            ->select(['id', 'name', 'code', 'manager_user_id'])
            ->with(['manager:id,name,email'])
            ->withCount('employees')
            ->orderBy('name')
            ->get();

        $totalDepartments = Department::query()->count();
        $emptyDepartmentsCount = Department::query()->doesntHave('employees')->count();

        return [
            'total_departments' => $totalDepartments,
            'empty_departments_count' => $emptyDepartmentsCount,
            'departments' => $departments,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function attendanceSummary(array $filters): array
    {
        $query = AttendanceRecord::query()
            ->join('employees', 'employees.id', '=', 'attendance_records.employee_id');

        $this->applyDateAndEntityFilters(
            $query,
            'attendance_records.attendance_date',
            $filters
        );

        $aggregate = $query->selectRaw(
            'COUNT(*) as total_records,
            SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as present_count,
            SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as late_count,
            SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as absent_count,
            SUM(CASE WHEN attendance_records.status = ? THEN 1 ELSE 0 END) as half_day_count',
            ['present', 'late', 'absent', 'half_day']
        )->first();

        $total = (int) ($aggregate?->total_records ?? 0);
        $productive = (int) (($aggregate?->present_count ?? 0) + ($aggregate?->late_count ?? 0) + ($aggregate?->half_day_count ?? 0));

        return [
            'total_attendance_records' => $total,
            'present_count' => (int) ($aggregate?->present_count ?? 0),
            'late_count' => (int) ($aggregate?->late_count ?? 0),
            'absent_count' => (int) ($aggregate?->absent_count ?? 0),
            'half_day_count' => (int) ($aggregate?->half_day_count ?? 0),
            'attendance_percentage' => $total > 0 ? round(($productive / $total) * 100, 2) : null,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function salaryDistribution(array $filters): array
    {
        $query = Salary::query()
            ->join('employees', 'employees.id', '=', 'salaries.employee_id');

        $this->applyDateAndEntityFilters(
            $query,
            'salaries.effective_date',
            $filters
        );

        $aggregate = $query->selectRaw(
            'COUNT(*) as total_salary_records,
            AVG(salaries.amount) as average_salary,
            MIN(salaries.amount) as min_salary,
            MAX(salaries.amount) as max_salary,
            SUM(salaries.bonuses) as total_bonuses,
            SUM(salaries.deductions) as total_deductions,
            SUM(salaries.net_salary) as total_net_salary_payout'
        )->first();

        return [
            'total_salary_records' => (int) ($aggregate?->total_salary_records ?? 0),
            'average_salary' => $aggregate?->average_salary !== null ? round((float) $aggregate->average_salary, 2) : null,
            'min_salary' => $aggregate?->min_salary !== null ? round((float) $aggregate->min_salary, 2) : null,
            'max_salary' => $aggregate?->max_salary !== null ? round((float) $aggregate->max_salary, 2) : null,
            'total_bonuses' => $aggregate?->total_bonuses !== null ? round((float) $aggregate->total_bonuses, 2) : 0.0,
            'total_deductions' => $aggregate?->total_deductions !== null ? round((float) $aggregate->total_deductions, 2) : 0.0,
            'total_net_salary_payout' => $aggregate?->total_net_salary_payout !== null ? round((float) $aggregate->total_net_salary_payout, 2) : 0.0,
        ];
    }

    /**
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function leaveStatistics(array $filters): array
    {
        $query = LeaveRequest::query()
            ->join('employees', 'employees.id', '=', 'leave_requests.employee_id');

        $this->applyDateAndEntityFilters(
            $query,
            'leave_requests.start_date',
            $filters
        );

        $aggregate = $query->selectRaw(
            'COUNT(*) as total_leave_requests,
            SUM(CASE WHEN leave_requests.status = ? THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN leave_requests.status = ? THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN leave_requests.status = ? THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN leave_requests.status = ? THEN 1 ELSE 0 END) as cancelled_count,
            AVG(leave_requests.total_days) as average_leave_duration',
            ['pending', 'approved', 'rejected', 'cancelled']
        )->first();

        $statusDistribution = (clone $query)
            ->selectRaw('leave_requests.status, COUNT(*) as total')
            ->groupBy('leave_requests.status')
            ->orderByDesc('total')
            ->get();

        return [
            'total_leave_requests' => (int) ($aggregate?->total_leave_requests ?? 0),
            'pending_count' => (int) ($aggregate?->pending_count ?? 0),
            'approved_count' => (int) ($aggregate?->approved_count ?? 0),
            'rejected_count' => (int) ($aggregate?->rejected_count ?? 0),
            'cancelled_count' => (int) ($aggregate?->cancelled_count ?? 0),
            'average_leave_duration' => $aggregate?->average_leave_duration !== null ? round((float) $aggregate->average_leave_duration, 2) : null,
            'most_frequent_statuses' => $statusDistribution,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function dashboardOverview(): array
    {
        $today = Carbon::today()->toDateString();

        $latestLeaves = LeaveRequest::query()
            ->select(['id', 'employee_id', 'status', 'start_date', 'end_date', 'created_at'])
            ->with(['employee:id,first_name,last_name,employee_code'])
            ->latest('created_at')
            ->limit(5)
            ->get();

        $latestEmployees = Employee::query()
            ->select(['id', 'first_name', 'last_name', 'employee_code', 'department_id', 'status', 'created_at'])
            ->with('department:id,name,code')
            ->latest('created_at')
            ->limit(5)
            ->get();

        return [
            'total_users' => User::query()->count(),
            'total_employees' => Employee::query()->count(),
            'total_departments' => Department::query()->count(),
            'total_pending_leaves' => LeaveRequest::query()->where('status', 'pending')->count(),
            'today_attendance_count' => AttendanceRecord::query()->whereDate('attendance_date', $today)->count(),
            'latest_leave_requests' => $latestLeaves,
            'latest_employees' => $latestEmployees,
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>|\Illuminate\Database\Query\Builder $query
     * @param array<string, mixed> $filters
     */
    private function applyDateAndEntityFilters($query, string $dateColumn, array $filters): void
    {
        $dateFrom = (string) ($filters['date_from'] ?? '');
        $dateTo = (string) ($filters['date_to'] ?? '');
        $employeeId = (string) ($filters['employee_id'] ?? '');
        $departmentId = (string) ($filters['department_id'] ?? '');

        $query
            ->when($dateFrom !== '', fn ($q) => $q->whereDate($dateColumn, '>=', $dateFrom))
            ->when($dateTo !== '', fn ($q) => $q->whereDate($dateColumn, '<=', $dateTo))
            ->when($employeeId !== '', fn ($q) => $q->where('employees.id', $employeeId))
            ->when($departmentId !== '', fn ($q) => $q->where('employees.department_id', $departmentId));
    }
}
