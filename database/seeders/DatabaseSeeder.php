<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First, seed roles and permissions
        $this->call(RolePermissionSeeder::class);

        // Seed games
        $this->call(MemoryTestGameSeeder::class);

        // Create test users with roles
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $admin->assignRole('admin');

        $moderator = User::factory()->create([
            'name' => 'Moderator',
            'email' => 'moderator@example.com',
        ]);
        $moderator->assignRole('moderator');

        $player = User::factory()->create([
            'name' => 'Test Player',
            'email' => 'player@example.com',
        ]);
        $player->assignRole('player');
    }
}
