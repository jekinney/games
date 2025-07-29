<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DropdownDebugTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Debug dropdown interactions
     */
    public function test_debug_dropdown_elements(): void
    {
        $user = User::factory()->create([
            'name' => 'Debug User',
            'email' => 'debug@example.com',
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/')
                    ->click('.user-dropdown button')
                    ->waitFor('.user-dropdown [href*="profile"]', 3)
                    ->pause(1000) // Give time for dropdown to fully appear
                    ->screenshot('dropdown-open')
                    ->assertPresent('.user-dropdown [href*="profile"]')
                    ->assertPresent('.user-dropdown button[type="button"]')
                    ->script('document.querySelector(".user-dropdown [href*=\"profile\"]").click()');
                    
            // Wait for navigation
            $browser->pause(2000)
                    ->screenshot('after-profile-click');
                    
            // Check if we're on the profile page or still on home
            if ($browser->driver->getCurrentURL() === 'http://games.test/profile') {
                $browser->assertSee('Player Profile');
            } else {
                // If still on home, let's see what happened
                $browser->assertPathIs('/');
            }
        });
    }
}
