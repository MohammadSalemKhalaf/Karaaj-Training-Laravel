<?php

namespace Database\Seeders;

use App\Models\JobApplication;
use App\Models\JobVacancy;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobApplicationSeeder extends Seeder
{
    public function run(): void
    {
        $jobSeekers = User::query()
            ->whereHas('role', function ($q) {
                $q->where('name', 'job_seeker');
            })
            ->get();

        $vacancies = JobVacancy::query()->get();

        // Create applications: mix of statuses and some with AI scores
        $applicationConfigs = [
            // Backend vacancy - high quality applications
            [
                'vacancy_index' => 0, // Senior Backend Engineer
                'seeker_index' => 0,
                'status' => 'submitted',
                'ai_score' => 92,
                'feedback' => 'Excellent match. Strong backend experience with relevant tech stack.',
            ],
            [
                'vacancy_index' => 0,
                'seeker_index' => 1,
                'status' => 'under_review',
                'ai_score' => 45,
                'feedback' => 'Frontend specialist, backend role may not be ideal fit.',
            ],
            [
                'vacancy_index' => 0,
                'seeker_index' => 2,
                'status' => 'rejected',
                'ai_score' => 28,
                'feedback' => 'Infrastructure focus but lacking core backend development experience.',
            ],
            // Frontend vacancy
            [
                'vacancy_index' => 1, // React Frontend Developer
                'seeker_index' => 1,
                'status' => 'submitted',
                'ai_score' => 88,
                'feedback' => 'Perfect match. Strong React and modern JavaScript skills.',
            ],
            [
                'vacancy_index' => 1,
                'seeker_index' => 0,
                'status' => 'under_review',
                'ai_score' => 35,
                'feedback' => 'Backend engineer applying for frontend role. Limited UI experience.',
            ],
            // DevOps vacancy
            [
                'vacancy_index' => 2, // DevOps Engineer
                'seeker_index' => 2,
                'status' => 'approved',
                'ai_score' => 94,
                'feedback' => 'Excellent DevOps candidate with Kubernetes and AWS expertise.',
            ],
            [
                'vacancy_index' => 2,
                'seeker_index' => 3,
                'status' => 'submitted',
                'ai_score' => 32,
                'feedback' => 'Data engineer, infrastructure experience limited.',
            ],
            // Data Engineer vacancy
            [
                'vacancy_index' => 4, // Data Engineer
                'seeker_index' => 3,
                'status' => 'submitted',
                'ai_score' => 91,
                'feedback' => 'Strong match. Relevant data technologies and experience.',
            ],
            [
                'vacancy_index' => 4,
                'seeker_index' => 4,
                'status' => 'under_review',
                'ai_score' => 50,
                'feedback' => 'QA specialist, some data testing experience but not core data engineering.',
            ],
            // QA Automation vacancy
            [
                'vacancy_index' => 5, // QA Automation Engineer
                'seeker_index' => 4,
                'status' => 'submitted',
                'ai_score' => 89,
                'feedback' => 'Excellent fit. Selenium, Python, and automation expertise match perfectly.',
            ],
            // Junior Developer vacancy - entry level
            [
                'vacancy_index' => 7, // Junior Developer
                'seeker_index' => 1,
                'status' => 'submitted',
                'ai_score' => 75,
                'feedback' => 'Good junior candidate with web development experience.',
            ],
            // Apply job seekers to various vacancies for realistic volume
            [
                'vacancy_index' => 1,
                'seeker_index' => 4,
                'status' => 'submitted',
                'ai_score' => 38,
                'feedback' => 'QA background, not frontend development.'
            ],
            [
                'vacancy_index' => 7,
                'seeker_index' => 0,
                'status' => 'submitted',
                'ai_score' => 55,
                'feedback' => 'Senior backend engineer ovqualified for junior position.',
            ],
        ];

        foreach ($applicationConfigs as $config) {
            if ($config['vacancy_index'] < count($vacancies) && $config['seeker_index'] < count($jobSeekers)) {
                $vacancy = $vacancies[$config['vacancy_index']];
                $seeker = $jobSeekers[$config['seeker_index']];
                $resume = Resume::query()->where('user_id', $seeker->id)->first();

                // Check if application already exists
                if (!JobApplication::query()->where('user_id', $seeker->id)->where('job_vacancy_id', $vacancy->id)->exists()) {
                    JobApplication::query()->create([
                        'user_id' => $seeker->id,
                        'job_vacancy_id' => $vacancy->id,
                        'resume_id' => $resume?->id,
                        'status' => $config['status'],
                        'ai_generated_score' => $config['ai_score'] ?? null,
                        'ai_generated_feedback' => $config['feedback'] ?? null,
                    ]);
                }
            }
        }
    }
}
