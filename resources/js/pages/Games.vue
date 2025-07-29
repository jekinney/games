<template>
    <Head title="Games Hub" />
    
    <MainLayout>
        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="mb-6 text-4xl font-bold text-white md:text-5xl">
                        🎮 <span class="bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">Games Hub</span>
                    </h1>
                    <p class="mx-auto mb-8 max-w-2xl text-lg text-gray-300">
                        Dive into exciting games, challenge your skills, and compete with players worldwide. Your gaming adventure awaits!
                    </p>
                    
                    <!-- Search Section -->
                    <div class="mx-auto max-w-lg">
                        <div class="relative">
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search games by name..."
                                class="w-full rounded-lg border border-white/20 bg-white/10 px-4 py-2.5 pr-10 text-white placeholder-gray-400 backdrop-blur-sm transition-all focus:border-purple-400 focus:outline-none focus:ring-2 focus:ring-purple-400/50"
                                @keyup.enter="searchGames"
                            />
                            <svg
                                class="absolute right-3 top-1/2 h-5 w-5 -translate-y-1/2 text-gray-400"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                                />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <GameFilters
            :filter-options="filterOptions"
            :active-filters="activeFilters"
            :search-query="searchQuery"
            storage-key="gamesFiltersVisible"
            @toggle-filter="toggleFilter"
            @clear-all="clearAllFilters"
        />

        <!-- Games Grid -->
        <div class="p-6">
            <div class="mx-auto max-w-6xl">
                <!-- Results Summary -->
                <div class="mb-4">
                    <p class="text-sm text-gray-300">
                        {{ filteredGames.length }} game{{ filteredGames.length !== 1 ? 's' : '' }}
                        <span v-if="activeFilters.length > 0 || searchQuery">
                            matching your filters
                        </span>
                    </p>
                </div>

                <div v-if="paginatedGames.length > 0" class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Dynamic Game Cards -->
                    <div
                        v-for="game in paginatedGames"
                        :key="game.id"
                        dusk="game-card"
                        class="group cursor-pointer rounded-lg border border-white/20 bg-white/10 p-6 backdrop-blur-sm transition-all hover:bg-white/20"
                    >
                        <div class="text-center">
                            <div class="mb-4 transition-transform group-hover:scale-110">
                                <img 
                                    :src="game.thumbnail || game.image" 
                                    :alt="game.name"
                                    class="mx-auto h-16 w-16 rounded-lg object-cover"
                                    @error="handleImageError"
                                />
                            </div>
                            <h3 class="mb-2 text-xl font-bold text-white">{{ game.name }}</h3>
                            <p class="mb-4 text-sm text-gray-300">
                                {{ game.description }}
                            </p>
                            <div class="mb-4 flex justify-center space-x-2">
                                <span class="rounded-full bg-purple-600 px-2 py-1 text-xs text-white">{{ game.category }}</span>
                                <span 
                                    :class="{
                                        'bg-green-600': game.difficulty === 'Easy',
                                        'bg-yellow-600': game.difficulty === 'Medium', 
                                        'bg-red-600': game.difficulty === 'Hard'
                                    }"
                                    class="rounded-full px-2 py-1 text-xs text-white"
                                >
                                    {{ game.difficulty }}
                                </span>
                                <span class="rounded-full bg-blue-600 px-2 py-1 text-xs text-white">{{ game.playerCount }}</span>
                            </div>
                            <button
                                @click="playGame(game.url)"
                                dusk="play-button"
                                class="mb-3 w-full transform rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-3 font-bold text-white transition-all hover:scale-105 hover:from-purple-700 hover:to-blue-700"
                            >
                                Play Now
                            </button>
                            
                            <!-- Leaderboard Button -->
                            <button
                                @click="openLeaderboard(game)"
                                dusk="leaderboard-button"
                                class="flex w-full items-center justify-center space-x-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2 font-medium text-white transition-all hover:border-white/40 hover:bg-white/20"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 9H4.5a2.5 2.5 0 0 0 0 5H6m0-5a2.5 2.5 0 0 1 0-5H4.5a2.5 2.5 0 0 0 0 5M6 9v6a6 6 0 0 0 12 0V9M6 9a6 6 0 1 1 12 0v0M18 9h1.5a2.5 2.5 0 0 0 0-5H18m0 5a2.5 2.5 0 0 1 0 5h1.5a2.5 2.5 0 0 0 0-5M18 9v6"/>
                                </svg>
                                <span>🏆 View Leaderboard</span>
                            </button>
                        </div>
                    </div>

                    <!-- Placeholder for more games if showing all -->
                    <div v-if="activeFilters.length === 0 && !searchQuery && currentPage === totalPages" class="rounded-lg border border-dashed border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="text-center text-gray-400">
                            <div class="mb-4 text-4xl">➕</div>
                            <h3 class="mb-2 text-lg font-semibold">More Games Coming Soon</h3>
                            <p class="text-sm">We're working on adding more exciting games to the hub!</p>
                        </div>
                    </div>
                </div>

                <!-- No Results Message -->
                <div v-else class="rounded-lg border border-white/20 bg-white/10 p-12 text-center backdrop-blur-sm">
                    <div class="mb-4 text-6xl">🔍</div>
                    <h3 class="mb-2 text-xl font-bold text-white">No Games Found</h3>
                    <p class="mb-4 text-gray-300">
                        No games match your current filters or search criteria.
                    </p>
                    <button
                        @click="clearAllFilters"
                        class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 font-medium text-white transition-all hover:from-purple-700 hover:to-pink-700"
                    >
                        Clear All Filters
                    </button>
                </div>

                <!-- Pagination (Bottom) -->
                <div v-if="showPagination" class="mt-8 flex items-center justify-center space-x-2">
                    <!-- Previous Button -->
                    <button
                        @click="currentPage = Math.max(1, currentPage - 1)"
                        :disabled="currentPage === 1"
                        :class="{ 'opacity-50 cursor-not-allowed': currentPage === 1 }"
                        class="flex items-center space-x-1 rounded px-3 py-1.5 text-sm font-medium text-white transition-all border border-white/20 bg-white/10 hover:bg-white/20"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        <span>Prev</span>
                    </button>

                    <!-- Page Numbers -->
                    <div class="flex space-x-1">
                        <button
                            v-for="page in totalPages"
                            :key="page"
                            @click="currentPage = page"
                            :class="{
                                'bg-gradient-to-r from-purple-500 to-pink-500 text-white': page === currentPage,
                                'border border-white/20 bg-white/10 text-white hover:bg-white/20': page !== currentPage
                            }"
                            class="rounded px-3 py-1.5 text-sm font-medium transition-all"
                        >
                            {{ page }}
                        </button>
                    </div>

                    <!-- Next Button -->
                    <button
                        @click="currentPage = Math.min(totalPages, currentPage + 1)"
                        :disabled="currentPage === totalPages"
                        :class="{ 'opacity-50 cursor-not-allowed': currentPage === totalPages }"
                        class="flex items-center space-x-1 rounded px-3 py-1.5 text-sm font-medium text-white transition-all border border-white/20 bg-white/10 hover:bg-white/20"
                    >
                        <span>Next</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Game Modal -->
        <div v-if="showGameModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="closeGame">
            <div class="h-[80vh] w-full max-w-6xl overflow-hidden rounded-lg bg-white" @click.stop>
                <div class="flex items-center justify-between border-b bg-gray-50 p-4">
                    <h2 class="text-lg font-semibold">Playing Game</h2>
                    <button @click="closeGame" dusk="close-game-button" class="text-2xl font-bold text-gray-500 hover:text-gray-700">×</button>
                </div>
                <iframe :src="currentGameUrl" dusk="game-iframe" class="h-[calc(100%-60px)] w-full border-0" frameborder="0"></iframe>
            </div>
        </div>

        <!-- Leaderboard Modal -->
        <div v-if="showLeaderboardModal" dusk="leaderboard-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="closeLeaderboard">
            <div class="w-full max-w-2xl max-h-[80vh] overflow-hidden rounded-lg bg-white" @click.stop>
                <div class="flex items-center justify-between border-b bg-gray-50 p-4">
                    <h2 class="text-lg font-semibold">
                        🏆 {{ selectedGame?.name }} Leaderboard
                    </h2>
                    <button @click="closeLeaderboard" dusk="close-leaderboard-button" class="text-2xl font-bold text-gray-500 hover:text-gray-700">×</button>
                </div>
                
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-80px)]">
                    <!-- Loading state -->
                    <div v-if="loadingLeaderboard" class="text-center py-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-purple-600 mx-auto mb-4"></div>
                        <p class="text-gray-600">Loading leaderboard...</p>
                    </div>
                    
                    <!-- Leaderboard content -->
                    <div v-else-if="leaderboardData">
                        <!-- Top 10 -->
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold mb-4 text-gray-800">Top 10 Players</h3>
                            <div class="space-y-2">
                                <div 
                                    v-for="(entry, index) in leaderboardData.topTen" 
                                    :key="entry.id"
                                    class="flex items-center justify-between p-3 rounded-lg"
                                    :class="{
                                        'bg-gradient-to-r from-yellow-100 to-yellow-200': index === 0,
                                        'bg-gradient-to-r from-gray-100 to-gray-200': index === 1,
                                        'bg-gradient-to-r from-orange-100 to-orange-200': index === 2,
                                        'bg-gray-50': index > 2
                                    }"
                                >
                                    <div class="flex items-center space-x-3">
                                        <span class="font-bold text-lg w-8">
                                            {{ index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}` }}
                                        </span>
                                        <div>
                                            <p class="font-semibold">{{ entry.user_name }}</p>
                                            <p class="text-sm text-gray-600">{{ formatDate(entry.created_at) }}</p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <p class="font-bold text-lg">{{ entry.score.toLocaleString() }}</p>
                                        <p v-if="entry.level_reached" class="text-sm text-gray-600">Level {{ entry.level_reached }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Current user score (if not in top 10) -->
                        <div v-if="leaderboardData.currentUser && !leaderboardData.currentUser.inTopTen" class="border-t pt-4">
                            <h3 class="text-lg font-semibold mb-3 text-gray-800">Your Best Score</h3>
                            <div class="flex items-center justify-between p-3 rounded-lg bg-blue-50 border border-blue-200">
                                <div class="flex items-center space-x-3">
                                    <span class="font-bold text-lg w-8">#{{ leaderboardData.currentUser.rank }}</span>
                                    <div>
                                        <p class="font-semibold">{{ leaderboardData.currentUser.user_name }}</p>
                                        <p class="text-sm text-gray-600">{{ formatDate(leaderboardData.currentUser.created_at) }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg">{{ leaderboardData.currentUser.score.toLocaleString() }}</p>
                                    <p v-if="leaderboardData.currentUser.level_reached" class="text-sm text-gray-600">Level {{ leaderboardData.currentUser.level_reached }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Empty state -->
                        <div v-if="leaderboardData.topTen.length === 0" class="text-center py-8">
                            <div class="text-6xl mb-4">🎮</div>
                            <h3 class="text-lg font-semibold mb-2">No scores yet!</h3>
                            <p class="text-gray-600">Be the first to play and set a high score!</p>
                        </div>
                    </div>
                    
                    <!-- Error state -->
                    <div v-else class="text-center py-8">
                        <div class="text-6xl mb-4">❌</div>
                        <h3 class="text-lg font-semibold mb-2">Failed to load leaderboard</h3>
                        <p class="text-gray-600 mb-4">There was an error loading the leaderboard data.</p>
                        <button 
                            @click="selectedGame && loadLeaderboardData(selectedGame)" 
                            class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                        >
                            Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import GameFilters from '@/components/GameFilters.vue';
import { Head } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

interface Game {
    id: number;
    name: string;
    description: string;
    image: string;
    thumbnail: string;
    url: string;
    category: string;
    difficulty: string;
    playerCount: string;
    isFeatured?: boolean;
    playCount?: number;
    averageRating?: number;
    estimatedPlayTime?: number;
    tags?: string[];
}

// Props from the controller
interface Props {
    title: string;
    games: Game[];
}

const props = defineProps<Props>();

const showGameModal = ref(false);
const currentGameUrl = ref('');
const searchQuery = ref('');
const activeFilters = ref<string[]>([]);
const currentPage = ref(1);
const gamesPerPage = 9;

// Leaderboard state
const showLeaderboardModal = ref(false);
const selectedGame = ref<Game | null>(null);
const leaderboardData = ref<any>(null);
const loadingLeaderboard = ref(false);

// Use games from props instead of hardcoded data
const allGames = ref<Game[]>(props.games);

// Get unique filter options
const filterOptions = computed(() => {
    const categories = [...new Set(allGames.value.map(game => game.category))];
    const difficulties = [...new Set(allGames.value.map(game => game.difficulty))];
    const playerCounts = [...new Set(allGames.value.map(game => game.playerCount))];
    
    return {
        categories,
        difficulties,
        playerCounts,
        all: [...categories, ...difficulties, ...playerCounts]
    };
});

// Filtered games based on search and active filters
const filteredGames = computed(() => {
    let games = allGames.value;

    // Apply search filter
    if (searchQuery.value.trim()) {
        const search = searchQuery.value.toLowerCase();
        games = games.filter(game => 
            game.name.toLowerCase().includes(search) ||
            game.description.toLowerCase().includes(search)
        );
    }

    // Apply category/difficulty/player count filters
    if (activeFilters.value.length > 0) {
        games = games.filter(game => {
            return activeFilters.value.some(filter => 
                game.category === filter || 
                game.difficulty === filter || 
                game.playerCount === filter
            );
        });
    }

    return games;
});

// Pagination computed properties
const totalPages = computed(() => Math.ceil(filteredGames.value.length / gamesPerPage));
const showPagination = computed(() => filteredGames.value.length > gamesPerPage);

const paginatedGames = computed(() => {
    const start = (currentPage.value - 1) * gamesPerPage;
    const end = start + gamesPerPage;
    return filteredGames.value.slice(start, end);
});

// Reset to page 1 when filters change
watch([searchQuery, activeFilters], () => {
    currentPage.value = 1;
}, { deep: true });

const playGame = (gameUrl: string) => {
    currentGameUrl.value = gameUrl;
    showGameModal.value = true;
};

const closeGame = () => {
    showGameModal.value = false;
    currentGameUrl.value = '';
};

const searchGames = () => {
    // Search is reactive, so this just triggers any additional search logic if needed
    console.log('Searching for games:', searchQuery.value);
};

const toggleFilter = (filter: string) => {
    const index = activeFilters.value.indexOf(filter);
    if (index > -1) {
        activeFilters.value.splice(index, 1);
    } else {
        activeFilters.value.push(filter);
    }
};

const clearAllFilters = () => {
    activeFilters.value = [];
    searchQuery.value = '';
};

// Leaderboard functions
const openLeaderboard = async (game: Game) => {
    selectedGame.value = game;
    
    // Check screen size - use modal for desktop, navigation for mobile
    const isMobile = window.innerWidth < 768;
    
    if (isMobile) {
        // Navigate to dedicated leaderboard page for mobile
        window.location.href = `/leaderboards/${game.name.toLowerCase().replace(/\s+/g, '-')}`;
    } else {
        // Show modal for desktop
        showLeaderboardModal.value = true;
        await loadLeaderboardData(game);
    }
};

const closeLeaderboard = () => {
    showLeaderboardModal.value = false;
    selectedGame.value = null;
    leaderboardData.value = null;
};

const loadLeaderboardData = async (game: Game) => {
    if (!game) return;
    
    loadingLeaderboard.value = true;
    leaderboardData.value = null;
    
    try {
        // Create game slug for API call
        const gameSlug = game.name.toLowerCase().replace(/\s+/g, '-');
        
        // Fetch leaderboard data from API
        const response = await fetch(`/api/leaderboards/${gameSlug}`);
        
        if (!response.ok) {
            throw new Error('Failed to fetch leaderboard data');
        }
        
        const data = await response.json();
        leaderboardData.value = data;
    } catch (error) {
        console.error('Error loading leaderboard:', error);
        leaderboardData.value = null;
    } finally {
        loadingLeaderboard.value = false;
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

const handleImageError = (event: Event) => {
    const img = event.target as HTMLImageElement;
    // Try multiple fallback paths
    if (img.src.includes('default-game.png')) {
        // Already tried default, use a data URL as final fallback
        img.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjQiIGhlaWdodD0iNjQiIHZpZXdCb3g9IjAgMCA2NCA2NCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjY0IiBoZWlnaHQ9IjY0IiBmaWxsPSIjOTMzM0VBIiByeD0iOCIvPgo8dGV4dCB4PSIzMiIgeT0iMzgiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0id2hpdGUiIHRleHQtYW5jaG9yPSJtaWRkbGUiPvCfjrs8L3RleHQ+Cjwvc3ZnPgo=';
    } else {
        // First try the main default image
        img.src = '/images/games/default-game.png';
    }
};
</script>
