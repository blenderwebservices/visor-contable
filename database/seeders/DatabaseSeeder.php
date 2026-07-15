<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);
        
        User::factory()->create([
            'name' => 'Reader User',
            'username' => 'reader',
            'email' => 'reader@example.com',
            'password' => Hash::make('password'),
            'role' => 'reader',
        ]);
    }
}
