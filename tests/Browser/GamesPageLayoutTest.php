<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GamesPageLayoutTest extends DuskTestCase
{
    public function test_games_page_uses_main_layout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('🎮 Games Hub')
                ->assertSee('Dive into exciting games, challenge your skills, and compete with players worldwide.')
                ->assertSee('Memory Test Game')
                ->assertSee('Speed Typing')
                ->assertSee('Math Quiz')
                ->assertSee('Snake Game')
                ->assertSee('Tic Tac Toe')
                ->assertSee('Games Hub'); // Logo from MainNavigation
        });
    }

    public function test_games_page_hero_section(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('🎮 Games Hub')
                ->assertSee('Dive into exciting games, challenge your skills, and compete with players worldwide. Your gaming adventure awaits!')
                ->assertPresent('input[placeholder="Search games by name..."]');
        });
    }

    public function test_games_page_search_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'memory')
                ->waitFor('[data-testid="game-card"], .group')
                ->assertSee('Memory Test Game')
                ->assertDontSee('Speed Typing')
                ->clear('input[placeholder="Search games by name..."]')
                ->pause(500)
                ->assertSee('Speed Typing'); // Should show all games again
        });
    }

    public function test_games_page_filter_toggle(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // Filters should be closed by default
                ->assertDontSee('Categories')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->assertSee('Categories')
                ->assertSee('Difficulty')
                ->assertSee('Players')
                ->click('button:contains("Filters")')
                ->waitUntilMissing('text:Categories')
                ->assertDontSee('Categories');
        });
    }

    public function test_games_page_filtering_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->waitFor('.group')
                ->assertSee('Memory Test Game')
                ->assertSee('Word Search')
                ->assertDontSee('Speed Typing')
                ->click('button:contains("Clear All")')
                ->pause(500)
                ->assertSee('Speed Typing'); // Should show all games again
        });
    }

    public function test_games_page_pagination_when_more_than_nine_games(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // With 12 games, should show pagination
                ->assertSee('2') // Page 2 button
                ->assertSee('Prev')
                ->assertSee('Next')
                ->click('button:contains("2")')
                ->waitFor('.group')
                ->assertSee('Space Invaders')
                ->assertSee('Sudoku Master')
                ->assertSee('Checkers')
                ->click('button:contains("1")')
                ->waitFor('.group')
                ->assertSee('Memory Test Game');
        });
    }

    public function test_game_cards_display_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->within('.group:first-child', function ($browser) {
                    $browser->assertSee('🧠')
                        ->assertSee('Memory Test Game')
                        ->assertSee('Test your memory skills')
                        ->assertSee('Puzzle')
                        ->assertSee('Medium')
                        ->assertSee('1 Player')
                        ->assertSee('Play Now');
                });
        });
    }

    public function test_game_modal_opens_and_closes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Play Now")')
                ->waitForText('Playing Game')
                ->assertSee('Playing Game')
                ->assertPresent('iframe')
                ->click('button:contains("×")')
                ->waitUntilMissing('text:Playing Game')
                ->assertDontSee('Playing Game');
        });
    }

    public function test_games_page_navigation_consistency_authenticated(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('Games Hub') // Logo text from MainNavigation
                ->assertSee($user->name); // User dropdown should show name
        });
    }

    public function test_games_page_results_summary(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('12 games') // Total number of games
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->waitFor('.group')
                ->assertSeeIn('p', 'matching your filters');
        });
    }

    public function test_no_results_message(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'nonexistentgame')
                ->pause(500)
                ->assertSee('No Games Found')
                ->assertSee('No games match your current filters or search criteria.')
                ->assertSee('Clear All Filters');
        });
    }

    public function test_session_storage_filter_state(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // Filters should be closed by default
                ->assertDontSee('Categories')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->refresh()
                ->waitForText('Games Hub')
                // Should remember filter state
                ->assertSee('Categories');
        });
    }
}
