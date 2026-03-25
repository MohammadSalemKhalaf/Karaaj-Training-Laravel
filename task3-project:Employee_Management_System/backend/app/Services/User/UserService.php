<?php

namespace App\Services\User;

use App\Models\Role;
use App\Models\User;
use App\Repositories\User\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserService
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getUsers(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->userRepository->paginate($filters, $perPage);
    }

    public function getUserById(string $id): ?User
    {
        return $this->userRepository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): User
    {
        $this->ensureRoleExists((string) $data['role_id']);

        $payload = $data;
        $payload['password'] = Hash::make((string) $data['password']);

        return $this->userRepository->create($payload);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateUser(User $user, array $data): User
    {
        $this->ensureRoleExists((string) $data['role_id']);

        $payload = $data;

        if (! empty($payload['password'])) {
            $payload['password'] = Hash::make((string) $payload['password']);
        } else {
            unset($payload['password']);
        }

        return $this->userRepository->update($user, $payload);
    }

    public function deleteUser(User $targetUser, User $authenticatedUser): void
    {
        if ($targetUser->id === $authenticatedUser->id) {
            throw ValidationException::withMessages([
                'user' => ['You cannot delete your own account.'],
            ]);
        }

        $this->userRepository->delete($targetUser);
    }

    /**
     * @return Collection<int, User>
     */
    public function getAvailableEmployees(): Collection
    {
        return $this->userRepository->getAvailableForEmployee();
    }

    private function ensureRoleExists(string $roleId): void
    {
        if (! Role::query()->whereKey($roleId)->exists()) {
            throw ValidationException::withMessages([
                'role_id' => ['The selected role is invalid.'],
            ]);
        }
    }
}