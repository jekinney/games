<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Settings, Database, Globe, Clock, MapPin, Save } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { ref } from 'vue';

interface AppSettings {
    app_name: string;
    app_url: string;
    timezone: string;
    locale: string;
}

const props = defineProps<{
    app_settings: AppSettings;
}>();

const settings = ref({ ...props.app_settings });

const saveSettings = () => {
    // This would normally submit to a backend endpoint
    console.log('Saving settings:', settings.value);
    alert('Settings saved! (Demo functionality)');
};
</script>

<template>
    <AppLayout>
        <Head title="Admin Settings" />

        <div class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">System Settings</h1>
                            <p class="mt-1 text-gray-600 dark:text-gray-400">
                                Configure your Games Hub platform settings
                            </p>
                        </div>
                        <Link href="/admin">
                            <Button variant="outline">
                                ← Back to Dashboard
                            </Button>
                        </Link>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Settings Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Application Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <Settings class="h-5 w-5" />
                                    <span>Application Settings</span>
                                </CardTitle>
                                <CardDescription>
                                    Basic configuration for your Games Hub platform
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Application Name
                                    </label>
                                    <Input
                                        v-model="settings.app_name"
                                        type="text"
                                        placeholder="Games Hub"
                                    />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        The name of your gaming platform
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Application URL
                                    </label>
                                    <div class="flex items-center space-x-2">
                                        <Globe class="h-4 w-4 text-gray-400" />
                                        <Input
                                            v-model="settings.app_url"
                                            type="url"
                                            placeholder="https://games.test"
                                            class="flex-1"
                                        />
                                    </div>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        The base URL where your platform is hosted
                                    </p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Timezone
                                        </label>
                                        <div class="flex items-center space-x-2">
                                            <Clock class="h-4 w-4 text-gray-400" />
                                            <Input
                                                v-model="settings.timezone"
                                                type="text"
                                                placeholder="UTC"
                                                class="flex-1"
                                            />
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Default Locale
                                        </label>
                                        <div class="flex items-center space-x-2">
                                            <MapPin class="h-4 w-4 text-gray-400" />
                                            <Input
                                                v-model="settings.locale"
                                                type="text"
                                                placeholder="en"
                                                class="flex-1"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Database Settings -->
                        <Card>
                            <CardHeader>
                                <CardTitle class="flex items-center space-x-2">
                                    <Database class="h-5 w-5" />
                                    <span>Database Configuration</span>
                                </CardTitle>
                                <CardDescription>
                                    Database and storage settings (Read-only for security)
                                </CardDescription>
                            </CardHeader>
                            <CardContent class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Database Connection
                                    </label>
                                    <Input
                                        value="SQLite"
                                        type="text"
                                        disabled
                                        class="bg-gray-100 dark:bg-gray-800"
                                    />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        Currently using SQLite database
                                    </p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Cache Driver
                                    </label>
                                    <Input
                                        value="File Cache"
                                        type="text"
                                        disabled
                                        class="bg-gray-100 dark:bg-gray-800"
                                    />
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        File-based caching is active
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Save Button -->
                        <div class="flex justify-end">
                            <Button @click="saveSettings" class="flex items-center space-x-2">
                                <Save class="h-4 w-4" />
                                <span>Save Settings</span>
                            </Button>
                        </div>
                    </div>

                    <!-- Settings Info Sidebar -->
                    <div class="space-y-6">
                        <!-- Settings Help -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Settings Help</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-4">
                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Application Name</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        This appears in the browser title, emails, and throughout the platform.
                                    </p>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Application URL</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Used for generating links in emails and API responses.
                                    </p>
                                </div>

                                <div>
                                    <h4 class="font-medium text-gray-900 dark:text-white mb-2">Timezone</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Default timezone for date/time display and scheduling.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Platform Status -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Platform Status</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Environment</span>
                                    <span class="text-sm font-medium text-green-600">Development</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Debug Mode</span>
                                    <span class="text-sm font-medium text-yellow-600">Enabled</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Maintenance</span>
                                    <span class="text-sm font-medium text-green-600">Offline</span>
                                </div>

                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600 dark:text-gray-400">Laravel Version</span>
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">12.x</span>
                                </div>
                            </CardContent>
                        </Card>

                        <!-- Quick Actions -->
                        <Card>
                            <CardHeader>
                                <CardTitle>Quick Actions</CardTitle>
                            </CardHeader>
                            <CardContent class="space-y-3">
                                <Button variant="outline" class="w-full justify-start" disabled>
                                    <Database class="h-4 w-4 mr-2" />
                                    Clear Cache
                                </Button>

                                <Button variant="outline" class="w-full justify-start" disabled>
                                    <Settings class="h-4 w-4 mr-2" />
                                    Run Migrations
                                </Button>

                                <Button variant="outline" class="w-full justify-start" disabled>
                                    <Globe class="h-4 w-4 mr-2" />
                                    Update Environment
                                </Button>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
