<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\JWTGuard;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    Role::query()->create([
        'name' => 'employee',
        'description' => 'Default employee role.',
    ]);
});

it('registers a new user without issuing jwt token', function (): void {
    $securePassword = 'Qv9#Lm2@Tx7!Az4';

    $response = postJson('/api/auth/register', [
        'name' => 'Jane Employee',
        'email' => 'jane@example.com',
        'password' => $securePassword,
        'password_confirmation' => $securePassword,
    ]);

    $response->assertCreated()
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'User registered successfully. Please login.')
        ->assertJsonPath('data.user.email', 'jane@example.com')
        ->assertJsonMissingPath('data.token')
        ->assertJsonMissingPath('data.token_type')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['user'],
            'meta',
        ]);

    $user = User::query()->where('email', 'jane@example.com')->firstOrFail();
    expect(Hash::check($securePassword, $user->password))->toBeTrue();
});

it('logs in successfully with valid credentials', function (): void {
    $role = Role::query()->where('name', 'employee')->firstOrFail();

    User::query()->create([
        'role_id' => $role->id,
        'name' => 'John Employee',
        'email' => 'john@example.com',
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);

    $response = postJson('/api/auth/login', [
        'email' => 'john@example.com',
        'password' => 'StrongP@ssw0rd',
    ]);

    $response->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.user.email', 'john@example.com')
        ->assertJsonStructure([
            'success',
            'message',
            'data' => ['user', 'token', 'token_type'],
            'meta' => ['expires_in'],
        ]);
});

it('rejects login with invalid credentials', function (): void {
    $response = postJson('/api/auth/login', [
        'email' => 'missing@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertUnauthorized()
        ->assertJsonPath('success', false)
        ->assertJsonPath('code', 'AUTH_INVALID_CREDENTIALS');
});

it('blocks unauthenticated access to me endpoint', function (): void {
    getJson('/api/auth/me')
        ->assertUnauthorized();
});

it('validates token access for protected me endpoint', function (): void {
    $role = Role::query()->where('name', 'employee')->firstOrFail();

    $user = User::query()->create([
        'role_id' => $role->id,
        'name' => 'Mina Employee',
        'email' => 'mina@example.com',
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);

    /** @var JWTGuard $guard */
    $guard = auth('api');
    $token = $guard->login($user);

    getJson('/api/auth/me', ['Authorization' => 'Bearer '.$token])
        ->assertSuccessful()
        ->assertJsonPath('success', true)
        ->assertJsonPath('data.email', 'mina@example.com');
});
