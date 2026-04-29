<?php

namespace App\Repositories\JobApplication;

use App\Models\JobApplication;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JobApplicationRepository
{
    /**
     * @param array<string, mixed> $filters
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $status = (string) ($filters['status'] ?? '');
        $jobVacancyId = (string) ($filters['job_vacancy_id'] ?? '');
        $companyId = (string) ($filters['company_id'] ?? '');
        $userId = (string) ($filters['user_id'] ?? '');

        return JobApplication::query()
            ->with(['user', 'jobVacancy', 'resume', 'jobVacancy.company'])
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($jobVacancyId !== '', fn ($query) => $query->where('job_vacancy_id', $jobVacancyId))
            ->when($userId !== '', fn ($query) => $query->where('user_id', $userId))
            ->when($companyId !== '', function ($query) use ($companyId): void {
                $query->whereHas('jobVacancy', fn ($q) => $q->where('company_id', $companyId));
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(string $id): ?JobApplication
    {
        return JobApplication::query()
            ->with(['user', 'jobVacancy', 'resume'])
            ->find($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(JobApplication $application, array $data): JobApplication
    {
        $application->fill($data)->save();

        return $application->load(['user', 'jobVacancy', 'resume']);
    }

    public function applicationExists(string $applicationId): bool
    {
        return JobApplication::query()->where('id', $applicationId)->exists();
    }

    public function userHasAppliedToVacancy(string $userId, string $jobVacancyId): bool
    {
        return JobApplication::query()
            ->where('user_id', $userId)
            ->where('job_vacancy_id', $jobVacancyId)
            ->exists();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): JobApplication
    {
        /** @var JobApplication $application */
        $application = JobApplication::query()->create($data);

        return $application->load(['user', 'resume', 'jobVacancy']);
    }

    /**
     * Get ranked applications with advanced filtering and sorting.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateRanked(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $status = (string) ($filters['status'] ?? '');
        $jobVacancyId = (string) ($filters['job_vacancy_id'] ?? '');
        $companyId = (string) ($filters['company_id'] ?? '');
        $minScore = isset($filters['min_score']) ? (int) $filters['min_score'] : null;
        $maxScore = isset($filters['max_score']) ? (int) $filters['max_score'] : null;
        $sortBy = (string) ($filters['sort_by'] ?? 'score');
        $sortDirection = (string) ($filters['sort_direction'] ?? 'desc');

        $query = JobApplication::query()
            ->with(['user', 'jobVacancy', 'resume', 'jobVacancy.company'])
            ->when($status !== '', fn ($q) => $q->where('status', $status))
            ->when($jobVacancyId !== '', fn ($q) => $q->where('job_vacancy_id', $jobVacancyId))
            ->when($minScore !== null, fn ($q) => $q->where('ai_generated_score', '>=', $minScore))
            ->when($maxScore !== null, fn ($q) => $q->where('ai_generated_score', '<=', $maxScore))
            ->when($companyId !== '', function ($query) use ($companyId): void {
                $query->whereHas('jobVacancy', fn ($q) => $q->where('company_id', $companyId));
            });

        // Apply sorting
        if ($sortBy === 'score') {
            $query->orderBy('ai_generated_score', strtoupper($sortDirection));
        } elseif ($sortBy === 'status') {
            $query->orderBy('status', strtoupper($sortDirection));
        } elseif ($sortBy === 'date') {
            $query->orderBy('created_at', strtoupper($sortDirection));
        } else {
            $query->orderByDesc('ai_generated_score');
        }

        // Secondary sort by created_at desc
        $query->orderByDesc('created_at');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get top candidates by score.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopCandidates(int $limit = 5)
    {
        return JobApplication::query()
            ->with(['user', 'jobVacancy', 'resume'])
            ->whereNotNull('ai_generated_score')
            ->orderByDesc('ai_generated_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get low score candidates.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowScoreCandidates(int $limit = 5)
    {
        return JobApplication::query()
            ->with(['user', 'jobVacancy', 'resume'])
            ->whereNotNull('ai_generated_score')
            ->orderBy('ai_generated_score')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
