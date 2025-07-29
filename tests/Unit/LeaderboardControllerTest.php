<?php

namespace Tests\Unit;

use App\Http\Controllers\LeaderboardController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery;

class LeaderboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected LeaderboardController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new LeaderboardController();
    }

    public function test_get_leaderboard_data_converts_slug_to_game_name()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        $this->assertEquals('Memory Test Game', $data['game']['name']);
        $this->assertEquals('memory-test-game', $data['game']['slug']);
    }

    public function test_get_leaderboard_data_handles_complex_slugs()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('super-mario-bros-3', $request);
        
        $data = $response->getData(true);
        $this->assertEquals('Super Mario Bros 3', $data['game']['name']);
        $this->assertEquals('super-mario-bros-3', $data['game']['slug']);
    }

    public function test_get_leaderboard_data_returns_mock_data_for_memory_game()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        
        $this->assertArrayHasKey('topTen', $data);
        $this->assertArrayHasKey('currentUser', $data);
        $this->assertArrayHasKey('game', $data);
        
        // Check mock data structure
        $this->assertCount(5, $data['topTen']);
        $this->assertEquals('GameMaster2024', $data['topTen'][0]['user_name']);
        $this->assertEquals(15750, $data['topTen'][0]['score']);
        $this->assertEquals(8, $data['topTen'][0]['level_reached']);
    }

    public function test_get_leaderboard_data_returns_empty_for_unknown_games()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('unknown-game', $request);
        
        $data = $response->getData(true);
        
        $this->assertEmpty($data['topTen']);
        $this->assertNull($data['currentUser']);
        $this->assertEquals('Unknown Game', $data['game']['name']);
    }

    public function test_get_leaderboard_data_includes_current_user_when_authenticated()
    {
        $user = User::factory()->create(['name' => 'TestPlayer']);
        $this->actingAs($user);
        
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        
        $this->assertNotNull($data['currentUser']);
        $this->assertEquals('TestPlayer', $data['currentUser']['user_name']);
        $this->assertEquals(8750, $data['currentUser']['score']);
        $this->assertEquals(12, $data['currentUser']['rank']);
        $this->assertFalse($data['currentUser']['inTopTen']);
    }

    public function test_get_leaderboard_data_returns_null_user_when_not_authenticated()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        
        $this->assertNull($data['currentUser']);
    }

    public function test_submit_score_validates_input()
    {
        $request = new Request([
            'score' => 1500,
            'level_reached' => 5,
            'time_played_seconds' => 120,
            'game_data' => ['moves' => 30],
            'completed_at' => '2025-07-29T10:00:00Z'
        ]);

        $response = $this->controller->submitScore($request, 'memory-test-game');
        
        $data = $response->getData(true);
        
        $this->assertEquals('Score submitted successfully!', $data['message']);
        $this->assertEquals(1500, $data['score']);
        $this->assertIsInt($data['rank']);
        $this->assertGreaterThan(0, $data['rank']);
        $this->assertLessThanOrEqual(50, $data['rank']);
    }

    public function test_submit_score_returns_random_rank()
    {
        $request = new Request(['score' => 2500]);

        $response1 = $this->controller->submitScore($request, 'test-game');
        $response2 = $this->controller->submitScore($request, 'test-game');
        
        $data1 = $response1->getData(true);
        $data2 = $response2->getData(true);
        
        // Since rank is random, we can't guarantee they're different,
        // but we can ensure they're in valid range
        $this->assertGreaterThanOrEqual(1, $data1['rank']);
        $this->assertLessThanOrEqual(50, $data1['rank']);
        $this->assertGreaterThanOrEqual(1, $data2['rank']);
        $this->assertLessThanOrEqual(50, $data2['rank']);
    }

    public function test_controller_methods_return_json_responses()
    {
        $request = new Request();
        
        $leaderboardResponse = $this->controller->getLeaderboardData('test-game', $request);
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $leaderboardResponse);
        
        $submitRequest = new Request(['score' => 1000]);
        $submitResponse = $this->controller->submitScore($submitRequest, 'test-game');
        $this->assertInstanceOf(\Illuminate\Http\JsonResponse::class, $submitResponse);
    }

    public function test_leaderboard_data_has_correct_date_format()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        
        foreach ($data['topTen'] as $entry) {
            $this->assertMatchesRegularExpression(
                '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}Z$/',
                $entry['created_at'],
                'Date should be in ISO 8601 format'
            );
        }
    }

    public function test_mock_data_is_sorted_by_score_descending()
    {
        $request = new Request();
        
        $response = $this->controller->getLeaderboardData('memory-test-game', $request);
        
        $data = $response->getData(true);
        $scores = array_column($data['topTen'], 'score');
        
        $sortedScores = $scores;
        rsort($sortedScores);
        
        $this->assertEquals($sortedScores, $scores, 'Scores should be in descending order');
    }

    public function test_game_slug_conversion_handles_edge_cases()
    {
        $request = new Request();
        
        // Test single word
        $response = $this->controller->getLeaderboardData('tetris', $request);
        $data = $response->getData(true);
        $this->assertEquals('Tetris', $data['game']['name']);
        
        // Test multiple hyphens
        $response = $this->controller->getLeaderboardData('super-mario-bros-world', $request);
        $data = $response->getData(true);
        $this->assertEquals('Super Mario Bros World', $data['game']['name']);
        
        // Test numbers
        $response = $this->controller->getLeaderboardData('final-fantasy-7', $request);
        $data = $response->getData(true);
        $this->assertEquals('Final Fantasy 7', $data['game']['name']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
