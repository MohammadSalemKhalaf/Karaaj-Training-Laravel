<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create(['name' => 'admin', 'description' => 'Administrator role']);
    Role::query()->create(['name' => 'employee', 'description' => 'Employee role']);
    Role::query()->create(['name' => 'manager', 'description' => 'Manager role']);
});

function createUserWithRole(string $roleName, string $email): User
{
    $role = Role::query()->where('name', $roleName)->firstOrFail();

    return User::query()->create([
        'role_id' => $role->id,
        'name' => ucfirst($roleName).' User',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

function tokenForUser(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

it('allows admin to access users list', function (): void {
    $admin = createUserWithRole('admin', 'admin-list@example.com');
    User::factory()->count(3)->create();

    $response = getJson('/api/users', [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['users'],
            'meta' => ['current_page', 'last_page', 'per_page', 'total'],
        ]);
});

it('blocks non admin user from users endpoints', function (): void {
    $employee = createUserWithRole('employee', 'employee-blocked@example.com');

    getJson('/api/users', [
        'Authorization' => 'Bearer '.tokenForUser($employee),
    ])
        ->assertForbidden()
        ->assertJsonPath('code', 'AUTH_FORBIDDEN');
});

it('creates a new user from admin endpoint', function (): void {
    $admin = createUserWithRole('admin', 'admin-create@example.com');
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    $response = postJson('/api/users', [
        'name' => 'Created User',
        'email' => 'created.user@example.com',
        'password' => 'Qv9#Lm2@Tx7!Az4',
        'password_confirmation' => 'Qv9#Lm2@Tx7!Az4',
        'role_id' => $employeeRole->id,
        'status' => 'active',
    ], [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'created.user@example.com');

    $user = User::query()->where('email', 'created.user@example.com')->firstOrFail();
    expect(Hash::check('Qv9#Lm2@Tx7!Az4', $user->password))->toBeTrue();
});

it('updates a user and keeps password optional on update', function (): void {
    $admin = createUserWithRole('admin', 'admin-update@example.com');
    $managerRole = Role::query()->where('name', 'manager')->firstOrFail();
    $target = createUserWithRole('employee', 'target-update@example.com');
    $oldPasswordHash = $target->password;

    $response = putJson('/api/users/'.$target->id, [
        'name' => 'Updated Target User',
        'email' => 'updated.target@example.com',
        'role_id' => $managerRole->id,
        'status' => 'inactive',
    ], [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'updated.target@example.com')
        ->assertJsonPath('data.user.status', 'inactive');

    $target->refresh();

    expect($target->password)->toBe($oldPasswordHash);
});

it('deletes a user and prevents deleting self', function (): void {
    $admin = createUserWithRole('admin', 'admin-delete@example.com');
    $target = createUserWithRole('employee', 'delete-target@example.com');

    deleteJson('/api/users/'.$target->id, [], [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ])
        ->assertSuccessful()
        ->assertJsonPath('success', true);

    expect(User::query()->find($target->id))->toBeNull();

    deleteJson('/api/users/'.$admin->id, [], [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});

it('returns paginated users with filtering support', function (): void {
    $admin = createUserWithRole('admin', 'admin-pagination@example.com');
    $employeeRole = Role::query()->where('name', 'employee')->firstOrFail();

    User::query()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Alice Filter',
        'email' => 'alice.filter@example.com',
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);

    User::query()->create([
        'role_id' => $employeeRole->id,
        'name' => 'Bob Filter',
        'email' => 'bob.filter@example.com',
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'inactive',
    ]);

    $response = getJson('/api/users?search=alice&status=active&role_id='.$employeeRole->id.'&per_page=1', [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('meta.per_page', 1)
        ->assertJsonPath('data.users.0.email', 'alice.filter@example.com');
});

it('validates duplicate email role and status on create', function (): void {
    $admin = createUserWithRole('admin', 'admin-validation@example.com');
    createUserWithRole('employee', 'duplicate@example.com');

    postJson('/api/users', [
        'name' => 'Duplicate User',
        'email' => 'duplicate@example.com',
        'password' => 'StrongP@ssw0rd',
        'password_confirmation' => 'StrongP@ssw0rd',
        'role_id' => '00000000-0000-0000-0000-000000000000',
        'status' => 'unknown',
    ], [
        'Authorization' => 'Bearer '.tokenForUser($admin),
    ])
        ->assertStatus(422)
        ->assertJsonPath('code', 'VALIDATION_ERROR');
});