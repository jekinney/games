# Roles and Permissions Configuration

This application uses a configuration-based approach for managing roles and permissions, making it easy to add, modify, or remove roles and permissions without touching the code.

## Configuration File

The main configuration is located in `config/roles.php` and contains:

### 1. Roles Definition
```php
'roles' => [
    [
        'name' => 'player',
        'guard_name' => 'web',
        'description' => 'Regular user who can play games and participate in community features',
    ],
    // ... more roles
]
```

### 2. Permissions Definition (Organized by Category)
```php
'permissions' => [
    'user_management' => [
        [
            'name' => 'view-users',
            'guard_name' => 'web',
            'description' => 'View user profiles and basic information',
            'category' => 'User Management',
        ],
        // ... more permissions
    ],
    // ... more categories
]
```

### 3. Role-Permission Assignments
```php
'role_permissions' => [
    'admin' => [
        'view-users',
        'create-users',
        'edit-users',
        'ban-users',
        // ... more permissions
    ],
    // ... more role assignments
]
```

### 4. Default Role
```php
'default_role' => 'player',
```

## Usage

### Seeding Roles and Permissions
```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Viewing Current Configuration
```bash
# List roles and permissions in database
php artisan roles:list

# Show permissions for each role
php artisan roles:list --permissions

# Show configuration structure
php artisan roles:list --config
```

### Using the Service Class
```php
use App\Services\RolePermissionService;

// Get available roles
$roles = RolePermissionService::getAvailableRoles();

// Get available permissions
$permissions = RolePermissionService::getAvailablePermissions();

// Get permissions by category
$categorizedPermissions = RolePermissionService::getPermissionsByCategory();

// Get role description
$description = RolePermissionService::getRoleDescription('admin');

// Get default role
$defaultRole = RolePermissionService::getDefaultRole();

// Check if role exists in config
$exists = RolePermissionService::roleExistsInConfig('moderator');
```

## Current Role Hierarchy

1. **Player** - Basic user, no special permissions
2. **Moderator** - Can moderate content and manage basic user interactions
3. **Admin** - Full administrative access except super admin functions
4. **Super Admin** - Complete system access including role and permission management

## Permission Categories

1. **User Management**
   - view-users
   - create-users
   - edit-users
   - delete-users
   - ban-users

2. **Content Management**
   - moderate-comments
   - manage-reports
   - manage-content

3. **System Administration**
   - manage-roles
   - manage-permissions
   - access-admin-panel

## Adding New Roles or Permissions

1. Edit `config/roles.php`
2. Add new roles to the `roles` array
3. Add new permissions to the appropriate category in `permissions` array
4. Update `role_permissions` to assign permissions to roles
5. Run the seeder: `php artisan db:seed --class=RolePermissionSeeder`

## Testing

The configuration system includes comprehensive tests:
```bash
php artisan test tests/Feature/RoleConfigurationTest.php
```

These tests ensure:
- Configuration matches database state
- Role-permission assignments are correct
- Service methods work as expected
- Permission categories are properly organized
- Role hierarchy is maintained
