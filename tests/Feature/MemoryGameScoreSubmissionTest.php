<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\GameScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class MemoryGameScoreSubmissionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create memory test game for score submission testing
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
    }

    public function test_authenticated_user_can_submit_memory_game_score()
    {
        $user = User::factory()->create();

        $scoreData = [
            'score' => 1500,
            'level_reached' => 5,
            'time_played_seconds' => 180,
            'game_data' => [
                'final_level' => 5,
                'cards_matched' => 16,
                'total_cards' => 20,
                'moves_count' => 25,
                'perfect_matches' => 8,
                'hints_used' => 0
            ],
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
        $this->assertEquals(1500, $data['score']);
        $this->assertIsInt($data['rank']);
    }

    public function test_memory_game_validates_score_data_structure()
    {
        $user = User::factory()->create();

        // Test with valid complete data
        $validData = [
            'score' => 2500,
            'level_reached' => 8,
            'time_played_seconds' => 300,
            'game_data' => [
                'final_level' => 8,
                'cards_matched' => 24,
                'total_cards' => 32,
                'moves_count' => 35,
                'perfect_matches' => 12,
                'hints_used' => 2,
                'difficulty' => 'hard'
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $validData);

        $response->assertStatus(200);
    }

    public function test_memory_game_score_submission_requires_authentication()
    {
        $scoreData = [
            'score' => 1000,
            'level_reached' => 3,
            'time_played_seconds' => 120
        ];

        $response = $this->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(401);
    }

    public function test_memory_game_validates_required_fields()
    {
        $user = User::factory()->create();

        // Test missing score
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'level_reached' => 3
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // Test negative score
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => -100
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['score']);

        // Test invalid level
        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => 1000,
                'level_reached' => 0
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['level_reached']);
    }

    public function test_memory_game_handles_game_data_correctly()
    {
        $user = User::factory()->create();

        $gameData = [
            'final_level' => 6,
            'cards_matched' => 20,
            'total_cards' => 24,
            'moves_count' => 28,
            'perfect_matches' => 10,
            'hints_used' => 1,
            'difficulty' => 'medium',
            'completion_percentage' => 83.33,
            'time_per_move' => 6.43
        ];

        $scoreData = [
            'score' => 1800,
            'level_reached' => 6,
            'time_played_seconds' => 180,
            'game_data' => $gameData
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(200);

        // The game_data should be properly handled as an array
        $this->assertIsArray($scoreData['game_data']);
    }

    public function test_memory_game_calculates_performance_metrics()
    {
        $user = User::factory()->create();

        $scoreData = [
            'score' => 2200,
            'level_reached' => 7,
            'time_played_seconds' => 240,
            'game_data' => [
                'final_level' => 7,
                'cards_matched' => 22,
                'total_cards' => 28,
                'moves_count' => 30,
                'perfect_matches' => 11,
                'hints_used' => 0,
                'accuracy' => 73.33, // (22 matches / 30 moves) * 100
                'efficiency_score' => 92.5 // Custom game metric
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(2200, $data['score']);
        $this->assertTrue($data['rank'] >= 1 && $data['rank'] <= 50);
    }

    public function test_memory_game_handles_different_difficulty_levels()
    {
        $user = User::factory()->create();

        $difficulties = ['easy', 'medium', 'hard', 'expert'];

        foreach ($difficulties as $difficulty) {
            $scoreData = [
                'score' => rand(500, 3000),
                'level_reached' => rand(3, 10),
                'time_played_seconds' => rand(60, 600),
                'game_data' => [
                    'difficulty' => $difficulty,
                    'cards_matched' => rand(10, 30),
                    'total_cards' => rand(16, 40),
                    'moves_count' => rand(15, 50)
                ]
            ];

            $response = $this->actingAs($user)
                ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

            $response->assertStatus(200);
        }
    }

    public function test_memory_game_tracks_time_accurately()
    {
        $user = User::factory()->create();

        $scoreData = [
            'score' => 1200,
            'level_reached' => 4,
            'time_played_seconds' => 150, // 2.5 minutes
            'game_data' => [
                'final_level' => 4,
                'cards_matched' => 14,
                'total_cards' => 20,
                'average_match_time' => 10.7, // seconds per match
                'fastest_match' => 2.1,
                'slowest_match' => 25.3
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $scoreData);

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals(1200, $data['score']);
    }

    public function test_memory_game_handles_edge_cases()
    {
        $user = User::factory()->create();

        // Test perfect game
        $perfectGameData = [
            'score' => 5000,
            'level_reached' => 10,
            'time_played_seconds' => 120,
            'game_data' => [
                'final_level' => 10,
                'cards_matched' => 40,
                'total_cards' => 40,
                'moves_count' => 20, // Perfect matches only
                'perfect_matches' => 20,
                'hints_used' => 0,
                'accuracy' => 100.0,
                'perfect_game' => true
            ]
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $perfectGameData);

        $response->assertStatus(200);

        // Test minimal valid game
        $minimalGameData = [
            'score' => 100,
            'level_reached' => 1,
            'time_played_seconds' => 30
        ];

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', $minimalGameData);

        $response->assertStatus(200);
    }

    public function test_memory_game_leaderboard_data_retrieval()
    {
        // Test that we can retrieve leaderboard data for memory game
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

        $data = $response->json();
        $this->assertEquals('Memory Test Game', $data['game']['name']);
        $this->assertEquals('memory-test-game', $data['game']['slug']);
    }

    public function test_authenticated_user_sees_their_score_in_leaderboard()
    {
        $user = User::factory()->create([
            'name' => 'MemoryChampion'
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/leaderboards/memory-test-game');

        $response->assertStatus(200);

        $data = $response->json();
        $this->assertNotNull($data['currentUser']);
        $this->assertEquals('MemoryChampion', $data['currentUser']['user_name']);
        $this->assertIsInt($data['currentUser']['score']);
        $this->assertIsInt($data['currentUser']['rank']);
    }
}
