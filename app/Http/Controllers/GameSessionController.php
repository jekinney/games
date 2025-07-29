<?php

namespace App\Http\Controllers;

use App\Events\GameStarted;
use App\Events\GameEnded;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GameSessionController extends Controller
{
    /**
     * Start a game session and track player count
     */
    public function startGame(Request $request, $gameSlug)
    {
        $validated = $request->validate([
            'session_id' => 'nullable|string',
        ]);

        $user = auth()->user();
        $sessionId = $validated['session_id'] ?? Str::uuid()->toString();
        
        // Track active players using cache
        $cacheKey = "game.{$gameSlug}.active_players";
        $activePlayers = Cache::get($cacheKey, []);
        
        // Add or update player in active list
        $playerKey = $user ? "user_{$user->id}" : "guest_{$sessionId}";
        $activePlayers[$playerKey] = [
            'user_id' => $user?->id,
            'user_name' => $user?->name ?? 'Guest',
            'session_id' => $sessionId,
            'started_at' => now()->toISOString(),
        ];
        
        // Cache for 1 hour, will be refreshed by heartbeat
        Cache::put($cacheKey, $activePlayers, 3600);
        
        // Dispatch real-time event
        GameStarted::dispatch(
            $gameSlug,
            $user?->id,
            $user?->name ?? 'Guest',
            $sessionId
        );
        
        return response()->json([
            'session_id' => $sessionId,
            'active_players_count' => count($activePlayers),
            'message' => 'Game session started'
        ]);
    }

    /**
     * End a game session
     */
    public function endGame(Request $request, $gameSlug)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'score' => 'nullable|integer',
            'time_played' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $sessionId = $validated['session_id'];
        
        // Remove player from active list
        $cacheKey = "game.{$gameSlug}.active_players";
        $activePlayers = Cache::get($cacheKey, []);
        
        $playerKey = $user ? "user_{$user->id}" : "guest_{$sessionId}";
        unset($activePlayers[$playerKey]);
        
        Cache::put($cacheKey, $activePlayers, 3600);
        
        // Dispatch real-time event
        GameEnded::dispatch(
            $gameSlug,
            $user?->id,
            $user?->name ?? 'Guest',
            $sessionId,
            $validated['score'] ?? null,
            $validated['time_played'] ?? null
        );
        
        return response()->json([
            'active_players_count' => count($activePlayers),
            'message' => 'Game session ended'
        ]);
    }

    /**
     * Heartbeat to keep session alive
     */
    public function heartbeat(Request $request, $gameSlug)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
        ]);

        $user = auth()->user();
        $sessionId = $validated['session_id'];
        
        $cacheKey = "game.{$gameSlug}.active_players";
        $activePlayers = Cache::get($cacheKey, []);
        
        $playerKey = $user ? "user_{$user->id}" : "guest_{$sessionId}";
        
        // Update last seen timestamp
        if (isset($activePlayers[$playerKey])) {
            $activePlayers[$playerKey]['last_seen'] = now()->toISOString();
            Cache::put($cacheKey, $activePlayers, 3600);
        }
        
        return response()->json([
            'active_players_count' => count($activePlayers),
            'status' => 'alive'
        ]);
    }

    /**
     * Get current active players for a game
     */
    public function getActivePlayers($gameSlug)
    {
        $cacheKey = "game.{$gameSlug}.active_players";
        $activePlayers = Cache::get($cacheKey, []);
        
        // Clean up stale sessions (older than 5 minutes)
        $cutoff = now()->subMinutes(5);
        $activePlayers = array_filter($activePlayers, function ($player) use ($cutoff) {
            $lastSeen = $player['last_seen'] ?? $player['started_at'];
            return now()->parse($lastSeen)->isAfter($cutoff);
        });
        
        Cache::put($cacheKey, $activePlayers, 3600);
        
        return response()->json([
            'active_players_count' => count($activePlayers),
            'active_players' => array_values($activePlayers)
        ]);
    }
}
