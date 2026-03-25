<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
});

function roleIdByName(string $roleName): string
{
    return (string) Role::query()->where('name', $roleName)->value('id');
}

function makeRoleUser(string $roleName, string $email): User
{
    return User::query()->create([
        'role_id' => roleIdByName($roleName),
        'name' => ucfirst($roleName).' User',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function jwtFor(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function makeDepartment(string $code): Department
{
    return Department::query()->create([
        'name' => 'Department '.$code,
        'code' => $code,
        'status' => 'active',
    ]);
}

function makeEmployeeForUser(User $user, string $suffix): Employee
{
    $department = makeDepartment('DEP-'.$suffix);

    return Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-'.$suffix,
        'first_name' => 'Emp',
        'last_name' => $suffix,
        'email' => 'employee.'.$suffix.'@company.test',
        'phone_number' => '+20111111'.$suffix,
        'hire_date' => '2026-01-01',
        'job_title' => 'Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

it('applies leave', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.apply@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7001');

    postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Medical leave',
        'start_date' => '2026-04-01',
        'end_date' => '2026-04-03',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.leave.employee.id', $employee->id)
        ->assertJsonPath('data.leave.status', 'pending');
});

it('approves leave', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.approve.case@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7002');
    $manager = makeRoleUser('manager', 'manager.leave.approve@example.com');

    $applyResponse = postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Family event',
        'start_date' => '2026-05-01',
        'end_date' => '2026-05-02',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])->assertCreated();

    $leaveId = (string) $applyResponse->json('data.leave.id');

    postJson('/api/leaves/'.$leaveId.'/approve', [], [
        'Authorization' => 'Bearer '.jwtFor($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.leave.status', 'approved')
        ->assertJsonPath('data.leave.approved_by.id', $manager->id);
});

it('rejects leave', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.reject.case@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7003');
    $admin = makeRoleUser('admin', 'admin.leave.reject@example.com');

    $applyResponse = postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Personal leave',
        'start_date' => '2026-06-10',
        'end_date' => '2026-06-12',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])->assertCreated();

    $leaveId = (string) $applyResponse->json('data.leave.id');

    postJson('/api/leaves/'.$leaveId.'/reject', [
        'rejection_reason' => 'Peak business period',
    ], [
        'Authorization' => 'Bearer '.jwtFor($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.leave.status', 'rejected');
});

it('blocks invalid status transitions', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.transitions@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7004');
    $manager = makeRoleUser('manager', 'manager.leave.transitions@example.com');

    $applyResponse = postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Transition test',
        'start_date' => '2026-07-01',
        'end_date' => '2026-07-03',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])->assertCreated();

    $leaveId = (string) $applyResponse->json('data.leave.id');

    postJson('/api/leaves/'.$leaveId.'/approve', [], [
        'Authorization' => 'Bearer '.jwtFor($manager),
    ])->assertSuccessful();

    deleteJson('/api/leaves/'.$leaveId, [], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    postJson('/api/leaves/'.$leaveId.'/reject', [
        'rejection_reason' => 'Cannot reject approved leave',
    ], [
        'Authorization' => 'Bearer '.jwtFor($manager),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('blocks overlapping leaves', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.overlap@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7005');

    LeaveRequest::query()->create([
        'employee_id' => $employee->id,
        'description' => 'Existing leave',
        'start_date' => '2026-08-10',
        'end_date' => '2026-08-12',
        'total_days' => 3,
        'status' => 'pending',
    ]);

    postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Overlapping leave',
        'start_date' => '2026-08-11',
        'end_date' => '2026-08-13',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('blocks manager from applying leave', function (): void {
    $manager = makeRoleUser('manager', 'manager.leave.apply.blocked@example.com');
    $employeeUser = makeRoleUser('employee', 'employee.leave.apply.blocked@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7006');

    postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Should fail',
        'start_date' => '2026-09-01',
        'end_date' => '2026-09-01',
    ], [
        'Authorization' => 'Bearer '.jwtFor($manager),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('blocks employee from approving leave', function (): void {
    $employeeUser = makeRoleUser('employee', 'employee.leave.approve.blocked@example.com');
    $employee = makeEmployeeForUser($employeeUser, '7007');

    $applyResponse = postJson('/api/leaves', [
        'employee_id' => $employee->id,
        'description' => 'Approval blocked test',
        'start_date' => '2026-10-01',
        'end_date' => '2026-10-02',
    ], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])->assertCreated();

    $leaveId = (string) $applyResponse->json('data.leave.id');

    postJson('/api/leaves/'.$leaveId.'/approve', [], [
        'Authorization' => 'Bearer '.jwtFor($employeeUser),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('allows employee to list only own leaves', function (): void {
    $employeeOneUser = makeRoleUser('employee', 'employee.leave.list.one@example.com');
    $employeeTwoUser = makeRoleUser('employee', 'employee.leave.list.two@example.com');

    $employeeOne = makeEmployeeForUser($employeeOneUser, '7008');
    $employeeTwo = makeEmployeeForUser($employeeTwoUser, '7009');

    LeaveRequest::query()->create([
        'employee_id' => $employeeOne->id,
        'description' => 'Own leave',
        'start_date' => '2026-11-01',
        'end_date' => '2026-11-01',
        'total_days' => 1,
        'status' => 'pending',
    ]);

    LeaveRequest::query()->create([
        'employee_id' => $employeeTwo->id,
        'description' => 'Other leave',
        'start_date' => '2026-11-02',
        'end_date' => '2026-11-02',
        'total_days' => 1,
        'status' => 'pending',
    ]);

    getJson('/api/leaves?per_page=10', [
        'Authorization' => 'Bearer '.jwtFor($employeeOneUser),
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.leaves.0.employee.id', $employeeOne->id);
});
