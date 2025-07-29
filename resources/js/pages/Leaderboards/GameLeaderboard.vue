<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head :title="`${game.name} - Leaderboard`" />

    <div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
      <div class="container mx-auto px-4 py-8">
        <!-- Header with Back Button -->
        <div class="flex items-center justify-between mb-8">
          <div class="flex items-center space-x-4">
            <button 
              @click="goBack"
              class="bg-white/10 backdrop-blur-sm border border-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/20 transition-all flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
              <span>Back</span>
            </button>
            
            <div>
              <h1 class="text-4xl font-bold text-white mb-2">🏆 {{ game.name }} Leaderboard</h1>
              <p class="text-purple-200">Best scores per player in {{ game.name }}!</p>
            </div>
          </div>
          
          <!-- Quick Action Buttons -->
          <div class="flex items-center space-x-2">
            <button 
              @click="refreshLeaderboard"
              class="bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white px-4 py-2 rounded-lg transition-all transform hover:scale-105 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>Refresh</span>
            </button>
            
            <Link 
              :href="`/games/${game.slug}`"
              class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-4 py-2 rounded-lg transition-all transform hover:scale-105 flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"/>
              </svg>
              <span>Play Now</span>
            </Link>
            
            <Link 
              :href="route('leaderboards')"
              class="bg-white/10 backdrop-blur-sm border border-white/20 text-white px-4 py-2 rounded-lg hover:bg-white/20 transition-all flex items-center space-x-2"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                <path d="M16 7h-2v4h6V9c0-1.1-.9-2-2-2zm-6 2H8V7H6c-1.1 0-2 .9-2 2v2h6V9zm8 6H8v4h10v-4zm-8-2V9H2v2h8zm10 0V9h-8v2h8z"/>
              </svg>
              <span>All Leaderboards</span>
            </Link>
          </div>
        </div>

        <!-- Timeframe Selector -->
        <div class="flex flex-wrap gap-2 mb-6">
          <button 
            v-for="(label, key) in timeframes"
            :key="key"
            @click="changeTimeframe(key)"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              currentTimeframe === key 
                ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg' 
                : 'bg-white/10 backdrop-blur-sm border border-white/20 text-white hover:bg-white/20'
            ]"
          >
            {{ label }}
          </button>
        </div>

        <!-- User Stats (if authenticated) -->
        <div v-if="userData" class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 p-6 mb-6">
          <h2 class="text-xl font-bold text-white mb-4">Your Performance</h2>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
              <div class="text-2xl font-bold text-white">{{ userData.score.toLocaleString() }}</div>
              <div class="text-purple-200 text-sm">Best Score</div>
            </div>
            <div class="text-center">
              <div class="text-2xl font-bold text-white">#{{ userData.rank || 'N/A' }}</div>
              <div class="text-purple-200 text-sm">Rank</div>
            </div>
            <div v-if="userData.level_reached" class="text-center">
              <div class="text-2xl font-bold text-white">{{ userData.level_reached }}</div>
              <div class="text-purple-200 text-sm">Level Reached</div>
            </div>
            <div v-if="userData.time_played" class="text-center">
              <div class="text-2xl font-bold text-white">{{ formatTime(userData.time_played) }}</div>
              <div class="text-purple-200 text-sm">Time Played</div>
            </div>
          </div>
        </div>

        <!-- Leaderboard Table -->
        <div class="bg-white/10 backdrop-blur-sm rounded-lg border border-white/20 overflow-hidden">
          <div class="p-6 border-b border-white/20">
            <h2 class="text-2xl font-bold text-white">
              🏆 Top Players - {{ timeframes[currentTimeframe] }}
            </h2>
            <p class="text-purple-200 mt-1">Best score per player</p>
          </div>
          
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-white/5">
                <tr>
                  <th class="text-left py-4 px-6 text-purple-200 font-medium">Rank</th>
                  <th class="text-left py-4 px-6 text-purple-200 font-medium">Player</th>
                  <th class="text-left py-4 px-6 text-purple-200 font-medium">Score</th>
                  <th class="text-left py-4 px-6 text-purple-200 font-medium">Level</th>
                  <th class="text-left py-4 px-6 text-purple-200 font-medium">Date</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="(score, index) in leaderboard" 
                  :key="score.id"
                  :class="[
                    'border-b border-white/10 hover:bg-white/5 transition-colors',
                    score.user_id === $page.props.auth?.user?.id ? 'bg-purple-500/20' : ''
                  ]"
                >
                  <td class="py-4 px-6">
                    <div class="flex items-center space-x-2">
                      <span 
                        v-if="index < 3"
                        class="text-2xl"
                      >
                        {{ index === 0 ? '🥇' : index === 1 ? '🥈' : '🥉' }}
                      </span>
                      <span class="text-white font-bold">#{{ index + 1 }}</span>
                    </div>
                  </td>
                  <td class="py-4 px-6">
                    <div class="flex items-center space-x-3">
                      <div class="w-8 h-8 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ score.user.name.charAt(0).toUpperCase() }}
                      </div>
                      <span class="text-white font-medium">{{ score.user.name }}</span>
                      <span v-if="score.user_id === $page.props.auth?.user?.id" class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                        You
                      </span>
                    </div>
                  </td>
                  <td class="py-4 px-6">
                    <span class="text-white font-bold text-lg">{{ score.score.toLocaleString() }}</span>
                  </td>
                  <td class="py-4 px-6">
                    <span class="text-purple-200">{{ score.level_reached || 'N/A' }}</span>
                  </td>
                  <td class="py-4 px-6">
                    <span class="text-purple-200 text-sm">{{ formatDate(score.created_at) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <!-- Empty State -->
            <div v-if="leaderboard.length === 0" class="text-center py-12">
              <div class="text-6xl mb-4">🏆</div>
              <h3 class="text-xl font-bold text-white mb-2">No scores yet!</h3>
              <p class="text-purple-200 mb-4">Be the first to set a score in {{ game.name }}.</p>
              <Link 
                :href="`/games/${game.slug}`"
                class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 text-white px-6 py-3 rounded-lg transition-all transform hover:scale-105 inline-flex items-center space-x-2"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"/>
                </svg>
                <span>Play {{ game.name }}</span>
              </Link>
            </div>
          </div>
        </div>

        <!-- Load More Button -->
        <div v-if="leaderboard.length >= 10" class="text-center mt-6">
          <button 
            @click="loadMore"
            :disabled="loading"
            class="bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 disabled:from-gray-400 disabled:to-gray-500 text-white px-6 py-3 rounded-lg transition-all transform hover:scale-105 disabled:scale-100"
          >
            {{ loading ? 'Loading...' : 'Load More' }}
          </button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'

interface User {
  id: number
  name: string
  email: string
}

interface Score {
  id: number
  user_id: number
  user: User
  score: number
  level_reached?: number
  time_played?: number
  created_at: string
}

interface Game {
  id: number
  name: string
  slug: string
  category: string
}

interface Props {
  game: Game
  leaderboard: Score[]
  currentTimeframe: '30' | '60' | '90' | '365' | 'all'
  userData?: Score & { rank?: number }
}

const props = defineProps<Props>()

const loading = ref(false)
const leaderboard = ref(props.leaderboard)

const timeframes = {
  '30': 'Last 30 Days',
  '60': 'Last 60 Days', 
  '90': 'Last 90 Days',
  '365': 'This Year',
  'all': 'All Time'
}

const currentTimeframe = ref(props.currentTimeframe)

const breadcrumbItems = computed(() => [
  { title: 'Dashboard', href: route('dashboard') },
  { title: 'Leaderboards', href: route('leaderboards') },
  { title: props.game.name, href: '#' }
])

const goBack = () => {
  router.visit(route('leaderboards'))
}

const changeTimeframe = (timeframe: string) => {
  if (timeframe === currentTimeframe.value) return
  
  loading.value = true
  router.get(route('leaderboards.game', props.game.slug), {
    timeframe
  }, {
    preserveScroll: true,
    onSuccess: (page: any) => {
      leaderboard.value = page.props.leaderboard
      currentTimeframe.value = timeframe as '30' | '60' | '90' | '365' | 'all'
    },
    onFinish: () => {
      loading.value = false
    }
  })
}

const refreshLeaderboard = () => {
  router.reload()
}

const loadMore = () => {
  // Implement pagination if needed
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const formatTime = (seconds: number) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}
</script>
