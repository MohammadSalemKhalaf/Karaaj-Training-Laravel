<?php

namespace App\Repositories\Auth;

use App\Models\Role;
use App\Models\User;

class AuthRepository
{
    public function getDefaultEmployeeRole(): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => 'employee'],
            ['description' => 'Default role for newly registered users.']
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createUser(array $data): User
    {
        return User::query()->create($data);
    }

    public function findUserById(string $id): ?User
    {
        return User::query()->with('role')->find($id);
    }
}
