<?php

namespace Database\Seeders;

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
        $this->call([
            InitialDataSeeder::class,
        ]);

        // Create a test admin user
        User::factory()->create([
            'name' => 'Admin User',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'status' => 'Both',
            'is_pic' => true,
        ]);

        // Create a test regular user
        User::factory()->create([
            'name' => 'Regular User',
            'username' => 'user',
            'email' => 'user@example.com',
            'status' => 'Requester',
            'is_pic' => false,
        ]);
    }
}
