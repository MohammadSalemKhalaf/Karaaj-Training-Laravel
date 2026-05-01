<?php

namespace Database\Seeders;

use App\Models\Resume;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResumeSeeder extends Seeder
{
    public function run(): void
    {
        // Get all job seekers
        $jobSeekers = User::query()
            ->whereHas('role', function ($q) {
                $q->where('name', 'job_seeker');
            })
            ->get();

        $resumes = [
            [
                'email' => 'seeker1@example.com',
                'summary' => 'Experienced Backend Engineer with 6 years in Laravel, Node.js, and cloud technologies. Passionate about building scalable systems.',
                'skills' => json_encode(['Laravel', 'Node.js', 'PostgreSQL', 'Docker', 'AWS', 'Redis', 'REST APIs']),
                'experience' => json_encode([
                    [
                        'position' => 'Senior Backend Engineer',
                        'company' => 'Previous Tech Startup',
                        'duration' => '2021-2023',
                        'description' => 'Led backend team, designed microservices architecture'
                    ],
                    [
                        'position' => 'Backend Developer',
                        'company' => 'StartupXYZ',
                        'duration' => '2019-2021',
                        'description' => 'Built REST APIs and managed database optimization'
                    ]
                ]),
                'education' => json_encode([
                    [
                        'degree' => 'Bachelor of Science',
                        'field' => 'Computer Science',
                        'institution' => 'State University',
                        'year' => 2018
                    ]
                ]),
            ],
            [
                'email' => 'seeker2@example.com',
                'summary' => 'Full Stack Developer with 4 years experience. Specialized in React, Vue, and modern JavaScript. Love building beautiful UIs.',
                'skills' => json_encode(['React', 'Vue.js', 'TypeScript', 'Tailwind CSS', 'Redux', 'JavaScript', 'HTML/CSS']),
                'experience' => json_encode([
                    [
                        'position' => 'Frontend Developer',
                        'company' => 'Web Agency Pro',
                        'duration' => '2020-2023',
                        'description' => 'Built responsive web applications for multiple clients'
                    ],
                    [
                        'position' => 'Junior Developer',
                        'company' => 'Digital Studio',
                        'duration' => '2019-2020',
                        'description' => 'Learned modern web development practices'
                    ]
                ]),
                'education' => json_encode([
                    [
                        'degree' => 'Bootcamp Certificate',
                        'field' => 'Full Stack Development',
                        'institution' => 'Code Academy',
                        'year' => 2019
                    ]
                ]),
            ],
            [
                'email' => 'seeker3@example.com',
                'summary' => 'DevOps Engineer with 5 years infrastructure experience. Expert in Kubernetes, Docker, and cloud deployment. Infrastructure as Code advocate.',
                'skills' => json_encode(['Kubernetes', 'Docker', 'AWS', 'Terraform', 'Jenkins', 'Linux', 'CI/CD', 'Monitoring']),
                'experience' => json_encode([
                    [
                        'position' => 'DevOps Engineer',
                        'company' => 'Cloud Infra Corp',
                        'duration' => '2019-2023',
                        'description' => 'Managed Kubernetes clusters and CI/CD pipelines'
                    ],
                    [
                        'position' => 'Systems Administrator',
                        'company' => 'Enterprise IT',
                        'duration' => '2017-2019',
                        'description' => 'Maintained on-premise infrastructure'
                    ]
                ]),
                'education' => json_encode([
                    [
                        'degree' => 'Associate Degree',
                        'field' => 'Network Administration',
                        'institution' => 'Technical College',
                        'year' => 2017
                    ]
                ]),
            ],
            [
                'email' => 'seeker4@example.com',
                'summary' => 'Data Engineer passionate about building data pipelines. 3 years experience with big data technologies and ETL processes.',
                'skills' => json_encode(['Apache Spark', 'Python', 'SQL', 'Airflow', 'BigQuery', 'PostgreSQL', 'Data Warehousing']),
                'experience' => json_encode([
                    [
                        'position' => 'Data Engineer',
                        'company' => 'Analytics Firm',
                        'duration' => '2021-2023',
                        'description' => 'Built and maintained data pipelines processing 10TB+ daily'
                    ],
                    [
                        'position' => 'Junior Data Analyst',
                        'company' => 'Business Intelligence Team',
                        'duration' => '2020-2021',
                        'description' => 'Analyzed datasets and created dashboards'
                    ]
                ]),
                'education' => json_encode([
                    [
                        'degree' => 'Bachelor of Science',
                        'field' => 'Statistics',
                        'institution' => 'University',
                        'year' => 2020
                    ]
                ]),
            ],
            [
                'email' => 'seeker5@example.com',
                'summary' => 'QA Automation Engineer with 3 years experience. Specialist in test automation frameworks and ensuring software quality.',
                'skills' => json_encode(['Selenium', 'Python', 'Cypress', 'Jest', 'TestNG', 'JIRA', 'API Testing']),
                'experience' => json_encode([
                    [
                        'position' => 'QA Automation Engineer',
                        'company' => 'Software Testing Co',
                        'duration' => '2021-2023',
                        'description' => 'Created automated test suites with 85% code coverage'
                    ],
                    [
                        'position' => 'Manual QA Tester',
                        'company' => 'Quality Assurance Dept',
                        'duration' => '2020-2021',
                        'description' => 'Performed manual testing and reported bugs'
                    ]
                ]),
                'education' => json_encode([
                    [
                        'degree' => 'ISTQB Certified',
                        'field' => 'Software Quality Assurance',
                        'institution' => 'Certification Body',
                        'year' => 2020
                    ]
                ]),
            ],
        ];

        foreach ($resumes as $resumeData) {
            $user = $jobSeekers->where('email', $resumeData['email'])->first();
            if ($user) {
                Resume::query()->firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'filename' => $user->name . '_resume.pdf'
                    ],
                    [
                        'file_url' => null,
                        'contact_details' => json_encode([
                            'phone' => fake()->phoneNumber(),
                            'location' => fake()->city() . ', ' . fake()->stateAbbr(),
                            'linkedin' => 'https://linkedin.com/in/' . strtolower(str_replace(' ', '-', $user->name))
                        ]),
                        'summary' => $resumeData['summary'],
                        'skills' => $resumeData['skills'],
                        'experience' => $resumeData['experience'],
                        'education' => $resumeData['education'],
                    ]
                );
            }
        }
    }
}
