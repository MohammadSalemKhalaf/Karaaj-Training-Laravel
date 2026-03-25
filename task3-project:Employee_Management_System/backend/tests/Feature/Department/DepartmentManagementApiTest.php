<?php

use App\Models\Department;
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

function createUserByRole(string $roleName, string $email): User
{
    $roleId = (string) Role::query()->where('name', $roleName)->value('id');

    return User::query()->create([
        'role_id' => $roleId,
        'name' => ucfirst($roleName).' User',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function authToken(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

it('creates department', function (): void {
    $admin = createUserByRole('admin', 'admin.department.create@example.com');

    postJson('/api/departments', [
        'name' => 'Technology',
        'code' => 'TECH',
        'description' => 'Technology team',
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.authToken($admin),
    ])
        ->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.department.code', 'TECH');
});

it('assigns manager to department', function (): void {
    $admin = createUserByRole('admin', 'admin.department.assign@example.com');
    $manager = createUserByRole('manager', 'manager.department.assign@example.com');

    postJson('/api/departments', [
        'name' => 'Operations',
        'code' => 'OPS',
        'manager_user_id' => $manager->id,
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.authToken($admin),
    ])
        ->assertCreated()
        ->assertJsonPath('data.department.manager.email', 'manager.department.assign@example.com');
});

it('blocks invalid manager role', function (): void {
    $admin = createUserByRole('admin', 'admin.department.invalid-manager@example.com');
    $employee = createUserByRole('employee', 'employee.department.invalid-manager@example.com');

    postJson('/api/departments', [
        'name' => 'Support',
        'code' => 'SUP',
        'manager_user_id' => $employee->id,
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.authToken($admin),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('updates department', function (): void {
    $manager = createUserByRole('manager', 'manager.department.update@example.com');

    $department = Department::query()->create([
        'name' => 'Sales',
        'code' => 'SAL',
        'status' => 'active',
    ]);

    putJson('/api/departments/'.$department->id, [
        'name' => 'Sales Updated',
        'code' => 'SAL-UPD',
        'status' => 'inactive',
        'manager_user_id' => null,
    ], [
        'Authorization' => 'Bearer '.authToken($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.department.name', 'Sales Updated')
        ->assertJsonPath('data.department.status', 'inactive');
});

it('deletes department', function (): void {
    $admin = createUserByRole('admin', 'admin.department.delete@example.com');

    $department = Department::query()->create([
        'name' => 'Legal',
        'code' => 'LEG',
        'status' => 'active',
    ]);

    deleteJson('/api/departments/'.$department->id, [], [
        'Authorization' => 'Bearer '.authToken($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    expect(Department::query()->find($department->id))->toBeNull();
});

it('lists and filters departments', function (): void {
    $manager = createUserByRole('manager', 'manager.department.list@example.com');

    Department::query()->create([
        'name' => 'Engineering',
        'code' => 'ENG',
        'status' => 'active',
    ]);

    Department::query()->create([
        'name' => 'Finance',
        'code' => 'FIN',
        'status' => 'inactive',
    ]);

    getJson('/api/departments?search=Engineering&status=active&per_page=1', [
        'Authorization' => 'Bearer '.authToken($manager),
    ])
        ->assertSuccessful()
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('data.departments.0.code', 'ENG');
});

it('blocks employee role from managing departments', function (): void {
    $employee = createUserByRole('employee', 'employee.department.blocked@example.com');

    getJson('/api/departments', [
        'Authorization' => 'Bearer '.authToken($employee),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});
