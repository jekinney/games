<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
    <!-- Header -->
    <div class="bg-black/20 backdrop-blur-sm border-b border-white/10 p-6">
      <div class="max-w-7xl mx-auto flex items-center justify-between">
        <div>
          <h1 class="text-4xl font-bold text-white mb-2">🎮 Games Hub</h1>
          <p class="text-gray-300">Choose a game to play and test your skills!</p>
        </div>
        
        <!-- Navigation and User Dropdown -->
        <div class="flex items-center space-x-4">
          <!-- Home Link -->
          <Link :href="route('home')" class="flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            <span class="hidden sm:inline">Home</span>
          </Link>

          <!-- Leaderboard Link -->
          <Link :href="route('leaderboards')" class="flex items-center space-x-2 px-4 py-2 text-white/80 hover:text-white hover:bg-white/10 rounded-lg transition-all">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M16 7h-2v4h6V9c0-1.1-.9-2-2-2zm-6 2H8V7H6c-1.1 0-2 .9-2 2v2h6V9zm8 6H8v4h10v-4zm-8-2V9H2v2h8zm10 0V9h-8v2h8z"/>
            </svg>
            <span class="hidden sm:inline">🏆 Leaderboards</span>
          </Link>

          <!-- Game Icon -->
          <div class="flex items-center space-x-2 px-4 py-2 text-white/60">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
              <path d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5zM9 13H7v2H5v-2H3v-2h2V9h2v2h2v2zm5.5-1c-.83 0-1.5-.67-1.5-1.5S13.67 9 14.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm2.5 3c-.83 0-1.5-.67-1.5-1.5S16.17 12 17 12s1.5.67 1.5 1.5S17.83 15 17 15z"/>
            </svg>
            <span class="hidden sm:inline text-sm">Games</span>
          </div>

          <!-- User Menu Dropdown -->
          <DropdownMenu>
            <DropdownMenuTrigger as-child>
              <Button variant="outline" size="icon" class="relative h-10 w-10 rounded-full bg-white/10 border-white/20 hover:bg-white/20">
                <Avatar class="h-8 w-8">
                  <AvatarImage :src="user.avatar || ''" :alt="user.name" />
                  <AvatarFallback class="bg-purple-600 text-white text-sm">
                    {{ getInitials(user.name) }}
                  </AvatarFallback>
                </Avatar>
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-56">
              <UserMenuContent :user="user" />
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
      </div>
    </div>

    <!-- Games Grid -->
    <div class="p-8">
      <div class="max-w-6xl mx-auto">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Memory Test Game Card -->
          <div class="bg-white/10 backdrop-blur-sm rounded-lg p-6 border border-white/20 hover:bg-white/20 transition-all group cursor-pointer">
            <div class="text-center">
              <div class="text-6xl mb-4 group-hover:scale-110 transition-transform">🧠</div>
              <h3 class="text-xl font-bold text-white mb-2">Memory Test Game</h3>
              <p class="text-gray-300 text-sm mb-4">
                Test your memory skills by finding matching pairs of cards. Progressive difficulty levels challenge your brain!
              </p>
              <div class="flex justify-center space-x-2 mb-4">
                <span class="px-2 py-1 bg-purple-600 text-white text-xs rounded-full">Puzzle</span>
                <span class="px-2 py-1 bg-green-600 text-white text-xs rounded-full">Medium</span>
                <span class="px-2 py-1 bg-blue-600 text-white text-xs rounded-full">1 Player</span>
              </div>
              <button 
                @click="playGame('/games/memory-test-game.html')"
                class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-all transform hover:scale-105 mb-3"
              >
                Play Now
              </button>
              
              <!-- Leaderboard Link -->
              <Link 
                :href="route('leaderboards.game', 'memory-test-game')"
                class="w-full flex items-center justify-center space-x-2 bg-white/10 hover:bg-white/20 text-white font-medium py-2 px-4 rounded-lg transition-all border border-white/20 hover:border-white/40"
              >
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M6 9H4.5a2.5 2.5 0 0 0 0 5H6m0-5a2.5 2.5 0 0 1 0-5H4.5a2.5 2.5 0 0 0 0 5M6 9v6a6 6 0 0 0 12 0V9M6 9a6 6 0 1 1 12 0v0M18 9h1.5a2.5 2.5 0 0 0 0-5H18m0 5a2.5 2.5 0 0 1 0 5h1.5a2.5 2.5 0 0 0 0-5M18 9v6" />
                </svg>
                <span>🏆 View Leaderboard</span>
              </Link>
            </div>
          </div>

          <!-- Placeholder for more games -->
          <div class="bg-white/5 backdrop-blur-sm rounded-lg p-6 border border-white/10 border-dashed">
            <div class="text-center text-gray-400">
              <div class="text-4xl mb-4">➕</div>
              <h3 class="text-lg font-semibold mb-2">More Games Coming Soon</h3>
              <p class="text-sm">
                We're working on adding more exciting games to the hub!
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Game Modal -->
    <div 
      v-if="showGameModal" 
      class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4"
      @click="closeGame"
    >
      <div class="bg-white rounded-lg w-full max-w-6xl h-[80vh] overflow-hidden" @click.stop>
        <div class="flex items-center justify-between p-4 border-b bg-gray-50">
          <h2 class="text-lg font-semibold">Playing Game</h2>
          <button 
            @click="closeGame"
            class="text-gray-500 hover:text-gray-700 text-2xl font-bold"
          >
            ×
          </button>
        </div>
        <iframe 
          :src="currentGameUrl" 
          class="w-full h-[calc(100%-60px)] border-0"
          frameborder="0"
        ></iframe>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { usePage, Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { User } from '@/types/index.d';

const page = usePage();
const user = page.props.auth.user as User;

const showGameModal = ref(false);
const currentGameUrl = ref('');

const playGame = (gameUrl: string) => {
  currentGameUrl.value = gameUrl;
  showGameModal.value = true;
};

const closeGame = () => {
  showGameModal.value = false;
  currentGameUrl.value = '';
};
</script>
