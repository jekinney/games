<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameScore;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaderboardController extends Controller
{
    /**
     * Display the main leaderboard page
     */
    public function index(): Response
    {
        $games = Game::active()
            ->select('id', 'name', 'slug', 'category', 'thumbnail_url')
            ->orderBy('name')
            ->get();

        return Inertia::render('Leaderboards/Index', [
            'games' => $games
        ]);
    }

    /**
     * Display leaderboard for a specific game
     */
    public function show(Game $game, Request $request): Response
    {
        $timeframe = $request->get('timeframe', 'all');
        $limit = $request->get('limit', 50);

        // Validate timeframe
        $validTimeframes = ['all', '30d', '60d', '90d', '1y'];
        if (!in_array($timeframe, $validTimeframes)) {
            $timeframe = 'all';
        }

        // Get leaderboard data
        $leaderboard = GameScore::getLeaderboard($game->id, $timeframe, $limit);

        // Add ranking to the results
        $leaderboard = $leaderboard->map(function ($score, $index) {
            $score->rank = $index + 1;
            return $score;
        });

        // Get current user's rank and score if authenticated
        $userData = null;
        if (auth()->check()) {
            $userBestScore = GameScore::getUserBestScore(auth()->id(), $game->id);
            $userRank = GameScore::getUserRank(auth()->id(), $game->id, $timeframe);
            
            if ($userBestScore) {
                $userData = [
                    'score' => $userBestScore->score,
                    'rank' => $userRank,
                    'level_reached' => $userBestScore->level_reached,
                    'time_played' => $userBestScore->time_played_seconds,
                ];
            }
        }

        return Inertia::render('Leaderboards/GameLeaderboard', [
            'game' => $game,
            'leaderboard' => $leaderboard,
            'timeframe' => $timeframe,
            'userData' => $userData,
            'timeframes' => [
                'all' => 'All Time',
                '30d' => 'Last 30 Days',
                '60d' => 'Last 60 Days',
                '90d' => 'Last 90 Days',
                '1y' => 'Last Year'
            ]
        ]);
    }

    /**
     * Submit a score for a game
     */
    public function submitScore(Request $request, Game $game)
    {
        $request->validate([
            'score' => 'required|integer|min:0',
            'level_reached' => 'nullable|integer|min:1',
            'time_played_seconds' => 'nullable|integer|min:0',
            'game_data' => 'nullable|array',
            'completed_at' => 'nullable|date'
        ]);

        $gameScore = GameScore::create([
            'user_id' => auth()->id(),
            'game_id' => $game->id,
            'score' => $request->score,
            'level_reached' => $request->level_reached,
            'time_played_seconds' => $request->time_played_seconds,
            'game_data' => $request->game_data,
            'completed_at' => $request->completed_at ?? now(),
        ]);

        // Increment play count
        $game->incrementPlayCount();

        return response()->json([
            'message' => 'Score submitted successfully!',
            'score' => $gameScore,
            'rank' => GameScore::getUserRank(auth()->id(), $game->id)
        ]);
    }

    /**
     * Get leaderboard data via API
     */
    public function getLeaderboardData(Game $game, Request $request)
    {
        $timeframe = $request->get('timeframe', 'all');
        $limit = $request->get('limit', 10);

        $leaderboard = GameScore::getLeaderboard($game->id, $timeframe, $limit);
        
        return response()->json([
            'leaderboard' => $leaderboard,
            'game' => $game->only(['id', 'name', 'slug'])
        ]);
    }

    /**
     * Get global leaderboard across all games
     */
    public function globalLeaderboard(Request $request)
    {
        $timeframe = $request->get('timeframe', 'all');
        
        $query = GameScore::with(['user', 'game'])
            ->select('user_id', \DB::raw('SUM(score) as total_score'), \DB::raw('COUNT(*) as games_played'))
            ->groupBy('user_id')
            ->orderBy('total_score', 'desc');

        // Apply timeframe filter
        switch ($timeframe) {
            case '30d':
                $query->withinTimeframe(30);
                break;
            case '60d':
                $query->withinTimeframe(60);
                break;
            case '90d':
                $query->withinTimeframe(90);
                break;
            case '1y':
                $query->withinTimeframe(365);
                break;
        }

        $globalLeaderboard = $query->limit(50)->get();

        return response()->json([
            'leaderboard' => $globalLeaderboard,
            'timeframe' => $timeframe
        ]);
    }
}
