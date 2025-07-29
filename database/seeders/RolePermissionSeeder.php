<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Get roles and permissions configuration
        $rolesConfig = config('roles.roles');
        $permissionsConfig = config('roles.permissions');
        $rolePermissionsConfig = config('roles.role_permissions');

        // Create all permissions first
        $this->createPermissions($permissionsConfig);

        // Create all roles
        $this->createRoles($rolesConfig);

        // Assign permissions to roles
        $this->assignPermissionsToRoles($rolePermissionsConfig);

        $this->command->info('Roles and permissions seeded successfully!');
    }

    /**
     * Create all permissions from configuration
     */
    private function createPermissions(array $permissionsConfig): void
    {
        foreach ($permissionsConfig as $category => $permissions) {
            foreach ($permissions as $permissionData) {
                Permission::firstOrCreate([
                    'name' => $permissionData['name'],
                    'guard_name' => $permissionData['guard_name'],
                ]);
                
                $this->command->info("Created permission: {$permissionData['name']}");
            }
        }
    }

    /**
     * Create all roles from configuration
     */
    private function createRoles(array $rolesConfig): void
    {
        foreach ($rolesConfig as $roleData) {
            Role::firstOrCreate([
                'name' => $roleData['name'],
                'guard_name' => $roleData['guard_name'],
            ]);
            
            $this->command->info("Created role: {$roleData['name']}");
        }
    }

    /**
     * Assign permissions to roles based on configuration
     */
    private function assignPermissionsToRoles(array $rolePermissionsConfig): void
    {
        foreach ($rolePermissionsConfig as $roleName => $permissionNames) {
            $role = Role::findByName($roleName);
            
            if (!empty($permissionNames)) {
                $permissions = Permission::whereIn('name', $permissionNames)->get();
                $role->syncPermissions($permissions);
                
                $permissionsList = implode(', ', $permissionNames);
                $this->command->info("Assigned permissions to {$roleName}: {$permissionsList}");
            } else {
                $role->syncPermissions([]);
                $this->command->info("Role {$roleName} has no special permissions");
            }
        }
    }
}
