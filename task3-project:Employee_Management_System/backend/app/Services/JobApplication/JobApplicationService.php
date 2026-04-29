<?php

namespace App\Services\JobApplication;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\User;
use App\Repositories\JobApplication\JobApplicationRepository;
use App\Services\Employee\EmployeeService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Services\AI\ResumeAnalysisService;

class JobApplicationService
{
    public function __construct(
        private readonly JobApplicationRepository $jobApplicationRepository,
        private readonly EmployeeService $employeeService,
        private readonly ResumeAnalysisService $resumeAnalysisService,
    ) {
    }

    /**
     * List job applications with filtering.
     *
     * @param array<string, mixed> $filters
     */
    public function listApplications(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->jobApplicationRepository->paginate($filters, 10);
    }

    /**
     * Candidate applies for a job vacancy.
     *
     * @param string $vacancyId The ID of the JobVacancy
     * @param string $userId The ID of the candidate (User)
     * @param array<string, mixed> $data Must contain resume_id
     *
     * @throws ValidationException
     */
    public function applyForVacancy(string $vacancyId, string $userId, array $data): JobApplication
    {
        $application = DB::transaction(function () use ($vacancyId, $userId, $data) {
            // 1. Validate vacancy exists
            $vacancy = JobVacancy::query()->find($vacancyId);
            if (! $vacancy) {
                throw ValidationException::withMessages([
                    'vacancy' => ['The job vacancy does not exist.'],
                ]);
            }

            // 2. Validate user exists
            $user = User::query()->find($userId);
            if (! $user) {
                throw ValidationException::withMessages([
                    'user' => ['The user does not exist.'],
                ]);
            }

            // 3. Validate resume exists (if provided)
            $resumeId = (string) ($data['resume_id'] ?? '');
            if ($resumeId) {
                $resume = \App\Models\Resume::query()->find($resumeId);
                if (! $resume) {
                    throw ValidationException::withMessages([
                        'resume' => ['The resume does not exist.'],
                    ]);
                }

                // Validate resume belongs to user
                if ($resume->user_id !== $userId) {
                    throw ValidationException::withMessages([
                        'resume' => ['This resume does not belong to you.'],
                    ]);
                }
            }

            // 4. Prevent duplicate applications
            if ($this->jobApplicationRepository->userHasAppliedToVacancy($userId, $vacancyId)) {
                throw ValidationException::withMessages([
                    'application' => ['You have already applied for this vacancy.'],
                ]);
            }

            // 5. Create application
            $applicationData = [
                'user_id' => $userId,
                'job_vacancy_id' => $vacancyId,
                'resume_id' => $resumeId ?: null,
                'status' => 'submitted',
                'ai_generated_score' => null,
                'ai_generated_feedback' => null,
            ];

            $application = $this->jobApplicationRepository->create($applicationData);

            // Log the application
            Log::channel('ems')->info('Job application submitted', [
                'event' => 'job_application.submitted',
                'application_id' => $application->id,
                'user_id' => $userId,
                'job_vacancy_id' => $vacancyId,
                'ip' => request()?->ip(),
            ]);

            return $application;
        });

        // After transaction commit, attempt AI resume analysis (do not block application creation)
        try {
            $analysis = null;

            $vacancy = JobVacancy::query()->find($vacancyId);
            $vacancyText = $vacancy?->description ?? null;

            if ($application->resume_id) {
                $resume = \App\Models\Resume::query()->find($application->resume_id);
                if ($resume) {
                    if (!empty($resume->fileUrl)) {
                        $analysis = $this->resumeAnalysisService->analyzeResumeForVacancy($resume->fileUrl, $vacancyText);
                    } else {
                        $parts = [];
                        if (! empty($resume->summary)) {
                            $parts[] = (string) $resume->summary;
                        }
                        if (! empty($resume->experience)) {
                            $parts[] = is_string($resume->experience) ? $resume->experience : json_encode($resume->experience);
                        }
                        if (! empty($resume->skills)) {
                            $parts[] = is_string($resume->skills) ? $resume->skills : json_encode($resume->skills);
                        }

                        $raw = implode("\n", $parts);
                        if (trim($raw) !== '') {
                            $analysis = $this->resumeAnalysisService->analyzeRawTextForVacancy($raw, $vacancyText);
                        }
                    }
                }
            }

            if (is_array($analysis)) {
                $this->jobApplicationRepository->update($application, [
                    'ai_generated_score' => $analysis['compatibility_score'] ?? null,
                    'ai_generated_feedback' => $analysis['feedback'] ?? null,
                ]);
            }
        } catch (\Throwable $e) {
            Log::channel('ems')->error('Resume analysis failed (non-blocking)', ['error' => $e->getMessage()]);
        }

        return $this->jobApplicationRepository->findById($application->id) ?: $application;
    }
    /**
     * Approve a job application and create an employee record in EMS.
     *
     * @param string $applicationId The ID of the JobApplication to approve
     * @param array<string, mixed> $data Must contain department_id and optional employee details
     *
     * @throws ValidationException
     */
    public function approveApplication(string $applicationId, array $data): JobApplication
    {
        // Start database transaction for consistency
        return DB::transaction(function () use ($applicationId, $data) {
            // 1. Validate application exists
            $application = $this->jobApplicationRepository->findById($applicationId);
            if (! $application) {
                throw ValidationException::withMessages([
                    'application' => ['The job application does not exist.'],
                ]);
            }

            // 2. Validate application is not already approved
            if (strtolower((string) $application->status) === 'approved') {
                throw ValidationException::withMessages([
                    'status' => ['This application has already been approved.'],
                ]);
            }

            // 3. Validate department_id is provided
            $departmentId = (string) ($data['department_id'] ?? '');
            if (! $departmentId) {
                throw ValidationException::withMessages([
                    'department_id' => ['The department ID is required for employee creation.'],
                ]);
            }

            // 4. Validate related objects exist
            if (! $application->user) {
                throw ValidationException::withMessages([
                    'user' => ['The associated user does not exist.'],
                ]);
            }

            if (! $application->jobVacancy) {
                throw ValidationException::withMessages([
                    'job_vacancy' => ['The associated job vacancy does not exist.'],
                ]);
            }

            // 5. Check if user is already an employee (prevent duplicate)
            $existingEmployee = \App\Models\Employee::query()
                ->where('user_id', $application->user_id)
                ->first();

            if ($existingEmployee) {
                throw ValidationException::withMessages([
                    'user_id' => ['This user is already an employee in the system.'],
                ]);
            }

            // 6. Prepare employee creation payload
            $employeePayload = [
                'user_id' => $application->user_id,
                'department_id' => $departmentId,
                'first_name' => $data['first_name'] ?? '',
                'last_name' => $data['last_name'] ?? '',
                'email' => $data['email'] ?? ($application->user->email ?? ''),
                'phone_number' => $data['phone_number'] ?? '',
                'address' => $data['address'] ?? null,
                'hire_date' => $data['hire_date'] ?? now()->toDateString(),
                'job_title' => $data['job_title'] ?? $application->jobVacancy->title ?? 'Employee',
                'employment_type' => $data['employment_type'] ?? 'full-time',
                'gender' => $data['gender'] ?? null,
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'status' => $data['employee_status'] ?? 'active',
            ];

            // 7. Create employee via EMS service
            $employee = $this->employeeService->createEmployee($employeePayload);

            // 8. Update JobApplication status to approved
            $application = $this->jobApplicationRepository->update($application, [
                'status' => 'approved',
            ]);

            // Log the approval
            Log::channel('ems')->info('Job application approved and employee created', [
                'event' => 'job_application.approved',
                'application_id' => $application->id,
                'employee_id' => $employee->id,
                'user_id' => $application->user_id,
                'job_vacancy_id' => $application->job_vacancy_id,
                'performed_by' => Auth::id(),
                'ip' => request()?->ip(),
            ]);

            return $application;
        });
    }

    /**
     * Get ranked applications with filtering and sorting.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     */
    public function listRankedApplications(array $filters, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->jobApplicationRepository->paginateRanked($filters, $perPage);
    }

    /**
     * Get top candidates by AI score.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTopCandidates(int $limit = 5)
    {
        return $this->jobApplicationRepository->getTopCandidates($limit);
    }

    /**
     * Get low score candidates.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLowScoreCandidates(int $limit = 5)
    {
        return $this->jobApplicationRepository->getLowScoreCandidates($limit);
    }
}
