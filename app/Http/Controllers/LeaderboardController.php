<?php

namespace App\Http\Controllers;

use App\Events\ScoreSubmitted;
use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * Get leaderboard data for API (modal usage)
     */
    public function getLeaderboardData($gameSlug, Request $request)
    {
        // Find game by slug
        $game = Game::where('slug', $gameSlug)->first();
        
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }
        
        // Get top 10 scores for this game
        $topTen = GameScore::where('game_id', $game->id)
            ->with('user') // Load the user relationship
            ->orderBy('score', 'desc')
            ->orderBy('created_at', 'asc') // Earlier submission wins ties
            ->limit(10)
            ->get()
            ->map(function ($score) {
                return [
                    'id' => $score->id,
                    'user_name' => $score->user->name ?? 'Anonymous',
                    'score' => $score->score,
                    'level_reached' => $score->level_reached,
                    'created_at' => $score->created_at->toISOString()
                ];
            })
            ->toArray();
        
        $currentUser = null;
        
        // Add current user if authenticated
        if (auth()->check()) {
            $userBestScore = GameScore::where('game_id', $game->id)
                ->where('user_id', auth()->id())
                ->with('user')
                ->orderBy('score', 'desc')
                ->first();
                
            if ($userBestScore) {
                // Calculate rank for user's best score
                $rank = GameScore::where('game_id', $game->id)
                    ->where(function ($query) use ($userBestScore) {
                        $query->where('score', '>', $userBestScore->score)
                            ->orWhere(function ($q) use ($userBestScore) {
                                $q->where('score', $userBestScore->score)
                                  ->where('created_at', '<', $userBestScore->created_at);
                            });
                    })
                    ->count() + 1;
                    
                $currentUser = [
                    'id' => $userBestScore->id,
                    'user_name' => $userBestScore->user->name ?? 'Anonymous',
                    'score' => $userBestScore->score,
                    'level_reached' => $userBestScore->level_reached,
                    'rank' => $rank,
                    'created_at' => $userBestScore->created_at->toISOString(),
                    'inTopTen' => $rank <= 10
                ];
            }
        }

        return response()->json([
            'topTen' => $topTen,
            'currentUser' => $currentUser,
            'game' => [
                'name' => $game->name,
                'slug' => $gameSlug
            ]
        ]);
    }    /**
     * Submit a score for a game
     */
    public function submitScore(Request $request, $gameSlug)
    {
        $validated = $request->validate([
            'score' => 'required|integer|min:1', // Scores must be positive
            'level_reached' => 'nullable|integer|min:1',
            'time_played_seconds' => 'nullable|integer|min:0',
            'game_data' => 'nullable|array',
            'completed_at' => 'nullable|date'
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        // Find the game by slug
        $game = Game::where('slug', $gameSlug)->first();
        if (!$game) {
            return response()->json(['error' => 'Game not found'], 404);
        }

        // Create GameScore record
        $gameScore = GameScore::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'score' => $validated['score'],
            'level_reached' => $validated['level_reached'] ?? null,
            'time_played_seconds' => $validated['time_played_seconds'] ?? null,
            'game_data' => $validated['game_data'] ?? null,
            'completed_at' => $validated['completed_at'] ?? now(),
        ]);

        // Calculate rank by counting scores higher than this one
        $rank = GameScore::where('game_id', $game->id)
            ->where('score', '>', $validated['score'])
            ->count() + 1;

        // Dispatch real-time event for leaderboard updates
        ScoreSubmitted::dispatch(
            $gameSlug,
            $user->id,
            $user->name,
            $validated['score'],
            $rank,
            $validated['game_data'] ?? null
        );
        
        return response()->json([
            'message' => 'Score submitted successfully!',
            'score' => $validated['score'],
            'rank' => $rank,
            'game_score_id' => $gameScore->id
        ]);
    }
}
