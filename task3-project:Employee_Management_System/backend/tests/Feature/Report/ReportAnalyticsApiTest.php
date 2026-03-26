<?php

use App\Models\AttendanceRecord;
use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\getJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
});

function reportRoleId(string $roleName): string
{
    return (string) Role::query()->where('name', $roleName)->value('id');
}

function reportUser(string $roleName, string $email): User
{
    return User::query()->create([
        'role_id' => reportRoleId($roleName),
        'name' => ucfirst($roleName).' Report User',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function reportToken(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function reportDepartment(string $name, string $code, ?User $manager = null): Department
{
    return Department::query()->create([
        'name' => $name,
        'code' => $code,
        'status' => 'active',
        'manager_user_id' => $manager?->id,
    ]);
}

function reportEmployee(User $user, Department $department, string $suffix, string $status = 'active', string $employmentType = 'full_time'): Employee
{
    return Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-'.$suffix,
        'first_name' => 'Emp',
        'last_name' => $suffix,
        'email' => 'report.employee.'.$suffix.'@company.test',
        'phone_number' => '+20130000'.$suffix,
        'hire_date' => '2026-01-01',
        'job_title' => 'Engineer',
        'employment_type' => $employmentType,
        'status' => $status,
    ]);
}

it('admin can access reports', function (): void {
    $admin = reportUser('admin', 'admin.reports.access@example.com');

    getJson('/api/reports/employees/summary', [
        'Authorization' => 'Bearer '.reportToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);
});

it('manager can access reports', function (): void {
    $manager = reportUser('manager', 'manager.reports.access@example.com');

    getJson('/api/reports/departments/distribution', [
        'Authorization' => 'Bearer '.reportToken($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);
});

it('employee is forbidden from reports', function (): void {
    $employeeUser = reportUser('employee', 'employee.reports.access@example.com');

    getJson('/api/reports/employees/summary', [
        'Authorization' => 'Bearer '.reportToken($employeeUser),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('attendance summary supports date filters', function (): void {
    $admin = reportUser('admin', 'admin.reports.attendance.filter@example.com');
    $manager = reportUser('manager', 'manager.reports.attendance.filter@example.com');

    $department = reportDepartment('Engineering', 'ENG-REP', $manager);
    $employeeUser = reportUser('employee', 'employee.reports.attendance.filter@example.com');
    $employee = reportEmployee($employeeUser, $department, '9101');

    AttendanceRecord::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-03-01',
        'check_in_time' => '2026-03-01 08:55:00',
        'check_out_time' => '2026-03-01 17:00:00',
        'status' => 'present',
    ]);

    AttendanceRecord::query()->create([
        'employee_id' => $employee->id,
        'attendance_date' => '2026-03-20',
        'check_in_time' => '2026-03-20 09:30:00',
        'check_out_time' => '2026-03-20 17:00:00',
        'status' => 'late',
    ]);

    getJson('/api/reports/attendance/summary?date_from=2026-03-15&date_to=2026-03-31', [
        'Authorization' => 'Bearer '.reportToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.total_attendance_records', 1)
        ->assertJsonPath('data.late_count', 1)
        ->assertJsonPath('meta.filters.date_from', '2026-03-15');
});

it('salary and leave reports support employee and department filters', function (): void {
    $admin = reportUser('admin', 'admin.reports.filter.scope@example.com');
    $manager = reportUser('manager', 'manager.reports.filter.scope@example.com');

    $departmentA = reportDepartment('Technology', 'TECH-REP', $manager);
    $departmentB = reportDepartment('Finance', 'FIN-REP');

    $employeeUserA = reportUser('employee', 'employee.reports.filter.scope.a@example.com');
    $employeeA = reportEmployee($employeeUserA, $departmentA, '9102');

    $employeeUserB = reportUser('employee', 'employee.reports.filter.scope.b@example.com');
    $employeeB = reportEmployee($employeeUserB, $departmentB, '9103');

    Salary::query()->create([
        'employee_id' => $employeeA->id,
        'amount' => 1000,
        'bonuses' => 100,
        'deductions' => 20,
        'net_salary' => 1080,
        'effective_date' => '2026-03-10',
    ]);

    Salary::query()->create([
        'employee_id' => $employeeB->id,
        'amount' => 1500,
        'bonuses' => 150,
        'deductions' => 30,
        'net_salary' => 1620,
        'effective_date' => '2026-03-11',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeA->id,
        'description' => 'Vacation',
        'start_date' => '2026-03-01',
        'end_date' => '2026-03-03',
        'total_days' => 3,
        'status' => 'approved',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeB->id,
        'description' => 'Medical',
        'start_date' => '2026-03-05',
        'end_date' => '2026-03-05',
        'total_days' => 1,
        'status' => 'pending',
    ]);

    getJson('/api/reports/salaries/distribution?department_id='.$departmentA->id.'&date_from=2026-03-01&date_to=2026-03-31', [
        'Authorization' => 'Bearer '.reportToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.total_salary_records', 1)
        ->assertJsonPath('meta.filters.department_id', $departmentA->id);

    getJson('/api/reports/leaves/statistics?employee_id='.$employeeA->id.'&date_from=2026-03-01&date_to=2026-03-31', [
        'Authorization' => 'Bearer '.reportToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.total_leave_requests', 1)
        ->assertJsonPath('data.approved_count', 1)
        ->assertJsonPath('meta.filters.employee_id', $employeeA->id);
});

it('employee summary returns expected structure', function (): void {
    $admin = reportUser('admin', 'admin.reports.employee.structure@example.com');
    $manager = reportUser('manager', 'manager.reports.employee.structure@example.com');

    $department = reportDepartment('Operations', 'OPS-REP', $manager);

    $activeUser = reportUser('employee', 'employee.reports.employee.structure.active@example.com');
    reportEmployee($activeUser, $department, '9104', 'active', 'full_time');

    $inactiveUser = reportUser('employee', 'employee.reports.employee.structure.inactive@example.com');
    reportEmployee($inactiveUser, $department, '9105', 'inactive', 'part_time');

    getJson('/api/reports/employees/summary', [
        'Authorization' => 'Bearer '.reportToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'total_employees',
                'active_employees',
                'inactive_employees',
                'on_leave_employees',
                'terminated_employees',
                'employment_type_distribution',
            ],
            'meta' => [
                'filters',
                'generated_at',
            ],
        ]);
});
