<?php

namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $roleId = (string) ($filters['role_id'] ?? '');
        $status = (string) ($filters['status'] ?? '');

        return User::query()
            ->with('role')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($roleId !== '', fn ($query) => $query->where('role_id', $roleId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?User
    {
        return User::query()->with('role')->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        /** @var User $user */
        $user = User::query()->create($data);

        return $user->load('role');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User
    {
        $user->fill($data)->save();

        return $user->load('role');
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}