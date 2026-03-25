<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Services\User\UserService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(private readonly UserService $userService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getUsers($request->only([
            'search',
            'role_id',
            'status',
            'per_page',
        ]));

        return ApiResponse::success(
            'Users fetched successfully.',
            [
                'users' => UserResource::collection($users->items()),
            ],
            [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return ApiResponse::error(
                'User not found.',
                ['user' => ['The requested user does not exist.']],
                'USER_NOT_FOUND',
                404
            );
        }

        return ApiResponse::success(
            'User fetched successfully.',
            ['user' => UserResource::make($user)]
        );
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $user = $this->userService->createUser($request->validated());

        return ApiResponse::success(
            'User created successfully.',
            ['user' => UserResource::make($user)],
            [],
            201
        );
    }

    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return ApiResponse::error(
                'User not found.',
                ['user' => ['The requested user does not exist.']],
                'USER_NOT_FOUND',
                404
            );
        }

        $updatedUser = $this->userService->updateUser($user, $request->validated());

        return ApiResponse::success(
            'User updated successfully.',
            ['user' => UserResource::make($updatedUser)]
        );
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return ApiResponse::error(
                'User not found.',
                ['user' => ['The requested user does not exist.']],
                'USER_NOT_FOUND',
                404
            );
        }

        $authenticatedUser = $request->user('api');

        if (! $authenticatedUser) {
            return ApiResponse::error(
                'Unauthenticated.',
                ['auth' => ['Authentication token is missing or invalid.']],
                'AUTH_UNAUTHENTICATED',
                401
            );
        }

        $this->userService->deleteUser($user, $authenticatedUser);

        return ApiResponse::success('User deleted successfully.');
    }
}