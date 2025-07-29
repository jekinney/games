<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_api_routes_are_registered()
    {
        // Test GET route exists
        $response = $this->getJson('/api/leaderboards/test-game');
        $response->assertStatus(200); // Should not be 404

        // Test POST route exists (should fail auth, not 404)
        $response = $this->postJson('/api/leaderboards/test-game/submit', ['score' => 1000]);
        $response->assertStatus(401); // Unauthenticated, not 404
    }

    public function test_leaderboard_get_route_accepts_various_slugs()
    {
        $slugs = [
            'simple-game',
            'game-with-numbers-123',
            'very-long-game-name-with-many-words',
            'a',
            'game-2024-edition'
        ];

        foreach ($slugs as $slug) {
            $response = $this->getJson("/api/leaderboards/{$slug}");
            $response->assertStatus(200);
            
            $data = $response->json();
            $this->assertEquals($slug, $data['game']['slug']);
        }
    }

    public function test_leaderboard_submit_route_requires_authentication()
    {
        $response = $this->postJson('/api/leaderboards/test-game/submit', [
            'score' => 1500
        ]);

        $response->assertStatus(401);
    }

    public function test_leaderboard_submit_route_works_with_authentication()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/leaderboards/test-game/submit', [
                'score' => 1500
            ]);

        $response->assertStatus(200);
    }

    public function test_api_routes_return_json_responses()
    {
        $response = $this->getJson('/api/leaderboards/test-game');
        
        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/json');
    }

    public function test_api_routes_handle_cors()
    {
        $response = $this->getJson('/api/leaderboards/test-game');
        
        $response->assertStatus(200)
            ->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function test_leaderboard_routes_with_special_characters()
    {
        // Test with URL-encoded characters (should work)
        $response = $this->getJson('/api/leaderboards/test%20game');
        $response->assertStatus(200);
        
        // Test with hyphens (normal case)
        $response = $this->getJson('/api/leaderboards/test-game');
        $response->assertStatus(200);
    }

    public function test_invalid_http_methods_return_405()
    {
        // Test invalid method on GET route
        $response = $this->putJson('/api/leaderboards/test-game');
        $response->assertStatus(405);

        // Test invalid method on POST route
        $response = $this->getJson('/api/leaderboards/test-game/submit');
        $response->assertStatus(405);
    }

    public function test_api_prefix_is_required()
    {
        // Test that routes without /api prefix don't work
        $response = $this->getJson('/leaderboards/test-game');
        $response->assertStatus(404);
    }

    public function test_leaderboard_routes_consistent_response_structure()
    {
        $games = ['game-1', 'game-2', 'memory-test-game', 'puzzle-game'];

        foreach ($games as $game) {
            $response = $this->getJson("/api/leaderboards/{$game}");
            
            $response->assertStatus(200)
                ->assertJsonStructure([
                    'topTen',
                    'currentUser',
                    'game' => [
                        'name',
                        'slug'
                    ]
                ]);
        }
    }
}
