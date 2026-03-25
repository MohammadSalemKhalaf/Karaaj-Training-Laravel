<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Engineering',
                'code' => 'ENG',
                'description' => 'Builds and maintains software products and infrastructure.',
            ],
            [
                'name' => 'Human Resources',
                'code' => 'HR',
                'description' => 'Manages recruitment, people operations, and policy governance.',
            ],
            [
                'name' => 'Finance',
                'code' => 'FIN',
                'description' => 'Handles budgeting, payroll controls, and financial reporting.',
            ],
        ];

        foreach ($departments as $department) {
            Department::query()->firstOrCreate(
                ['code' => $department['code']],
                [
                    'name' => $department['name'],
                    'description' => $department['description'],
                    'status' => 'active',
                ]
            );
        }
    }
}
