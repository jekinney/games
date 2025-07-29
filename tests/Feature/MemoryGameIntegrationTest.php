<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoryGameIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_memory_game_page_loads_correctly()
    {
        $response = $this->get('/games/memory-test-game.html');

        $response->assertStatus(200);
        $response->assertSee('Memory Test Game');
        $response->assertSee('submitScore'); // Function should be present in JS
    }

    public function test_memory_game_has_proper_authentication_integration()
    {
        $user = User::factory()->create();

        // Test as authenticated user
        $response = $this->actingAs($user)->get('/games/memory-test-game.html');

        $response->assertStatus(200);
        // The game should load properly for authenticated users
        $response->assertSee('Memory Test Game');
    }

    public function test_memory_game_works_for_guest_users()
    {
        // Test as guest user - game should load but not submit scores
        $response = $this->get('/games/memory-test-game.html');

        $response->assertStatus(200);
        $response->assertSee('Memory Test Game');
        $response->assertSee('submitScore'); // Function exists but won't submit for guests
    }

    public function test_memory_game_javascript_functions_are_present()
    {
        $response = $this->get('/games/memory-test-game.html');

        $response->assertStatus(200);

        $content = $response->getContent();

        // Check that key game functions are present
        $this->assertStringContainsString('function startGame()', $content);
        $this->assertStringContainsString('function endGame()', $content);
        $this->assertStringContainsString('async function submitScore()', $content);
        $this->assertStringContainsString('function updateStats()', $content);
        $this->assertStringContainsString('function generateCards()', $content);
    }

    public function test_memory_game_has_proper_score_submission_endpoint()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Check that the game uses the correct API endpoint
        $this->assertStringContainsString('/api/leaderboards/memory-test-game/submit', $content);
    }

    public function test_memory_game_handles_authentication_properly()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Check that authentication handling is present
        $this->assertStringContainsString('userToken', $content);
        $this->assertStringContainsString('localStorage.getItem', $content);
        $this->assertStringContainsString('User not authenticated', $content);
    }

    public function test_memory_game_collects_comprehensive_game_data()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Verify that the game collects the expected data fields
        $expectedDataFields = [
            'final_level',
            'cards_matched',
            'total_cards',
            'score',
            'level_reached',
            'time_played_seconds'
        ];

        foreach ($expectedDataFields as $field) {
            $this->assertStringContainsString($field, $content);
        }
    }

    public function test_memory_game_error_handling()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Check that proper error handling is in place
        $this->assertStringContainsString('catch (error)', $content);
        $this->assertStringContainsString('console.error', $content);
        $this->assertStringContainsString('Failed to submit score', $content);
    }

    public function test_game_directory_structure()
    {
        // Verify that the memory game file exists in the correct location
        $gamePath = public_path('games/memory-test-game.html');
        $this->assertFileExists($gamePath);

        // Check that it's accessible via web route
        $response = $this->get('/games/memory-test-game.html');
        $response->assertStatus(200);
    }

    public function test_memory_game_csrf_token_handling()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Check that CSRF token is properly handled in the game
        $this->assertStringContainsString('X-CSRF-TOKEN', $content);
        $this->assertStringContainsString('csrf-token', $content);
    }

    public function test_memory_game_response_handling()
    {
        $response = $this->get('/games/memory-test-game.html');

        $content = $response->getContent();

        // Verify that the game handles API responses properly
        $this->assertStringContainsString('response.ok', $content);
        $this->assertStringContainsString('result.rank', $content);
        $this->assertStringContainsString('Score submitted!', $content);
    }
}
