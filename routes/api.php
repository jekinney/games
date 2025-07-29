<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Game;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\GameSessionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Games API routes
Route::get('/games', function () {
    return Game::where('is_active', true)
        ->orderBy('is_featured', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
});

Route::post('/games/{game}/play', function (Game $game) {
    $game->increment('play_count');
    return response()->json(['success' => true]);
});

// Game Session API routes (for real-time player tracking)
Route::prefix('games/{gameSlug}')->group(function () {
    Route::post('/start', [GameSessionController::class, 'startGame']);
    Route::post('/end', [GameSessionController::class, 'endGame']);
    Route::post('/heartbeat', [GameSessionController::class, 'heartbeat']);
    Route::get('/players', [GameSessionController::class, 'getActivePlayers']);
});

// Leaderboard API routes
Route::get('/leaderboards/{gameSlug}', [LeaderboardController::class, 'getLeaderboardData']);
Route::middleware('auth')->group(function () {
    Route::post('/leaderboards/{gameSlug}/submit', [LeaderboardController::class, 'submitScore']);
});
