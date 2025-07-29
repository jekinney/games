<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get('/profile');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_users_can_visit_the_profile()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Profile/Index')
                ->has('user')
                ->has('stats')
        );
    }

    public function test_profile_displays_user_information()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertInertia(fn (Assert $page) => 
            $page->component('Profile/Index')
                ->where('user.name', 'John Doe')
                ->where('user.email', 'john@example.com')
                ->has('user.roles')
                ->has('user.permissions')
        );
    }

    public function test_profile_shows_user_role()
    {
        $user = User::factory()->create();
        
        // Seed roles
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        // Assign default role
        $defaultRole = config('roles.default_role', 'player');
        $user->assignRole($defaultRole);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertInertia(fn (Assert $page) => 
            $page->component('Profile/Index')
                ->where('user.roles.0', $defaultRole)
        );
    }
}
