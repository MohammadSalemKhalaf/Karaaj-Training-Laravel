<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Get a manager to be the company owner
        $owner = User::query()->where('email', 'manager1@ems.local')->first();

        $companies = [
            [
                'name' => 'TechFlow Solutions',
                'address' => '123 Silicon Valley, San Francisco, CA 94105',
                'industry' => 'Software Development',
                'website' => 'https://techflow.example.com',
            ],
            [
                'name' => 'CloudNine Enterprise',
                'address' => '456 Cloud Park, Seattle, WA 98101',
                'industry' => 'Cloud Services',
                'website' => 'https://cloudnine.example.com',
            ],
            [
                'name' => 'DataViz Analytics',
                'address' => '789 Data Lane, Austin, TX 78701',
                'industry' => 'Data Analytics',
                'website' => 'https://dataviz.example.com',
            ],
            [
                'name' => 'SecureNet Systems',
                'address' => '321 Security Ave, Boston, MA 02101',
                'industry' => 'Cybersecurity',
                'website' => 'https://securenet.example.com',
            ],
            [
                'name' => 'InnovateLabs',
                'address' => '654 Innovation Blvd, Denver, CO 80202',
                'industry' => 'Research & Development',
                'website' => 'https://innovatelabs.example.com',
            ],
        ];

        foreach ($companies as $company) {
            Company::query()->firstOrCreate(
                ['name' => $company['name']],
                [
                    'address' => $company['address'],
                    'industry' => $company['industry'],
                    'website' => $company['website'],
                    'owner_id' => $owner?->id,
                ]
            );
        }
    }
}
