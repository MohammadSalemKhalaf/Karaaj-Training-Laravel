<?php

namespace App\Repositories\JobVacancy;

use App\Models\JobVacancy;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobVacancyRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $search = (string) ($filters['search'] ?? '');
        $title = (string) ($filters['title'] ?? '');
        $companyId = (string) ($filters['company_id'] ?? '');
        $categoryId = (string) ($filters['category_id'] ?? '');
        $type = (string) ($filters['type'] ?? '');
        $status = (string) ($filters['status'] ?? '');

        return JobVacancy::query()
            ->with(['company', 'jobCategory'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($innerQuery) use ($search): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%')
                        ->orWhere('location', 'like', '%'.$search.'%');
                });
            })
            ->when($title !== '', fn ($query) => $query->where('title', 'like', '%'.$title.'%'))
            ->when($companyId !== '', fn ($query) => $query->where('company_id', $companyId))
            ->when($categoryId !== '', fn ($query) => $query->where('category_id', $categoryId))
            ->when($type !== '', fn ($query) => $query->where('type', $type))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?JobVacancy
    {
        return JobVacancy::query()
            ->with(['company', 'jobCategory'])
            ->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): JobVacancy
    {
        /** @var JobVacancy $vacancy */
        $vacancy = JobVacancy::query()->create($data);

        return $vacancy->load(['company', 'jobCategory']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(JobVacancy $vacancy, array $data): JobVacancy
    {
        $vacancy->fill($data)->save();

        return $vacancy->load(['company', 'jobCategory']);
    }

    public function delete(JobVacancy $vacancy): void
    {
        $vacancy->delete();
    }

    public function vacancyExists(string $vacancyId): bool
    {
        return JobVacancy::query()->where('id', $vacancyId)->exists();
    }
}
