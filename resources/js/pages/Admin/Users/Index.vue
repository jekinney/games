<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Edit, Plus, Search, Shield, Trash2, UserCheck, Users, UserX } from 'lucide-vue-next';
import { ref } from 'vue';

interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    roles: string[];
}

interface PaginatedUsers {
    data: User[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
}

defineProps<{
    users: PaginatedUsers;
}>();

const searchQuery = ref('');

// Function to get role color
const getRoleColor = (role: string) => {
    const colors = {
        'super-admin': 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        admin: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300',
        moderator: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300',
        player: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
    };
    return colors[role as keyof typeof colors] || 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300';
};

// Function to format role name
const formatRole = (role: string) => {
    return role
        .split('-')
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
};

// Function to handle search
const handleSearch = () => {
    router.get(
        route('admin.users'),
        { search: searchQuery.value },
        {
            preserveState: true,
            replace: true,
        },
    );
};
</script>

<template>
    <AppLayout>
        <Head title="User Management - Admin" />

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">User Management</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">Manage platform users and their permissions</p>
                        </div>
                        <Link href="/admin">
                            <Button variant="outline"> ← Back to Dashboard </Button>
                        </Link>
                    </div>
                </div>

                <!-- Search and Filters -->
                <Card class="mb-6">
                    <CardContent class="p-6">
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <div class="relative">
                                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-gray-400" />
                                    <Input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="Search users by name or email..."
                                        class="pl-10"
                                        @keyup.enter="handleSearch"
                                    />
                                </div>
                            </div>
                            <Button @click="handleSearch"> Search </Button>
                            <Button variant="outline" disabled>
                                <Plus class="mr-2 h-4 w-4" />
                                Add User
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Users Table -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <Users class="h-5 w-5" />
                            <span>All Users</span>
                        </CardTitle>
                        <CardDescription> Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">User</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">Status</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">Role</th>
                                        <th class="px-4 py-3 text-left font-medium text-gray-900 dark:text-white">Joined</th>
                                        <th class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="user in users.data"
                                        :key="user.id"
                                        class="border-b border-gray-100 hover:bg-gray-50 dark:border-gray-800 dark:hover:bg-gray-800/50"
                                    >
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-3">
                                                <div class="flex-shrink-0">
                                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gray-300 dark:bg-gray-600">
                                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                            {{ user.name.charAt(0).toUpperCase() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">{{ user.name }}</div>
                                                    <div class="text-sm text-gray-600 dark:text-gray-400">{{ user.email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center space-x-2">
                                                <Badge v-if="user.email_verified_at" variant="outline" class="border-green-600 text-green-600">
                                                    <UserCheck class="mr-1 h-3 w-3" />
                                                    Verified
                                                </Badge>
                                                <Badge v-else variant="outline" class="border-yellow-600 text-yellow-600">
                                                    <UserX class="mr-1 h-3 w-3" />
                                                    Unverified
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-1">
                                                <Badge v-for="role in user.roles" :key="role" :class="getRoleColor(role)">
                                                    <Shield class="mr-1 h-3 w-3" />
                                                    {{ formatRole(role) }}
                                                </Badge>
                                                <Badge v-if="user.roles.length === 0" variant="outline" class="border-gray-600 text-gray-600">
                                                    No Role
                                                </Badge>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="text-sm text-gray-900 dark:text-white">{{ user.created_at }}</div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Button variant="outline" size="sm" disabled>
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                                <Button variant="outline" size="sm" disabled>
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="users.last_page > 1" class="mt-6 flex items-center justify-between">
                            <div class="text-sm text-gray-600 dark:text-gray-400">
                                Showing {{ users.from }} to {{ users.to }} of {{ users.total }} results
                            </div>
                            <div class="flex items-center space-x-2">
                                <Button
                                    v-for="page in Array.from({ length: users.last_page }, (_, i) => i + 1)"
                                    :key="page"
                                    :variant="page === users.current_page ? 'default' : 'outline'"
                                    size="sm"
                                    @click="router.get(route('admin.users', { page }))"
                                >
                                    {{ page }}
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
