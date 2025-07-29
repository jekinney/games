<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class GamesPageNoResultsDisplayBrowserTest extends DuskTestCase
{
    /**
     * Test that "No Games Found" message does not appear when games are rendered
     */
    public function test_no_games_found_not_visible_with_games_present()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub')
                    ->waitForText('Memory Test Game'); // Wait for games to load

            // Verify games are visible
            $browser->assertSee('Memory Test Game')
                    ->assertSee('Speed Typing')
                    ->assertSee('Math Quiz')
                    ->assertSee('Play Now');

            // Verify "No Games Found" is not visible
            $browser->assertDontSee('No Games Found')
                    ->assertDontSee('No games match your current filters');

            // Verify the games grid is present
            $browser->assertPresent('.grid.grid-cols-1.gap-6.md\\:grid-cols-2.lg\\:grid-cols-3');
            
            // Verify the no results div is not visible
            $browser->assertNotPresent('.text-center.backdrop-blur-sm:has-text("No Games Found")');
        });
    }

    /**
     * Test DOM structure to ensure mutually exclusive display
     */
    public function test_dom_structure_prevents_simultaneous_display()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Check that games container is visible
            $gamesGrid = $browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::cssSelector('[data-testid="games-grid"], .grid.grid-cols-1.gap-6'));
            $this->assertGreaterThan(0, count($gamesGrid), 'Games grid should be present');

            // Check that no results message is not in DOM or not visible
            $noResultsElements = $browser->driver->findElements(\Facebook\WebDriver\WebDriverBy::xpath("//*[contains(text(), 'No Games Found')]"));
            
            if (count($noResultsElements) > 0) {
                // If the element exists, it should not be visible
                foreach ($noResultsElements as $element) {
                    $this->assertFalse($element->isDisplayed(), 'No Games Found message should not be visible when games are present');
                }
            }
        });
    }

    /**
     * Test pagination coexistence with games
     */
    public function test_pagination_coexists_with_games()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Games should be visible
            $browser->assertSee('Memory Test Game');

            // Pagination should be visible (since we have 12 games > 9 per page)
            $browser->assertSee('Next')
                    ->assertSee('Prev');

            // No empty state
            $browser->assertDontSee('No Games Found');
        });
    }

    /**
     * Test filtering behavior doesn't trigger false positives
     */
    public function test_filtering_with_results_shows_games_not_empty_state()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Open filters
            $browser->click('button:has-text("Filters")')
                    ->waitFor('.grid.grid-cols-1.gap-3.lg\\:grid-cols-3', 2);

            // Apply a filter that should return results
            $browser->click('button:has-text("Puzzle")')
                    ->pause(500); // Wait for filtering

            // Should still see games, not empty state
            $browser->assertSee('Memory Test Game') // This is a puzzle game
                    ->assertDontSee('No Games Found');

            // Results summary should update
            $browser->assertSeeIn('.text-sm.text-gray-300', 'game'); // Should show count
        });
    }

    /**
     * Test search functionality doesn't show empty state when results exist
     */
    public function test_search_with_results_shows_games()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Search for a game that exists
            $browser->type('input[placeholder="Search games by name..."]', 'Memory')
                    ->pause(500); // Wait for search filtering

            // Should show the matching game, not empty state
            $browser->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');
        });
    }

    /**
     * Test JavaScript-driven conditional rendering
     */
    public function test_javascript_conditional_rendering()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Execute JavaScript to check Vue.js reactive data
            $result = $browser->driver->executeScript("
                // Find the Vue component instance
                const app = document.querySelector('#app').__vue_app__;
                if (app) {
                    const instance = app._container._vnode.component;
                    if (instance && instance.setupState) {
                        return {
                            paginatedGamesLength: instance.setupState.paginatedGames?.length || 0,
                            filteredGamesLength: instance.setupState.filteredGames?.length || 0,
                            showGameModal: instance.setupState.showGameModal,
                            searchQuery: instance.setupState.searchQuery
                        };
                    }
                }
                return null;
            ");

            if ($result) {
                $this->assertGreaterThan(0, $result['paginatedGamesLength'], 'Paginated games should have content');
                $this->assertGreaterThan(0, $result['filteredGamesLength'], 'Filtered games should have content');
            }

            // Verify the conditional logic in DOM
            $browser->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');
        });
    }

    /**
     * Test responsive behavior maintains correct display
     */
    public function test_responsive_display_correctness()
    {
        $this->browse(function (Browser $browser) {
            // Test on desktop
            $browser->resize(1200, 800)
                    ->visit('/games')
                    ->waitForText('Games Hub')
                    ->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');

            // Test on tablet
            $browser->resize(768, 1024)
                    ->pause(500)
                    ->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');

            // Test on mobile
            $browser->resize(375, 667)
                    ->pause(500)
                    ->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');
        });
    }

    /**
     * Test edge case: navigate between pages
     */
    public function test_pagination_navigation_maintains_correct_display()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub');

            // Go to page 2
            $browser->click('button:has-text("2")')
                    ->pause(500);

            // Should still show games on page 2, not empty state
            $browser->assertPresent('.grid.grid-cols-1.gap-6')
                    ->assertDontSee('No Games Found');

            // Go back to page 1
            $browser->click('button:has-text("1")')
                    ->pause(500);

            // Should show page 1 games
            $browser->assertSee('Memory Test Game')
                    ->assertDontSee('No Games Found');
        });
    }

    /**
     * Comprehensive regression test for the bug fix
     */
    public function test_regression_no_simultaneous_games_and_empty_state()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                    ->waitForText('Games Hub')
                    ->pause(1000); // Ensure all Vue.js rendering is complete

            // Get all text content from the page
            $pageText = $browser->driver->findElement(\Facebook\WebDriver\WebDriverBy::tagName('body'))->getText();

            // Verify games are present
            $this->assertStringContainsString('Memory Test Game', $pageText);
            $this->assertStringContainsString('Speed Typing', $pageText);

            // Verify "No Games Found" is NOT present
            $this->assertStringNotContainsString('No Games Found', $pageText);
            $this->assertStringNotContainsString('No games match your current filters', $pageText);

            // Additional DOM checks
            $browser->assertPresent('.grid.grid-cols-1.gap-6') // Games grid should exist
                    ->assertMissing('[data-testid="no-results"]'); // No results should not exist

            // Verify conditional elements
            $gamesVisible = $browser->driver->executeScript("
                return document.querySelector('.grid.grid-cols-1.gap-6') !== null && 
                       getComputedStyle(document.querySelector('.grid.grid-cols-1.gap-6')).display !== 'none';
            ");
            
            $this->assertTrue($gamesVisible, 'Games grid should be visible');

            // Check if no-results element exists and if so, ensure it's hidden
            $noResultsHidden = $browser->driver->executeScript("
                const noResults = document.querySelector('div:has-text(\"No Games Found\")');
                return noResults === null || getComputedStyle(noResults).display === 'none';
            ");

            $this->assertTrue($noResultsHidden, 'No results message should be hidden or not exist');
        });
    }
}
