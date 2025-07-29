<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Gamepad2, Users, Trophy, Zap } from 'lucide-vue-next';

defineProps<{
    auth: {
        user?: any;
    };
}>();
</script>

<template>
    <Head title="Welcome to Games Hub" />
    
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation -->
        <nav class="bg-black/20 backdrop-blur-sm border-b border-white/10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <Gamepad2 class="h-8 w-8 text-purple-400" />
                        <span class="ml-2 text-xl font-bold text-white">Games Hub</span>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <Link 
                            href="/games" 
                            class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                        >
                            Games
                        </Link>
                        
                        <div v-if="auth.user" class="flex items-center space-x-4">
                            <span class="text-gray-300">Welcome, {{ auth.user.name }}!</span>
                            <Link
                                :href="route('profile')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Profile
                            </Link>
                        </div>
                        
                        <div v-else class="flex items-center space-x-4">
                            <Link
                                :href="route('login')"
                                class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Log in
                            </Link>
                            <Link
                                :href="route('register')"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md text-sm font-medium transition-colors"
                            >
                                Get Started
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">
                        Welcome to 
                        <span class="bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent">
                            Games Hub
                        </span>
                    </h1>
                    <p class="text-xl text-gray-300 mb-8 max-w-3xl mx-auto">
                        Discover amazing games, connect with fellow gamers, and dive into immersive experiences. 
                        Your gaming adventure starts here!
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <Link href="/games">
                            <Button size="lg" class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3">
                                <Gamepad2 class="h-5 w-5 mr-2" />
                                Explore Games
                            </Button>
                        </Link>
                        
                        <Link v-if="!auth.user" :href="route('register')">
                            <Button variant="outline" size="lg" class="border-purple-400 text-purple-400 hover:bg-purple-400 hover:text-white px-8 py-3">
                                Join Community
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="py-24 bg-black/20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-16">
                    <h2 class="text-3xl font-bold text-white mb-4">Why Choose Games Hub?</h2>
                    <p class="text-gray-300 text-lg">Everything you need for the ultimate gaming experience</p>
                </div>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <div class="text-center p-6 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-purple-600 rounded-full mb-4">
                            <Gamepad2 class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Diverse Game Library</h3>
                        <p class="text-gray-300">From action-packed adventures to mind-bending puzzles, discover games that match your style.</p>
                    </div>
                    
                    <div class="text-center p-6 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-pink-600 rounded-full mb-4">
                            <Users class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Gaming Community</h3>
                        <p class="text-gray-300">Connect with players worldwide, join discussions, and make lasting friendships.</p>
                    </div>
                    
                    <div class="text-center p-6 rounded-lg bg-white/5 backdrop-blur-sm border border-white/10">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-full mb-4">
                            <Trophy class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="text-xl font-semibold text-white mb-2">Achievements & Rewards</h3>
                        <p class="text-gray-300">Track your progress, unlock achievements, and compete on global leaderboards.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-24">
            <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl font-bold text-white mb-4">Ready to Start Gaming?</h2>
                <p class="text-gray-300 text-lg mb-8">
                    Join thousands of gamers who have already discovered their next favorite game.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <Link href="/games">
                        <Button size="lg" class="bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white px-8 py-3">
                            <Zap class="h-5 w-5 mr-2" />
                            Browse Games Now
                        </Button>
                    </Link>
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
