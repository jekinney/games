<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to admin dashboard.');
        }

        // Get admin dashboard data
        $stats = [
            'total_users' => User::count(),
            'verified_users' => User::whereNotNull('email_verified_at')->count(),
            'unverified_users' => User::whereNull('email_verified_at')->count(),
            'admin_users' => User::role(['super-admin', 'admin'])->count(),
            'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
        ];

        $recent_users = User::with('roles')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at->format('M j, Y'),
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'recent_users' => $recent_users,
        ]);
    }

    /**
     * Display the users management page.
     */
    public function users(): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to user management.');
        }

        $users = User::with('roles')
            ->latest()
            ->paginate(15)
            ->through(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at->format('M j, Y g:i A'),
                    'roles' => $user->roles->pluck('name')->toArray(),
                ];
            });

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Display the settings page.
     */
    public function settings(): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to admin settings.');
        }

        return Inertia::render('Admin/Settings', [
            'app_settings' => [
                'app_name' => config('app.name'),
                'app_url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
        ]);
    }

    /**
     * Display the games management page.
     */
    public function games(Request $request): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        $query = Game::query();

        // Search functionality
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $games = $query->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(function ($game) {
                return [
                    'id' => $game->id,
                    'name' => $game->name,
                    'slug' => $game->slug,
                    'description' => $game->description,
                    'category' => $game->category,
                    'difficulty' => $game->difficulty,
                    'is_active' => $game->is_active,
                    'is_featured' => $game->is_featured,
                    'play_count' => $game->play_count,
                    'average_rating' => $game->average_rating,
                    'created_at' => $game->created_at->format('M j, Y'),
                    'image_url' => $game->image_url,
                    'thumbnail_url' => $game->thumbnail_url,
                ];
            });

        return Inertia::render('Admin/Games/Index', [
            'games' => $games,
            'filters' => $request->only(['search', 'category', 'status']),
            'categories' => ['action', 'puzzle', 'strategy', 'arcade', 'simulation', 'sports'],
        ]);
    }

    /**
     * Show the form for creating a new game.
     */
    public function createGame(): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        return Inertia::render('Admin/Games/Create', [
            'categories' => ['action', 'puzzle', 'strategy', 'arcade', 'simulation', 'sports'],
            'difficulties' => ['easy', 'medium', 'hard'],
        ]);
    }

    /**
     * Store a newly created game in storage.
     */
    public function storeGame(Request $request)
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'how_to_play' => 'nullable|string',
            'category' => 'required|in:action,puzzle,strategy,arcade,simulation,sports',
            'difficulty' => 'required|in:easy,medium,hard',
            'min_players' => 'required|integer|min:1',
            'max_players' => 'required|integer|min:1',
            'estimated_play_time' => 'nullable|integer|min:1',
            'game_file_url' => 'required|url',
            'image_url' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'tags' => 'nullable|array',
            'controls' => 'nullable|array',
            'developer_notes' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        Game::create($validated);

        return redirect()->route('admin.games')->with('success', 'Game created successfully!');
    }

    /**
     * Show the form for editing the specified game.
     */
    public function editGame(Game $game): Response
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        return Inertia::render('Admin/Games/Edit', [
            'game' => [
                'id' => $game->id,
                'name' => $game->name,
                'slug' => $game->slug,
                'description' => $game->description,
                'how_to_play' => $game->how_to_play,
                'image_url' => $game->image_url,
                'thumbnail_url' => $game->thumbnail_url,
                'game_file_url' => $game->game_file_url,
                'category' => $game->category,
                'min_players' => $game->min_players,
                'max_players' => $game->max_players,
                'estimated_play_time' => $game->estimated_play_time,
                'difficulty' => $game->difficulty,
                'is_active' => $game->is_active,
                'is_featured' => $game->is_featured,
                'tags' => $game->tags,
                'controls' => $game->controls,
                'developer_notes' => $game->developer_notes,
            ],
            'categories' => ['action', 'puzzle', 'strategy', 'arcade', 'simulation', 'sports'],
            'difficulties' => ['easy', 'medium', 'hard'],
        ]);
    }

    /**
     * Update the specified game in storage.
     */
    public function updateGame(Request $request, Game $game)
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'how_to_play' => 'nullable|string',
            'category' => 'required|in:action,puzzle,strategy,arcade,simulation,sports',
            'difficulty' => 'required|in:easy,medium,hard',
            'min_players' => 'required|integer|min:1',
            'max_players' => 'required|integer|min:1',
            'estimated_play_time' => 'nullable|integer|min:1',
            'game_file_url' => 'required|url',
            'image_url' => 'nullable|url',
            'thumbnail_url' => 'nullable|url',
            'tags' => 'nullable|array',
            'controls' => 'nullable|array',
            'developer_notes' => 'nullable|string',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        $game->update($validated);

        return redirect()->route('admin.games')->with('success', 'Game updated successfully!');
    }

    /**
     * Remove the specified game from storage.
     */
    public function destroyGame(Game $game)
    {
        // Check if user has admin permissions
        if (!auth()->user()->hasAnyRole(['super-admin', 'admin'])) {
            abort(403, 'Unauthorized access to game management.');
        }

        $game->delete();

        return redirect()->route('admin.games')->with('success', 'Game deleted successfully!');
    }
}
