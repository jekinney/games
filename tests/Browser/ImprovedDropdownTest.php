<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ImprovedDropdownTest extends DuskTestCase
{
    public function test_new_dropdown_component_works(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/')
                ->waitForText('Welcome to')
                // Check that user dropdown trigger is present
                ->assertSee($user->name)
                // Look for the dropdown trigger button with data-slot attribute
                ->waitFor('[data-slot="dropdown-menu-trigger"]', 5)
                ->click('[data-slot="dropdown-menu-trigger"]')
                ->pause(1000) // Wait for dropdown animation
                ->assertSee('Profile')
                ->assertSee('Log Out');
        });
    }

    public function test_dropdown_logout_functionality(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/')
                ->waitForText('Welcome to')
                // Click on the dropdown trigger
                ->waitFor('[data-slot="dropdown-menu-trigger"]', 5)
                ->click('[data-slot="dropdown-menu-trigger"]')
                ->pause(1000) // Wait for dropdown animation
                // Click logout button
                ->click('button:contains("Log Out")')
                ->waitForLocation('/') // Should redirect to home
                ->assertSee('Log in') // Should see login link for guests
                ->assertSee('Get Started'); // Should see register link for guests
        });
    }
}
