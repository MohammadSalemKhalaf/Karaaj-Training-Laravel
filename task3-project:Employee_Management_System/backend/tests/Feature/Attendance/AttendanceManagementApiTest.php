<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
});

function attendanceRoleId(string $name): string
{
    return (string) Role::query()->where('name', $name)->value('id');
}

function attendanceUser(string $role, string $email): User
{
    return User::query()->create([
        'role_id' => attendanceRoleId($role),
        'name' => ucfirst($role).' Attendance',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function attendanceToken(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function attendanceEmployee(User $user, string $suffix): Employee
{
    $department = Department::query()->create([
        'name' => 'Department '.$suffix,
        'code' => 'ATD-'.$suffix,
        'status' => 'active',
    ]);

    return Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-'.$suffix,
        'first_name' => 'Emp',
        'last_name' => $suffix,
        'email' => 'attendance.'.$suffix.'@company.test',
        'phone_number' => '+20111111'.$suffix,
        'hire_date' => '2026-01-01',
        'job_title' => 'Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

it('check-in success', function (): void {
    $employeeUser = attendanceUser('employee', 'employee.attendance.checkin@example.com');
    attendanceEmployee($employeeUser, '8101');

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.attendance.status', fn ($status) => in_array($status, ['present', 'late'], true));
});

it('double check-in blocked', function (): void {
    $employeeUser = attendanceUser('employee', 'employee.attendance.double@example.com');
    attendanceEmployee($employeeUser, '8102');

    $token = attendanceToken($employeeUser);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated();

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('check-out success', function (): void {
    $employeeUser = attendanceUser('employee', 'employee.attendance.checkout@example.com');
    attendanceEmployee($employeeUser, '8103');

    $token = attendanceToken($employeeUser);

    postJson('/api/attendance/check-in', [], [
        'Authorization' => 'Bearer '.$token,
    ])->assertCreated();

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.attendance.check_out_time', fn ($value) => $value !== null);
});

it('no check-out without check-in', function (): void {
    $employeeUser = attendanceUser('employee', 'employee.attendance.no-checkin@example.com');
    attendanceEmployee($employeeUser, '8104');

    postJson('/api/attendance/check-out', [], [
        'Authorization' => 'Bearer '.attendanceToken($employeeUser),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});
