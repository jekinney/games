<script setup lang="ts">
import { 
    DropdownMenu, 
    DropdownMenuContent, 
    DropdownMenuItem, 
    DropdownMenuSeparator, 
    DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu';
import { Link, usePage, router } from '@inertiajs/vue3';
import { Gamepad2, ChevronDown, User, LogOut } from 'lucide-vue-next';

const page = usePage();
const auth = page.props.auth as { user?: any };

const handleLogout = () => {
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
                </div>

                <div class="flex items-center space-x-4">

                    <!-- Authenticated User Dropdown -->
                    <DropdownMenu v-if="auth.user">
                        <DropdownMenuTrigger as-child>
                            <button
                                class="flex items-center space-x-2 rounded-md px-3 py-2 text-sm font-medium text-gray-300 transition-colors hover:text-white focus:outline-none focus:ring-2 focus:ring-purple-500"
                            >
                                <span>{{ auth.user.name }}</span>
                                <ChevronDown class="h-4 w-4" />
                            </button>
                        </DropdownMenuTrigger>
                        
                        <DropdownMenuContent align="end" class="w-48">
                            <DropdownMenuItem as-child>
                                <Link
                                    :href="route('profile')"
                                    class="flex w-full items-center"
                                >
                                    <User class="mr-2 h-4 w-4" />
                                    Profile
                                </Link>
                            </DropdownMenuItem>
                            
                            <DropdownMenuSeparator />
                            
                            <DropdownMenuItem as-child>
                                <button
                                    @click="handleLogout"
                                    type="button"
                                    class="flex w-full items-center"
                                >
                                    <LogOut class="mr-2 h-4 w-4" />
                                    Log Out
                                </button>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>

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
