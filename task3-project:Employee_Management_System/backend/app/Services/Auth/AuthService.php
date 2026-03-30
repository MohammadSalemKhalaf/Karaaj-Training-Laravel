<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Repositories\Auth\AuthRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\JWTGuard;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    public function __construct(private readonly AuthRepository $authRepository)
    {
    }

    /**
     * @param array<string, mixed> $validated
     * @return array<string, mixed>
     */
    public function register(array $validated): array
    {
        $defaultRole = $this->authRepository->getDefaultEmployeeRole();

        $user = $this->authRepository->createUser([
            'role_id' => $defaultRole->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $freshUser = $this->authRepository->findUserById($user->id) ?? $user;

        Log::channel('ems')->info('User registration completed', [
            'event' => 'auth.registered',
            'user_id' => $freshUser->id,
            'email' => $freshUser->email,
            'performed_by' => $freshUser->id,
            'ip' => request()?->ip(),
        ]);

        return [
            'user' => $freshUser,
        ];
    }

    /**
     * @param array<string, string> $credentials
     * @return array<string, mixed>|null
     */
    public function login(array $credentials): ?array
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $token = $guard->attempt($credentials);

        if (! $token) {
            return null;
        }

        /** @var User $user */
        $user = $guard->user();
        $user->forceFill(['last_login_at' => now()])->save();
        $user->loadMissing('role');

        Log::channel('ems')->info('We are logged in to the EMS OSP', [
            'event' => 'auth.login.success',
            'user_id' => $user->id,
            'email' => $user->email,
            'performed_by' => $user->id,
            'ip' => request()?->ip(),
        ]);

        return $this->buildAuthPayload($user, $token);
    }

    public function logout(): void
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        /** @var User $user */
        $user = $guard->user();

        $guard->logout();

        Log::channel('ems')->info('User logout completed', [
            'event' => 'auth.logout',
            'user_id' => $user->id,
            'email' => $user->email,
            'performed_by' => $user->id,
            'ip' => request()?->ip(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        $token = $guard->refresh();

        /** @var User $user */
        $user = JWTAuth::setToken($token)->toUser();
        $user->loadMissing('role');

        return $this->buildAuthPayload($user, $token);
    }

    public function me(): User
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        /** @var User $user */
        $user = $guard->user();

        return $user->loadMissing('role');
    }

    /**
     * @return array<string, mixed>
     */
    private function buildAuthPayload(User $user, string $token): array
    {
        /** @var JWTGuard $guard */
        $guard = auth('api');

        return [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
        ];
    }
}
