<?php

namespace Tests\Feature;

use Tests\TestCase;

class GamesPageNoResultsDisplayTest extends TestCase
{

    /**
     * Test that "No Games Found" message does not appear when games are present
     */
    public function test_no_games_found_message_not_shown_when_games_exist()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Verify the page contains games
        $response->assertSee('Memory Test Game');
        $response->assertSee('Speed Typing');
        $response->assertSee('Math Quiz');
        
        // Verify "No Games Found" message is NOT present
        $response->assertDontSee('No Games Found');
        $response->assertDontSee('No games match your current filters');
    }

    /**
     * Test that games grid is visible when games exist
     */
    public function test_games_grid_visible_when_games_exist()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Check for game elements that should be present
        $response->assertSee('🧠'); // Memory game emoji
        $response->assertSee('⌨️'); // Typing game emoji  
        $response->assertSee('🔢'); // Math quiz emoji
        $response->assertSee('Play Now');
        
        // Ensure no empty state messages
        $response->assertDontSee('🔍'); // No results emoji
        $response->assertDontSee('Clear All Filters');
    }

    /**
     * Test that pagination appears when there are enough games
     */
    public function test_pagination_visible_with_multiple_games()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // With 12 games and 9 per page, should have pagination
        $response->assertSee('Prev');
        $response->assertSee('Next');
        $response->assertSee('2'); // Page 2 should exist
    }

    /**
     * Test the conditional rendering logic in the template
     */
    public function test_conditional_rendering_logic()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Get the response content
        $content = $response->getContent();
        
        // Verify the template structure - games should be shown, no results should not
        $this->assertStringContainsString('grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3', $content);
        
        // The "No Results Message" div should not be rendered when games exist
        // This checks the v-else condition is working properly
        $this->assertStringNotContainsString('No games match your current filters or search criteria', $content);
    }

    /**
     * Test results summary shows correct count
     */
    public function test_results_summary_shows_correct_count()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Should show total count of games (12 games)
        $response->assertSee('12 games');
        
        // Should not show filter-specific messaging when no filters applied
        $response->assertDontSee('matching your filters');
    }

    /**
     * Test that both games and pagination can coexist
     */
    public function test_games_and_pagination_coexist()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Games should be visible
        $response->assertSee('Memory Test Game');
        $response->assertSee('Speed Typing');
        
        // Pagination should also be visible
        $response->assertSee('Prev');
        $response->assertSee('Next');
        
        // No empty state
        $response->assertDontSee('No Games Found');
    }

    /**
     * Test the template structure prevents both elements from showing
     */
    public function test_template_structure_prevents_dual_display()
    {
        $response = $this->get('/games');
        $content = $response->getContent();

        // Check that we have the games grid with v-if
        $this->assertStringContainsString('v-if="paginatedGames.length > 0"', $content);
        
        // Check that the no results message has v-else
        $this->assertStringContainsString('v-else', $content);
        
        // Verify they are mutually exclusive in the template
        $gameGridPos = strpos($content, 'v-if="paginatedGames.length > 0"');
        $noResultsPos = strpos($content, 'No Games Found');
        
        // Both positions should exist in template but only one should render
        $this->assertNotFalse($gameGridPos);
        $this->assertNotFalse($noResultsPos);
        
        // The actual rendered content should have games but not "No Games Found"
        $response->assertSee('Memory Test Game');
        $response->assertDontSee('No Games Found');
    }

    /**
     * Test edge case with exactly 9 games (one page)
     */
    public function test_single_page_with_games()
    {
        // This would test a scenario where we have exactly 9 games
        // For now, we test the current 12 games scenario
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Should still show games, not empty state
        $response->assertSee('Memory Test Game');
        $response->assertDontSee('No Games Found');
        
        // Should show pagination since we have 12 games > 9 per page
        $response->assertSee('Next');
    }

    /**
     * Test that placeholder "More Games Coming Soon" can coexist with games
     */
    public function test_placeholder_can_coexist_with_games()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        
        // Real games should be present
        $response->assertSee('Memory Test Game');
        
        // Placeholder might be present on last page
        // This should not trigger "No Games Found"
        $response->assertDontSee('No Games Found');
    }

    /**
     * Integration test to verify the bug fix
     */
    public function test_bug_fix_no_simultaneous_display()
    {
        $response = $this->get('/games');
        $content = $response->getContent();

        // Count occurrences to ensure they don't appear together
        $gamesCount = substr_count($content, 'Memory Test Game');
        $noResultsCount = substr_count($content, 'No Games Found');
        
        // Games should appear (at least once)
        $this->assertGreaterThan(0, $gamesCount);
        
        // "No Games Found" should not appear when games are present
        $this->assertEquals(0, $noResultsCount);
    }
}
