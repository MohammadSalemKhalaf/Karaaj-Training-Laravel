<?php

namespace App\Http\Controllers\Api\JobVacancy;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobVacancy\StoreJobVacancyRequest;
use App\Http\Requests\JobVacancy\UpdateJobVacancyRequest;
use App\Http\Resources\JobVacancy\JobVacancyResource;
use App\Services\JobVacancy\JobVacancyService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobVacancyController extends Controller
{
    public function __construct(private readonly JobVacancyService $vacancyService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $vacancies = $this->vacancyService->getVacancies($request->only([
            'search',
            'title',
            'company_id',
            'category_id',
            'type',
            'status',
            'per_page',
        ]));

        return ApiResponse::success(
            'Job vacancies fetched successfully.',
            [
                'vacancies' => JobVacancyResource::collection($vacancies->items()),
            ],
            [
                'current_page' => $vacancies->currentPage(),
                'last_page' => $vacancies->lastPage(),
                'per_page' => $vacancies->perPage(),
                'total' => $vacancies->total(),
            ]
        );
    }

    public function show(string $id): JsonResponse
    {
        $vacancy = $this->vacancyService->getVacancyById($id);

        if (! $vacancy) {
            return ApiResponse::error(
                'Job vacancy not found.',
                ['vacancy' => ['The requested vacancy does not exist.']],
                'VACANCY_NOT_FOUND',
                404
            );
        }

        return ApiResponse::success(
            'Job vacancy fetched successfully.',
            ['vacancy' => JobVacancyResource::make($vacancy)]
        );
    }

    public function store(StoreJobVacancyRequest $request): JsonResponse
    {
        $vacancy = $this->vacancyService->createVacancy($request->validated());

        return ApiResponse::success(
            'Job vacancy created successfully.',
            ['vacancy' => JobVacancyResource::make($vacancy)],
            [],
            201
        );
    }

    public function update(UpdateJobVacancyRequest $request, string $id): JsonResponse
    {
        $vacancy = $this->vacancyService->getVacancyById($id);

        if (! $vacancy) {
            return ApiResponse::error(
                'Job vacancy not found.',
                ['vacancy' => ['The requested vacancy does not exist.']],
                'VACANCY_NOT_FOUND',
                404
            );
        }

        $updatedVacancy = $this->vacancyService->updateVacancy($vacancy, $request->validated());

        return ApiResponse::success(
            'Job vacancy updated successfully.',
            ['vacancy' => JobVacancyResource::make($updatedVacancy)]
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $vacancy = $this->vacancyService->getVacancyById($id);

        if (! $vacancy) {
            return ApiResponse::error(
                'Job vacancy not found.',
                ['vacancy' => ['The requested vacancy does not exist.']],
                'VACANCY_NOT_FOUND',
                404
            );
        }

        $this->vacancyService->deleteVacancy($vacancy);

        return ApiResponse::success('Job vacancy deleted successfully.');
    }
}
