<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:list {--permissions : Show permissions for each role} {--config : Show configuration structure}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List roles and permissions in the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('config')) {
            $this->showConfiguration();
            return;
        }

        $this->showRolesAndPermissions();
    }

    /**
     * Show the configuration structure
     */
    private function showConfiguration()
    {
        $this->info('Roles Configuration Structure:');
        $this->line('');
        
        $rolesConfig = config('roles.roles');
        $permissionsConfig = config('roles.permissions');
        
        $this->info('📁 Roles:');
        foreach ($rolesConfig as $role) {
            $this->line("  • {$role['name']} - {$role['description']}");
        }
        
        $this->line('');
        $this->info('🔐 Permissions by Category:');
        foreach ($permissionsConfig as $category => $permissions) {
            $categoryName = ucwords(str_replace('_', ' ', $category));
            $this->line("  📂 {$categoryName}:");
            foreach ($permissions as $permission) {
                $this->line("    • {$permission['name']} - {$permission['description']}");
            }
        }
        
        $this->line('');
        $this->info('🎯 Default Role: ' . config('roles.default_role'));
    }

    /**
     * Show current roles and permissions in the database
     */
    private function showRolesAndPermissions()
    {
        $this->info('Current Roles and Permissions in Database:');
        $this->line('');

        $roles = Role::with('permissions')->get();
        
        if ($roles->isEmpty()) {
            $this->warn('No roles found in database. Run the seeder first: php artisan db:seed --class=RolePermissionSeeder');
            return;
        }

        foreach ($roles as $role) {
            $this->info("🎭 Role: {$role->name}");
            
            if ($this->option('permissions') && $role->permissions->isNotEmpty()) {
                $this->line("   Permissions:");
                foreach ($role->permissions as $permission) {
                    $this->line("   • {$permission->name}");
                }
            } elseif ($this->option('permissions')) {
                $this->line("   No special permissions");
            }
            
            $this->line('');
        }

        if (!$this->option('permissions')) {
            $this->comment('Use --permissions flag to see permissions for each role');
        }
        
        $this->line('');
        $this->info('💡 Tip: Use --config flag to see the configuration structure');
    }
}
