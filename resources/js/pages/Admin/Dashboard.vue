<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Users, UserCheck, UserX, Shield, Calendar, TrendingUp, Eye, Settings, Database } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';

interface Stats {
    total_users: number;
    verified_users: number;
    unverified_users: number;
    admin_users: number;
    recent_registrations: number;
}

interface RecentUser {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    roles: string[];
}

defineProps<{
    stats: Stats;
    recent_users: RecentUser[];
}>();

// Function to get role color
const getRoleColor = (role: string) => {
    const colors = {
        'super-admin': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        'admin': 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        'moderator': 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        'player': 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

// Function to format role name
const formatRole = (role: string) => {
    return role.split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
};
</script>

<template>
    <AppLayout>
        <Head title="Admin Dashboard" />

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Admin Dashboard</h1>
                    <p class="mt-1 text-gray-600 dark:text-gray-400">
                        Monitor and manage your Games Hub platform
                    </p>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                    <!-- Total Users -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <Users class="h-8 w-8 text-blue-600" />
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.total_users }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Users</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Verified Users -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UserCheck class="h-8 w-8 text-green-600" />
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.verified_users }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Verified</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Unverified Users -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <UserX class="h-8 w-8 text-red-600" />
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.unverified_users }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Unverified</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Admin Users -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <Shield class="h-8 w-8 text-purple-600" />
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.admin_users }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">Admins</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Recent Registrations -->
                    <Card>
                        <CardContent class="p-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <TrendingUp class="h-8 w-8 text-indigo-600" />
                                </div>
                                <div class="ml-4">
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ stats.recent_registrations }}
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400">This Week</div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Recent Users -->
                    <div class="lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <Users class="h-5 w-5" />
                                    <span>Recent Users</span>
                                </CardTitle>
                                <CardDescription>
                                    Latest user registrations
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-4">
                                    <div v-for="user in recent_users" :key="user.id" class="flex items-center justify-between p-4 border border-gray-200 dark:border-gray-700 rounded-lg">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="h-10 w-10 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                        {{ user.name.charAt(0).toUpperCase() }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-500">{{ user.created_at }}</div>
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <Badge v-if="user.email_verified_at" variant="outline" class="text-green-600 border-green-600">
                                                ✓ Verified
                                            </Badge>
                                            <Badge v-else variant="outline" class="text-yellow-600 border-yellow-600">
                                                ⚠ Unverified
                                            </Badge>
                                            <Badge 
                                                v-if="user.roles.length > 0"
                                                :class="getRoleColor(user.roles[0])"
                                            >
                                                {{ formatRole(user.roles[0]) }}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                                    <Link :href="route('admin.users')">
                                        <Button variant="outline" class="w-full">
                                            <Eye class="h-4 w-4 mr-2" />
                                            View All Users
                                        </Button>
                                    </Link>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <!-- Quick Actions -->
                    <div class="space-y-6">
                        <!-- Management Actions -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                                <CardDescription>
                                    Common administrative tasks
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Link :href="route('admin.users')" class="block">
                                    <Button variant="outline" class="w-full justify-start">
                                        <Users class="h-4 w-4 mr-2" />
                                        Manage Users
                                    </Button>
                                </Link>
                                
                                <Link :href="route('admin.settings')" class="block">
                                    <Button variant="outline" class="w-full justify-start">
                                        <Settings class="h-4 w-4 mr-2" />
                                        System Settings
                                    </Button>
                                </Link>
                                
                                <Link :href="route('admin.games')" class="block">
                                    <Button variant="outline" class="w-full justify-start">
                                        <Gamepad2 class="h-4 w-4 mr-2" />
                                        Manage Games
                                    </Button>
                                </Link>
                                
                                <Button variant="outline" class="w-full justify-start" disabled>
                                    <Database class="h-4 w-4 mr-2" />
                                    Game Analytics
                                    <span class="ml-auto text-xs text-gray-500">Coming Soon</span>
                                </Button>
                            </CardContent>
                        </Card>

                        <!-- System Status -->
                        <Card>
                            <CardHeader>
                                <CardTitle>System Status</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Database</span>
                                    <Badge variant="outline" class="text-green-600 border-green-600">
                                        ✓ Online
                                    </Badge>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Cache</span>
                                    <Badge variant="outline" class="text-green-600 border-green-600">
                                        ✓ Active
                                    </Badge>
                                </div>
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Queue</span>
                                    <Badge variant="outline" class="text-green-600 border-green-600">
                                        ✓ Running
                                    </Badge>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Recent Activity -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <Calendar class="h-5 w-5" />
                                    <span>Platform Activity</span>
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div class="space-y-3">
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ stats.recent_registrations }} new users</div>
                                        <div class="text-gray-600 dark:text-gray-400">registered this week</div>
                                    </div>
                                    
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ stats.verified_users }} verified accounts</div>
                                        <div class="text-gray-600 dark:text-gray-400">out of {{ stats.total_users }} total users</div>
                                    </div>
                                    
                                    <div class="text-sm">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ Math.round((stats.verified_users / stats.total_users) * 100) }}% verification rate</div>
                                        <div class="text-gray-600 dark:text-gray-400">for email verification</div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
