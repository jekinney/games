<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\RolePermissionService;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleConfigurationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed the roles and permissions from configuration
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_configuration_roles_match_database_roles()
    {
        $configRoles = RolePermissionService::getAvailableRoles();
        $databaseRoles = Role::pluck('name')->toArray();
        
        sort($configRoles);
        sort($databaseRoles);
        
        $this->assertEquals($configRoles, $databaseRoles);
    }

    public function test_configuration_permissions_match_database_permissions()
    {
        $configPermissions = RolePermissionService::getAvailablePermissions();
        $databasePermissions = Permission::pluck('name')->toArray();
        
        sort($configPermissions);
        sort($databasePermissions);
        
        $this->assertEquals($configPermissions, $databasePermissions);
    }

    public function test_role_permissions_match_configuration()
    {
        $allRolePermissions = RolePermissionService::getAllRolePermissions();
        
        foreach ($allRolePermissions as $roleName => $expectedPermissions) {
            $role = Role::findByName($roleName);
            $actualPermissions = $role->permissions->pluck('name')->toArray();
            
            sort($expectedPermissions);
            sort($actualPermissions);
            
            $this->assertEquals($expectedPermissions, $actualPermissions, 
                "Role '{$roleName}' permissions don't match configuration");
        }
    }

    public function test_role_service_methods_work_correctly()
    {
        // Test role description
        $this->assertNotNull(RolePermissionService::getRoleDescription('player'));
        $this->assertNull(RolePermissionService::getRoleDescription('non-existent-role'));
        
        // Test permission description
        $this->assertNotNull(RolePermissionService::getPermissionDescription('view-users'));
        $this->assertNull(RolePermissionService::getPermissionDescription('non-existent-permission'));
        
        // Test default role
        $this->assertEquals('player', RolePermissionService::getDefaultRole());
        
        // Test role exists
        $this->assertTrue(RolePermissionService::roleExistsInConfig('player'));
        $this->assertFalse(RolePermissionService::roleExistsInConfig('non-existent-role'));
        
        // Test permission exists
        $this->assertTrue(RolePermissionService::permissionExistsInConfig('view-users'));
        $this->assertFalse(RolePermissionService::permissionExistsInConfig('non-existent-permission'));
    }

    public function test_permissions_are_organized_by_category()
    {
        $permissionsByCategory = RolePermissionService::getPermissionsByCategory();
        
        $this->assertArrayHasKey('user_management', $permissionsByCategory);
        $this->assertArrayHasKey('content_management', $permissionsByCategory);
        $this->assertArrayHasKey('system_administration', $permissionsByCategory);
        
        // Check some expected permissions in each category
        $this->assertContains('view-users', $permissionsByCategory['user_management']);
        $this->assertContains('moderate-comments', $permissionsByCategory['content_management']);
        $this->assertContains('manage-roles', $permissionsByCategory['system_administration']);
    }

    public function test_role_hierarchy_is_maintained()
    {
        $playerPermissions = RolePermissionService::getRolePermissions('player');
        $moderatorPermissions = RolePermissionService::getRolePermissions('moderator');
        $adminPermissions = RolePermissionService::getRolePermissions('admin');
        $superAdminPermissions = RolePermissionService::getRolePermissions('super-admin');
        
        // Player should have no special permissions
        $this->assertEmpty($playerPermissions);
        
        // Moderator should have some permissions
        $this->assertNotEmpty($moderatorPermissions);
        
        // Admin should have more permissions than moderator
        $this->assertGreaterThan(count($moderatorPermissions), count($adminPermissions));
        
        // Super admin should have the most permissions
        $this->assertGreaterThan(count($adminPermissions), count($superAdminPermissions));
        
        // Super admin should have role management permissions
        $this->assertContains('manage-roles', $superAdminPermissions);
        $this->assertContains('manage-permissions', $superAdminPermissions);
    }
}
