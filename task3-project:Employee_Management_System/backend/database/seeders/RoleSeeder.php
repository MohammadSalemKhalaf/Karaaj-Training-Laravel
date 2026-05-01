<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Platform administrator with full system access.',
            ],
            [
                'name' => 'manager',
                'description' => 'Department manager with team management access.',
            ],
            [
                'name' => 'employee',
                'description' => 'Regular employee with limited access.',
            ],
            [
                'name' => 'job_seeker',
                'description' => 'Job seeker applying for vacancies.',
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(
                ['name' => $role['name']],
                ['description' => $role['description']]
            );
        }
    }
}
