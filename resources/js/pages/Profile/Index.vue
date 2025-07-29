<script setup lang="ts">
import Badge from '@/components/ui/badge/Badge.vue';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, Calendar, Gamepad2, Mail, Settings, Shield, Star, Trophy, User, Users } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    roles: string[];
    permissions: string[];
}

interface Stats {
    member_since: string;
    total_games_played: number;
    achievements_earned: number;
    community_points: number;
}

defineProps<{
    user: User;
    stats: Stats;
}>();

// Function to get role color
const getRoleColor = (role: string) => {
    const colors = {
        'super-admin': 'bg-red-500/20 text-red-300 border-red-500/30',
        admin: 'bg-orange-500/20 text-orange-300 border-orange-500/30',
        moderator: 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        player: 'bg-green-500/20 text-green-300 border-green-500/30',
    };
    return colors[role as keyof typeof colors] || 'bg-gray-500/20 text-gray-300 border-gray-500/30';
};

// Function to format role name
const formatRole = (role: string) => {
    return role
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};
</script>

<template>
    <Head title="My Profile - Games Hub" />

    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation -->
        <nav class="border-b border-white/10 bg-black/20 backdrop-blur-sm">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center">
                            <Gamepad2 class="h-8 w-8 text-purple-400" />
                            <span class="ml-2 text-xl font-bold text-white">Games Hub</span>
                        </Link>
                    </div>

                    <div class="flex items-center space-x-4">
                        <Link href="/" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"> Home </Link>
                        <Link href="/games" class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white">
                            Games
                        </Link>
                        <span class="rounded-md px-3 py-2 text-sm font-medium text-purple-400"> Profile </span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header Section -->
        <div class="py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-8 text-center">
                    <h1 class="mb-4 text-4xl font-bold text-white">Player Profile</h1>
                    <p class="mx-auto max-w-2xl text-xl text-gray-300">Manage your gaming identity and track your progress</p>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                    <!-- Left Column - Profile Info -->
                    <div class="space-y-6 lg:col-span-2">
                        <!-- Basic Information Card -->
                        <div class="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                            <div class="mb-6 flex items-center space-x-3">
                                <User class="h-6 w-6 text-purple-400" />
                                <h2 class="text-xl font-bold text-white">Profile Information</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div>
                                    <label class="text-sm font-medium text-gray-400">Player Name</label>
                                    <div class="mt-2 text-xl font-semibold text-white">{{ user.name }}</div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-400">Email</label>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <Mail class="h-4 w-4 text-gray-400" />
                                        <span class="text-white">{{ user.email }}</span>
                                        <Badge v-if="user.email_verified_at" class="border-green-500/30 bg-green-500/20 text-green-300">
                                            ✓ Verified
                                        </Badge>
                                        <Badge v-else class="border-yellow-500/30 bg-yellow-500/20 text-yellow-300"> ⚠ Unverified </Badge>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-400">Member Since</label>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <Calendar class="h-4 w-4 text-gray-400" />
                                        <span class="text-white">{{ stats.member_since }}</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="text-sm font-medium text-gray-400">Player Rank</label>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <Shield class="h-4 w-4 text-gray-400" />
                                        <div class="flex flex-wrap gap-2">
                                            <Badge v-for="role in user.roles" :key="role" :class="getRoleColor(role)">
                                                {{ formatRole(role) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 border-t border-white/10 pt-6">
                                <Link :href="route('profile.edit')">
                                    <Button class="bg-purple-600 text-white hover:bg-purple-700">
                                        <Settings class="mr-2 h-4 w-4" />
                                        Edit Profile
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Gaming Statistics Card -->
                        <div class="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                            <div class="mb-6 flex items-center space-x-3">
                                <Trophy class="h-6 w-6 text-purple-400" />
                                <h2 class="text-xl font-bold text-white">Gaming Statistics</h2>
                            </div>

                            <div class="grid grid-cols-1 gap-8 md:grid-cols-3">
                                <div class="text-center">
                                    <div class="mb-3 rounded-lg bg-purple-600/20 p-6">
                                        <Gamepad2 class="mx-auto mb-2 h-8 w-8 text-purple-400" />
                                        <div class="text-3xl font-bold text-purple-300">
                                            {{ stats.total_games_played }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Games Played</div>
                                </div>

                                <div class="text-center">
                                    <div class="mb-3 rounded-lg bg-yellow-600/20 p-6">
                                        <Trophy class="mx-auto mb-2 h-8 w-8 text-yellow-400" />
                                        <div class="text-3xl font-bold text-yellow-300">
                                            {{ stats.achievements_earned }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Achievements</div>
                                </div>

                                <div class="text-center">
                                    <div class="mb-3 rounded-lg bg-blue-600/20 p-6">
                                        <Star class="mx-auto mb-2 h-8 w-8 text-blue-400" />
                                        <div class="text-3xl font-bold text-blue-300">
                                            {{ stats.community_points }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Community Points</div>
                                </div>
                            </div>

                            <div class="mt-8 border-t border-white/10 pt-6">
                                <div class="text-center">
                                    <Users class="mx-auto mb-4 h-12 w-12 text-gray-500" />
                                    <h3 class="mb-2 text-lg font-semibold text-white">Start Your Gaming Journey!</h3>
                                    <p class="mb-4 text-gray-400">Explore our game library and start earning achievements</p>
                                    <Link href="/games">
                                        <Button variant="outline" class="border-purple-400 text-purple-400 hover:bg-purple-400 hover:text-white">
                                            <Gamepad2 class="mr-2 h-4 w-4" />
                                            Browse Games
                                        </Button>
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Quick Actions & Status -->
                    <div class="space-y-6">
                        <!-- Quick Actions Card -->
                        <div class="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                            <h3 class="mb-4 text-lg font-bold text-white">Quick Actions</h3>
                            <div class="space-y-3">
                                <Link :href="route('profile.edit')" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <Settings class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Account Settings</span>
                                    </div>
                                </Link>

                                <Link :href="route('password.edit')" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <Shield class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Change Password</span>
                                    </div>
                                </Link>

                                <Link :href="route('appearance')" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <Star class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Appearance</span>
                                    </div>
                                </Link>

                                <Link href="/games" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <Gamepad2 class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Browse Games</span>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Account Status Card -->
                        <div class="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                            <h3 class="mb-4 text-lg font-bold text-white">Account Status</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Email Status</span>
                                    <Badge v-if="user.email_verified_at" class="border-green-500/30 bg-green-500/20 text-green-300">
                                        ✓ Verified
                                    </Badge>
                                    <Badge v-else class="border-yellow-500/30 bg-yellow-500/20 text-yellow-300"> ⚠ Pending </Badge>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Player Rank</span>
                                    <Badge v-if="user.roles.length > 0" :class="getRoleColor(user.roles[0])">
                                        {{ formatRole(user.roles[0]) }}
                                    </Badge>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Profile Status</span>
                                    <Badge class="border-green-500/30 bg-green-500/20 text-green-300"> ✓ Active </Badge>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Card -->
                        <div class="rounded-lg border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                            <h3 class="mb-4 text-lg font-bold text-white">Explore More</h3>
                            <div class="space-y-3">
                                <Link href="/" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <ArrowLeft class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Back to Home</span>
                                    </div>
                                </Link>

                                <Link href="/games" class="block">
                                    <div
                                        class="flex items-center rounded-lg border border-white/10 bg-white/5 p-3 transition-colors hover:bg-white/10"
                                    >
                                        <Gamepad2 class="mr-3 h-5 w-5 text-purple-400" />
                                        <span class="text-white">Game Library</span>
                                    </div>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-white/10 bg-black/40">
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <div class="text-center text-gray-400">
                    <p>&copy; 2025 Games Hub. Built with Laravel and Vue.js.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
