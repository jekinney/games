<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create roles if they don't exist
        $roles = ['super-admin', 'admin', 'moderator', 'player'];
        
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@games.test'],
            [
                'name' => 'Admin User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        $admin->assignRole('admin');

        // Create super admin user
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@games.test'],
            [
                'name' => 'Super Admin',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        $superAdmin->assignRole('super-admin');

        // Create some regular users for testing
        $regularUsers = [
            ['name' => 'John Player', 'email' => 'john@games.test'],
            ['name' => 'Jane Gamer', 'email' => 'jane@games.test'],
            ['name' => 'Bob Tester', 'email' => 'bob@games.test'],
        ];

        foreach ($regularUsers as $userData) {
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'name' => $userData['name'],
                    'email_verified_at' => now(),
                    'password' => bcrypt('password'),
                ]
            );

            $user->assignRole('player');
        }

        $this->command->info('Admin users created successfully:');
        $this->command->info('- Admin: admin@games.test (password: password)');
        $this->command->info('- Super Admin: superadmin@games.test (password: password)');
        $this->command->info('- Regular Users: john@games.test, jane@games.test, bob@games.test (password: password)');
    }
}
