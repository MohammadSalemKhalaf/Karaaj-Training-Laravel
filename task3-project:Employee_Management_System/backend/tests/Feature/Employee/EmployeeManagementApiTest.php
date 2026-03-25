<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
});

function roleId(string $name): string
{
    return (string) Role::query()->where('name', $name)->value('id');
}

function createUserForRole(string $roleName, string $email): User
{
    return User::query()->create([
        'role_id' => roleId($roleName),
        'name' => ucfirst($roleName).' Account',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function tokenFor(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function createDepartment(string $code = 'ENG'): Department
{
    return Department::query()->create([
        'name' => 'Engineering '.$code,
        'code' => $code,
        'status' => 'active',
    ]);
}

it('creates an employee', function (): void {
    $admin = createUserForRole('admin', 'admin.employee.create@example.com');
    $candidateUser = createUserForRole('employee', 'candidate.employee.create@example.com');
    $department = createDepartment('ENG-CREATE');

    postJson('/api/employees', [
        'user_id' => $candidateUser->id,
        'department_id' => $department->id,
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'email' => 'jane.doe@company.test',
        'phone_number' => '+201000000001',
        'hire_date' => '2026-01-01',
        'job_title' => 'Software Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.tokenFor($admin),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.employee.employee_code', 'EMP-0001');
});

it('blocks duplicate user id assignment', function (): void {
    $admin = createUserForRole('admin', 'admin.employee.duplicate-user@example.com');
    $assignedUser = createUserForRole('employee', 'already.assigned@example.com');
    $anotherUser = createUserForRole('employee', 'another.user@example.com');
    $department = createDepartment('ENG-DUP-USER');

    Employee::query()->create([
        'user_id' => $assignedUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-2001',
        'first_name' => 'Assigned',
        'last_name' => 'User',
        'email' => 'assigned@company.test',
        'phone_number' => '+201000000010',
        'hire_date' => '2026-01-01',
        'job_title' => 'Analyst',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);

    postJson('/api/employees', [
        'user_id' => $assignedUser->id,
        'department_id' => $department->id,
        'first_name' => 'Duplicate',
        'last_name' => 'Assignment',
        'email' => 'duplicate.assignment@company.test',
        'phone_number' => '+201000000011',
        'hire_date' => '2026-01-02',
        'job_title' => 'Support Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.tokenFor($admin),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');

    expect(Employee::query()->where('user_id', $anotherUser->id)->doesntExist())->toBeTrue();
});

it('auto increments employee code during create', function (): void {
    $manager = createUserForRole('manager', 'manager.employee.code@example.com');
    $department = createDepartment('ENG-CODE');

    $firstUser = createUserForRole('employee', 'first.code.auto@example.com');
    $secondUser = createUserForRole('employee', 'second.code.auto@example.com');

    $firstResponse = postJson('/api/employees', [
        'user_id' => $firstUser->id,
        'department_id' => $department->id,
        'first_name' => 'First',
        'last_name' => 'Auto',
        'email' => 'first.auto@company.test',
        'phone_number' => '+201000000020',
        'hire_date' => '2026-01-03',
        'job_title' => 'Designer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.tokenFor($manager),
    ]);

    $secondResponse = postJson('/api/employees', [
        'user_id' => $secondUser->id,
        'department_id' => $department->id,
        'first_name' => 'Second',
        'last_name' => 'Auto',
        'email' => 'second.auto@company.test',
        'phone_number' => '+201000000021',
        'hire_date' => '2026-01-04',
        'job_title' => 'Designer',
        'employment_type' => 'part_time',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.tokenFor($manager),
    ]);

    $firstResponse->assertCreated()
        ->assertJsonPath('data.employee.employee_code', 'EMP-0001');

    $secondResponse->assertCreated()
        ->assertJsonPath('data.employee.employee_code', 'EMP-0002');
});

it('updates an employee', function (): void {
    $manager = createUserForRole('manager', 'manager.employee.update@example.com');
    $user = createUserForRole('employee', 'employee.update@example.com');
    $department = createDepartment('ENG-UPD');

    $employee = Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-4001',
        'first_name' => 'Old',
        'last_name' => 'Name',
        'email' => 'old.name@company.test',
        'phone_number' => '+201000000030',
        'hire_date' => '2026-01-05',
        'job_title' => 'QA Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);

    putJson('/api/employees/'.$employee->id, [
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-4001',
        'first_name' => 'Updated',
        'last_name' => 'Name',
        'email' => 'updated.name@company.test',
        'phone_number' => '+201000000031',
        'hire_date' => '2026-01-05',
        'job_title' => 'Senior QA Engineer',
        'employment_type' => 'contract',
        'status' => 'inactive',
    ], [
        'Authorization' => 'Bearer '.tokenFor($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.employee.full_name', 'Updated Name')
        ->assertJsonPath('data.employee.employment_type', 'contract');
});

it('deletes an employee', function (): void {
    $admin = createUserForRole('admin', 'admin.employee.delete@example.com');
    $user = createUserForRole('employee', 'employee.delete@example.com');
    $department = createDepartment('ENG-DEL');

    $employee = Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-5001',
        'first_name' => 'Delete',
        'last_name' => 'Me',
        'email' => 'delete.me@company.test',
        'phone_number' => '+201000000040',
        'hire_date' => '2026-01-06',
        'job_title' => 'Accountant',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);

    deleteJson('/api/employees/'.$employee->id, [], [
        'Authorization' => 'Bearer '.tokenFor($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    expect(Employee::query()->find($employee->id))->toBeNull();
});

it('supports filtering and pagination', function (): void {
    $admin = createUserForRole('admin', 'admin.employee.filter@example.com');
    $department = createDepartment('ENG-FILTER');

    $aliceUser = createUserForRole('employee', 'alice.employee@example.com');
    Employee::query()->create([
        'user_id' => $aliceUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-6001',
        'first_name' => 'Alice',
        'last_name' => 'Filter',
        'email' => 'alice.filter@company.test',
        'phone_number' => '+201000000050',
        'hire_date' => '2026-01-07',
        'job_title' => 'Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);

    $bobUser = createUserForRole('employee', 'bob.employee@example.com');
    Employee::query()->create([
        'user_id' => $bobUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-6002',
        'first_name' => 'Bob',
        'last_name' => 'Filter',
        'email' => 'bob.filter@company.test',
        'phone_number' => '+201000000051',
        'hire_date' => '2026-01-08',
        'job_title' => 'Engineer',
        'employment_type' => 'part_time',
        'status' => 'inactive',
    ]);

    getJson('/api/employees?search=alice&department_id='.$department->id.'&status=active&employment_type=full_time&per_page=1', [
        'Authorization' => 'Bearer '.tokenFor($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('data.employees.0.email', 'alice.filter@company.test');
});

it('blocks employee role from employee module', function (): void {
    $employeeUser = createUserForRole('employee', 'blocked.employee.module@example.com');

    getJson('/api/employees', [
        'Authorization' => 'Bearer '.tokenFor($employeeUser),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('returns only unassigned employee-role users for employee selection', function (): void {
    $manager = createUserForRole('manager', 'manager.selection@example.com');
    $department = createDepartment('ENG-SEL');

    $availableEmployeeUser = createUserForRole('employee', 'available.employee@example.com');
    $assignedEmployeeUser = createUserForRole('employee', 'assigned.employee@example.com');
    createUserForRole('admin', 'admin.selection@example.com');
    createUserForRole('manager', 'other.manager.selection@example.com');

    Employee::query()->create([
        'user_id' => $assignedEmployeeUser->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-7001',
        'first_name' => 'Assigned',
        'last_name' => 'Employee',
        'email' => 'assigned.employee@company.test',
        'phone_number' => '+201000000070',
        'hire_date' => '2026-01-09',
        'job_title' => 'Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);

    $response = getJson('/api/users/available-for-employee', [
        'Authorization' => 'Bearer '.tokenFor($manager),
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonFragment([
            'email' => $availableEmployeeUser->email,
        ])
        ->assertJsonMissing([
            'email' => $assignedEmployeeUser->email,
        ])
        ->assertJsonMissing([
            'email' => 'admin.selection@example.com',
        ])
        ->assertJsonMissing([
            'email' => 'other.manager.selection@example.com',
        ]);
});
