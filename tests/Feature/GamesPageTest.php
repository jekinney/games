<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class GamesPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_games_page_can_be_rendered()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
        );
    }

    public function test_games_page_is_games_component()
    {
        $response = $this->get('/games');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
        );
    }

    public function test_games_page_accessible_without_authentication()
    {
        $response = $this->get('/games');
        $response->assertStatus(200);
    }

    public function test_games_page_accessible_with_authentication()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/games');
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
                ->where('auth.user.id', $user->id)
        );
    }

    public function test_games_page_has_correct_title()
    {
        $response = $this->get('/games');
        
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->where('title', 'Games Hub')
        );
    }

    public function test_games_page_navigation_structure()
    {
        $response = $this->get('/games');
        
        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Games')
                ->has('auth')
        );
    }

    public function test_games_route_named_correctly()
    {
        $response = $this->get(route('games'));
        $response->assertStatus(200);
    }

    public function test_home_page_is_welcome_component()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertInertia(fn (Assert $page) => 
            $page->component('Welcome')
                ->has('auth')
        );
    }
}
