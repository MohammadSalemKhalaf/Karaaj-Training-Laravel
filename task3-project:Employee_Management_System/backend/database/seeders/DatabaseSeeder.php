<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call all seeders in order
        $this->call([
            DepartmentSeeder::class,
            RoleSeeder::class,
            UserSeeder::class,
            CompanySeeder::class,
            JobCategorySeeder::class,
            JobVacancySeeder::class,
            ResumeSeeder::class,
            JobApplicationSeeder::class,
            EmployeeSeeder::class,
            OperationalDataSeeder::class,
        ]);
    }
}
