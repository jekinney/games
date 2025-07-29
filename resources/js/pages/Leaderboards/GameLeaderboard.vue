<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head :title="`${game.name} - Leaderboard`" />

                <div class="min-h-screen bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900">
            <div class="container mx-auto px-4 py-8">
                
                <!-- Page Header -->
                <div class="mb-8 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Title Section -->
                    <div class="flex items-center space-x-4">
                        <button
                            @click="goBack"
                            class="flex items-center space-x-2 rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-800 backdrop-blur-sm transition-all hover:bg-gray-200"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            <span>Back</span>
                        </button>

                        <div>
                            <h1 class="mb-2 text-3xl font-bold text-white lg:text-4xl">
                                🏆 {{ game.name }} Leaderboard
                            </h1>
                            <p class="text-purple-200">
                                Best scores per player in {{ game.name }}!
                            </p>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-wrap items-center gap-3">
                        <button
                            @click="refreshLeaderboard"
                            class="flex transform items-center space-x-2 rounded-lg bg-gradient-to-r from-green-500 to-emerald-500 px-4 py-2 text-white transition-all hover:scale-105 hover:from-green-600 hover:to-emerald-600"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                                />
                            </svg>
                            <span>Refresh</span>
                        </button>

                        <Link
                            :href="`/games/${game.slug}`"
                            class="flex transform items-center space-x-2 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 px-4 py-2 text-white transition-all hover:scale-105 hover:from-purple-600 hover:to-pink-600"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"
                                />
                            </svg>
                            <span>Play Now</span>
                        </Link>

                        <Link
                            :href="route('leaderboards')"
                            class="flex items-center space-x-2 rounded-lg border border-gray-300 bg-gray-100 px-4 py-2 text-gray-800 backdrop-blur-sm transition-all hover:bg-gray-200"
                        >
                            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M16 7h-2v4h6V9c0-1.1-.9-2-2-2zm-6 2H8V7H6c-1.1 0-2 .9-2 2v2h6V9zm8 6H8v4h10v-4zm-8-2V9H2v2h8zm10 0V9h-8v2h8z"
                                />
                            </svg>
                            <span>All Leaderboards</span>
                        </Link>
                    </div>
                </div>

                <!-- Time Period Filter -->
                <div class="mb-8">
                    <h3 class="mb-4 text-lg font-semibold text-white">
                        📅 Time Period
                    </h3>
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="(label, key) in timeframes"
                            :key="key"
                            @click="changeTimeframe(key)"
                            :class="[
                                'rounded-lg px-4 py-2 text-sm font-medium transition-all',
                                currentTimeframe === key
                                    ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg'
                                    : 'border border-gray-300 bg-gray-100 text-gray-800 backdrop-blur-sm hover:bg-gray-200',
                            ]"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>

                <!-- Personal Stats Section -->
                <div 
                    v-if="userData" 
                    class="mb-8 rounded-xl border border-gray-300 bg-gray-100 p-6 backdrop-blur-sm"
                >
                    <h2 class="mb-6 flex items-center text-xl font-bold text-gray-800">
                        <span class="mr-2">👤</span>
                        Your Performance
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-6 md:grid-cols-4">
                        <div class="text-center">
                            <div class="mb-1 text-3xl font-bold text-gray-800">
                                {{ userData.score.toLocaleString() }}
                            </div>
                            <div class="text-sm text-gray-600">Best Score</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="mb-1 text-3xl font-bold text-gray-800">
                                #{{ userData.rank || 'N/A' }}
                            </div>
                            <div class="text-sm text-gray-600">Current Rank</div>
                        </div>
                        
                        <div v-if="userData.level_reached" class="text-center">
                            <div class="mb-1 text-3xl font-bold text-gray-800">
                                {{ userData.level_reached }}
                            </div>
                            <div class="text-sm text-gray-600">Highest Level</div>
                        </div>
                        
                        <div v-if="userData.time_played" class="text-center">
                            <div class="mb-1 text-3xl font-bold text-gray-800">
                                {{ formatTime(userData.time_played) }}
                            </div>
                            <div class="text-sm text-gray-600">Best Time</div>
                        </div>
                    </div>
                </div>

                <!-- Main Leaderboard -->
                <div class="overflow-hidden rounded-xl border border-gray-300 bg-gray-100 backdrop-blur-sm">
                    <!-- Table Header -->
                    <div class="border-b border-gray-300 p-6">
                        <h2 class="mb-2 flex items-center text-2xl font-bold text-gray-800">
                            <span class="mr-3">🏆</span>
                            Top Players - {{ timeframes[currentTimeframe] }}
                        </h2>
                        <p class="text-gray-600">
                            Showing the best score per player
                        </p>
                    </div>

                    <!-- Leaderboard Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-200">
                                <tr>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                        Rank
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                        Player
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                        Score
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                        Level
                                    </th>
                                    <th class="px-6 py-4 text-left font-semibold text-gray-700">
                                        Date Achieved
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr
                                    v-for="(score, index) in leaderboard"
                                    :key="score.id"
                                    :class="[
                                        'transition-colors hover:bg-gray-200',
                                        score.user_id === $page.props.auth?.user?.id 
                                            ? 'bg-purple-100 ring-1 ring-purple-400/50' 
                                            : '',
                                    ]"
                                >
                                    <!-- Rank Column -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <span v-if="index < 3" class="text-2xl">
                                                {{ index === 0 ? '🥇' : index === 1 ? '🥈' : '🥉' }}
                                            </span>
                                            <span class="text-lg font-bold text-gray-800">
                                                #{{ index + 1 }}
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Player Column -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-r from-purple-400 to-pink-400 text-sm font-bold text-white"
                                            >
                                                {{ score.user.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="font-medium text-gray-800">
                                                    {{ score.user.name }}
                                                </span>
                                                <span
                                                    v-if="score.user_id === $page.props.auth?.user?.id"
                                                    class="text-xs text-purple-600"
                                                >
                                                    (You)
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Score Column -->
                                    <td class="px-6 py-4">
                                        <span class="text-xl font-bold text-gray-800">
                                            {{ score.score.toLocaleString() }}
                                        </span>
                                    </td>
                                    
                                    <!-- Level Column -->
                                    <td class="px-6 py-4">
                                        <span class="text-gray-600">
                                            {{ score.level_reached || 'N/A' }}
                                        </span>
                                    </td>
                                    
                                    <!-- Date Column -->
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-600">
                                            {{ formatDate(score.created_at) }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <!-- Empty State -->
                        <div v-if="leaderboard.length === 0" class="py-16 text-center">
                            <div class="mb-6 text-8xl">🏆</div>
                            <h3 class="mb-3 text-2xl font-bold text-gray-800">
                                No scores yet!
                            </h3>
                            <p class="mb-6 text-lg text-gray-600">
                                Be the first to set a score in {{ game.name }}.
                            </p>
                            <Link
                                :href="`/games/${game.slug}`"
                                class="inline-flex transform items-center space-x-3 rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 px-8 py-4 text-lg font-semibold text-white transition-all hover:scale-105 hover:from-purple-600 hover:to-pink-600"
                            >
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5z"
                                    />
                                </svg>
                                <span>Play {{ game.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Load More Section -->
                <div v-if="leaderboard.length >= 10" class="mt-8 text-center">
                    <button
                        @click="loadMore"
                        :disabled="loading"
                        class="transform rounded-lg bg-gradient-to-r from-purple-500 to-pink-500 px-8 py-3 text-lg font-semibold text-white transition-all hover:scale-105 hover:from-purple-600 hover:to-pink-600 disabled:scale-100 disabled:from-gray-400 disabled:to-gray-500"
                    >
                        {{ loading ? 'Loading...' : 'Load More Players' }}
                    </button>
                </div>
                
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface Score {
    id: number;
    user_id: number;
    user: User;
    score: number;
    level_reached?: number;
    time_played?: number;
    created_at: string;
}

interface Game {
    id: number;
    name: string;
    slug: string;
    category: string;
}

interface Props {
    game: Game;
    leaderboard: Score[];
    currentTimeframe: '30' | '60' | '90' | '365' | 'all';
    userData?: Score & { rank?: number };
}

const props = defineProps<Props>();

const loading = ref(false);
const leaderboard = ref(props.leaderboard);

const timeframes = {
    '30': 'Last 30 Days',
    '60': 'Last 60 Days',
    '90': 'Last 90 Days',
    '365': 'This Year',
    all: 'All Time',
};

const currentTimeframe = ref(props.currentTimeframe);

const breadcrumbItems = computed(() => [
    { title: 'Dashboard', href: route('dashboard') },
    { title: 'Leaderboards', href: route('leaderboards') },
    { title: props.game.name, href: '#' },
]);

const goBack = () => {
    router.visit(route('leaderboards'));
};

const changeTimeframe = (timeframe: string) => {
    if (timeframe === currentTimeframe.value) return;

    loading.value = true;
    router.get(
        route('leaderboards.game', props.game.slug),
        {
            timeframe,
        },
        {
            preserveScroll: true,
            onSuccess: (page: any) => {
                leaderboard.value = page.props.leaderboard;
                currentTimeframe.value = timeframe as '30' | '60' | '90' | '365' | 'all';
            },
            onFinish: () => {
                loading.value = false;
            },
        },
    );
};

const refreshLeaderboard = () => {
    router.reload();
};

const loadMore = () => {
    // Implement pagination if needed
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const formatTime = (seconds: number) => {
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};
</script>
