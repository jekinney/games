<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_can_get_leaderboard_data_for_memory_test_game()
    {
        $response = $this->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'topTen' => [
                    '*' => [
                        'id',
                        'user_name',
                        'score',
                        'level_reached',
                        'created_at'
                    ]
                ],
                'currentUser',
                'game' => [
                    'name',
                    'slug'
                ]
            ]);

        // Check that we get mock data for Memory Test Game
        $data = $response->json();
        $this->assertEquals('Memory Test Game', $data['game']['name']);
        $this->assertEquals('memory-test-game', $data['game']['slug']);
        $this->assertCount(5, $data['topTen']); // Mock data has 5 entries
        $this->assertEquals('GameMaster2024', $data['topTen'][0]['user_name']);
        $this->assertEquals(15750, $data['topTen'][0]['score']);
    }

    public function test_can_get_leaderboard_data_for_other_games()
    {
        $response = $this->getJson('/api/leaderboards/puzzle-master');

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
        $this->assertEquals('Puzzle Master', $data['game']['name']);
        $this->assertEquals('puzzle-master', $data['game']['slug']);
        $this->assertEmpty($data['topTen']); // No mock data for other games
    }

    public function test_leaderboard_shows_current_user_when_authenticated()
    {
        $user = User::factory()->create([
            'name' => 'TestUser123'
        ]);

        $response = $this->actingAs($user)->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotNull($data['currentUser']);
        $this->assertEquals('TestUser123', $data['currentUser']['user_name']);
        $this->assertEquals(8750, $data['currentUser']['score']);
        $this->assertEquals(12, $data['currentUser']['rank']);
        $this->assertFalse($data['currentUser']['inTopTen']);
    }

    public function test_leaderboard_shows_null_current_user_when_not_authenticated()
    {
        $response = $this->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNull($data['currentUser']);
    }

    public function test_can_submit_score_when_authenticated()
    {
        $user = User::factory()->create();

        $scoreData = [
            'score' => 12500,
            'level_reached' => 6,
            'time_played_seconds' => 180,
            'game_data' => ['moves' => 45, 'hints_used' => 2],
            'completed_at' => now()->toISOString()
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'score',
                'rank'
            ]);

        $data = $response->json();
        $this->assertEquals('Score submitted successfully!', $data['message']);
        $this->assertEquals(12500, $data['score']);
        $this->assertIsInt($data['rank']);
        $this->assertGreaterThan(0, $data['rank']);
    }

    public function test_cannot_submit_score_when_not_authenticated()
    {
        $scoreData = [
            'score' => 12500,
            'level_reached' => 6
        ];

        $response = $this->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(401);
    }

    public function test_score_submission_validates_required_fields()
    {
        $user = User::factory()->create();

        // Test missing score
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // Test invalid score
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => -100
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // Test invalid level_reached
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => 1000,
                'level_reached' => 0
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level_reached']);
    }

    public function test_score_submission_accepts_optional_fields()
    {
        $user = User::factory()->create();

        $scoreData = [
            'score' => 5000,
            'level_reached' => 3,
            'time_played_seconds' => 120,
            'game_data' => ['difficulty' => 'easy'],
            'completed_at' => '2025-07-29T10:00:00Z'
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(200);
    }

    public function test_leaderboard_endpoint_handles_special_characters_in_slug()
    {
        $response = $this->getJson('/api/leaderboards/test-game-with-numbers-123');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals('Test Game With Numbers 123', $data['game']['name']);
        $this->assertEquals('test-game-with-numbers-123', $data['game']['slug']);
    }

    public function test_leaderboard_data_structure_is_consistent()
    {
        $response = $this->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200);

        $data = $response->json();

        // Verify top ten structure
        foreach ($data['topTen'] as $entry) {
            $this->assertArrayHasKey('id', $entry);
            $this->assertArrayHasKey('user_name', $entry);
            $this->assertArrayHasKey('score', $entry);
            $this->assertArrayHasKey('level_reached', $entry);
            $this->assertArrayHasKey('created_at', $entry);
            
            $this->assertIsInt($entry['id']);
            $this->assertIsString($entry['user_name']);
            $this->assertIsInt($entry['score']);
            $this->assertIsInt($entry['level_reached']);
            $this->assertIsString($entry['created_at']);
        }

        // Verify game structure
        $this->assertArrayHasKey('name', $data['game']);
        $this->assertArrayHasKey('slug', $data['game']);
        $this->assertIsString($data['game']['name']);
        $this->assertIsString($data['game']['slug']);
    }

    public function test_top_ten_is_ordered_by_score_descending()
    {
        $response = $this->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200);

        $data = $response->json();
        $scores = array_column($data['topTen'], 'score');

        // Verify scores are in descending order
        $sortedScores = $scores;
        rsort($sortedScores);
        $this->assertEquals($sortedScores, $scores);

        // Verify first score is highest
        if (!empty($scores)) {
            $this->assertEquals(max($scores), $scores[0]);
        }
    }

    public function test_cors_headers_are_present()
    {
        $response = $this->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200)
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }
}
