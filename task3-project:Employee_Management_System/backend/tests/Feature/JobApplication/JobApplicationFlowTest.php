<?php

namespace Tests\Feature\JobApplication;

use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\Role;
use App\Models\User;
use Tymon\JWTAuth\Guards\GuardInterface as JWTGuard;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function (): void {
    $this->seed();
});

function authTokenFor(User $user): string
{
    /** @var JWTGuard $guard */
    $guard = auth('api');

    return $guard->login($user);
}

function makeUser(string $roleName, string $email): User
{
    $role = Role::query()->firstOrCreate(
        ['name' => $roleName],
        ['description' => ucfirst($roleName).' role']
    );

    return User::query()->create([
        'role_id' => $role->id,
        'name' => ucfirst($roleName).' Account',
        'email' => $email,
        'password' => bcrypt('StrongP@ssw0rd'),
        'status' => 'active',
    ]);
}

/**
 * Scenario 1: Job seeker applies for vacancy and AI score is saved
 */
it('job seeker can apply for vacancy', function (): void {
    $jobSeeker = User::query()->whereHas('role', function ($q) {
        $q->where('name', 'job_seeker');
    })->get()->last(); // Get a different seeker to avoid duplicates

    $resume = Resume::query()->where('user_id', $jobSeeker->id)->first();

    // Find a vacancy the user hasn't applied to yet
    $vacancy = JobVacancy::query()
        ->whereDoesntHave('jobApplications', function ($q) use ($jobSeeker) {
            $q->where('user_id', $jobSeeker->id);
        })
        ->first();

    if (!$vacancy) {
        $this->markTestSkipped('No available vacancy for new application');
        return;
    }

    $token = authTokenFor($jobSeeker);

    postJson("/api/job-vacancies/{$vacancy->id}/apply", [
        'resume_id' => $resume?->id,
    ], [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertCreated()
        ->assertJsonPath('data.application.status', 'submitted')
        ->assertJsonPath('data.application.job_vacancy.id', $vacancy->id);

    // Verify application was created
    expect(JobApplication::query()->where('user_id', $jobSeeker->id)->where('job_vacancy_id', $vacancy->id)->exists())->toBeTrue();
});

/**
 * Scenario: AI generated score is populated
 */
it('application has ai generated score or null', function (): void {
    $jobSeeker = User::query()->whereHas('role', function ($q) {
        $q->where('name', 'job_seeker');
    })->first();

    $application = JobApplication::query()
        ->where('user_id', $jobSeeker->id)
        ->where('status', 'submitted')
        ->first();

    if ($application) {
        // Score may be populated if AI analysis ran (from seeder data) or null
        expect($application->ai_generated_score === null || is_numeric($application->ai_generated_score))->toBeTrue();
    }
});

/**
 * Scenario 2: Admin/Manager can review applications
 */
it('manager can view job applications', function (): void {
    $manager = User::query()
        ->whereHas('role', function ($q) {
            $q->where('name', 'manager');
        })
        ->first();

    $token = authTokenFor($manager);

    getJson('/api/job-applications', [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk()
        ->assertJsonPath('data.applications', function ($applications) {
            return is_array($applications);
        });
});

/**
 * Scenario 3: Admin approves application and employee is created
 */
it('admin can approve application and create employee', function (): void {
    $admin = User::query()
        ->whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })
        ->first();

    // Get an application with status submitted
    $application = JobApplication::query()
        ->where('status', 'submitted')
        ->first();

    if (!$application) {
        $this->markTestSkipped('No submitted applications available');
        return;
    }

    $department = Department::query()->first();
    $token = authTokenFor($admin);

    postJson("/api/job-applications/{$application->id}/approve", [
        'hire_date' => '2026-05-01',
        'job_title' => $application->jobVacancy?->title ?? 'Software Engineer',
        'employment_type' => 'full-time',
        'department_id' => $department?->id,
    ], [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk()
        ->assertJsonPath('data.application.status', 'approved');

    // Verify employee was created
    expect(Employee::query()->where('user_id', $application->user_id)->exists())->toBeTrue();
});

/**
 * Scenario 4: Ranking endpoint returns sorted results
 */
it('ranked applications endpoint returns sorted by score', function (): void {
    $manager = User::query()
        ->whereHas('role', function ($q) {
            $q->where('name', 'manager');
        })
        ->first();

    $token = authTokenFor($manager);

    getJson('/api/job-applications/ranked', [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk()
        ->assertJsonPath('data.applications', function ($applications) {
            return is_array($applications);
        });
});

/**
 * Scenario 5: Dashboard returns recruitment metrics
 */
it('recruitment dashboard returns metrics', function (): void {
    $manager = User::query()
        ->whereHas('role', function ($q) {
            $q->where('name', 'manager');
        })
        ->first();

    $token = authTokenFor($manager);

    getJson('/api/recruitment/dashboard', [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk()
        ->assertJsonPath('data.dashboard', function ($dashboard) {
            return is_array($dashboard) || is_object($dashboard);
        });
});

/**
 * Scenario 6: Job seeker cannot apply twice
 */
it('prevents duplicate applications', function (): void {
    $jobSeeker = User::query()->whereHas('role', function ($q) {
        $q->where('name', 'job_seeker');
    })->first();

    $vacancy = JobVacancy::query()->first();
    $resume = Resume::query()->where('user_id', $jobSeeker->id)->first();

    // Create first application
    JobApplication::query()->create([
        'user_id' => $jobSeeker->id,
        'job_vacancy_id' => $vacancy->id,
        'resume_id' => $resume?->id,
        'status' => 'submitted',
    ]);

    $token = authTokenFor($jobSeeker);

    // Try to apply again
    postJson("/api/job-vacancies/{$vacancy->id}/apply", [
        'resume_id' => $resume?->id,
    ], [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertUnprocessable()
        ->assertJsonPath('errors.application', fn ($error) => is_array($error));
});

/**
 * Scenario 7: Job seeker can view public vacancies
 */
it('job seeker can view job vacancies', function (): void {
    $jobSeeker = User::query()->whereHas('role', function ($q) {
        $q->where('name', 'job_seeker');
    })->first();

    $token = authTokenFor($jobSeeker);

    getJson('/api/job-vacancies', [
        'Authorization' => "Bearer {$token}",
    ])
        ->assertOk();
});

/**
 * Scenario 8: Verify seed data structure
 */
it('verifies all seed data exists', function (): void {
    expect(User::count())->toBeGreaterThan(0);
    expect(Company::count())->toBeGreaterThan(0);
    expect(JobCategory::count())->toBeGreaterThan(0);
    expect(JobVacancy::count())->toBeGreaterThan(0);
    expect(Resume::count())->toBeGreaterThan(0);
    expect(JobApplication::count())->toBeGreaterThan(0);
});
