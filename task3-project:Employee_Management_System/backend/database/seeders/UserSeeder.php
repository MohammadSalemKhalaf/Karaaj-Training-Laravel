<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $adminRole = Role::query()->where('name', 'admin')->first();
        $managerRole = Role::query()->where('name', 'manager')->first();
        $employeeRole = Role::query()->where('name', 'employee')->first();
        $jobSeekerRole = Role::query()->where('name', 'job_seeker')->first();

        // Create 2 Admins
        User::query()->firstOrCreate(
            ['email' => 'admin1@ems.local'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Sarah Anderson',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'admin2@ems.local'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Michael Chen',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // Create 2 Managers
        User::query()->firstOrCreate(
            ['email' => 'manager1@ems.local'],
            [
                'role_id' => $managerRole->id,
                'name' => 'Emily Watson',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        User::query()->firstOrCreate(
            ['email' => 'manager2@ems.local'],
            [
                'role_id' => $managerRole->id,
                'name' => 'David Johnson',
                'password' => Hash::make('password'),
                'status' => 'active',
            ]
        );

        // Create 5 Job Seekers
        $jobSeekerData = [
            ['email' => 'seeker1@example.com', 'name' => 'Alex Rivera'],
            ['email' => 'seeker2@example.com', 'name' => 'Jordan Smith'],
            ['email' => 'seeker3@example.com', 'name' => 'Casey Taylor'],
            ['email' => 'seeker4@example.com', 'name' => 'Morgan Lee'],
            ['email' => 'seeker5@example.com', 'name' => 'Taylor Martinez'],
        ];

        foreach ($jobSeekerData as $data) {
            User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $jobSeekerRole->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );
        }

        // Create 3 Employees
        $employeeData = [
            ['email' => 'employee1@ems.local', 'name' => 'Robert Brown'],
            ['email' => 'employee2@ems.local', 'name' => 'Jessica Davis'],
            ['email' => 'employee3@ems.local', 'name' => 'Christopher Wilson'],
        ];

        foreach ($employeeData as $data) {
            User::query()->firstOrCreate(
                ['email' => $data['email']],
                [
                    'role_id' => $employeeRole->id,
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'status' => 'active',
                ]
            );
        }
    }
}
