<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/games', [GamesController::class, 'index'])->name('games');

// Leaderboard routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/leaderboards', [LeaderboardController::class, 'index'])->name('leaderboards');
    Route::get('/leaderboards/{game:slug}', [LeaderboardController::class, 'show'])->name('leaderboards.game');
    Route::post('/leaderboards/{game:slug}/submit', [LeaderboardController::class, 'submitScore'])->name('leaderboards.submit');
});

Route::get('profile', [ProfileController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('profile');

// Admin routes (protected by role middleware)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    
    // Game management routes
    Route::get('/games', [AdminController::class, 'games'])->name('games');
    Route::get('/games/create', [AdminController::class, 'createGame'])->name('games.create');
    Route::post('/games', [AdminController::class, 'storeGame'])->name('games.store');
    Route::get('/games/{game}/edit', [AdminController::class, 'editGame'])->name('games.edit');
    Route::put('/games/{game}', [AdminController::class, 'updateGame'])->name('games.update');
    Route::delete('/games/{game}', [AdminController::class, 'destroyGame'])->name('games.destroy');
});

// Keep dashboard for future admin panel use
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
