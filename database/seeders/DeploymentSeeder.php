<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DeploymentSeeder extends Seeder
{
    /**
     * Seed essential data needed for the application to function properly.
     * This seeder is safe to run multiple times and should only include
     * essential data like roles, permissions, and critical configuration.
     */
    public function run(): void
    {
        $this->command->info('🌱 Running deployment seeders...');

        // Essential roles and permissions - always needed
        $this->call(RolePermissionSeeder::class);

        // Only seed games if none exist (don't duplicate)
        if (\App\Models\Game::count() === 0) {
            $this->command->info('📊 No games found, seeding default games...');
            $this->call(MemoryTestGameSeeder::class);
        } else {
            $this->command->info('📊 Games already exist, skipping game seeding...');
        }

        $this->command->info('✅ Deployment seeding completed!');
    }
}
