<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GamesController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index()
    {
        $games = Game::orderBy('created_at', 'desc')->paginate(15);
        
        return Inertia::render('Admin/Games/Index', [
            'games' => $games
        ]);
    }

    /**
     * Show the form for creating a new game.
     */
    public function create()
    {
        return Inertia::render('Admin/Games/Create');
    }

    /**
     * Store a newly created game in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'how_to_play' => 'nullable|string',
            'category' => 'required|string|in:puzzle,arcade,strategy,action,adventure,simulation,sports,racing,educational,casual',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'min_players' => 'required|integer|min:1',
            'max_players' => 'required|integer|min:1',
            'estimated_play_time' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'tags' => 'nullable|array',
            'developer_notes' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['game_file_url'] = '/games/' . $validated['slug'] . '.js';

        Game::create($validated);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game created successfully!');
    }

    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        return Inertia::render('Admin/Games/Show', [
            'game' => $game
        ]);
    }

    /**
     * Show the form for editing the specified game.
     */
    public function edit(Game $game)
    {
        return Inertia::render('Admin/Games/Edit', [
            'game' => $game
        ]);
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'how_to_play' => 'nullable|string',
            'category' => 'required|string|in:puzzle,arcade,strategy,action,adventure,simulation,sports,racing,educational,casual',
            'difficulty' => 'required|string|in:easy,medium,hard',
            'min_players' => 'required|integer|min:1',
            'max_players' => 'required|integer|min:1',
            'estimated_play_time' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'tags' => 'nullable|array',
            'developer_notes' => 'nullable|string',
        ]);

        $game->update($validated);

        return redirect()->route('admin.games.index')
            ->with('success', 'Game updated successfully!');
    }

    /**
     * Remove the specified game from storage.
     */
    public function destroy(Game $game)
    {
        $game->delete();

        return redirect()->route('admin.games.index')
            ->with('success', 'Game deleted successfully!');
    }
}
