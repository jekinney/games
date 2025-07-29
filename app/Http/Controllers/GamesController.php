<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GamesController extends Controller
{
    /**
     * Display the games page
     */
    public function index(): Response
    {
        $games = Game::getPublicGames();

        return Inertia::render('Games', [
            'title' => 'Games Hub',
            'games' => $games
        ]);
    }

    /**
     * Serve individual game by slug
     */
    public function show(string $slug)
    {
        $game = Game::where('slug', $slug)->firstOrFail();
        
        // If the game has a game_url, serve it as a proper view with Laravel context
        if ($game->game_url) {
            $filePath = public_path($game->game_url);
            
            if (file_exists($filePath) && str_ends_with($filePath, '.html')) {
                // Read the HTML content
                $content = file_get_contents($filePath);
                
                // Replace Laravel placeholders with actual values
                $content = str_replace('{{ csrf_token() }}', csrf_token(), $content);
                
                return response($content)->header('Content-Type', 'text/html');
            }
        }
        
        abort(404);
    }
}
