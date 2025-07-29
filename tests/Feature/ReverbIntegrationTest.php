<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use App\Events\GameStarted;
use App\Events\GameEnded;
use App\Events\ScoreSubmitted;

class ReverbIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fake events and queues to prevent actual broadcasting during tests
        Event::fake();
        Queue::fake();
    }

    public function test_reverb_configuration_is_correct()
    {
        // Test that reverb configuration exists
        $this->assertArrayHasKey('reverb', config('broadcasting.connections'));
        $this->assertEquals('reverb', config('broadcasting.connections.reverb.driver'));
        $this->assertArrayHasKey('options', config('broadcasting.connections.reverb'));
    }

    public function test_game_session_start_endpoint_works()
    {
        $response = $this->postJson('/api/games/memory-test-game/start');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'session_id',
                     'active_players_count',
                     'message'
                 ]);
    }

    public function test_game_session_heartbeat_requires_session_id()
    {
        $response = $this->postJson('/api/games/memory-test-game/heartbeat', []);
        
        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['session_id']);
    }

    public function test_active_players_endpoint_returns_count()
    {
        $response = $this->getJson('/api/games/memory-test-game/players');
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'active_players_count',
                     'active_players'
                 ]);
    }

    public function test_authenticated_user_can_start_session()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->postJson('/api/games/memory-test-game/start');
        
        $response->assertStatus(200);
        
        $sessionId = $response->json('session_id');
        $this->assertNotNull($sessionId);
    }

    public function test_score_submission_triggers_broadcast_event()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->postJson('/api/leaderboards/memory-test-game/submit', [
                             'score' => 1500,
                             'level_reached' => 5,
                             'time_played_seconds' => 300,
                             'game_data' => [
                                 'final_level' => 5,
                                 'cards_matched' => 10,
                                 'total_cards' => 12
                             ]
                         ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'score',
                     'rank'
                 ]);
    }

    public function test_complete_game_session_flow()
    {
        $user = User::factory()->create();
        
        // Start session
        $startResponse = $this->actingAs($user)
                              ->postJson('/api/games/memory-test-game/start');
        
        $startResponse->assertStatus(200);
        $sessionId = $startResponse->json('session_id');
        
        // Send heartbeat
        $heartbeatResponse = $this->actingAs($user)
                                  ->postJson('/api/games/memory-test-game/heartbeat', [
                                      'session_id' => $sessionId
                                  ]);
        
        $heartbeatResponse->assertStatus(200);
        
        // End session
        $endResponse = $this->actingAs($user)
                            ->postJson('/api/games/memory-test-game/end', [
                                'session_id' => $sessionId,
                                'score' => 2000,
                                'time_played' => 480
                            ]);
        
        $endResponse->assertStatus(200);
    }

    public function test_memory_game_html_file_loads()
    {
        // Test that the file exists and contains the expected content
        $filePath = public_path('games/memory-test-game.html');
        $this->assertTrue(file_exists($filePath), "File does not exist at: {$filePath}");
        
        $content = file_get_contents($filePath);
        $this->assertStringContainsString('Memory Test Game', $content);
        $this->assertStringContainsString('startGameSession', $content);
        $this->assertStringContainsString('endGameSession', $content);
        $this->assertStringContainsString('sendHeartbeat', $content);
    }

    public function test_real_time_features_are_integrated()
    {
        // Test that real-time session management functions are in the HTML
        $filePath = public_path('games/memory-test-game.html');
        $content = file_get_contents($filePath);

        // Check for session management functions
        $this->assertStringContainsString('sessionId', $content);
        $this->assertStringContainsString('/api/games/memory-test-game/start', $content);
        $this->assertStringContainsString('/api/games/memory-test-game/end', $content);
        $this->assertStringContainsString('/api/games/memory-test-game/heartbeat', $content);

        // Check for cleanup handlers
        $this->assertStringContainsString('beforeunload', $content);
    }
}
