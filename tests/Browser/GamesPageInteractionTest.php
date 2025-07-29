<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GamesPageInteractionTest extends DuskTestCase
{
    public function test_search_filters_games_by_name(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'chess')
                ->pause(500)
                ->assertSee('Chess Master')
                ->assertDontSee('Memory Test Game')
                ->clear('input[placeholder="Search games by name..."]')
                ->pause(500)
                ->assertSee('Memory Test Game');
        });
    }

    public function test_search_filters_games_by_description(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'typing')
                ->pause(500)
                ->assertSee('Speed Typing')
                ->assertDontSee('Chess Master');
        });
    }

    public function test_category_filter_puzzle_games(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(500)
                ->assertSee('Memory Test Game')
                ->assertSee('Word Search')
                ->assertSee('Sudoku Master')
                ->assertDontSee('Speed Typing')
                ->assertDontSee('Snake Game');
        });
    }

    public function test_category_filter_arcade_games(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Arcade")')
                ->pause(500)
                ->assertSee('Snake Game')
                ->assertSee('Tetris Classic')
                ->assertSee('Space Invaders')
                ->assertDontSee('Memory Test Game')
                ->assertDontSee('Chess Master');
        });
    }

    public function test_difficulty_filter_easy_games(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Easy")')
                ->pause(500)
                ->assertSee('Speed Typing')
                ->assertSee('Tic Tac Toe')
                ->assertSee('Word Search')
                ->assertDontSee('Math Quiz')
                ->assertDontSee('Chess Master');
        });
    }

    public function test_difficulty_filter_hard_games(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Hard")')
                ->pause(500)
                ->assertSee('Math Quiz')
                ->assertSee('Tetris Classic')
                ->assertSee('Chess Master')
                ->assertSee('Sudoku Master')
                ->assertDontSee('Speed Typing')
                ->assertDontSee('Tic Tac Toe');
        });
    }

    public function test_player_count_filter_single_player(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("1 Player")')
                ->pause(500)
                ->assertSee('Memory Test Game')
                ->assertSee('Speed Typing')
                ->assertSee('Math Quiz')
                ->assertDontSee('Tic Tac Toe')
                ->assertDontSee('Chess Master');
        });
    }

    public function test_player_count_filter_two_player(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("2 Player")')
                ->pause(500)
                ->assertSee('Tic Tac Toe')
                ->assertSee('Chess Master')
                ->assertSee('Checkers')
                ->assertDontSee('Memory Test Game')
                ->assertDontSee('Speed Typing');
        });
    }

    public function test_multiple_filters_or_logic(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->click('button:contains("Skill")')
                ->pause(500)
                // Should show games from both categories (OR logic)
                ->assertSee('Memory Test Game') // Puzzle
                ->assertSee('Speed Typing'); // Skill
        });
    }

    public function test_filter_buttons_active_state_styling(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(100)
                // Check active state styling
                ->assertScript('
                    const button = document.querySelector("button:contains(\'Puzzle\')") || 
                                  Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Puzzle"));
                    return button && button.classList.contains("from-purple-500");
                ');
        });
    }

    public function test_clear_all_filters_button(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->type('input[placeholder="Search games by name..."]', 'memory')
                ->pause(500)
                ->assertSee('Clear All')
                ->click('button:contains("Clear All")')
                ->pause(500)
                ->assertSee('Speed Typing') // All games should be visible
                ->assertValue('input[placeholder="Search games by name..."]', ''); // Search should be cleared
        });
    }

    public function test_pagination_navigation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // Test Next button
                ->click('button:contains("Next")')
                ->pause(500)
                ->assertSee('Space Invaders')
                ->assertSee('Sudoku Master')
                ->assertSee('Checkers')
                // Test Previous button
                ->click('button:contains("Prev")')
                ->pause(500)
                ->assertSee('Memory Test Game')
                ->assertSee('Speed Typing');
        });
    }

    public function test_pagination_page_numbers(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("2")')
                ->pause(500)
                ->assertSee('Space Invaders')
                ->click('button:contains("1")')
                ->pause(500)
                ->assertSee('Memory Test Game');
        });
    }

    public function test_pagination_disabled_states(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // On page 1, Previous should be disabled
                ->assertScript('
                    const prevButton = Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Prev"));
                    return prevButton && prevButton.disabled;
                ')
                ->click('button:contains("2")')
                ->pause(500)
                // On page 2 (last page), Next should be disabled
                ->assertScript('
                    const nextButton = Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Next"));
                    return nextButton && nextButton.disabled;
                ');
        });
    }

    public function test_pagination_resets_on_filter_change(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("2")')
                ->pause(500)
                ->assertSee('Space Invaders')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(500)
                // Should be back on page 1 after filtering
                ->assertSee('Memory Test Game'); // First puzzle game
        });
    }

    public function test_game_modal_interaction(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->within('.group:first-child', function ($browser) {
                    $browser->click('button:contains("Play Now")');
                })
                ->waitForText('Playing Game')
                ->assertPresent('iframe')
                ->assertAttribute('iframe', 'src', '/games/memory-test-game.html')
                // Test clicking outside to close
                ->click('body')
                ->waitUntilMissing('text:Playing Game');
        });
    }

    public function test_results_summary_updates(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('12 games')
                ->type('input[placeholder="Search games by name..."]', 'chess')
                ->pause(500)
                ->assertSee('1 game matching your filters')
                ->clear('input[placeholder="Search games by name..."]')
                ->pause(500)
                ->assertSee('12 games');
        });
    }

    public function test_responsive_layout(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->resize(375, 667) // Mobile size
                ->assertPresent('.grid-cols-1')
                ->resize(768, 1024) // Tablet size
                ->assertPresent('.md\\:grid-cols-2')
                ->resize(1024, 768) // Desktop size
                ->assertPresent('.lg\\:grid-cols-3');
        });
    }
}
