<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/vue3';
import { Gamepad2, Trophy, Users, Zap } from 'lucide-vue-next';
import MainLayout from '@/layouts/MainLayout.vue';

defineProps<{
    auth: {
        user?: any;
    };
}>();
</script>

<template>
    <Head title="Welcome to Games Hub" />
    
    <MainLayout>
        <!-- Hero Section -->
        <div class="relative overflow-hidden">
            <div class="mx-auto max-w-7xl px-4 py-24 sm:px-6 lg:px-8">
                <div class="text-center">
                    <h1 class="mb-6 text-4xl font-bold text-white md:text-6xl">
                        Welcome to
                        <span class="bg-gradient-to-r from-purple-400 to-pink-600 bg-clip-text text-transparent"> Games Hub </span>
                    </h1>
                    <p class="mx-auto mb-8 max-w-3xl text-xl text-gray-300">
                        Discover amazing games, connect with fellow gamers, and dive into immersive experiences. Your gaming adventure starts here!
                    </p>

                    <div class="flex flex-col justify-center gap-4 sm:flex-row">
                        <Link href="/games">
                            <Button size="lg" class="bg-purple-600 px-8 py-3 text-white hover:bg-purple-700">
                                <Gamepad2 class="mr-2 h-5 w-5" />
                                Explore Games
                            </Button>
                        </Link>

                        <Link v-if="!auth.user" :href="route('register')">
                            <Button
                                variant="outline"
                                size="lg"
                                class="border-purple-400 px-8 py-3 text-purple-400 hover:bg-purple-400 hover:text-white"
                            >
                                Join Community
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="bg-black/20 py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-16 text-center">
                    <h2 class="mb-4 text-3xl font-bold text-white">Why Choose Games Hub?</h2>
                    <p class="text-lg text-gray-300">Everything you need for the ultimate gaming experience</p>
                </div>

                <div class="grid gap-8 md:grid-cols-3">
                    <div class="rounded-lg border border-white/10 bg-white/5 p-6 text-center backdrop-blur-sm">
                        <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-purple-600">
                            <Gamepad2 class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-white">Diverse Game Library</h3>
                        <p class="text-gray-300">From action-packed adventures to mind-bending puzzles, discover games that match your style.</p>
                    </div>

                    <div class="rounded-lg border border-white/10 bg-white/5 p-6 text-center backdrop-blur-sm">
                        <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-pink-600">
                            <Users class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-white">Gaming Community</h3>
                        <p class="text-gray-300">Connect with players worldwide, join discussions, and make lasting friendships.</p>
                    </div>

                    <div class="rounded-lg border border-white/10 bg-white/5 p-6 text-center backdrop-blur-sm">
                        <div class="mb-4 inline-flex h-16 w-16 items-center justify-center rounded-full bg-blue-600">
                            <Trophy class="h-8 w-8 text-white" />
                        </div>
                        <h3 class="mb-2 text-xl font-semibold text-white">Achievements & Rewards</h3>
                        <p class="text-gray-300">Track your progress, unlock achievements, and compete on global leaderboards.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="py-24">
            <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
                <h2 class="mb-4 text-3xl font-bold text-white">Ready to Start Gaming?</h2>
                <p class="mb-8 text-lg text-gray-300">Join thousands of gamers who have already discovered their next favorite game.</p>

                <div class="flex flex-col justify-center gap-4 sm:flex-row">
                    <Link href="/games">
                        <Button
                            size="lg"
                            class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-3 text-white hover:from-purple-700 hover:to-pink-700"
                        >
                            <Zap class="mr-2 h-5 w-5" />
                            Browse Games Now
                        </Button>
                    </Link>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
