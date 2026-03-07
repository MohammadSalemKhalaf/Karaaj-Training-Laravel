<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'role' => 'admin',
            'password'=> Hash::make('12345678'),
        ]);

        User::factory()->create([
            'name' => 'Admin2',
            'email' => 'admin2@gmail.com',
            'role' => 'admin',
            'password'=> Hash::make('12345678'),
        ]);

          User::factory()->create([
            'name' => 'editor',
            'email' => 'editor@gmail.com',
            'role' => 'editor',
            'password'=> Hash::make('12345678'),
        ]);
    }
}
