<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Roles Configuration
    |--------------------------------------------------------------------------
    |
    | Define all available roles in the system. Each role has a name, guard,
    | and description for clarity.
    |
    */
    'roles' => [
        [
            'name' => 'player',
            'guard_name' => 'web',
            'description' => 'Regular user who can play games and participate in community features',
        ],
        [
            'name' => 'moderator',
            'guard_name' => 'web',
            'description' => 'Can moderate content and manage basic user interactions',
        ],
        [
            'name' => 'admin',
            'guard_name' => 'web',
            'description' => 'Full administrative access except super admin functions',
        ],
        [
            'name' => 'super-admin',
            'guard_name' => 'web',
            'description' => 'Complete system access including role and permission management',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permissions Configuration
    |--------------------------------------------------------------------------
    |
    | Define all available permissions in the system. Permissions are grouped
    | by category for better organization.
    |
    */
    'permissions' => [
        // User Management Permissions
        'user_management' => [
            [
                'name' => 'view-users',
                'guard_name' => 'web',
                'description' => 'View user profiles and basic information',
                'category' => 'User Management',
            ],
            [
                'name' => 'create-users',
                'guard_name' => 'web',
                'description' => 'Create new user accounts',
                'category' => 'User Management',
            ],
            [
                'name' => 'edit-users',
                'guard_name' => 'web',
                'description' => 'Edit existing user profiles and information',
                'category' => 'User Management',
            ],
            [
                'name' => 'delete-users',
                'guard_name' => 'web',
                'description' => 'Delete user accounts',
                'category' => 'User Management',
            ],
            [
                'name' => 'ban-users',
                'guard_name' => 'web',
                'description' => 'Ban or suspend user accounts',
                'category' => 'User Management',
            ],
        ],

        // Content Management Permissions
        'content_management' => [
            [
                'name' => 'moderate-comments',
                'guard_name' => 'web',
                'description' => 'Moderate user comments and reviews',
                'category' => 'Content Management',
            ],
            [
                'name' => 'manage-reports',
                'guard_name' => 'web',
                'description' => 'Handle user reports and complaints',
                'category' => 'Content Management',
            ],
            [
                'name' => 'manage-content',
                'guard_name' => 'web',
                'description' => 'Create, edit, and delete site content',
                'category' => 'Content Management',
            ],
        ],

        // System Administration Permissions
        'system_administration' => [
            [
                'name' => 'manage-roles',
                'guard_name' => 'web',
                'description' => 'Create, edit, and assign user roles',
                'category' => 'System Administration',
            ],
            [
                'name' => 'manage-permissions',
                'guard_name' => 'web',
                'description' => 'Create, edit, and assign permissions',
                'category' => 'System Administration',
            ],
            [
                'name' => 'access-admin-panel',
                'guard_name' => 'web',
                'description' => 'Access administrative dashboard and functions',
                'category' => 'System Administration',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Role-Permission Assignments
    |--------------------------------------------------------------------------
    |
    | Define which permissions each role should have. This creates the
    | relationship between roles and their allowed permissions.
    |
    */
    'role_permissions' => [
        'player' => [
            // Players have no special permissions beyond basic user functionality
        ],
        
        'moderator' => [
            'view-users',
            'moderate-comments',
            'manage-reports',
        ],
        
        'admin' => [
            'view-users',
            'create-users',
            'edit-users',
            'ban-users',
            'moderate-comments',
            'manage-reports',
            'manage-content',
            'access-admin-panel',
        ],
        
        'super-admin' => [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'ban-users',
            'moderate-comments',
            'manage-reports',
            'manage-content',
            'manage-roles',
            'manage-permissions',
            'access-admin-panel',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Role
    |--------------------------------------------------------------------------
    |
    | The default role assigned to new users upon registration.
    |
    */
    'default_role' => 'player',
];
