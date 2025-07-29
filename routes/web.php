<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\GamesController;
use App\Http\Controllers\Admin\GamesController as AdminGamesController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/games', [GamesController::class, 'index'])->name('games');

// Route to serve individual games by slug through controller
Route::get('/games/{slug}', [GamesController::class, 'show'])->name('games.show');

Route::get('profile', [ProfileController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('profile');

// Admin routes (protected by role middleware)
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    
    // Game management routes with resource controller
    Route::resource('games', AdminGamesController::class);
});

// Keep dashboard for future admin panel use
Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
