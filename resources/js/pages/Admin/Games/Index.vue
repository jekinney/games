<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Gamepad2, Plus, Search, Star, Trash2, Users } from 'lucide-vue-next';
import { ref } from 'vue';

interface Game {
    id: number;
    name: string;
    slug: string;
    description: string;
    category: string;
    difficulty: string;
    is_active: boolean;
    is_featured: boolean;
    play_count: number;
    average_rating: number;
    created_at: string;
    image_url?: string;
    thumbnail_url?: string;
}

interface PaginatedGames {
    data: Game[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

interface Filters {
    search?: string;
    category?: string;
    status?: string;
}

const props = defineProps<{
    games: PaginatedGames;
    filters: Filters;
    categories: string[];
}>();

const searchQuery = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedStatus = ref(props.filters.status || '');

// Function to get category color
const getCategoryColor = (category: string) => {
    const colors = {
        action: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        puzzle: 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300',
        strategy: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        arcade: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        simulation: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        sports: 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300',
    };
    return colors[category as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

// Function to get difficulty color
const getDifficultyColor = (difficulty: string) => {
    const colors = {
        easy: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        medium: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        hard: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
    };
    return colors[difficulty as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
};

// Function to handle search and filters
const handleFilter = () => {
    router.get(
        route('admin.games'),
        {
            search: searchQuery.value,
            category: selectedCategory.value,
            status: selectedStatus.value,
        },
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Function to clear filters
const clearFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = '';
    selectedStatus.value = '';
    router.get(
        route('admin.games'),
        {},
        {
            preserveState: true,
            replace: true,
        },
    );
};

// Function to delete game
const deleteGame = (game: Game) => {
    if (confirm(`Are you sure you want to delete "${game.name}"? This action cannot be undone.`)) {
        router.delete(route('admin.games.destroy', game.id));
    }
};
</script>

<template>
    <AppLayout>
        <Head title="Game Management - Admin" />

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Game Management</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage games, metadata, and game files</p>
                        </div>
                        <div class="flex items-center space-x-4">
                            <Link :href="route('admin.games.create')">
                                <Button>
                                    <Plus class="mr-2 h-4 w-4" />
                                    Add New Game
                                </Button>
                            </Link>
                            <Link href="/admin">
                                <Button variant="outline"> ← Back to Dashboard </Button>
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <Card class="mb-6">
                    <CardContent class="p-6">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
                            <div class="relative">
                                <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                <Input v-model="searchQuery" type="text" placeholder="Search games..." class="pl-10" @keyup.enter="handleFilter" />
                            </div>

                            <select
                                v-model="selectedCategory"
                                @change="handleFilter"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category.charAt(0).toUpperCase() + category.slice(1) }}
                                </option>
                            </select>

                            <select
                                v-model="selectedStatus"
                                @change="handleFilter"
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>

                            <div class="flex space-x-2">
                                <Button @click="handleFilter" class="flex-1"> Search </Button>
                                <Button @click="clearFilters" variant="outline" class="flex-1"> Clear </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Games Grid -->
                <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                    <Card v-for="game in games.data" :key="game.id" class="overflow-hidden">
                        <div v-if="game.thumbnail_url" class="aspect-video bg-gray-200 dark:bg-gray-700">
                            <img :src="game.thumbnail_url" :alt="game.name" class="h-full w-full object-cover" />
                        </div>
                        <div v-else class="flex aspect-video items-center justify-center bg-gray-200 dark:bg-gray-700">
                            <Gamepad2 class="h-12 w-12 text-gray-400" />
                        </div>

                        <CardHeader class="pb-3">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <CardTitle class="text-lg">{{ game.name }}</CardTitle>
                                    <CardDescription class="mt-1 line-clamp-2">
                                        {{ game.description }}
                                    </CardDescription>
                                </div>
                                <div class="ml-2 flex items-center space-x-1">
                                    <Star v-if="game.is_featured" class="h-4 w-4 text-yellow-500" />
                                </div>
                            </div>
                        </CardHeader>

                        <CardContent class="pt-0">
                            <div class="mb-4 flex flex-wrap gap-2">
                                <Badge :class="getCategoryColor(game.category)">
                                    {{ game.category.charAt(0).toUpperCase() + game.category.slice(1) }}
                                </Badge>
                                <Badge :class="getDifficultyColor(game.difficulty)">
                                    {{ game.difficulty.charAt(0).toUpperCase() + game.difficulty.slice(1) }}
                                </Badge>
                                <Badge v-if="game.is_active" variant="outline" class="border-green-600 text-green-600"> Active </Badge>
                                <Badge v-else variant="outline" class="border-red-600 text-red-600"> Inactive </Badge>
                            </div>

                            <div class="mb-4 flex items-center justify-between text-sm text-gray-600 dark:text-gray-400">
                                <div class="flex items-center space-x-1">
                                    <Users class="h-4 w-4" />
                                    <span>{{ game.play_count }} plays</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <Star class="h-4 w-4" />
                                    <span>{{ game.average_rating }}/5</span>
                                </div>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Link :href="route('admin.games.edit', game.id)" class="flex-1">
                                    <Button variant="outline" size="sm" class="w-full">
                                        <Edit class="mr-2 h-4 w-4" />
                                        Edit
                                    </Button>
                                </Link>
                                <Button variant="outline" size="sm" @click="deleteGame(game)" class="text-red-600 hover:bg-red-50 hover:text-red-700">
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Empty State -->
                <div v-if="games.data.length === 0" class="py-12 text-center">
                    <Gamepad2 class="mx-auto mb-4 h-16 w-16 text-gray-400" />
                    <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">No games found</h3>
                    <p class="mb-4 text-gray-600 dark:text-gray-400">Get started by creating your first game.</p>
                    <Link :href="route('admin.games.create')">
                        <Button>
                            <Plus class="mr-2 h-4 w-4" />
                            Add New Game
                        </Button>
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="games.last_page > 1" class="flex items-center justify-between">
                    <div class="text-sm text-gray-600 dark:text-gray-400">Showing {{ games.from }} to {{ games.to }} of {{ games.total }} games</div>
                    <div class="flex items-center space-x-2">
                        <Button
                            v-for="page in Array.from({ length: games.last_page }, (_, i) => i + 1)"
                            :key="page"
                            :variant="page === games.current_page ? 'default' : 'outline'"
                            size="sm"
                            @click="
                                router.get(
                                    route('admin.games', {
                                        page,
                                        search: searchQuery,
                                        category: selectedCategory,
                                        status: selectedStatus,
                                    }),
                                )
                            "
                        >
                            {{ page }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
