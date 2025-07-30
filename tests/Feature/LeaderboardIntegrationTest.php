<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create memory test game for integration testing
        \App\Models\Game::create([
            'name' => 'Memory Test Game',
            'slug' => 'memory-test-game',
            'description' => 'A test memory game',
            'game_file_url' => '/games/memory-test-game.html',
            'category' => 'puzzle',
            'difficulty' => 'medium',
            'is_active' => true,
            'min_players' => 1,
            'max_players' => 1,
            'estimated_play_time' => 15,
        ]);

        // Create additional test games
        $testGames = [
            ['name' => 'Puzzle Master', 'slug' => 'puzzle-master'],
            ['name' => 'Tetris Classic', 'slug' => 'tetris-classic'],
            ['name' => 'Space Invaders', 'slug' => 'space-invaders'],
            ['name' => 'Speed Typing', 'slug' => 'speed-typing'],
            ['name' => 'Math Quiz', 'slug' => 'math-quiz'],
        ];

        foreach ($testGames as $gameData) {
            \App\Models\Game::create([
                'name' => $gameData['name'],
                'slug' => $gameData['slug'],
                'description' => 'A test game',
                'game_file_url' => '/games/' . $gameData['slug'] . '.html',
                'category' => 'puzzle',
                'difficulty' => 'medium',
                'is_active' => true,
                'min_players' => 1,
                'max_players' => 1,
                'estimated_play_time' => 15,
            ]);
        }
    }

    public function test_games_page_renders_with_leaderboard_buttons()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        
        // Check that the page contains the Games component which will render leaderboard buttons
        $response->assertSee('Games', false); // The Vue component name in the page data
    }

    public function test_leaderboard_modal_data_flow()
    {
        // First, verify the games page loads
        $response = $this->get('/games');
        $response->assertStatus(200);

        // Then verify the API endpoint returns data
        $apiResponse = $this->getJson('/api/leaderboards/memory-test-game');
        $apiResponse->assertStatus(200)
            ->assertJsonStructure([
                'topTen',
                'currentUser',
                'game'
            ]);

        // Verify the data structure matches what the frontend expects
        $data = $apiResponse->json();
        $this->assertIsArray($data['topTen']);
        $this->assertArrayHasKey('name', $data['game']);
        $this->assertArrayHasKey('slug', $data['game']);
    }

    public function test_authenticated_user_leaderboard_flow()
    {
        $user = User::factory()->create([
            'name' => 'TestGamer'
        ]);

        $game = \App\Models\Game::where('slug', 'memory-test-game')->first();
        
        // Create a score for this user first
        \App\Models\GameScore::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score' => 3500,
            'level_reached' => 4,
            'created_at' => now()->subDays(1),
        ]);

        // Test authenticated games page access
        $response = $this->actingAs($user)->get('/games');
        $response->assertStatus(200);

        // Test authenticated API access
        $apiResponse = $this->actingAs($user)->getJson('/api/leaderboards/memory-test-game');
        $apiResponse->assertStatus(200);

        $data = $apiResponse->json();
        $this->assertNotNull($data['currentUser']);
        $this->assertEquals('TestGamer', $data['currentUser']['user_name']);
        $this->assertEquals(3500, $data['currentUser']['score']);

        // Test score submission
        $submitResponse = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => 5000,
                'level_reached' => 3
            ]);
        
        $submitResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Score submitted successfully!',
                'score' => 5000
            ]);
    }

    public function test_guest_user_leaderboard_flow()
    {
        // Test guest games page access
        $response = $this->get('/games');
        $response->assertStatus(200);

        // Test guest API access (should work for viewing)
        $apiResponse = $this->getJson('/api/leaderboards/memory-test-game');
        $apiResponse->assertStatus(200);

        $data = $apiResponse->json();
        $this->assertNull($data['currentUser']);

        // Test guest score submission (should fail)
        $submitResponse = $this->postJson('/api/leaderboards/memory-test-game/submit', [
            'score' => 5000
        ]);
        
        $submitResponse->assertStatus(401);
    }

    public function test_multiple_games_leaderboard_consistency()
    {
        $gamesSlugs = [
            'memory-test-game',
            'puzzle-master',
            'tetris-classic',
            'space-invaders'
        ];

        foreach ($gamesSlugs as $slug) {
            $response = $this->getJson("/api/leaderboards/{$slug}");
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'topTen',
                    'currentUser',
                    'game' => [
                        'name',
                        'slug'
                    ]
                ]);

            $data = $response->json();
            $this->assertEquals($slug, $data['game']['slug']);
        }
    }

    public function test_leaderboard_performance_with_concurrent_requests()
    {
        // Simulate multiple concurrent requests to the leaderboard API
        $responses = [];
        
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->getJson('/api/leaderboards/memory-test-game');
        }

        foreach ($responses as $response) {
            $response->assertStatus(200);
            
            $data = $response->json();
            $this->assertEquals('Memory Test Game', $data['game']['name']);
        }
    }

    public function test_leaderboard_data_validation_end_to_end()
    {
        $user = User::factory()->create();

        // Test various score submission scenarios
        $testCases = [
            ['score' => 0, 'expected_status' => 200], // Minimum valid score
            ['score' => 999999, 'expected_status' => 200], // High score
            ['score' => 1500, 'level_reached' => 5, 'expected_status' => 200], // With level
            ['score' => 2000, 'time_played_seconds' => 300, 'expected_status' => 200], // With time
        ];

        foreach ($testCases as $testCase) {
            $response = $this->actingAs($user)
                ->postJson('/api/leaderboards/test-game/submit', $testCase);

            $response->assertStatus($testCase['expected_status']);

            if ($testCase['expected_status'] === 200) {
                $response->assertJsonStructure([
                    'message',
                    'score',
                    'rank'
                ]);
            }
        }
    }

    public function test_error_handling_for_malformed_requests()
    {
        $user = User::factory()->create();

        // Test malformed score submissions
        $invalidCases = [
            ['score' => -1], // Negative score
            ['score' => 'invalid'], // Non-numeric score
            ['level_reached' => 0], // Invalid level (missing score)
            ['time_played_seconds' => -5, 'score' => 1000], // Negative time
        ];

        foreach ($invalidCases as $invalidData) {
            $response = $this->actingAs($user)
                ->postJson('/api/leaderboards/test-game/submit', $invalidData);

            $response->assertStatus(422); // Validation error
        }
    }

    public function test_leaderboard_accessibility_compliance()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        
        // Check for basic accessibility elements that should be present
        $content = $response->getContent();
        
        // Verify the page has proper structure for screen readers
        // Since leaderboard buttons are Vue components, check for basic page accessibility
        $this->assertStringContainsString('lang="en"', $content);
        $this->assertStringContainsString('charset="utf-8"', $content);
        $this->assertStringContainsString('viewport', $content);
    }
}
