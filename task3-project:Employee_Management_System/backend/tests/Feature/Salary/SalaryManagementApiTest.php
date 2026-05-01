<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Salary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
});

function roleIdentifier(string $roleName): string
{
    return (string) Role::query()->where('name', $roleName)->value('id');
}

function makeUser(string $roleName, string $email): User
{
    return User::query()->create([
        'role_id' => roleIdentifier($roleName),
        'name' => ucfirst($roleName).' Account',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function authTokenFor(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function makeEmployee(string $suffix): Employee
{
    $department = Department::query()->create([
        'name' => 'Department '.$suffix,
        'code' => 'D'.$suffix,
        'status' => 'active',
    ]);

    $user = makeUser('employee', 'salary.'.$suffix.'@example.com');

    return Employee::query()->create([
        'user_id' => $user->id,
        'department_id' => $department->id,
        'employee_code' => 'EMP-'.$suffix,
        'first_name' => 'Emp',
        'last_name' => $suffix,
        'email' => 'emp.'.$suffix.'@company.test',
        'phone_number' => '+20111111'.$suffix,
        'hire_date' => '2026-01-01',
        'job_title' => 'Engineer',
        'employment_type' => 'full_time',
        'status' => 'active',
    ]);
}

it('creates salary record', function (): void {
    $admin = makeUser('admin', 'admin.salary.create@example.com');
    $employee = makeEmployee('1001');

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 1000,
        'bonuses' => 200,
        'deductions' => 50,
        'effective_date' => '2026-02-01',
    ], [
        'Authorization' => 'Bearer '.authTokenFor($admin),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.salary.employee.id', $employee->id);
});

it('calculates net salary correctly', function (): void {
    $manager = makeUser('manager', 'manager.salary.calc@example.com');
    $employee = makeEmployee('1002');

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 1000,
        'bonuses' => 250,
        'deductions' => 100,
        'effective_date' => '2026-02-01',
    ], [
        'Authorization' => 'Bearer '.authTokenFor($manager),
    ])
        ->assertCreated()
        ->assertJsonPath('data.salary.net_salary', 1150);
});

it('supports multiple salary records per employee', function (): void {
    $admin = makeUser('admin', 'admin.salary.multi@example.com');
    $employee = makeEmployee('1003');
    $token = authTokenFor($admin);

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 1000,
        'bonuses' => 100,
        'deductions' => 20,
        'effective_date' => '2026-01-01',
    ], ['Authorization' => 'Bearer '.$token])->assertCreated();

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 1200,
        'bonuses' => 100,
        'deductions' => 10,
        'effective_date' => '2026-02-01',
    ], ['Authorization' => 'Bearer '.$token])->assertCreated();

    getJson('/api/employees/'.$employee->id.'/salaries', [
        'Authorization' => 'Bearer '.$token,
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 2);
});

it('returns employee salary history', function (): void {
    $manager = makeUser('manager', 'manager.salary.history@example.com');
    $employee = makeEmployee('1004');

    Salary::query()->create([
        'employee_id' => $employee->id,
        'amount' => 1300,
        'bonuses' => 100,
        'deductions' => 50,
        'net_salary' => 1350,
        'effective_date' => '2026-03-01',
    ]);

    getJson('/api/employees/'.$employee->id.'/salaries', [
        'Authorization' => 'Bearer '.authTokenFor($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.salaries.0.employee.id', $employee->id);
});

it('blocks invalid salary values', function (): void {
    $admin = makeUser('admin', 'admin.salary.invalid@example.com');
    $employee = makeEmployee('1005');

    postJson('/api/salaries', [
        'employee_id' => $employee->id,
        'amount' => 100,
        'bonuses' => 0,
        'deductions' => 500,
        'effective_date' => '2026-03-01',
    ], [
        'Authorization' => 'Bearer '.authTokenFor($admin),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('filters salary list by employee and date range', function (): void {
    $manager = makeUser('manager', 'manager.salary.filter@example.com');
    $employee = makeEmployee('1006');

    Salary::query()->create([
        'employee_id' => $employee->id,
        'amount' => 1000,
        'bonuses' => 100,
        'deductions' => 50,
        'net_salary' => 1050,
        'effective_date' => '2026-01-01',
    ]);

    Salary::query()->create([
        'employee_id' => $employee->id,
        'amount' => 1100,
        'bonuses' => 120,
        'deductions' => 20,
        'net_salary' => 1200,
        'effective_date' => '2026-03-01',
    ]);

    getJson('/api/salaries?employee_id='.$employee->id.'&from_date=2026-02-01&to_date=2026-03-31&per_page=10', [
        'Authorization' => 'Bearer '.authTokenFor($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.total', 1)
        ->assertJsonPath('data.salaries.0.effective_date', '2026-03-01T00:00:00.000000Z');
});

it('blocks employee role from managing salaries', function (): void {
    $employeeUser = makeUser('employee', 'employee.salary.blocked@example.com');

    getJson('/api/salaries', [
        'Authorization' => 'Bearer '.authTokenFor($employeeUser),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});
