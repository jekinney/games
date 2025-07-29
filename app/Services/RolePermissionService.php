<?php

namespace App\Services;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionService
{
    /**
     * Get all available roles from configuration
     */
    public static function getAvailableRoles(): array
    {
        return collect(config('roles.roles'))->pluck('name')->toArray();
    }

    /**
     * Get all available permissions from configuration
     */
    public static function getAvailablePermissions(): array
    {
        $permissions = [];
        $permissionsConfig = config('roles.permissions');
        
        foreach ($permissionsConfig as $category => $categoryPermissions) {
            foreach ($categoryPermissions as $permission) {
                $permissions[] = $permission['name'];
            }
        }
        
        return $permissions;
    }

    /**
     * Get permissions by category
     */
    public static function getPermissionsByCategory(): array
    {
        $permissionsConfig = config('roles.permissions');
        $result = [];
        
        foreach ($permissionsConfig as $category => $permissions) {
            $result[$category] = collect($permissions)->pluck('name')->toArray();
        }
        
        return $result;
    }

    /**
     * Get role description
     */
    public static function getRoleDescription(string $roleName): ?string
    {
        $roles = config('roles.roles');
        
        foreach ($roles as $role) {
            if ($role['name'] === $roleName) {
                return $role['description'];
            }
        }
        
        return null;
    }

    /**
     * Get permission description
     */
    public static function getPermissionDescription(string $permissionName): ?string
    {
        $permissionsConfig = config('roles.permissions');
        
        foreach ($permissionsConfig as $category => $permissions) {
            foreach ($permissions as $permission) {
                if ($permission['name'] === $permissionName) {
                    return $permission['description'];
                }
            }
        }
        
        return null;
    }

    /**
     * Get default role from configuration
     */
    public static function getDefaultRole(): string
    {
        return config('roles.default_role', 'player');
    }

    /**
     * Check if a role exists in configuration
     */
    public static function roleExistsInConfig(string $roleName): bool
    {
        return in_array($roleName, self::getAvailableRoles());
    }

    /**
     * Check if a permission exists in configuration
     */
    public static function permissionExistsInConfig(string $permissionName): bool
    {
        return in_array($permissionName, self::getAvailablePermissions());
    }

    /**
     * Get role permissions from configuration
     */
    public static function getRolePermissions(string $roleName): array
    {
        $rolePermissions = config('roles.role_permissions');
        
        return $rolePermissions[$roleName] ?? [];
    }

    /**
     * Get all role-permission mappings from configuration
     */
    public static function getAllRolePermissions(): array
    {
        return config('roles.role_permissions', []);
    }
}
