<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GamesPageJavaScriptTest extends DuskTestCase
{
    public function test_search_input_reactivity(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('12 games')
                ->type('input[placeholder="Search games by name..."]', 'm')
                ->pause(200)
                ->assertSee('Memory Test Game')
                ->assertSee('Math Quiz')
                ->type('input[placeholder="Search games by name..."]', 'emo')
                ->pause(200)
                ->assertSee('Memory Test Game')
                ->assertDontSee('Math Quiz');
        });
    }

    public function test_search_clears_properly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'nonexistent')
                ->pause(200)
                ->assertSee('No Games Found')
                ->clear('input[placeholder="Search games by name..."]')
                ->pause(200)
                ->assertSee('Memory Test Game')
                ->assertDontSee('No Games Found');
        });
    }

    public function test_filter_toggle_animation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->assertScript('
                    const chevron = document.querySelector("svg.rotate-180");
                    return chevron !== null;
                ')
                ->click('button:contains("Filters")')
                ->waitUntilMissing('text:Categories')
                ->assertScript('
                    const chevron = document.querySelector("svg.rotate-180");
                    return chevron === null;
                ');
        });
    }

    public function test_session_storage_persistence(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->assertScript('
                    return sessionStorage.getItem("gamesFiltersVisible") === "true";
                ')
                ->click('button:contains("Filters")')
                ->waitUntilMissing('text:Categories')
                ->assertScript('
                    return sessionStorage.getItem("gamesFiltersVisible") === "false";
                ');
        });
    }

    public function test_session_storage_restoration(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->refresh()
                ->waitForText('Games Hub')
                ->assertSee('Categories'); // Should be restored from session storage
        });
    }

    public function test_multiple_filter_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->click('button:contains("Easy")')
                ->pause(200)
                ->assertSee('Word Search') // Should be both Puzzle and Easy
                ->assertScript('
                    const puzzleBtn = Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Puzzle"));
                    const easyBtn = Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Easy"));
                    return puzzleBtn.classList.contains("from-purple-500") && easyBtn.classList.contains("from-purple-500");
                ');
        });
    }

    public function test_filter_deselection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(200)
                ->assertSee('Memory Test Game')
                ->assertDontSee('Speed Typing')
                ->click('button:contains("Puzzle")') // Click again to deselect
                ->pause(200)
                ->assertSee('Speed Typing'); // Should show all games again
        });
    }

    public function test_pagination_state_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("2")')
                ->pause(200)
                ->assertSee('Space Invaders')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(200)
                // Should reset to page 1
                ->assertSee('Memory Test Game');
        });
    }

    public function test_game_modal_state_management(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Play Now")')
                ->waitForText('Playing Game')
                ->assertScript('
                    return document.body.style.overflow === "hidden";
                ') // Check if body scroll is disabled
                ->click('button:contains("×")')
                ->waitUntilMissing('text:Playing Game')
                ->pause(100)
                ->assertScript('
                    return document.body.style.overflow !== "hidden";
                '); // Check if body scroll is restored
        });
    }

    public function test_game_modal_backdrop_click(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Play Now")')
                ->waitForText('Playing Game')
                ->click('.fixed.inset-0') // Click backdrop
                ->waitUntilMissing('text:Playing Game');
        });
    }

    public function test_responsive_grid_changes(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->resize(375, 667) // Mobile
                ->assertScript('
                    const grid = document.querySelector(".grid");
                    return grid.classList.contains("grid-cols-1");
                ')
                ->resize(768, 1024) // Tablet
                ->assertScript('
                    const grid = document.querySelector(".grid");
                    return grid.classList.contains("md:grid-cols-2");
                ')
                ->resize(1024, 768) // Desktop
                ->assertScript('
                    const grid = document.querySelector(".grid");
                    return grid.classList.contains("lg:grid-cols-3");
                ');
        });
    }

    public function test_hover_effects_on_game_cards(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->mouseover('.group:first-child')
                ->pause(200)
                ->assertScript('
                    const card = document.querySelector(".group:first-child");
                    const emoji = card.querySelector(".text-6xl");
                    const computedStyle = window.getComputedStyle(emoji);
                    return computedStyle.transform && computedStyle.transform !== "none";
                '); // Check if emoji scale transform is applied
        });
    }

    public function test_game_card_button_interactions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->within('.group:first-child', function ($browser) {
                    $browser->mouseover('button:contains("Play Now")')
                        ->pause(100)
                        ->assertScript('
                            const button = document.querySelector("button:contains(\'Play Now\')") || 
                                          Array.from(document.querySelectorAll("button")).find(b => b.textContent.includes("Play Now"));
                            const computedStyle = window.getComputedStyle(button);
                            return computedStyle.transform && computedStyle.transform !== "none";
                        '); // Check hover scale effect
                });
        });
    }

    public function test_clear_all_filters_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'memory')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Puzzle")')
                ->pause(200)
                ->click('button:contains("Clear All")')
                ->pause(200)
                ->assertValue('input[placeholder="Search games by name..."]', '')
                ->assertSee('Speed Typing') // All games should be visible
                ->assertScript('
                    const buttons = Array.from(document.querySelectorAll("button"));
                    const activeFilters = buttons.filter(b => b.classList.contains("from-purple-500"));
                    return activeFilters.length === 0;
                '); // No filters should be active
        });
    }

    public function test_enter_key_search_functionality(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', 'chess')
                ->keys('input[placeholder="Search games by name..."]', '{enter}')
                ->pause(200)
                ->assertSee('Chess Master')
                ->assertDontSee('Memory Test Game');
        });
    }

    public function test_pagination_boundary_conditions(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // Test clicking Next on last page (should not change)
                ->click('button:contains("2")')
                ->pause(200)
                ->click('button:contains("Next")')
                ->pause(200)
                ->assertSee('Space Invaders') // Should still be on page 2
                // Test clicking Previous on first page
                ->click('button:contains("1")')
                ->pause(200)
                ->click('button:contains("Prev")')
                ->pause(200)
                ->assertSee('Memory Test Game'); // Should still be on page 1
        });
    }
}
