<template>
    <div class="border-b border-white/10 bg-black/10 py-4 backdrop-blur-sm">
        <div class="mx-auto max-w-6xl px-4">
            <div class="mb-3 flex items-center justify-between">
                <button
                    @click="showFilters = !showFilters"
                    class="flex items-center space-x-2 text-base font-semibold text-white transition-colors hover:text-purple-300"
                >
                    <span>Filters</span>
                    <svg
                        :class="{ 'rotate-180': showFilters }"
                        class="h-4 w-4 transition-transform"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </button>
                <button
                    v-if="hasActiveFilters"
                    @click="$emit('clear-all')"
                    class="text-xs text-purple-400 hover:text-purple-300"
                >
                    Clear All
                </button>
            </div>
            
            <div v-show="showFilters" class="grid grid-cols-1 gap-3 lg:grid-cols-3">
                <!-- Category Filters -->
                <div>
                    <span class="mb-1.5 block text-xs font-medium text-gray-400">Categories</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="category in filterOptions.categories"
                            :key="`category-${category}`"
                            @click="$emit('toggle-filter', category)"
                            :class="getFilterButtonClass(category)"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-all"
                        >
                            {{ category }}
                        </button>
                    </div>
                </div>

                <!-- Difficulty Filters -->
                <div>
                    <span class="mb-1.5 block text-xs font-medium text-gray-400">Difficulty</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="difficulty in filterOptions.difficulties"
                            :key="`difficulty-${difficulty}`"
                            @click="$emit('toggle-filter', difficulty)"
                            :class="getFilterButtonClass(difficulty)"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-all"
                        >
                            {{ difficulty }}
                        </button>
                    </div>
                </div>

                <!-- Player Count Filters -->
                <div>
                    <span class="mb-1.5 block text-xs font-medium text-gray-400">Players</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button
                            v-for="playerCount in filterOptions.playerCounts"
                            :key="`players-${playerCount}`"
                            @click="$emit('toggle-filter', playerCount)"
                            :class="getFilterButtonClass(playerCount)"
                            class="rounded px-2.5 py-1 text-xs font-medium transition-all"
                        >
                            {{ playerCount }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue';

interface FilterOptions {
    categories: string[];
    difficulties: string[];
    playerCounts: string[];
}

interface Props {
    filterOptions: FilterOptions;
    activeFilters: string[];
    searchQuery?: string;
    storageKey?: string;
}

const props = withDefaults(defineProps<Props>(), {
    searchQuery: '',
    storageKey: 'gameFiltersVisible'
});

defineEmits<{
    'toggle-filter': [filter: string];
    'clear-all': [];
}>();

// Initialize showFilters from session storage or default to false
const showFilters = ref((() => {
    if (typeof window !== 'undefined') {
        const saved = sessionStorage.getItem(props.storageKey);
        return saved ? JSON.parse(saved) : false;
    }
    return false;
})());

// Watch for changes to showFilters and save to session storage
watch(showFilters, (newValue) => {
    if (typeof window !== 'undefined') {
        sessionStorage.setItem(props.storageKey, JSON.stringify(newValue));
    }
});

const hasActiveFilters = computed(() => {
    return props.activeFilters.length > 0 || props.searchQuery;
});

const getFilterButtonClass = (filter: string) => {
    const isActive = props.activeFilters.includes(filter);
    return isActive
        ? 'bg-gradient-to-r from-purple-500 to-pink-500 text-white shadow-lg'
        : 'border border-white/20 bg-white/10 text-white backdrop-blur-sm hover:bg-white/20';
};
</script>

 
 