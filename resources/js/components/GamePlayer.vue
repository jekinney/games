<template>
  <div class="game-player-container">
    <!-- Game Header -->
    <div class="game-header" v-if="selectedGame">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ selectedGame.name }}</h1>
          <p class="text-gray-600 mt-2">{{ selectedGame.description }}</p>
        </div>
        <button 
          @click="selectedGame = null" 
          class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors"
        >
          ← Back to Games
        </button>
      </div>
    </div>

    <!-- Game Selection -->
    <div v-if="!selectedGame" class="games-grid">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Available Games</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="game in games" 
          :key="game.id"
          class="game-card bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow cursor-pointer"
          @click="playGame(game)"
        >
          <div class="aspect-video bg-gradient-to-br from-purple-400 to-blue-500 flex items-center justify-center">
            <div class="text-white text-4xl">🎮</div>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-semibold text-gray-900 mb-2">{{ game.name }}</h3>
            <p class="text-gray-600 text-sm mb-4">{{ game.description }}</p>
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-2">
                <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                  {{ game.category }}
                </span>
                <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                  {{ game.difficulty }}
                </span>
              </div>
              <button class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                Play
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Player -->
    <div v-if="selectedGame" class="game-player">
      <iframe 
        :src="selectedGame.game_file_url" 
        class="w-full h-screen border-0 rounded-lg shadow-lg"
        frameborder="0"
        allowfullscreen
      ></iframe>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex items-center justify-center min-h-64">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-purple-600"></div>
    </div>

    <!-- Error State -->
    <div v-if="error" class="text-center text-red-600 p-8">
      <div class="text-xl mb-2">😟 Oops!</div>
      <p>{{ error }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';

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
