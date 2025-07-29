<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Header -->
        <div class="border-b border-white/10 bg-black/20 p-6 backdrop-blur-sm">
            <div class="mx-auto flex max-w-7xl items-center justify-between">
                <div>
                    <h1 class="mb-2 text-4xl font-bold text-white">🎮 Games Hub</h1>
                    <p class="text-gray-300">Choose a game to play and test your skills!</p>
                </div>

                <!-- Navigation and User Dropdown -->
                <div class="flex items-center space-x-4">
                    <!-- Home Link -->
                    <Link
                        :href="route('home')"
                        class="flex items-center space-x-2 rounded-lg px-4 py-2 text-white/80 transition-all hover:bg-white/10 hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
                            />
                        </svg>
                        <span class="hidden sm:inline">Home</span>
                    </Link>

                    <!-- Leaderboard Link -->
                    <Link
                        :href="route('leaderboards')"
                        class="flex items-center space-x-2 rounded-lg px-4 py-2 text-white/80 transition-all hover:bg-white/10 hover:text-white"
                    >
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M16 7h-2v4h6V9c0-1.1-.9-2-2-2zm-6 2H8V7H6c-1.1 0-2 .9-2 2v2h6V9zm8 6H8v4h10v-4zm-8-2V9H2v2h8zm10 0V9h-8v2h8z" />
                        </svg>
                        <span class="hidden sm:inline">🏆 Leaderboards</span>
                    </Link>

                    <!-- Game Icon -->
                    <div class="flex items-center space-x-2 px-4 py-2 text-white/60">
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M7.5 10.5C7.5 11.33 6.83 12 6 12s-1.5-.67-1.5-1.5S5.17 9 6 9s1.5.67 1.5 1.5zM19.5 10.5c0 .83-.67 1.5-1.5 1.5s-1.5-.67-1.5-1.5S17.17 9 18 9s1.5.67 1.5 1.5zM17 6H7c-2.76 0-5 2.24-5 5v4c0 2.76 2.24 5 5 5h10c2.76 0 5-2.24 5-5v-4c0-2.76-2.24-5-5-5zM9 13H7v2H5v-2H3v-2h2V9h2v2h2v2zm5.5-1c-.83 0-1.5-.67-1.5-1.5S13.67 9 14.5 9s1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm2.5 3c-.83 0-1.5-.67-1.5-1.5S16.17 12 17 12s1.5.67 1.5 1.5S17.83 15 17 15z"
                            />
                        </svg>
                        <span class="hidden text-sm sm:inline">Games</span>
                    </div>

                    <!-- User Menu Dropdown -->
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button
                                variant="outline"
                                size="icon"
                                class="relative h-10 w-10 rounded-full border-white/20 bg-white/10 hover:bg-white/20"
                            >
                                <Avatar class="h-8 w-8">
                                    <AvatarImage :src="user.avatar" :alt="user.name" />
                                    <AvatarFallback class="bg-purple-600 text-sm text-white">
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
            <div class="mx-auto max-w-6xl">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
                    <!-- Memory Test Game Card -->
                    <div
                        class="group cursor-pointer rounded-lg border border-white/20 bg-white/10 p-6 backdrop-blur-sm transition-all hover:bg-white/20"
                    >
                        <div class="text-center">
                            <div class="mb-4 text-6xl transition-transform group-hover:scale-110">🧠</div>
                            <h3 class="mb-2 text-xl font-bold text-white">Memory Test Game</h3>
                            <p class="mb-4 text-sm text-gray-300">
                                Test your memory skills by finding matching pairs of cards. Progressive difficulty levels challenge your brain!
                            </p>
                            <div class="mb-4 flex justify-center space-x-2">
                                <span class="rounded-full bg-purple-600 px-2 py-1 text-xs text-white">Puzzle</span>
                                <span class="rounded-full bg-green-600 px-2 py-1 text-xs text-white">Medium</span>
                                <span class="rounded-full bg-blue-600 px-2 py-1 text-xs text-white">1 Player</span>
                            </div>
                            <button
                                @click="playGame('/games/memory-test-game.html')"
                                class="mb-3 w-full transform rounded-lg bg-gradient-to-r from-purple-600 to-blue-600 px-6 py-3 font-bold text-white transition-all hover:scale-105 hover:from-purple-700 hover:to-blue-700"
                            >
                                Play Now
                            </button>

                            <!-- Leaderboard Link -->
                            <Link
                                :href="route('leaderboards.game', 'memory-test-game')"
                                class="flex w-full items-center justify-center space-x-2 rounded-lg border border-white/20 bg-white/10 px-4 py-2 font-medium text-white transition-all hover:border-white/40 hover:bg-white/20"
                            >
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                    <path
                                        d="M6 9H4.5a2.5 2.5 0 0 0 0 5H6m0-5a2.5 2.5 0 0 1 0-5H4.5a2.5 2.5 0 0 0 0 5M6 9v6a6 6 0 0 0 12 0V9M6 9a6 6 0 1 1 12 0v0M18 9h1.5a2.5 2.5 0 0 0 0-5H18m0 5a2.5 2.5 0 0 1 0 5h1.5a2.5 2.5 0 0 0 0-5M18 9v6"
                                    />
                                </svg>
                                <span>🏆 View Leaderboard</span>
                            </Link>
                        </div>
                    </div>

                    <!-- Placeholder for more games -->
                    <div class="rounded-lg border border-dashed border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                        <div class="text-center text-gray-400">
                            <div class="mb-4 text-4xl">➕</div>
                            <h3 class="mb-2 text-lg font-semibold">More Games Coming Soon</h3>
                            <p class="text-sm">We're working on adding more exciting games to the hub!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Game Modal -->
        <div v-if="showGameModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 backdrop-blur-sm" @click="closeGame">
            <div class="h-[80vh] w-full max-w-6xl overflow-hidden rounded-lg bg-white" @click.stop>
                <div class="flex items-center justify-between border-b bg-gray-50 p-4">
                    <h2 class="text-lg font-semibold">Playing Game</h2>
                    <button @click="closeGame" class="text-2xl font-bold text-gray-500 hover:text-gray-700">×</button>
                </div>
                <iframe :src="currentGameUrl" class="h-[calc(100%-60px)] w-full border-0" frameborder="0"></iframe>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { User } from '@/types/index.d';
import { Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

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
