# Adding Games to the System - Developer Documentation

## Overview
This document provides comprehensive instructions for adding new games to the Laravel-based games platform. The system uses Laravel for the backend, Inertia.js for seamless page transitions, Vue.js for the frontend, and stores game data in a MySQL database.

## Table of Contents
1. [System Architecture](#system-architecture)
2. [Game Model Structure](#game-model-structure)
3. [Database Schema](#database-schema)
4. [Adding Games Through Admin Panel](#adding-games-through-admin-panel)
5. [Adding Games Programmatically](#adding-games-programmatically)
6. [Frontend Integration](#frontend-integration)
7. [File Management](#file-management)
8. [Testing Your Games](#testing-your-games)
9. [Troubleshooting](#troubleshooting)

## System Architecture

### Key Components
- **Backend**: Laravel 12 with Eloquent ORM
- **Frontend**: Vue.js 3 with Inertia.js
- **Database**: MySQL with migrations
- **File Storage**: Local/Cloud storage for game assets
- **Admin Panel**: Full CRUD operations for game management
- **API Routes**: RESTful endpoints for game data
- **Real-time Features**: Laravel Reverb for live updates
- **Broadcasting**: WebSocket connections for player tracking

### Real-time Features
- **Live Player Counts**: See how many players are currently playing each game
- **Real-time Leaderboards**: Instant updates when new scores are submitted
- **Player Join/Leave Events**: Track when players start or stop playing
- **Game Session Management**: Automatic session tracking with heartbeat

### File Structure
```
app/
├── Http/Controllers/
│   ├── AdminController.php      # Admin game management
│   └── GamesController.php      # Public games display
├── Models/
│   └── Game.php                 # Game model with relationships
database/
├── migrations/
│   └── create_games_table.php   # Database schema
└── seeders/
    └── DatabaseSeeder.php       # Sample data
resources/
├── js/pages/
│   ├── Games.vue               # Public games listing
│   └── Admin/Games/            # Admin game management
└── views/
routes/
├── web.php                     # Web routes
└── api.php                     # API endpoints
```

## Game Model Structure

### Game Model (`app/Models/Game.php`)
The Game model includes the following attributes:

#### Core Properties
- `name` (string, required): Game display name
- `slug` (string, unique): URL-friendly identifier (auto-generated)
- `description` (text, required): Game description for players
- `how_to_play` (text, optional): Instructions for playing

#### File URLs
- `game_file_url` (string, required): URL to JavaScript game file
- `image_url` (string, optional): Main game image
- `thumbnail_url` (string, optional): Thumbnail for game listing

#### Game Properties
- `category` (string): Game category (action, puzzle, strategy, etc.)
- `difficulty` (enum): easy, medium, hard
- `min_players` (integer): Minimum number of players
- `max_players` (integer): Maximum number of players
- `estimated_play_time` (integer): Estimated time in minutes

#### Metadata
- `is_active` (boolean): Whether game is publicly visible
- `is_featured` (boolean): Whether game appears in featured section
- `play_count` (integer): Number of times played
- `average_rating` (decimal): Average user rating
- `tags` (json): Array of tags for filtering
- `controls` (json): Array of control instructions
- `developer_notes` (text): Internal notes for developers

## Database Schema

### Migration File: `create_games_table.php`
```php
Schema::create('games', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description');
    $table->text('how_to_play')->nullable();
    $table->string('image_url')->nullable();
    $table->string('thumbnail_url')->nullable();
    $table->string('game_file_url');
    $table->string('category')->default('action');
    $table->integer('min_players')->default(1);
    $table->integer('max_players')->default(1);
    $table->integer('estimated_play_time')->nullable();
    $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium');
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->integer('play_count')->default(0);
    $table->decimal('average_rating', 3, 2)->default(0.00);
    $table->json('tags')->nullable();
    $table->json('controls')->nullable();
    $table->text('developer_notes')->nullable();
    $table->timestamps();
    
    // Indexes for performance
    $table->index(['is_active', 'is_featured']);
    $table->index('category');
    $table->index('difficulty');
});
```

## Adding Games Through Admin Panel

### Step 1: Access Admin Panel
1. Log in as an admin user (requires 'super-admin' or 'admin' role)
2. Navigate to `/admin/games`
3. Click "Create New Game"

### Step 2: Fill Out Game Information
**Required Fields:**
- Game Name
- Description
- JavaScript File URL (path to your game's main JS file)

**Optional Fields:**
- How to Play instructions
- Category selection
- Difficulty level
- Player count range
- Estimated play time
- Image URLs
- Tags (comma-separated)
- Control instructions
- Developer notes

### Step 3: Configure Game Settings
- **Active Status**: Toggle to make game visible to public
- **Featured Status**: Toggle to include in featured games section
- **Category**: Choose from predefined categories
- **Difficulty**: Select easy, medium, or hard

### Step 4: Save and Test
1. Click "Save Game"
2. Navigate to `/games` to see your game in the listing
3. Test the game functionality

## Adding Games Programmatically

### Method 1: Using Eloquent Model
```php
use App\Models\Game;

$game = Game::create([
    'name' => 'My Awesome Game',
    'description' => 'An exciting new puzzle game that challenges your mind.',
    'how_to_play' => 'Click on tiles to match colors and clear the board.',
    'game_file_url' => '/games/my-awesome-game.js',
    'category' => 'puzzle',
    'difficulty' => 'medium',
    'min_players' => 1,
    'max_players' => 1,
    'estimated_play_time' => 15,
    'is_active' => true,
    'is_featured' => false,
    'tags' => ['matching', 'colors', 'brain-teaser'],
    'controls' => ['mouse', 'touch'],
]);
```

### Method 2: Using Database Seeder
Create a seeder file:
```bash
php artisan make:seeder GamesSeeder
```

Add games in the seeder:
```php
// database/seeders/GamesSeeder.php
use App\Models\Game;

public function run()
{
    $games = [
        [
            'name' => 'Memory Master',
            'description' => 'Test your memory with this challenging card matching game.',
            'game_file_url' => '/games/memory-master.js',
            'category' => 'puzzle',
            'difficulty' => 'easy',
            'tags' => ['memory', 'cards', 'matching'],
        ],
        // Add more games...
    ];

    foreach ($games as $gameData) {
        Game::create($gameData);
    }
}
```

Run the seeder:
```bash
php artisan db:seed --class=GamesSeeder
```

### Method 3: Using Artisan Command
Create a custom command:
```bash
php artisan make:command AddGameCommand
```

Implement the command:
```php
// app/Console/Commands/AddGameCommand.php
public function handle()
{
    $name = $this->ask('Game name?');
    $description = $this->ask('Game description?');
    $gameFileUrl = $this->ask('JavaScript file URL?');
    $category = $this->choice('Category?', ['action', 'puzzle', 'strategy', 'arcade']);
    
    Game::create([
        'name' => $name,
        'description' => $description,
        'game_file_url' => $gameFileUrl,
        'category' => $category,
        'is_active' => true,
    ]);
    
    $this->info("Game '{$name}' created successfully!");
}
```

## Frontend Integration

### Game Display Component (`resources/js/pages/Games.vue`)
The Games.vue component handles:
- Fetching game data
- Filtering and search functionality
- Pagination
- Game card display

### Adding Game Data to Frontend
Games are currently hardcoded in the Vue component. To fetch from database:

1. **Update GamesController:**
```php
// app/Http/Controllers/GamesController.php
public function index(): Response
{
    $games = Game::active()
        ->orderBy('is_featured', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
        
    return Inertia::render('Games', [
        'games' => $games
    ]);
}
```

2. **Update Vue Component:**
```vue
<!-- resources/js/pages/Games.vue -->
<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    games: Array
});

const allGames = ref(props.games);
// Remove hardcoded games array
</script>
```

### Game Card Structure
Each game card displays:
- Game name and emoji/icon
- Description
- Category and difficulty badges
- Player count
- Play button
- Leaderboard link (if authenticated)

## File Management

### Game JavaScript Files
1. **Location**: Store in `public/games/` directory
2. **Naming**: Use kebab-case matching the slug
3. **Structure**: Each game should be self-contained

### Example Game File Structure:
```
public/
└── games/
    ├── memory-test.js
    ├── speed-typing.js
    ├── math-quiz.js
    └── assets/
        ├── images/
        ├── sounds/
        └── sprites/
```

### Image Assets
1. **Thumbnails**: 300x200px recommended
2. **Main Images**: 800x600px recommended
3. **Format**: PNG or JPG
4. **Storage**: `public/images/games/` or cloud storage

### Asset URLs
- Local: `/games/my-game.js`
- Cloud: `https://cdn.example.com/games/my-game.js`

## Testing Your Games

### Automated Testing
The system includes comprehensive tests in `tests/Feature/GamesPageNoResultsDisplayTest.php`:

```bash
# Run specific game tests
php artisan test tests/Feature/GamesPageNoResultsDisplayTest.php

# Run all tests
php artisan test

# Run tests with coverage
php artisan test --coverage
```

### Manual Testing Checklist
1. **Admin Panel**:
   - [ ] Can create new game
   - [ ] Can edit existing game
   - [ ] Can delete game
   - [ ] Form validation works
   - [ ] File uploads work

2. **Public Display**:
   - [ ] Game appears in listing
   - [ ] Filtering works correctly
   - [ ] Search functionality works
   - [ ] Pagination works
   - [ ] Game loads properly

3. **Game Functionality**:
   - [ ] JavaScript file loads
   - [ ] Game initializes correctly
   - [ ] Controls work as expected
   - [ ] Game can be completed
   - [ ] Score submission works (if applicable)

### Testing Commands
```bash
# Clear cache after adding games
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# Check routes
php artisan route:list | grep games

# Check database
php artisan tinker
>>> App\Models\Game::count()
>>> App\Models\Game::active()->get()
```

## API Integration

### Available API Endpoints
```php
// Get all active games
GET /api/games

// Record game play
POST /api/games/{game}/play

// Game Session Management (Real-time)
POST /api/games/{gameSlug}/start       // Start playing session
POST /api/games/{gameSlug}/end         // End playing session  
POST /api/games/{gameSlug}/heartbeat   // Keep session alive
GET /api/games/{gameSlug}/players      // Get active players

// Submit score (authenticated)
POST /api/leaderboards/{game:slug}/submit

// Get leaderboard data (authenticated)
GET /api/leaderboards/{game:slug}
```

### Real-time Integration with Reverb

#### WebSocket Channels
```javascript
// Subscribe to leaderboard updates
window.Echo.channel(`game.${gameSlug}.leaderboard`)
    .listen('.score.submitted', (data) => {
        console.log('New score:', data);
        // Update leaderboard display
    });

// Subscribe to player activity
window.Echo.channel(`game.${gameSlug}.players`)
    .listen('.game.started', (data) => {
        console.log('Player joined:', data);
        // Update active player count
    })
    .listen('.game.ended', (data) => {
        console.log('Player left:', data);
        // Update active player count
    });
```

#### Using Real-time Composable
```vue
<template>
    <div>
        <RealTimeGameWidget 
            :game-slug="'memory-test-game'"
            :show-player-list="true"
            :show-recent-scores="true"
            :show-controls="true"
        />
    </div>
</template>

<script setup>
import RealTimeGameWidget from '@/components/RealTimeGameWidget.vue';
import { useRealTimeGame } from '@/composables/useRealTimeGame';

const gameSlug = 'memory-test-game';
const {
    activePlayersCount,
    recentScores,
    startGameSession,
    endGameSession
} = useRealTimeGame(gameSlug);
</script>
```

### Using APIs in Game Files
```javascript
// Record that game was played
fetch(`/api/games/${gameId}/play`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});

// Submit score
fetch(`/api/leaderboards/${gameSlug}/submit`, {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        score: playerScore,
        time_played: timeInSeconds
    })
});
```

## Categories and Classifications

### Available Categories
- `action`: Fast-paced games requiring quick reflexes
- `puzzle`: Logic and problem-solving games
- `strategy`: Games requiring planning and tactics
- `arcade`: Classic arcade-style games
- `simulation`: Life or process simulation games
- `sports`: Sports-related games

### Difficulty Levels
- `easy`: Simple mechanics, low learning curve
- `medium`: Moderate complexity
- `hard`: Complex mechanics, high skill requirement

### Adding New Categories
1. Update the enum in the migration
2. Add category to admin form options
3. Update frontend filtering
4. Add category badge colors in the Game model

## Common Patterns

### Game Data Structure
```javascript
// Standard game object structure
const gameData = {
    id: 1,
    name: "Game Name",
    slug: "game-name",
    description: "Game description",
    emoji: "🎮",
    url: "/games/game-file.js",
    category: "puzzle",
    difficulty: "medium",
    playerCount: "1 Player",
    leaderboardRoute: "leaderboards.game"
};
```

### Game File Template
```javascript
// Basic game structure
class MyGame {
    constructor(containerId) {
        this.container = document.getElementById(containerId);
        this.score = 0;
        this.gameActive = false;
        this.init();
    }
    
    init() {
        this.setupUI();
        this.bindEvents();
    }
    
    setupUI() {
        // Create game interface
    }
    
    bindEvents() {
        // Setup event listeners
    }
    
    start() {
        this.gameActive = true;
        // Start game loop
    }
    
    end() {
        this.gameActive = false;
        this.submitScore();
    }
    
    submitScore() {
        // Submit to leaderboard
    }
}
```

## Troubleshooting

### Common Issues

1. **Game doesn't appear in listing**
   - Check `is_active` is true
   - Verify database connection
   - Clear cache

2. **JavaScript file not loading**
   - Check file path is correct
   - Verify file exists in public directory
   - Check web server configuration

3. **Admin panel access denied**
   - Verify user has admin role
   - Check role permissions

4. **Slug conflicts**
   - Ensure game names are unique
   - Check for special characters in names

### Debug Commands
```bash
# Check game records
php artisan tinker
>>> App\Models\Game::all()

# Verify routes
php artisan route:list | grep admin

# Check permissions
php artisan tinker
>>> auth()->user()->hasRole('admin')

# Clear all caches
php artisan optimize:clear
```

### Log Files
- Laravel logs: `storage/logs/laravel.log`
- Web server logs: Check your web server configuration
- Browser console: For JavaScript game errors

## Best Practices

1. **Naming Conventions**
   - Use descriptive game names
   - Ensure slugs are URL-friendly
   - Use consistent file naming

2. **Performance**
   - Optimize game assets
   - Use appropriate image sizes
   - Minimize JavaScript file size

3. **User Experience**
   - Provide clear instructions
   - Include intuitive controls
   - Add error handling in games

4. **Security**
   - Validate all inputs
   - Sanitize file uploads
   - Use CSRF protection

5. **Testing**
   - Test on multiple devices
   - Verify responsiveness
   - Check accessibility

## Future Enhancements

### Planned Features
- Batch game import
- Game versioning
- Advanced analytics
- Social features
- Achievement system

### Extension Points
- Custom game categories
- Rating system
- Comment system
- Tournament mode
- Multiplayer support

---

## Quick Reference

### Essential Commands
```bash
# Create new game via admin panel
/admin/games/create

# Add game programmatically
Game::create($gameData);

# Run tests
php artisan test

# Clear cache
php artisan cache:clear
```

### Key Files
- Model: `app/Models/Game.php`
- Controller: `app/Http/Controllers/AdminController.php`
- Migration: `database/migrations/create_games_table.php`
- Routes: `routes/web.php`, `routes/api.php`
- Frontend: `resources/js/pages/Games.vue`

This documentation should serve as your comprehensive guide for adding and managing games in the system. Keep it updated as the system evolves and new features are added.
