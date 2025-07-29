<template>
    <Head title="Leaderboards" />
    
    <MainLayout>
        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="mb-4 bg-gradient-to-r from-white via-purple-200 to-white bg-clip-text text-4xl font-bold tracking-tight text-transparent sm:text-5xl lg:text-6xl">
                        🏆 Game Leaderboards
                    </h1>
                    <p class="mx-auto mb-12 max-w-3xl text-xl text-purple-100">
                        Compete with players worldwide and climb the ranks in your favorite games
                    </p>

                    <!-- Stats Grid -->
                    <div class="mx-auto grid max-w-4xl grid-cols-1 gap-6 md:grid-cols-3">
                        <div class="rounded-lg border border-white/20 bg-white/10 p-6 backdrop-blur-sm">
                            <div class="text-center">
                                <div class="mb-2 text-3xl">🎯</div>
                                <div class="text-2xl font-bold text-white">{{ totalScores }}</div>
                                <div class="text-sm text-purple-200">Total Scores</div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-white/20 bg-white/10 p-6 backdrop-blur-sm">
                            <div class="text-center">
                                <div class="mb-2 text-3xl">👥</div>
                                <div class="text-2xl font-bold text-white">{{ props.activePlayers }}</div>
                                <div class="text-sm text-purple-200">Active Players</div>
                            </div>
                        </div>

                        <div class="rounded-lg border border-white/20 bg-white/10 p-6 backdrop-blur-sm">
                            <div class="text-center">
                                <div class="mb-2 text-3xl">🔥</div>
                                <div class="text-2xl font-bold text-white">{{ props.todaysGames }}</div>
                                <div class="text-sm text-purple-200">Games Today</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Selection Section -->
        <div class="border-b border-white/10 bg-black/10 py-4 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="mb-6 text-center text-2xl font-bold text-white">Choose Your Game</h2>
                
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <div
                        v-for="game in props.games"
                        :key="game.id"
                        class="cursor-pointer rounded-lg border border-white/20 bg-white/5 p-6 transition-all hover:border-white/40 hover:bg-white/10"
                        @click="selectGame(game)"
                    >
                        <div class="mb-4 flex items-start justify-between">
                            <div class="text-4xl">🏆</div>
                            <div class="flex space-x-2">
                                <span
                                    v-if="game.category"
                                    :class="getCategoryColor(game.category)"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                >
                                    {{ game.category }}
                                </span>
                                <span
                                    v-if="game.difficulty"
                                    :class="getDifficultyColor(game.difficulty)"
                                    class="rounded-full px-2 py-1 text-xs font-medium"
                                >
                                    {{ game.difficulty }}
                                </span>
                            </div>
                        </div>
                        
                        <h3 class="mb-2 text-xl font-bold text-white">{{ game.title }}</h3>
                        <p class="mb-4 text-sm text-purple-200">{{ game.description }}</p>
                        
                        <div class="mb-4 flex items-center text-sm text-purple-200">
                            <span class="mr-4">🎮 Players: {{ game.players_count || 'N/A' }}</span>
                            <span>⭐ Best: {{ game.best_score || 'No scores yet' }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <button 
                                class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-4 py-2 text-sm font-bold text-white transition-all hover:from-purple-700 hover:to-pink-700"
                                @click.stop="showGameLeaderboard(game)"
                            >
                                View Leaderboard
                            </button>
                            <button 
                                class="rounded-lg border border-white/20 px-4 py-2 text-sm text-white transition-all hover:bg-white/10"
                                @click.stop="playGame(game)"
                            >
                                Play Now
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Global Leaderboard Button -->
                <div class="mt-8 text-center">
                    <button
                        @click="showGlobalLeaderboard = true"
                        class="mr-4 rounded-lg bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-3 font-bold text-white transition-all hover:from-blue-700 hover:to-purple-700"
                    >
                        🌟 View Global Leaderboard
                    </button>
                    <Link
                        :href="route('games')"
                        class="inline-flex transform items-center space-x-2 rounded-lg bg-gradient-to-r from-green-600 to-teal-600 px-6 py-3 font-bold text-white transition-all hover:scale-105 hover:from-green-700 hover:to-teal-700"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"
                            />
                        </svg>
                        <span>🎮 Play Games</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Global Leaderboard Modal -->
        <div
            v-if="showGlobalLeaderboard"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            @click="showGlobalLeaderboard = false"
        >
            <div class="w-full max-w-2xl rounded-lg border border-white/20 bg-gray-900/95 p-8 backdrop-blur-sm" @click.stop>
                <div class="text-center">
                    <div class="mb-4 text-6xl">🚀</div>
                    <h3 class="mb-4 text-2xl font-bold text-white">Coming Soon!</h3>
                    <p class="mb-6 text-purple-200">We're working on bringing you comprehensive rankings across all games!</p>
                    <button
                        @click="showGlobalLeaderboard = false"
                        class="rounded-lg bg-gradient-to-r from-purple-600 to-pink-600 px-6 py-3 font-bold text-white"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>

        <!-- Coming Soon Modal -->
        <div
            v-if="showComingSoon"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            @click="showComingSoon = false"
        >
            <div class="w-full max-w-md rounded-lg border border-white/20 bg-gray-900/95 p-8 backdrop-blur-sm" @click.stop>
                <div class="text-center">
                    <div class="mb-4 text-6xl">🚀</div>
                    <p class="font-medium text-white">We're working on bringing you comprehensive rankings across all games!</p>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Game {
    id: number;
    title: string;
    description: string;
    category?: string;
    difficulty?: string;
    players_count?: number;
    best_score?: number;
}

interface Props {
    games: Game[];
    activePlayers: number;
    todaysGames: number;
}

const props = defineProps<Props>();

const showGlobalLeaderboard = ref(false);
const showComingSoon = ref(false);

const totalScores = computed(() => {
    return props.games.reduce((total, game) => total + (game.players_count || 0), 0);
});

const selectGame = (game: Game) => {
    console.log('Selected game:', game.title);
    showComingSoon.value = true;
};

const showGameLeaderboard = (game: Game) => {
    console.log('Show leaderboard for:', game.title);
    showComingSoon.value = true;
};

const playGame = (game: Game) => {
    console.log('Play game:', game.title);
    showComingSoon.value = true;
};

const getCategoryColor = (category?: string) => {
    if (!category) return 'bg-gray-500/20 text-gray-300';
    
    const colors: Record<string, string> = {
        'puzzle': 'bg-blue-500/20 text-blue-300',
        'arcade': 'bg-orange-500/20 text-orange-300',
        'action': 'bg-red-500/20 text-red-300',
        'strategy': 'bg-purple-500/20 text-purple-300',
        'casual': 'bg-green-500/20 text-green-300',
    };
    return colors[category] || 'bg-gray-500/20 text-gray-300';
};

const getDifficultyColor = (difficulty?: string) => {
    if (!difficulty) return 'bg-gray-500/20 text-gray-300';
    
    const colors: Record<string, string> = {
        'easy': 'bg-green-500/20 text-green-300',
        'medium': 'bg-yellow-500/20 text-yellow-300',
        'hard': 'bg-red-500/20 text-red-300',
    };
    return colors[difficulty] || 'bg-gray-500/20 text-gray-300';
};
</script>
