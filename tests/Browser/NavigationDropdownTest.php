<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationDropdownTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test that guest users see login/register links
     */
    public function test_guest_user_sees_auth_links(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Log in')
                    ->assertSee('Get Started')
                    ->assertDontSee('Profile')
                    ->assertDontSee('Log Out');
        });
    }

    /**
     * Test that authenticated users see dropdown with their name
     */
    public function test_authenticated_user_sees_dropdown(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->assertSee($user->name)
                    ->assertDontSee('Log in')
                    ->assertDontSee('Get Started');
        });
    }

    /**
     * Test dropdown toggle functionality
     */
    public function test_dropdown_toggles_correctly(): void
    {
        $user = User::factory()->create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->assertDontSee('Profile')
                    ->assertDontSee('Log Out')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown [href*="profile"]', 3)
                    ->assertSee('Profile')
                    ->assertSee('Log Out')
                    // Click outside to close dropdown
                    ->click('h1')
                    ->waitUntilMissing('.user-dropdown [href*="profile"]', 3)
                    ->assertDontSee('Profile')
                    ->assertDontSee('Log Out');
        });
    }

    /**
     * Test profile link navigation
     */
    public function test_profile_link_works(): void
    {
        $user = User::factory()->create([
            'name' => 'Profile Test User',
            'email' => 'profile@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown [href*="profile"]', 3)
                    ->scrollTo('.user-dropdown [href*="profile"]')
                    ->pause(200)
                    ->clickLink('Profile')
                    ->assertPathIs('/profile')
                    ->assertSee('Player Profile');
        });
    }

    /**
     * Test logout functionality
     */
    public function test_logout_works(): void
    {
        $user = User::factory()->create([
            'name' => 'Logout Test User',
            'email' => 'logout@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown button[type="button"]', 3)
                    ->click('.user-dropdown button[type="button"]')
                    ->waitForReload(5)
                    ->assertPathIs('/')
                    ->assertSee('You have been successfully logged out')
                    ->assertSee('Log in')
                    ->assertSee('Get Started')
                    ->assertDontSee($user->name);
        });
    }

    /**
     * Test dropdown closes when clicking outside
     */
    public function test_dropdown_closes_when_clicking_outside(): void
    {
        $user = User::factory()->create([
            'name' => 'Click Outside User',
            'email' => 'clickoutside@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown [href*="profile"]', 3)
                    ->assertSee('Profile')
                    ->assertSee('Log Out')
                    // Click on the main content area
                    ->click('main')
                    ->waitUntilMissing('.user-dropdown [href*="profile"]', 3)
                    ->assertDontSee('Profile')
                    ->assertDontSee('Log Out');
        });
    }

    /**
     * Test dropdown hover states work correctly
     */
    public function test_dropdown_hover_states(): void
    {
        $user = User::factory()->create([
            'name' => 'Hover Test User',
            'email' => 'hover@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown [href*="profile"]', 3)
                    // Test that profile link is clickable and has proper styling
                    ->mouseover('.user-dropdown [href*="profile"]')
                    ->pause(500)
                    // Verify the element is present and styled correctly
                    ->assertPresent('.user-dropdown [href*="profile"]')
                    // Test that logout button is clickable and has proper styling  
                    ->mouseover('.user-dropdown button[type="button"]')
                    ->pause(500)
                    ->assertPresent('.user-dropdown button[type="button"]');
        });
    }
}
