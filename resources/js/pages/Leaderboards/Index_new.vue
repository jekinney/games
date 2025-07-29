<template>
    <Head title="Leaderboards" />
    
    <MainLayout>
        <div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
            <div class="container mx-auto px-4 py-8">
                <!-- Hero Section -->
                <div class="mb-12 text-center">
                    <h1 class="mb-4 text-5xl font-bold text-white">🏆 Game Leaderboards</h1>
                    <p class="mb-8 text-xl text-purple-200">Compete with players worldwide and climb the ranks!</p>

                    <!-- Quick Stats -->
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

                <!-- Game Selection -->
                <div class="mb-8">
                    <h2 class="mb-6 text-center text-3xl font-bold text-white">Choose a Game to View Leaderboard</h2>
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="game in props.games"
                            :key="game.id"
                            class="group transform cursor-pointer rounded-xl border border-white/20 bg-white/5 backdrop-blur-sm transition-all hover:scale-105 hover:border-purple-400/50 hover:bg-white/10"
                            @click="viewGameLeaderboard(game)"
                        >
                            <div class="p-6">
                                <div class="mb-4 flex items-start justify-between">
                                    <div class="text-4xl">🏆</div>
                                    <div class="flex space-x-2">
                                        <span 
                                            class="rounded-full px-2 py-1 text-xs font-medium"
                                            :class="getCategoryColor(game.category)"
                                        >
                                            {{ game.category }}
                                        </span>
                                        <span 
                                            v-if="game.difficulty"
                                            class="rounded-full px-2 py-1 text-xs font-medium"
                                            :class="getDifficultyColor(game.difficulty)"
                                        >
                                            {{ game.difficulty }}
                                        </span>
                                    </div>
                                </div>
                                
                                <h3 class="mb-2 text-lg font-bold text-white transition-colors group-hover:text-purple-300">
                                    {{ game.name }}
                                </h3>
                                
                                <div class="mb-4 flex items-center text-sm text-purple-200">
                                    <svg class="mr-1 h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    View Leaderboard
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <div class="text-xs text-purple-300">
                                        {{ game.estimated_play_time ? `~${game.estimated_play_time} min` : 'Quick play' }}
                                    </div>
                                    <div class="text-white/60 transition-colors group-hover:text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            class="flex transform items-center space-x-2 rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-3 font-bold text-white transition-all hover:scale-105 hover:from-purple-700 hover:to-blue-700"
                        >
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                            </svg>
                            <span>🌟 Global Leaderboard</span>
                        </button>

                        <Link
                            :href="route('games')"
                            class="flex transform items-center space-x-2 rounded-lg bg-gradient-to-r from-green-600 to-teal-600 px-6 py-3 font-bold text-white transition-all hover:scale-105 hover:from-green-700 hover:to-teal-700"
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
        </div>

        <!-- Global Leaderboard Modal -->
        <div
            v-if="showGlobalLeaderboard"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm"
            @click="showGlobalLeaderboard = false"
        >
            <div class="max-h-[80vh] w-full max-w-4xl overflow-hidden rounded-lg border border-white/20 bg-white/10 backdrop-blur-sm" @click.stop>
                <div class="flex items-center justify-between border-b border-white/20 p-6">
                    <h2 class="text-2xl font-bold text-white">🌟 Global Leaderboard</h2>
                    <button @click="showGlobalLeaderboard = false" class="text-2xl text-white/60 transition-colors hover:text-white">×</button>
                </div>
                <div class="max-h-[60vh] overflow-y-auto p-6">
                    <p class="mb-4 text-center text-lg text-purple-200">Coming soon! Global rankings across all games.</p>
                    <div class="text-center">
                        <div class="mb-4 text-6xl">🚀</div>
                        <p class="font-medium text-white">We're working on bringing you comprehensive rankings across all games!</p>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<script setup lang="ts">
import MainLayout from '@/layouts/MainLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface Game {
    id: number;
    name: string;
    slug: string;
    category: string;
    difficulty?: string;
    estimated_play_time?: number;
}

interface Props {
    games: Game[];
    totalGames: number;
    activePlayers: number;
    todaysGames: number;
}

const props = defineProps<Props>();

const showGlobalLeaderboard = ref(false);

const totalScores = computed(() => {
    return props.totalGames * 50; // Estimate based on total games
});

const viewGameLeaderboard = (game: Game) => {
    router.visit(`/leaderboards/${game.slug}`);
};

const getCategoryColor = (category: string) => {
    const colors: Record<string, string> = {
        'action': 'bg-red-500/20 text-red-300',
        'puzzle': 'bg-purple-500/20 text-purple-300',
        'strategy': 'bg-blue-500/20 text-blue-300',
        'arcade': 'bg-orange-500/20 text-orange-300',
        'simulation': 'bg-green-500/20 text-green-300',
        'sports': 'bg-indigo-500/20 text-indigo-300',
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
