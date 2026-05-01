<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\JobCategory;
use App\Models\JobVacancy;
use Illuminate\Database\Seeder;

class JobVacancySeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::query()->get();
        $categories = JobCategory::query()->get();

        $vacancies = [
            // TechFlow Solutions
            [
                'title' => 'Senior Backend Engineer',
                'description' => 'Looking for an experienced backend engineer with expertise in Laravel, Node.js, and microservices architecture. Must have 5+ years experience.',
                'location' => 'San Francisco, CA',
                'salary' => 150000,
                'type' => 'full-time',
                'category' => 'Backend Development',
                'company_index' => 0,
            ],
            [
                'title' => 'React Frontend Developer',
                'description' => 'We seek a talented React developer to build modern, responsive user interfaces. Experience with TypeScript and Redux required.',
                'location' => 'San Francisco, CA',
                'salary' => 130000,
                'type' => 'full-time',
                'category' => 'Frontend Development',
                'company_index' => 0,
            ],
            // CloudNine Enterprise
            [
                'title' => 'DevOps Engineer',
                'description' => 'Join our infrastructure team. Experience with Kubernetes, Docker, AWS/GCP, and CI/CD pipelines required. Must have strong Linux knowledge.',
                'location' => 'Seattle, WA',
                'salary' => 145000,
                'type' => 'full-time',
                'category' => 'DevOps Engineer',
                'company_index' => 1,
            ],
            [
                'title' => 'Full Stack Engineer',
                'description' => 'Develop full-stack applications. Proficiency in Vue.js, Python, and PostgreSQL required. Startup experience is a plus.',
                'location' => 'Seattle, WA',
                'salary' => 120000,
                'type' => 'full-time',
                'category' => 'Full Stack Development',
                'company_index' => 1,
            ],
            // DataViz Analytics
            [
                'title' => 'Data Engineer',
                'description' => 'Build data pipelines and ETL processes. Experience with Apache Spark, Python, and SQL required. Must understand data warehousing.',
                'location' => 'Austin, TX',
                'salary' => 135000,
                'type' => 'full-time',
                'category' => 'Data Engineering',
                'company_index' => 2,
            ],
            [
                'title' => 'QA Automation Engineer',
                'description' => 'Create automation test frameworks and manage test infrastructure. Selenium, Python, and Cypress experience required.',
                'location' => 'Austin, TX',
                'salary' => 95000,
                'type' => 'full-time',
                'category' => 'QA Automation',
                'company_index' => 2,
            ],
            // SecureNet Systems
            [
                'title' => 'Security Engineer',
                'description' => 'Develop security solutions and conduct penetration testing. CISSP or CEH certification preferred. 3+ years experience required.',
                'location' => 'Boston, MA',
                'salary' => 140000,
                'type' => 'full-time',
                'category' => 'Backend Development',
                'company_index' => 3,
            ],
            // InnovateLabs
            [
                'title' => 'Junior Developer',
                'description' => 'Entry-level position for recent graduates or career changers. We provide training in Laravel and modern web technologies.',
                'location' => 'Denver, CO',
                'salary' => 70000,
                'type' => 'full-time',
                'category' => 'Full Stack Development',
                'company_index' => 4,
            ],
            [
                'title' => 'Mobile Developer',
                'description' => 'Develop native iOS and Android applications. Swift and Kotlin experience required. 2+ years experience needed.',
                'location' => 'Denver, CO',
                'salary' => 125000,
                'type' => 'full-time',
                'category' => 'Mobile Development',
                'company_index' => 4,
            ],
            [
                'title' => 'Product Manager',
                'description' => 'Lead product vision and strategy. Must have experience with Agile methodologies and stakeholder management. Tech background required.',
                'location' => 'Denver, CO',
                'salary' => 140000,
                'type' => 'full-time',
                'category' => 'Product Manager',
                'company_index' => 4,
            ],
        ];

        foreach ($vacancies as $vacancy) {
            $company = $companies[$vacancy['company_index']] ?? $companies->first();
            $category = $categories->where('name', $vacancy['category'])->first();

            JobVacancy::query()->firstOrCreate(
                [
                    'title' => $vacancy['title'],
                    'company_id' => $company->id,
                ],
                [
                    'description' => $vacancy['description'],
                    'location' => $vacancy['location'],
                    'salary' => $vacancy['salary'],
                    'type' => $vacancy['type'],
                    'category_id' => $category?->id,
                    'view_count' => rand(10, 500),
                ]
            );
        }
    }
}
