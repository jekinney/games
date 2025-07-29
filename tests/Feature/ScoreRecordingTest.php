<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ScoreRecordingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events to prevent broadcasting during tests
        Event::fake();
        
        // Create a test game
        Game::factory()->create([
            'name' => 'Memory Test Game',
            'slug' => 'memory-test-game',
            'description' => 'Test your memory skills',
            'category' => 'puzzle',
            'difficulty' => 'easy',
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_submit_score()
    {
        $user = User::factory()->create();
        $game = Game::where('slug', 'memory-test-game')->first();

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => 1500,
                'level_reached' => 3,
                'time_played_seconds' => 120,
                'game_data' => ['total_cards' => 12]
            ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'message' => 'Score submitted successfully!',
                     'score' => 1500
                 ]);

        // Verify score was saved to database
        $this->assertDatabaseHas('game_scores', [
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score' => 1500,
            'level_reached' => 3,
        ]);
    }

    public function test_negative_scores_are_rejected()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/memory-test-game/submit', [
                'score' => -100,
                'level_reached' => 1,
            ]);

        $response->assertStatus(422);
    }

    public function test_leaderboard_returns_correct_ranking()
    {
        $users = User::factory()->count(3)->create();
        $game = Game::where('slug', 'memory-test-game')->first();

        // Create test scores
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

        $response = $this->get('/api/leaderboards/memory-test-game');
        
        $response->assertStatus(200);
        $leaderboard = $response->json('topTen');
        
        // Verify scores are ordered correctly (highest first)
        $this->assertEquals(2000, $leaderboard[0]['score']);
        $this->assertEquals(1500, $leaderboard[1]['score']);
        $this->assertEquals(1000, $leaderboard[2]['score']);
    }
}
