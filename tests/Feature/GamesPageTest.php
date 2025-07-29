<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;

class GamesPageTest extends TestCase
{
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
