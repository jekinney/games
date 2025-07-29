<script setup lang="ts">
import { Link, usePage, router } from '@inertiajs/vue3';
import { Gamepad2, ChevronDown, User, LogOut } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';

const page = usePage();
const auth = page.props.auth as { user?: any };
const isDropdownOpen = ref(false);

const toggleDropdown = () => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

const closeDropdown = () => {
    isDropdownOpen.value = false;
};

// Close dropdown when clicking outside
const handleClickOutside = (event: Event) => {
    const target = event.target as HTMLElement;
    const dropdown = document.querySelector('.user-dropdown');
    if (dropdown && !dropdown.contains(target)) {
        closeDropdown();
    }
};

// Add event listener when component mounts
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

const handleLogout = () => {
    closeDropdown();
    router.post(route('logout'), {}, {
        onSuccess: () => {
            // The controller should handle the redirect and flash message
            console.log('User logged out successfully');
        }
    });
};
</script>

<template>
    <nav class="border-b border-white/10 bg-black/20 backdrop-blur-sm">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 justify-between">
                <div class="flex items-center space-x-6">
                    <Link href="/" class="flex items-center">
                        <Gamepad2 class="h-8 w-8 text-purple-400" />
                        <span class="ml-2 text-xl font-bold text-white">Games Hub</span>
                    </Link>

                    <!-- Navigation Links -->
                    <Link 
                        href="/games" 
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                    >
                        Games
                    </Link>
                    <Link 
                        href="/leaderboards" 
                        class="rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white"
                    >
                        Leaderboards
                    </Link>
                </div>

                <div class="flex items-center space-x-4">

                    <!-- Authenticated User Dropdown -->
                    <div v-if="auth.user" class="relative user-dropdown">
                        <button
                            @click="toggleDropdown"
                            class="flex items-center space-x-2 rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            :class="{ 'text-white bg-white/10': isDropdownOpen }"
                        >
                            <span>{{ auth.user.name }}</span>
                            <ChevronDown class="h-4 w-4 transition-transform" :class="{ 'rotate-180': isDropdownOpen }" />
                        </button>

                        <!-- Dropdown Menu -->
                        <div
                            v-show="isDropdownOpen"
                            class="absolute right-0 mt-2 w-48 rounded-md border border-white/10 bg-gray-800/95 py-1 shadow-xl backdrop-blur-sm z-50"
                        >
                            <Link
                                :href="route('profile')"
                                class="block px-4 py-2 text-sm text-gray-300 transition-colors hover:bg-white/10 hover:text-white"
                                @click="closeDropdown"
                            >
                                <div class="flex items-center">
                                    <User class="mr-3 h-4 w-4" />
                                    Profile
                                </div>
                            </Link>
                            
                            <button
                                @click="handleLogout"
                                type="button"
                                class="block w-full px-4 py-2 text-left text-sm text-gray-300 transition-colors hover:bg-white/10 hover:text-white"
                            >
                                <div class="flex items-center">
                                    <LogOut class="mr-3 h-4 w-4" />
                                    Log Out
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Guest Links -->
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
