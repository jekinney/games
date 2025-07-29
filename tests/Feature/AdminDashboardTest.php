<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'super-admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'player']);
    }

    public function test_admin_dashboard_accessible_by_admin_users(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recent_users')
        );
    }

    public function test_admin_dashboard_accessible_by_super_admin_users(): void
    {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole('super-admin');

        $response = $this->actingAs($superAdmin)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats')
            ->has('recent_users')
        );
    }

    public function test_admin_dashboard_forbidden_for_regular_users(): void
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $response = $this->actingAs($user)
            ->get('/admin');

        $response->assertStatus(403);
    }

    public function test_admin_dashboard_forbidden_for_guests(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_users_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/admin/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users')
        );
    }

    public function test_admin_settings_page_accessible_by_admin(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)
            ->get('/admin/settings');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Settings')
            ->has('app_settings')
        );
    }

    public function test_admin_dashboard_shows_correct_stats(): void
    {
        $admin = User::factory()->create(['email_verified_at' => null]); // Unverified admin
        $admin->assignRole('admin');

        // Create some test users with specific verification status
        $verifiedUsers = User::factory()->count(3)->create(['email_verified_at' => now()]);
        $unverifiedUsers = User::factory()->count(2)->create(['email_verified_at' => null]);

        $response = $this->actingAs($admin)
            ->get('/admin');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Dashboard')
            ->has('stats', fn ($stats) => $stats
                ->where('total_users', 6) // 3 verified + 2 unverified + 1 admin
                ->where('verified_users', 3)
                ->where('unverified_users', 3) // 2 regular unverified + 1 admin (unverified)
                ->where('admin_users', 1)
                ->has('recent_registrations')
            )
        );
    }
}
