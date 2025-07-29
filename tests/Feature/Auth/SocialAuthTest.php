<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for each test
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    /** @test */
    public function user_can_redirect_to_github_provider()
    {
        $response = $this->get('/auth/github');
        
        $response->assertStatus(302);
        $this->assertStringContainsString('github.com', $response->getTargetUrl());
    }

    /** @test */
    public function user_can_redirect_to_google_provider()
    {
        $response = $this->get('/auth/google');
        
        $response->assertStatus(302);
        $this->assertStringContainsString('google.com', $response->getTargetUrl());
    }

    /** @test */
    public function user_can_redirect_to_facebook_provider()
    {
        $response = $this->get('/auth/facebook');
        
        $response->assertStatus(302);
        $this->assertStringContainsString('facebook.com', $response->getTargetUrl());
    }

    /** @test */
    public function user_can_login_with_github_account()
    {
        // Mock Socialite
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://github.com/avatar.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

        $response = $this->get('/auth/github/callback');

        $response->assertRedirect('/profile');
        
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'provider' => 'github',
            'provider_id' => '123456',
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue($user->hasRole('player'));
    }

    /** @test */
    public function existing_user_can_login_with_social_provider()
    {
        // Create existing user
        $user = User::factory()->create([
            'email' => 'john@example.com',
            'provider' => 'github',
            'provider_id' => '123456',
        ]);
        $user->assignRole('player');

        // Mock Socialite
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getId')->andReturn('123456');
        $socialiteUser->shouldReceive('getName')->andReturn('John Doe Updated');
        $socialiteUser->shouldReceive('getEmail')->andReturn('john@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('https://github.com/new-avatar.jpg');

        Socialite::shouldReceive('driver->user')->andReturn($socialiteUser);

        $response = $this->get('/auth/github/callback');

        $response->assertRedirect('/profile');
        
        // Check user was updated
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe Updated',
            'avatar' => 'https://github.com/new-avatar.jpg',
        ]);
    }

    /** @test */
    public function user_can_register_with_traditional_form()
    {
        $response = $this->post('/register', [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/profile');
        
        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
            'name' => 'Jane Doe',
            'provider' => null,
        ]);

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertTrue($user->hasRole('player'));
    }

    /** @test */
    public function user_can_login_with_traditional_form()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);
        $user->assignRole('player');

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/profile');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }
}
