<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\Auth\AuthUserResource;
use App\Services\Auth\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $payload = $this->authService->register($request->validated());

        return ApiResponse::success(
            'User registered successfully. Please login.',
            [
                'user' => AuthUserResource::make($payload['user']),
            ],
            [],
            201
        );
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $payload = $this->authService->login($request->validated());

        if (! $payload) {
            return ApiResponse::error(
                'Invalid credentials provided.',
                ['email' => ['The provided credentials are incorrect.']],
                'AUTH_INVALID_CREDENTIALS',
                401
            );
        }

        return ApiResponse::success(
            'Login completed successfully.',
            [
                'user' => AuthUserResource::make($payload['user']),
                'token' => $payload['token'],
                'token_type' => $payload['token_type'],
            ],
            ['expires_in' => $payload['expires_in']]
        );
    }

    public function logout(): JsonResponse
    {
        $this->authService->logout();

        return ApiResponse::success('Logout completed successfully.');
    }

    public function refresh(): JsonResponse
    {
        $payload = $this->authService->refresh();

        return ApiResponse::success(
            'Token refreshed successfully.',
            [
                'user' => AuthUserResource::make($payload['user']),
                'token' => $payload['token'],
                'token_type' => $payload['token_type'],
            ],
            ['expires_in' => $payload['expires_in']]
        );
    }

    public function me(): JsonResponse
    {
        return ApiResponse::success(
            'Authenticated user profile fetched successfully.',
            AuthUserResource::make($this->authService->me())
        );
    }
}
