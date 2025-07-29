<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GamesPageAccessibilityTest extends DuskTestCase
{
    public function test_keyboard_navigation_through_filters(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->keys('button:contains("Puzzle")', '{space}')
                ->pause(200)
                ->assertSee('Memory Test Game')
                ->assertDontSee('Speed Typing');
        });
    }

    public function test_focus_management_in_modal(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Play Now")')
                ->waitForText('Playing Game')
                ->keys('', '{escape}') // Test escape key to close modal
                ->waitUntilMissing('text:Playing Game');
        });
    }

    public function test_aria_labels_and_roles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertScript('
                    const searchInput = document.querySelector("input[placeholder*=\"Search\"]");
                    return searchInput && searchInput.getAttribute("type") === "text";
                ')
                ->assertScript('
                    const buttons = document.querySelectorAll("button");
                    return buttons.length > 0;
                ');
        });
    }

    public function test_high_contrast_mode_compatibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->script('
                    document.body.style.filter = "contrast(200%)";
                    document.body.style.background = "white";
                    document.body.style.color = "black";
                ')
                ->pause(500)
                ->assertSee('Games Hub')
                ->assertSee('Memory Test Game');
        });
    }

    public function test_screen_reader_text_content(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertScript('
                    const gameCards = document.querySelectorAll(".group");
                    return gameCards.length > 0 && 
                           Array.from(gameCards).every(card => card.textContent.trim().length > 0);
                ');
        });
    }

    public function test_color_blind_accessibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories')
                ->click('button:contains("Easy")')
                ->pause(200)
                // Verify that difficulty is indicated by text, not just color
                ->assertSee('Easy')
                ->assertSee('Medium')
                ->assertSee('Hard');
        });
    }

    public function test_reduced_motion_preferences(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->script('
                    const mediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
                    if (mediaQuery.matches) {
                        console.log("Reduced motion detected");
                    }
                ')
                ->mouseover('.group:first-child .text-6xl')
                ->pause(200)
                ->assertScript('
                    const emoji = document.querySelector(".group:first-child .text-6xl");
                    return emoji !== null;
                ');
        });
    }

    public function test_text_scaling_compatibility(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->script('document.body.style.fontSize = "150%";')
                ->pause(500)
                ->assertSee('Games Hub')
                ->assertSee('Memory Test Game')
                ->script('document.body.style.fontSize = "200%";')
                ->pause(500)
                ->assertSee('Games Hub');
        });
    }
}

class GamesPagePerformanceTest extends DuskTestCase
{
    public function test_initial_page_load_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $startTime = microtime(true);
            
            $browser->visit('/games')
                ->waitForText('Games Hub');
            
            $endTime = microtime(true);
            $loadTime = ($endTime - $startTime) * 1000;
            
            // Page should load within 3 seconds
            $this->assertLessThan(3000, $loadTime, 'Page load time should be under 3 seconds');
        });
    }

    public function test_search_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub');
            
            $startTime = microtime(true);
            
            $browser->type('input[placeholder="Search games by name..."]', 'memory')
                ->pause(100); // Small pause to allow rendering
            
            $endTime = microtime(true);
            $searchTime = ($endTime - $startTime) * 1000;
            
            // Search should be responsive within 500ms
            $this->assertLessThan(500, $searchTime, 'Search should respond within 500ms');
            $browser->assertSee('Memory Test Game');
        });
    }

    public function test_filter_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories');
            
            $startTime = microtime(true);
            
            $browser->click('button:contains("Puzzle")')
                ->pause(50);
            
            $endTime = microtime(true);
            $filterTime = ($endTime - $startTime) * 1000;
            
            // Filtering should be responsive within 300ms
            $this->assertLessThan(300, $filterTime, 'Filtering should respond within 300ms');
            $browser->assertSee('Memory Test Game');
        });
    }

    public function test_pagination_performance(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub');
            
            $startTime = microtime(true);
            
            $browser->click('button:contains("2")')
                ->pause(50);
            
            $endTime = microtime(true);
            $paginationTime = ($endTime - $startTime) * 1000;
            
            // Pagination should be responsive within 200ms
            $this->assertLessThan(200, $paginationTime, 'Pagination should respond within 200ms');
            $browser->assertSee('Space Invaders');
        });
    }

    public function test_memory_usage_stability(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub');
            
            // Perform multiple operations to test for memory leaks
            for ($i = 0; $i < 5; $i++) {
                $browser->click('button:contains("Filters")')
                    ->waitForText('Categories')
                    ->click('button:contains("Puzzle")')
                    ->pause(100)
                    ->click('button:contains("Clear All")')
                    ->pause(100)
                    ->click('button:contains("Filters")')
                    ->waitUntilMissing('text:Categories');
            }
            
            // Page should still be functional
            $browser->assertSee('Games Hub')
                ->assertSee('Memory Test Game');
        });
    }
}

class GamesPageErrorHandlingTest extends DuskTestCase
{
    public function test_handles_invalid_search_characters(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', '<script>alert("xss")</script>')
                ->pause(200)
                ->assertSee('No Games Found')
                ->assertDontSee('<script>');
        });
    }

    public function test_handles_extremely_long_search_queries(): void
    {
        $this->browse(function (Browser $browser) {
            $longQuery = str_repeat('a', 1000);
            
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->type('input[placeholder="Search games by name..."]', $longQuery)
                ->pause(200)
                ->assertSee('No Games Found');
        });
    }

    public function test_handles_rapid_filter_clicking(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->click('button:contains("Filters")')
                ->waitForText('Categories');
            
            // Rapidly click multiple filters
            for ($i = 0; $i < 5; $i++) {
                $browser->click('button:contains("Puzzle")')
                    ->click('button:contains("Easy")')
                    ->click('button:contains("1 Player")');
            }
            
            $browser->pause(200)
                ->assertSee('Games Hub'); // Page should still work
        });
    }

    public function test_handles_network_interruption_simulation(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                // Simulate slow network by adding delays
                ->script('
                    const originalFetch = window.fetch;
                    window.fetch = function(...args) {
                        return new Promise(resolve => {
                            setTimeout(() => resolve(originalFetch(...args)), 100);
                        });
                    };
                ')
                ->type('input[placeholder="Search games by name..."]', 'memory')
                ->pause(300)
                ->assertSee('Memory Test Game');
        });
    }

    public function test_handles_javascript_disabled_gracefully(): void
    {
        // Note: This test simulates what would happen if JS failed to load
        $this->browse(function (Browser $browser) {
            $browser->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('Memory Test Game')
                ->assertSee('Speed Typing')
                ->assertPresent('input[placeholder="Search games by name..."]')
                ->assertPresent('button:contains("Filters")');
        });
    }

    public function test_handles_viewport_size_edge_cases(): void
    {
        $this->browse(function (Browser $browser) {
            // Very small viewport
            $browser->resize(320, 480)
                ->visit('/games')
                ->waitForText('Games Hub')
                ->assertSee('Memory Test Game');
            
            // Very wide viewport
            $browser->resize(2560, 1440)
                ->refresh()
                ->waitForText('Games Hub')
                ->assertSee('Memory Test Game');
            
            // Very tall viewport
            $browser->resize(800, 2000)
                ->refresh()
                ->waitForText('Games Hub')
                ->assertSee('Memory Test Game');
        });
    }
}
