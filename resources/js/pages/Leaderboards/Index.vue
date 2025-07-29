<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Leaderboards" />

    <div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      <div class="container mx-auto px-4 py-8">
        <!-- Hero Section -->
        <div class="text-center mb-12">
          <h1 class="text-5xl font-bold text-white mb-4">🏆 Game Leaderboards</h1>
          <p class="text-xl text-purple-200 mb-8">Compete with players worldwide and climb the ranks!</p>
          
          <!-- Quick Stats -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
              <div class="text-center">
                <div class="text-3xl mb-2">🎯</div>
                <div class="text-2xl font-bold text-white">{{ totalScores }}</div>
                <div class="text-purple-200 text-sm">Total Scores</div>
              </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
              <div class="text-center">
                <div class="text-3xl mb-2">👥</div>
                <div class="text-2xl font-bold text-white">{{ props.activePlayers }}</div>
                <div class="text-purple-200 text-sm">Active Players</div>
              </div>
            </div>
            
            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20">
              <div class="text-center">
                <div class="text-3xl mb-2">🔥</div>
                <div class="text-2xl font-bold text-white">{{ props.todaysGames }}</div>
                <div class="text-purple-200 text-sm">Games Today</div>
              </div>
            </div>
          </div>
        </div>

        <!-- Game Selection -->
        <div class="mb-8">
          <h2 class="text-3xl font-bold text-white mb-6 text-center">Choose a Game to View Leaderboard</h2>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div 
              v-for="game in props.games" 
              :key="game.id"
              class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 hover:bg-white/20 transition-all group cursor-pointer transform hover:scale-105"
              @click="viewGameLeaderboard(game)"
            >
              <div class="p-6">
                <div class="flex items-center space-x-4">
                  <div class="text-4xl">🎮</div>
                  <div class="flex-1">
                    <h3 class="text-lg font-bold text-white group-hover:text-purple-300 transition-colors">
                      {{ game.name }}
                    </h3>
                    <p class="text-purple-200 text-sm capitalize">{{ game.category }}</p>
                  </div>
                  <div class="text-white/60 group-hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="text-center">
          <div class="flex flex-wrap justify-center gap-4">
            <button 
              @click="showGlobalLeaderboard = true"
              class="bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
              </svg>
              <span>🌟 Global Leaderboard</span>
            </button>
            
            <Link 
              :href="route('games')"
              class="bg-gradient-to-r from-green-600 to-teal-600 hover:from-green-700 hover:to-teal-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 flex items-center space-x-2"
            >
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"/>
              </svg>
              <span>🎮 Play Games</span>
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Global Leaderboard Modal -->
    <div 
      v-if="showGlobalLeaderboard" 
      class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click="showGlobalLeaderboard = false"
    >
      <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-lg w-full max-w-4xl max-h-[80vh] overflow-hidden" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-white/20">
          <h2 class="text-2xl font-bold text-white">🌟 Global Leaderboard</h2>
          <button 
            @click="showGlobalLeaderboard = false"
            class="text-white/60 hover:text-white text-2xl transition-colors"
          >
            ×
          </button>
        </div>
        <div class="p-6 overflow-y-auto max-h-[60vh]">
          <p class="text-purple-200 mb-4 text-center text-lg">Coming soon! Global rankings across all games.</p>
          <div class="text-center">
            <div class="text-6xl mb-4">🚀</div>
            <p class="text-white font-medium">We're working on bringing you comprehensive rankings across all games!</p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface Game {
  id: number
  name: string
  slug: string
  category: string
}

interface Props {
  games: Game[]
  totalGames: number
  activePlayers: number
  todaysGames: number
}

const props = defineProps<Props>()

const showGlobalLeaderboard = ref(false)

const breadcrumbItems = computed(() => [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Leaderboards', href: '#' }
])

const totalScores = computed(() => {
  return props.totalGames * 50 // Estimate based on total games
})

const viewGameLeaderboard = (game: Game) => {
  router.visit(`/leaderboards/${game.slug}`)
}
</script>
