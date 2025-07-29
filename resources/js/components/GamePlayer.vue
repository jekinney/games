<template>
    <div class="game-player-container">
        <!-- Game Header -->
        <div class="game-header" v-if="selectedGame">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ selectedGame.name }}</h1>
                    <p class="mt-2 text-gray-600">{{ selectedGame.description }}</p>
                </div>
                <button @click="selectedGame = null" class="rounded-lg bg-gray-600 px-4 py-2 text-white transition-colors hover:bg-gray-700">
                    ← Back to Games
                </button>
            </div>
        </div>

        <!-- Game Selection -->
        <div v-if="!selectedGame" class="games-grid">
            <h2 class="mb-6 text-2xl font-bold text-gray-900">Available Games</h2>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="game in games"
                    :key="game.id"
                    class="game-card cursor-pointer overflow-hidden rounded-lg bg-white shadow-lg transition-shadow hover:shadow-xl"
                    @click="playGame(game)"
                >
                    <div class="flex aspect-video items-center justify-center bg-gradient-to-br from-purple-400 to-blue-500">
                        <div class="text-4xl text-white">🎮</div>
                    </div>
                    <div class="p-6">
                        <h3 class="mb-2 text-xl font-semibold text-gray-900">{{ game.name }}</h3>
                        <p class="mb-4 text-sm text-gray-600">{{ game.description }}</p>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-2">
                                <span class="rounded-full bg-blue-100 px-2 py-1 text-xs text-blue-800">
                                    {{ game.category }}
                                </span>
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs text-green-800">
                                    {{ game.difficulty }}
                                </span>
                            </div>
                            <button class="rounded-lg bg-purple-600 px-4 py-2 text-white transition-colors hover:bg-purple-700">Play</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Player -->
        <div v-if="selectedGame" class="game-player">
            <iframe :src="selectedGame.game_file_url" class="h-screen w-full rounded-lg border-0 shadow-lg" frameborder="0" allowfullscreen></iframe>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="flex min-h-64 items-center justify-center">
            <div class="h-12 w-12 animate-spin rounded-full border-b-2 border-purple-600"></div>
        </div>

        <!-- Error State -->
        <div v-if="error" class="p-8 text-center text-red-600">
            <div class="mb-2 text-xl">😟 Oops!</div>
            <p>{{ error }}</p>
        </div>
    </div>
</template>

<script setup lang="ts">
import axios from 'axios';
import { onMounted, ref } from 'vue';

interface Game {
    id: number;
    name: string;
    slug: string;
    description: string;
    how_to_play?: string;
    category: string;
    difficulty: string;
    is_active: boolean;
    min_players: number;
    max_players: number;
    estimated_play_time?: number;
    thumbnail_url?: string;
    image_url?: string;
    game_file_url: string;
    tags: string[];
    controls: string[];
    is_featured: boolean;
    play_count: number;
    average_rating: number;
}

const games = ref<Game[]>([]);
const selectedGame = ref<Game | null>(null);
const loading = ref(false);
const error = ref('');

const fetchGames = async () => {
    loading.value = true;
    error.value = '';

    try {
        const response = await axios.get('/api/games');
        games.value = response.data.filter((game: Game) => game.is_active);
    } catch (err) {
        error.value = 'Failed to load games. Please try again later.';
        console.error('Error fetching games:', err);
    } finally {
        loading.value = false;
    }
};

const playGame = (game: Game) => {
    selectedGame.value = game;
    // Increment play count
    incrementPlayCount(game.id);
};

const incrementPlayCount = async (gameId: number) => {
    try {
        await axios.post(`/api/games/${gameId}/play`);
    } catch (err) {
        console.error('Error incrementing play count:', err);
    }
};

onMounted(() => {
    fetchGames();
});
</script>

<style scoped>
.game-player-container {
    min-height: 100vh;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    padding: 20px;
}

.games-grid {
    max-width: 1200px;
    margin: 0 auto;
}

.game-card {
    transition: all 0.3s ease;
}

.game-card:hover {
    transform: translateY(-5px);
}

.game-player {
    max-width: 1200px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .game-player-container {
        padding: 10px;
    }

    .games-grid {
        padding: 0 10px;
    }
}
</style>
