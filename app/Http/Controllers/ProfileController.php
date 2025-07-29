<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        return Inertia::render('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'stats' => [
                'member_since' => $user->created_at->diffForHumans(),
                'total_games_played' => 0, // Will be updated when we add games
                'achievements_earned' => 0, // Will be updated when we add achievements
                'community_points' => 0, // Will be updated when we add point system
            ]
        ]);
    }
}
