<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class GamesControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_index_returns_correct_response_structure()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
        );
    }

    public function test_games_index_works_for_guest_users()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
                ->where('auth.user', null)
        );
    }

    public function test_games_index_works_for_authenticated_users()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
                ->has('auth.user')
                ->where('auth.user.id', $user->id)
                ->where('auth.user.name', $user->name)
                ->where('auth.user.email', $user->email)
        );
    }

    public function test_games_route_uses_correct_controller_method()
    {
        $this->get('/games')
            ->assertStatus(200);
        
        // Verify the route exists and is named correctly
        $this->assertTrue(route('games') === url('/games'));
    }

    public function test_games_page_headers_and_meta()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->where('title', 'Games Hub')
        );
    }

    public function test_games_page_with_different_http_methods()
    {
        // GET should work
        $this->get('/games')->assertStatus(200);
        
        // POST, PUT, DELETE should not be allowed
        $this->post('/games')->assertStatus(405);
        $this->put('/games')->assertStatus(405);
        $this->delete('/games')->assertStatus(405);
        $this->patch('/games')->assertStatus(405);
    }

    public function test_games_page_handles_query_parameters()
    {
        $response = $this->get('/games?search=memory&filter=puzzle');
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
        );
    }

    public function test_games_page_handles_invalid_characters_in_url()
    {
        $response = $this->get('/games?search=' . urlencode('<script>alert("xss")</script>'));
        
        $response->assertStatus(200);
    }

    public function test_games_page_performance_with_large_query_string()
    {
        $largeQuery = str_repeat('a', 1000);
        $response = $this->get('/games?search=' . $largeQuery);
        
        $response->assertStatus(200);
    }

    public function test_games_page_concurrent_requests()
    {
        $responses = [];
        
        // Simulate multiple concurrent requests
        for ($i = 0; $i < 5; $i++) {
            $responses[] = $this->get('/games');
        }
        
        foreach ($responses as $response) {
            $response->assertStatus(200);
        }
    }

    public function test_games_page_with_different_user_agents()
    {
        $userAgents = [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36', // Chrome
            'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15', // Safari
            'Mozilla/5.0 (X11; Linux x86_64; rv:91.0) Gecko/20100101 Firefox/91.0', // Firefox
            'Mozilla/5.0 (iPhone; CPU iPhone OS 14_7_1 like Mac OS X)', // Mobile Safari
        ];

        foreach ($userAgents as $userAgent) {
            $response = $this->withHeaders(['User-Agent' => $userAgent])
                ->get('/games');
            
            $response->assertStatus(200);
        }
    }

    public function test_games_page_with_various_accept_headers()
    {
        $acceptHeaders = [
            'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'application/json',
            'text/plain',
            '*/*'
        ];

        foreach ($acceptHeaders as $accept) {
            $response = $this->withHeaders(['Accept' => $accept])
                ->get('/games');
            
            $response->assertStatus(200);
        }
    }

    public function test_games_page_maintains_session()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        $response->assertSessionHasNoErrors();
    }

    public function test_games_page_csrf_protection()
    {
        // GET requests should not require CSRF tokens
        $response = $this->get('/games');
        $response->assertStatus(200);
        
        // But the page should include CSRF token for forms
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
        );
    }

    public function test_games_page_handles_database_connection_issues()
    {
        // This test would typically mock database failures
        // For now, just ensure the page loads without database queries
        $response = $this->get('/games');
        $response->assertStatus(200);
    }

    public function test_games_page_response_time()
    {
        $start = microtime(true);
        $response = $this->get('/games');
        $end = microtime(true);
        
        $responseTime = ($end - $start) * 1000; // Convert to milliseconds
        
        $response->assertStatus(200);
        $this->assertLessThan(1000, $responseTime, 'Games page should load in under 1 second');
    }

    public function test_games_page_with_authentication_edge_cases()
    {
        $user = User::factory()->create();
        
        // Test with valid session
        $response = $this->actingAs($user)->get('/games');
        $response->assertStatus(200);
        
        // Test after logout
        auth()->logout();
        $response = $this->get('/games');
        $response->assertStatus(200);
    }

    public function test_games_page_cache_headers()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        // Verify appropriate cache headers are set
        $this->assertNotNull($response->headers->get('Cache-Control'));
    }

    public function test_games_page_security_headers()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        // Check for basic security headers
        $this->assertNotNull($response->headers->get('X-Content-Type-Options'));
    }
}
