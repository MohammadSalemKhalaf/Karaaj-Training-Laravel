<?php

namespace Database\Seeders;

use App\Models\JobCategory;
use Illuminate\Database\Seeder;

class JobCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Backend Development',
            'Frontend Development',
            'DevOps Engineer',
            'Data Engineering',
            'Full Stack Development',
            'Mobile Development',
            'QA Automation',
            'Product Manager',
        ];

        foreach ($categories as $category) {
            JobCategory::query()->firstOrCreate(
                ['name' => $category],
            );
        }
    }
}
