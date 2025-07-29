<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import Badge from '@/components/ui/badge/Badge.vue';
import { User, Calendar, Mail, Shield, Settings, Trophy, Gamepad2, Star, ArrowLeft, Users } from 'lucide-vue-next';

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
        'admin': 'bg-orange-500/20 text-orange-300 border-orange-500/30',
        'moderator': 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        'player': 'bg-green-500/20 text-green-300 border-green-500/30',
    };
    return colors[role as keyof typeof colors] || 'bg-gray-500/20 text-gray-300 border-gray-500/30';
};

// Function to format role name
const formatRole = (role: string) => {
    return role.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <Head title="My Profile - Games Hub" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation -->
        <nav class="bg-black/20 backdrop-blur-sm border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center">
                            <Gamepad2 class="h-8 w-8 text-purple-400" />
                            <span class="ml-2 text-xl font-bold text-white">Games Hub</span>
                        </Link>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <Link 
                            href="/" 
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Home
                        </Link>
                        <Link 
                            href="/games" 
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Games
                        </Link>
                        <span class="text-purple-400 px-3 py-2 rounded-md text-sm font-medium">
                            Profile
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Header Section -->
        <div class="py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-white mb-4">
                        Player Profile
                    </h1>
                    <p class="text-xl text-gray-300 max-w-2xl mx-auto">
                        Manage your gaming identity and track your progress
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Content -->
        <div class="pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Left Column - Profile Info -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Basic Information Card -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <User class="h-6 w-6 text-purple-400" />
                                <h2 class="text-xl font-bold text-white">Profile Information</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="text-sm font-medium text-gray-400">Player Name</label>
                                    <div class="mt-2 text-xl text-white font-semibold">{{ user.name }}</div>
                                </div>
                                
                                <div>
                                    <label class="text-sm font-medium text-gray-400">Email</label>
                                    <div class="mt-2 flex items-center space-x-2">
                                        <Mail class="h-4 w-4 text-gray-400" />
                                        <span class="text-white">{{ user.email }}</span>
                                        <Badge 
                                            v-if="user.email_verified_at" 
                                            class="bg-green-500/20 text-green-300 border-green-500/30"
                                        >
                                            ✓ Verified
                                        </Badge>
                                        <Badge 
                                            v-else 
                                            class="bg-yellow-500/20 text-yellow-300 border-yellow-500/30"
                                        >
                                            ⚠ Unverified
                                        </Badge>
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
                                            <Badge 
                                                v-for="role in user.roles" 
                                                :key="role"
                                                :class="getRoleColor(role)"
                                            >
                                                {{ formatRole(role) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="pt-6 border-t border-white/10 mt-6">
                                <Link :href="route('profile.edit')">
                                    <Button class="bg-purple-600 hover:bg-purple-700 text-white">
                                        <Settings class="h-4 w-4 mr-2" />
                                        Edit Profile
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <!-- Gaming Statistics Card -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
                            <div class="flex items-center space-x-3 mb-6">
                                <Trophy class="h-6 w-6 text-purple-400" />
                                <h2 class="text-xl font-bold text-white">Gaming Statistics</h2>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                                <div class="text-center">
                                    <div class="bg-purple-600/20 rounded-lg p-6 mb-3">
                                        <Gamepad2 class="h-8 w-8 text-purple-400 mx-auto mb-2" />
                                        <div class="text-3xl font-bold text-purple-300">
                                            {{ stats.total_games_played }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Games Played</div>
                                </div>
                                
                                <div class="text-center">
                                    <div class="bg-yellow-600/20 rounded-lg p-6 mb-3">
                                        <Trophy class="h-8 w-8 text-yellow-400 mx-auto mb-2" />
                                        <div class="text-3xl font-bold text-yellow-300">
                                            {{ stats.achievements_earned }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Achievements</div>
                                </div>
                                
                                <div class="text-center">
                                    <div class="bg-blue-600/20 rounded-lg p-6 mb-3">
                                        <Star class="h-8 w-8 text-blue-400 mx-auto mb-2" />
                                        <div class="text-3xl font-bold text-blue-300">
                                            {{ stats.community_points }}
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-400">Community Points</div>
                                </div>
                            </div>

                            <div class="mt-8 pt-6 border-t border-white/10">
                                <div class="text-center">
                                    <Users class="h-12 w-12 text-gray-500 mx-auto mb-4" />
                                    <h3 class="text-lg font-semibold text-white mb-2">Start Your Gaming Journey!</h3>
                                    <p class="text-gray-400 mb-4">Explore our game library and start earning achievements</p>
                                    <Link href="/games">
                                        <Button variant="outline" class="border-purple-400 text-purple-400 hover:bg-purple-400 hover:text-white">
                                            <Gamepad2 class="h-4 w-4 mr-2" />
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
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-white mb-4">Quick Actions</h3>
                            <div class="space-y-3">
                                <Link :href="route('profile.edit')" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <Settings class="h-5 w-5 text-purple-400 mr-3" />
                                        <span class="text-white">Account Settings</span>
                                    </div>
                                </Link>
                                
                                <Link :href="route('password.edit')" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <Shield class="h-5 w-5 text-purple-400 mr-3" />
                                        <span class="text-white">Change Password</span>
                                    </div>
                                </Link>
                                
                                <Link :href="route('appearance')" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <Star class="h-5 w-5 text-purple-400 mr-3" />
                                        <span class="text-white">Appearance</span>
                                    </div>
                                </Link>
                                
                                <Link href="/games" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <Gamepad2 class="h-5 w-5 text-purple-400 mr-3" />
                                        <span class="text-white">Browse Games</span>
                                    </div>
                                </Link>
                            </div>
                        </div>

                        <!-- Account Status Card -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-white mb-4">Account Status</h3>
                            <div class="space-y-4">
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Email Status</span>
                                    <Badge 
                                        v-if="user.email_verified_at" 
                                        class="bg-green-500/20 text-green-300 border-green-500/30"
                                    >
                                        ✓ Verified
                                    </Badge>
                                    <Badge 
                                        v-else 
                                        class="bg-yellow-500/20 text-yellow-300 border-yellow-500/30"
                                    >
                                        ⚠ Pending
                                    </Badge>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Player Rank</span>
                                    <Badge 
                                        v-if="user.roles.length > 0"
                                        :class="getRoleColor(user.roles[0])"
                                    >
                                        {{ formatRole(user.roles[0]) }}
                                    </Badge>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-gray-400">Profile Status</span>
                                    <Badge class="bg-green-500/20 text-green-300 border-green-500/30">
                                        ✓ Active
                                    </Badge>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Card -->
                        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-white mb-4">Explore More</h3>
                            <div class="space-y-3">
                                <Link href="/" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <ArrowLeft class="h-5 w-5 text-purple-400 mr-3" />
                                        <span class="text-white">Back to Home</span>
                                    </div>
                                </Link>
                                
                                <Link href="/games" class="block">
                                    <div class="flex items-center p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors border border-white/10">
                                        <Gamepad2 class="h-5 w-5 text-purple-400 mr-3" />
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
        <footer class="bg-black/40 border-t border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="text-center text-gray-400">
                    <p>&copy; 2025 Games Hub. Built with Laravel and Vue.js.</p>
                </div>
            </div>
        </footer>
    </div>
</template>
