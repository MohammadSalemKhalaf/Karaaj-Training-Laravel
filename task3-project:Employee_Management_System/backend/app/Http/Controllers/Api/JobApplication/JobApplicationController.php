<?php

namespace App\Http\Controllers\Api\JobApplication;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobApplication\ApplyJobApplicationRequest;
use App\Http\Requests\JobApplication\ApplyJobVacancyRequest;
use App\Http\Requests\JobApplication\ApproveJobApplicationRequest;
use App\Http\Requests\ListRankedApplicationsRequest;
use App\Http\Resources\JobApplication\JobApplicationResource;
use App\Http\Resources\RecruitmentDashboardResource;
use App\Services\JobApplication\JobApplicationService;
use App\Services\Recruitment\RecruitmentDashboardService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobApplicationController extends Controller
{
    public function __construct(
        private readonly JobApplicationService $jobApplicationService,
        private readonly RecruitmentDashboardService $dashboardService,
    ) {
    }

    public function apply(string $vacancyId, ApplyJobVacancyRequest $request): JsonResponse
    {
        try {
            $application = $this->jobApplicationService->applyForVacancy(
                $vacancyId,
                auth()->id() ?? '',
                $request->validated()
            );

            return ApiResponse::success(
                'Application submitted successfully.',
                ['application' => JobApplicationResource::make($application)],
                [],
                201
            );
        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                'VALIDATION_ERROR',
                422
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to submit application.',
                ['error' => [$e->getMessage()]],
                'APPLICATION_FAILED',
                500
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'status',
                'job_vacancy_id',
                'company_id',
                'user_id',
                'per_page',
            ]);

            $applications = $this->jobApplicationService->listApplications($filters);

            return ApiResponse::success(
                'Applications fetched successfully.',
                [
                    'applications' => JobApplicationResource::collection($applications->items()),
                ],
                [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to fetch applications.',
                ['error' => [$e->getMessage()]],
                'FETCH_FAILED',
                500
            );
        }
    }

    public function approve(string $id, ApproveJobApplicationRequest $request): JsonResponse
    {
        try {
            // Approve application and create employee
            $application = $this->jobApplicationService->approveApplication($id, $request->validated());

            return ApiResponse::success(
                'Job application approved and employee created successfully.',
                ['application' => JobApplicationResource::make($application)],
                [],
                200
            );
        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Validation failed.',
                $e->errors(),
                'VALIDATION_ERROR',
                422
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to approve application.',
                ['error' => [$e->getMessage()]],
                'APPROVAL_FAILED',
                500
            );
        }
    }

    /**
     * Get ranked applications with filtering and sorting.
     */
    public function rankedApplications(ListRankedApplicationsRequest $request): JsonResponse
    {
        try {
            $filters = $request->validated();
            $perPage = (int) ($filters['per_page'] ?? 15);

            $applications = $this->jobApplicationService->listRankedApplications($filters, $perPage);

            return ApiResponse::success(
                'Ranked applications fetched successfully.',
                [
                    'applications' => JobApplicationResource::collection($applications->items()),
                ],
                [
                    'current_page' => $applications->currentPage(),
                    'last_page' => $applications->lastPage(),
                    'per_page' => $applications->perPage(),
                    'total' => $applications->total(),
                ]
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to fetch ranked applications.',
                ['error' => [$e->getMessage()]],
                'FETCH_FAILED',
                500
            );
        }
    }

    /**
     * Get recruiter dashboard metrics.
     */
    public function dashboard(): JsonResponse
    {
        try {
            $metrics = $this->dashboardService->getDashboardMetrics();

            return ApiResponse::success(
                'Dashboard metrics fetched successfully.',
                ['dashboard' => new RecruitmentDashboardResource($metrics)],
                []
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to fetch dashboard metrics.',
                ['error' => [$e->getMessage()]],
                'FETCH_FAILED',
                500
            );
        }
    }

    /**
     * Get top candidates by AI score.
     */
    public function topCandidates(): JsonResponse
    {
        try {
            $limit = (int) (request('limit') ?? 5);
            $candidates = $this->jobApplicationService->getTopCandidates($limit);

            return ApiResponse::success(
                'Top candidates fetched successfully.',
                [
                    'candidates' => JobApplicationResource::collection($candidates),
                ],
                []
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to fetch top candidates.',
                ['error' => [$e->getMessage()]],
                'FETCH_FAILED',
                500
            );
        }
    }

    /**
     * Get low score candidates.
     */
    public function lowScoreCandidates(): JsonResponse
    {
        try {
            $limit = (int) (request('limit') ?? 5);
            $candidates = $this->jobApplicationService->getLowScoreCandidates($limit);

            return ApiResponse::success(
                'Low score candidates fetched successfully.',
                [
                    'candidates' => JobApplicationResource::collection($candidates),
                ],
                []
            );
        } catch (\Exception $e) {
            return ApiResponse::error(
                'Failed to fetch low score candidates.',
                ['error' => [$e->getMessage()]],
                'FETCH_FAILED',
                500
            );
        }
    }
}
