<?php

namespace App\Services\JobVacancy;

use App\Models\JobVacancy;
use App\Repositories\JobVacancy\JobVacancyRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class JobVacancyService
{
    public function __construct(private readonly JobVacancyRepository $vacancyRepository)
    {
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getVacancies(array $filters): LengthAwarePaginator
    {
        $perPage = max(1, min(100, (int) ($filters['per_page'] ?? 10)));

        return $this->vacancyRepository->paginate($filters, $perPage);
    }

    public function getVacancyById(string $id): ?JobVacancy
    {
        return $this->vacancyRepository->findById($id);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createVacancy(array $data): JobVacancy
    {
        $this->validateVacancyData($data);

        $vacancy = $this->vacancyRepository->create($data);

        Log::channel('ems')->info('Job vacancy created', [
            'event' => 'vacancy.created',
            'vacancy_id' => $vacancy->id,
            'company_id' => $vacancy->company_id,
            'title' => $vacancy->title,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $vacancy;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateVacancy(JobVacancy $vacancy, array $data): JobVacancy
    {
        $this->validateVacancyData($data);

        $updatedVacancy = $this->vacancyRepository->update($vacancy, $data);

        Log::channel('ems')->info('Job vacancy updated', [
            'event' => 'vacancy.updated',
            'vacancy_id' => $updatedVacancy->id,
            'company_id' => $updatedVacancy->company_id,
            'title' => $updatedVacancy->title,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);

        return $updatedVacancy;
    }

    public function deleteVacancy(JobVacancy $vacancy): void
    {
        $this->vacancyRepository->delete($vacancy);

        Log::channel('ems')->info('Job vacancy soft deleted', [
            'event' => 'vacancy.deleted',
            'vacancy_id' => $vacancy->id,
            'company_id' => $vacancy->company_id,
            'title' => $vacancy->title,
            'performed_by' => Auth::id(),
            'ip' => request()?->ip(),
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateVacancyData(array $data): void
    {
        if (! \App\Models\Company::query()->whereKey((string) $data['company_id'])->exists()) {
            throw ValidationException::withMessages([
                'company_id' => ['The selected company is invalid.'],
            ]);
        }

        if (isset($data['category_id']) && $data['category_id']) {
            if (! \App\Models\JobCategory::query()->whereKey((string) $data['category_id'])->exists()) {
                throw ValidationException::withMessages([
                    'category_id' => ['The selected job category is invalid.'],
                ]);
            }
        }
    }
}
