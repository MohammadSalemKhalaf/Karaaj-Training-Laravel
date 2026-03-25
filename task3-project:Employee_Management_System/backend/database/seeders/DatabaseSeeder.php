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
        $adminRole = Role::query()->firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Platform administrator role.']
        );

        Role::query()->firstOrCreate(
            ['name' => 'manager'],
            ['description' => 'Department manager role.']
        );

        $employeeRole = Role::query()->firstOrCreate(
            ['name' => 'employee'],
            ['description' => 'Employee role.']
        );

        User::factory()->create([
            'role_id' => $adminRole->id,
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => '12345678',
        ]);

        User::factory()->create([
            'role_id' => $employeeRole->id,
            'name' => 'Employee User',
            'email' => 'employee@example.com',
            'password' => '12345678',
        ]);
    }
}
