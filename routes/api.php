<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Game;
use App\Http\Controllers\LeaderboardController;

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

// Leaderboard API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/leaderboards/{game:slug}', [LeaderboardController::class, 'getLeaderboardData']);
    Route::post('/leaderboards/{game:slug}/submit', [LeaderboardController::class, 'submitScore']);
    Route::get('/leaderboards/global', [LeaderboardController::class, 'globalLeaderboard']);
});
