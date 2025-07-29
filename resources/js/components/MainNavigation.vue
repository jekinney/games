<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { getInitials } from '@/composables/useInitials';
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronDown, Gamepad2, Home, LogOut, Settings, Trophy, User as UserIcon } from 'lucide-vue-next';
import { computed } from 'vue';

const page = usePage();
const auth = computed(() => page.props.auth);

const handleLogout = () => {
    router.post('/logout');
};

const isCurrentRoute = (url: string) => page.url === url;
</script>

<template>
    <nav class="border-b border-white/10 bg-black/20 backdrop-blur-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <!-- Logo -->
                <div class="flex items-center">
                    <Link href="/" class="flex items-center">
                        <Gamepad2 class="h-8 w-8 text-purple-400" />
                        <span class="ml-2 text-xl font-bold text-white">Games Hub</span>
                    </Link>
                </div>

                <!-- Main Navigation -->
                <div class="hidden md:flex md:items-center md:space-x-8">
                    <Link 
                        href="/" 
                        class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="isCurrentRoute('/') ? 'text-purple-400' : 'text-gray-300 hover:text-white'"
                    >
                        <Home class="mr-2 h-4 w-4" />
                        Home
                    </Link>
                    
                    <Link 
                        href="/games" 
                        class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="isCurrentRoute('/games') ? 'text-purple-400' : 'text-gray-300 hover:text-white'"
                    >
                        <Gamepad2 class="mr-2 h-4 w-4" />
                        Games
                    </Link>
                    
                    <Link 
                        href="/leaderboards" 
                        class="flex items-center rounded-md px-3 py-2 text-sm font-medium transition-colors"
                        :class="isCurrentRoute('/leaderboards') || page.url.startsWith('/leaderboards/') ? 'text-purple-400' : 'text-gray-300 hover:text-white'"
                    >
                        <Trophy class="mr-2 h-4 w-4" />
                        Leaderboards
                    </Link>
                </div>

                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <!-- Mobile menu items -->
                    <div class="flex md:hidden">
                        <Link 
                            href="/games" 
                            class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                        >
                            Games
                        </Link>
                    </div>

                    <!-- Authentication -->
                    <div v-if="auth.user" class="flex items-center">
                        <DropdownMenu>
                            <DropdownMenuTrigger as-child>
                                <Button
                                    variant="ghost"
                                    class="flex items-center space-x-2 rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                                >
                                    <Avatar class="h-6 w-6">
                                        <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.name" />
                                        <AvatarFallback class="bg-purple-600 text-xs text-white">
                                            {{ getInitials(auth.user.name) }}
                                        </AvatarFallback>
                                    </Avatar>
                                    <span class="hidden sm:inline">{{ auth.user.name }}</span>
                                    <ChevronDown class="h-4 w-4" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" class="w-56">
                                <DropdownMenuItem as-child>
                                    <Link 
                                        href="/profile" 
                                        class="flex w-full items-center"
                                    >
                                        <UserIcon class="mr-2 h-4 w-4" />
                                        Profile
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuItem as-child>
                                    <Link 
                                        href="/settings/profile" 
                                        class="flex w-full items-center"
                                    >
                                        <Settings class="mr-2 h-4 w-4" />
                                        Settings
                                    </Link>
                                </DropdownMenuItem>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem as-child>
                                    <button 
                                        @click="handleLogout"
                                        class="flex w-full items-center"
                                    >
                                        <LogOut class="mr-2 h-4 w-4" />
                                        Log out
                                    </button>
                                </DropdownMenuItem>
                            </DropdownMenuContent>
                        </DropdownMenu>
                    </div>

                    <div v-else class="flex items-center space-x-4">
                        <Link
                            :href="route('login')"
                            class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                        >
                            Log in
                        </Link>
                        <Link
                            :href="route('register')"
                            class="rounded-md bg-purple-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-purple-700"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</template>
