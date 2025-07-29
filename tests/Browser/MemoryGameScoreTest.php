<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class MemoryGameScoreTest extends DuskTestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure we have a memory test game in the database
        Game::factory()->create([
            'name' => 'Memory Test Game',
            'slug' => 'memory-test-game',
            'description' => 'Test your memory skills with this challenging card matching game.',
            'category' => 'puzzle',
            'difficulty' => 'easy',
            'is_active' => true,
            'image_url' => '/images/games/memory-test.svg',
            'thumbnail_url' => '/images/games/memory-test.svg',
        ]);
    }

    /**
     * Test score validation and sanitization
     */
    public function test_score_validation()
    {
        $user = User::factory()->create();
        $game = Game::where('slug', 'memory-test-game')->first();

        // Test with invalid score data (should fail validation)
        $response = $this->actingAs($user)->postJson('/api/leaderboards/memory-test-game/submit', [
            'score' => -100, // Invalid negative score
            'time_played_seconds' => 60,
            'level_reached' => 1,
            'game_data' => ['total_cards' => 12]
        ]);

        $response->assertStatus(422); // Validation error

        // Test with valid score data
        $response = $this->actingAs($user)->postJson('/api/leaderboards/memory-test-game/submit', [
            'score' => 1200,
            'time_played_seconds' => 120,
            'level_reached' => 5,
            'game_data' => ['total_cards' => 12]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Score submitted successfully!',
            'score' => 1200
        ]);
        
        // Verify score was saved to database
        $this->assertDatabaseHas('game_scores', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score' => 1200,
        ]);
    }

    /**
     * Test multiple scores and ranking
     */
    public function test_score_ranking_system()
    {
        $users = User::factory()->count(3)->create();
        $game = Game::where('slug', 'memory-test-game')->first();

        // Create scores for multiple users
        GameScore::factory()->create([
            'user_id' => $users[0]->id,
            'game_id' => $game->id,
            'score' => 1000,
        ]);

        GameScore::factory()->create([
            'user_id' => $users[1]->id,
            'game_id' => $game->id,
            'score' => 2000,
        ]);

        GameScore::factory()->create([
            'user_id' => $users[2]->id,
            'game_id' => $game->id,
            'score' => 1500,
        ]);

        // Test leaderboard API endpoint
        $response = $this->get('/api/leaderboards/memory-test-game');
        $response->assertStatus(200);

        $leaderboard = $response->json('topTen');
        
        // Verify scores are ordered correctly (highest first)
        $this->assertEquals(2000, $leaderboard[0]['score']);
        $this->assertEquals(1500, $leaderboard[1]['score']);
        $this->assertEquals(1000, $leaderboard[2]['score']);
    }
}
