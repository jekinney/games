<template>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <!-- Live Player Count -->
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                🎮 Live Activity
            </h3>
            <div class="flex items-center space-x-2">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ activePlayersCount }} {{ activePlayersCount === 1 ? 'player' : 'players' }} online
                </span>
            </div>
        </div>

        <!-- Active Players List -->
        <div v-if="showPlayerList && activePlayers.length > 0" class="mb-4">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Active Players:
            </h4>
            <div class="space-y-1">
                <div 
                    v-for="player in activePlayers.slice(0, 5)" 
                    :key="player.session_id"
                    class="flex items-center space-x-2 text-xs text-gray-600 dark:text-gray-400"
                >
                    <div class="w-1.5 h-1.5 bg-green-400 rounded-full"></div>
                    <span>{{ player.user_name }}</span>
                    <span class="text-gray-400">{{ formatTimeAgo(player.started_at) }}</span>
                </div>
                <div v-if="activePlayers.length > 5" class="text-xs text-gray-500 dark:text-gray-400 ml-3">
                    +{{ activePlayers.length - 5 }} more...
                </div>
            </div>
        </div>

        <!-- Recent Scores -->
        <div v-if="showRecentScores && recentScores.length > 0">
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                🏆 Recent Scores:
            </h4>
            <div class="space-y-2">
                <div 
                    v-for="score in recentScores.slice(0, 3)" 
                    :key="`${score.user_id}-${score.timestamp}`"
                    class="flex items-center justify-between p-2 bg-gray-50 dark:bg-gray-700 rounded text-xs"
                >
                    <div class="flex items-center space-x-2">
                        <span class="font-medium text-gray-900 dark:text-white">
                            {{ score.user_name }}
                        </span>
                        <span class="px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full text-xs">
                            #{{ score.rank }}
                        </span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="font-bold text-green-600 dark:text-green-400">
                            {{ formatScore(score.score) }}
                        </span>
                        <span class="text-gray-400">
                            {{ formatTimeAgo(score.timestamp) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Session Controls -->
        <div v-if="showControls" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
            <div class="flex space-x-2">
                <button
                    v-if="!isGameActive"
                    @click="startGame"
                    class="flex-1 px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded text-sm font-medium transition-colors"
                >
                    🎮 Start Playing
                </button>
                <button
                    v-else
                    @click="endGame"
                    class="flex-1 px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded text-sm font-medium transition-colors"
                >
                    🛑 Stop Playing
                </button>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { useRealTimeGame } from '../composables/useRealTimeGame';

interface Props {
    gameSlug: string;
    showPlayerList?: boolean;
    showRecentScores?: boolean;
    showControls?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showPlayerList: true,
    showRecentScores: true,
    showControls: true,
});

const {
    activePlayers,
    activePlayersCount,
    recentScores,
    sessionId,
    startGameSession,
    endGameSession,
} = useRealTimeGame(props.gameSlug);

const isGameActive = computed(() => !!sessionId.value);

// Format score with commas
const formatScore = (score: number): string => {
    return score.toLocaleString();
};

// Format relative time
const formatTimeAgo = (timestamp: string): string => {
    const now = new Date();
    const time = new Date(timestamp);
    const diffInSeconds = Math.floor((now.getTime() - time.getTime()) / 1000);
    
    if (diffInSeconds < 60) {
        return 'just now';
    } else if (diffInSeconds < 3600) {
        const minutes = Math.floor(diffInSeconds / 60);
        return `${minutes}m ago`;
    } else if (diffInSeconds < 86400) {
        const hours = Math.floor(diffInSeconds / 3600);
        return `${hours}h ago`;
    } else {
        const days = Math.floor(diffInSeconds / 86400);
        return `${days}d ago`;
    }
};

// Game session controls
const startGame = () => {
    startGameSession();
};

const endGame = () => {
    endGameSession();
};
</script>
