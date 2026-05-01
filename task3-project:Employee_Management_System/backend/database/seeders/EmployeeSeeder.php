<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\JobApplication;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Get all departments
        $departments = Department::query()->get();
        if ($departments->isEmpty()) {
            return;
        }

        // Create employees from approved job applications
        $approvedApplications = JobApplication::query()
            ->where('status', 'approved')
            ->with('user', 'jobVacancy')
            ->get();

        foreach ($approvedApplications as $application) {
            if (!Employee::query()->where('user_id', $application->user_id)->exists()) {
                Employee::query()->create([
                    'user_id' => $application->user_id,
                    'department_id' => $departments->random()->id,
                    'employee_code' => 'EMP-' . str_pad(Employee::count() + 1, 5, '0', STR_PAD_LEFT),
                    'first_name' => explode(' ', $application->user->name)[0],
                    'last_name' => implode(' ', array_slice(explode(' ', $application->user->name), 1)) ?: 'Unknown',
                    'email' => $application->user->email,
                    'phone_number' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'hire_date' => now()->subDays(rand(30, 365)),
                    'job_title' => $application->jobVacancy?->title ?? 'Software Engineer',
                    'employment_type' => 'full-time',
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'date_of_birth' => fake()->dateTimeBetween('-50 years', '-25 years'),
                    'status' => 'active',
                ]);
            }
        }

        // Create employees from employee role users (not from job applications)
        $employeeRoleUsers = User::query()
            ->whereHas('role', function ($q) {
                $q->where('name', 'employee');
            })
            ->get();

        foreach ($employeeRoleUsers as $user) {
            if (!Employee::query()->where('user_id', $user->id)->exists()) {
                $counter = Employee::count() + 1;
                Employee::query()->create([
                    'user_id' => $user->id,
                    'department_id' => $departments->random()->id,
                    'employee_code' => 'EMP-' . str_pad($counter, 5, '0', STR_PAD_LEFT),
                    'first_name' => explode(' ', $user->name)[0],
                    'last_name' => implode(' ', array_slice(explode(' ', $user->name), 1)) ?: 'Unknown',
                    'email' => $user->email,
                    'phone_number' => fake()->phoneNumber(),
                    'address' => fake()->address(),
                    'hire_date' => now()->subDays(rand(30, 365)),
                    'job_title' => fake()->jobTitle(),
                    'employment_type' => 'full-time',
                    'gender' => fake()->randomElement(['male', 'female', 'other']),
                    'date_of_birth' => fake()->dateTimeBetween('-50 years', '-25 years'),
                    'status' => 'active',
                ]);
            }
        }
    }
}
