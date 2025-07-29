<template>
    <AppLayout title="Create Game">
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg">
                    <div class="border-b border-gray-200 bg-white p-6 sm:px-20">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-semibold text-gray-900">Create New Game</h2>
                                <p class="mt-1 text-sm text-gray-600">Add a new game with all its metadata and configuration.</p>
                            </div>
                            <Link
                                :href="route('admin.games')"
                                class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-gray-900"
                            >
                                ← Back to Games
                            </Link>
                        </div>
                    </div>

                    <div class="p-6 sm:px-20">
                        <form @submit.prevent="submit">
                            <!-- Basic Information Section -->
                            <div class="mb-8">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Basic Information</h3>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Game Name</label>
                                        <input
                                            id="name"
                                            v-model="form.name"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.name }"
                                            required
                                        />
                                        <div v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</div>
                                    </div>

                                    <div>
                                        <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL-friendly name)</label>
                                        <input
                                            id="slug"
                                            v-model="form.slug"
                                            type="text"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.slug }"
                                        />
                                        <div v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</div>
                                        <p class="mt-1 text-xs text-gray-500">Leave empty to auto-generate from name</p>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                        <textarea
                                            id="description"
                                            v-model="form.description"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.description }"
                                            required
                                        ></textarea>
                                        <div v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="how_to_play" class="block text-sm font-medium text-gray-700">How to Play</label>
                                        <textarea
                                            id="how_to_play"
                                            v-model="form.how_to_play"
                                            rows="4"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.how_to_play }"
                                        ></textarea>
                                        <div v-if="form.errors.how_to_play" class="mt-2 text-sm text-red-600">{{ form.errors.how_to_play }}</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Game Settings Section -->
                            <div class="mb-8">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Game Settings</h3>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                    <div>
                                        <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                                        <select
                                            id="category"
                                            v-model="form.category"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.category }"
                                        >
                                            <option value="">Select Category</option>
                                            <option value="puzzle">Puzzle</option>
                                            <option value="action">Action</option>
                                            <option value="adventure">Adventure</option>
                                            <option value="strategy">Strategy</option>
                                            <option value="casual">Casual</option>
                                            <option value="arcade">Arcade</option>
                                        </select>
                                        <div v-if="form.errors.category" class="mt-2 text-sm text-red-600">{{ form.errors.category }}</div>
                                    </div>

                                    <div>
                                        <label for="difficulty" class="block text-sm font-medium text-gray-700">Difficulty</label>
                                        <select
                                            id="difficulty"
                                            v-model="form.difficulty"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.difficulty }"
                                        >
                                            <option value="">Select Difficulty</option>
                                            <option value="easy">Easy</option>
                                            <option value="medium">Medium</option>
                                            <option value="hard">Hard</option>
                                            <option value="expert">Expert</option>
                                        </select>
                                        <div v-if="form.errors.difficulty" class="mt-2 text-sm text-red-600">{{ form.errors.difficulty }}</div>
                                    </div>

                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                        <select
                                            id="status"
                                            v-model="form.status"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.status }"
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="published">Published</option>
                                            <option value="archived">Archived</option>
                                        </select>
                                        <div v-if="form.errors.status" class="mt-2 text-sm text-red-600">{{ form.errors.status }}</div>
                                    </div>

                                    <div>
                                        <label for="min_players" class="block text-sm font-medium text-gray-700">Minimum Players</label>
                                        <input
                                            id="min_players"
                                            v-model.number="form.min_players"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.min_players }"
                                        />
                                        <div v-if="form.errors.min_players" class="mt-2 text-sm text-red-600">{{ form.errors.min_players }}</div>
                                    </div>

                                    <div>
                                        <label for="max_players" class="block text-sm font-medium text-gray-700">Maximum Players</label>
                                        <input
                                            id="max_players"
                                            v-model.number="form.max_players"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.max_players }"
                                        />
                                        <div v-if="form.errors.max_players" class="mt-2 text-sm text-red-600">{{ form.errors.max_players }}</div>
                                    </div>

                                    <div>
                                        <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Duration (minutes)</label>
                                        <input
                                            id="estimated_duration"
                                            v-model.number="form.estimated_duration"
                                            type="number"
                                            min="1"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.estimated_duration }"
                                        />
                                        <div v-if="form.errors.estimated_duration" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.estimated_duration }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Media and Files Section -->
                            <div class="mb-8">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Media and Files</h3>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="thumbnail_url" class="block text-sm font-medium text-gray-700">Thumbnail URL</label>
                                        <input
                                            id="thumbnail_url"
                                            v-model="form.thumbnail_url"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.thumbnail_url }"
                                        />
                                        <div v-if="form.errors.thumbnail_url" class="mt-2 text-sm text-red-600">{{ form.errors.thumbnail_url }}</div>
                                    </div>

                                    <div>
                                        <label for="featured_image_url" class="block text-sm font-medium text-gray-700">Featured Image URL</label>
                                        <input
                                            id="featured_image_url"
                                            v-model="form.featured_image_url"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.featured_image_url }"
                                        />
                                        <div v-if="form.errors.featured_image_url" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.featured_image_url }}
                                        </div>
                                    </div>

                                    <div class="md:col-span-2">
                                        <label for="javascript_file_url" class="block text-sm font-medium text-gray-700">JavaScript File URL</label>
                                        <input
                                            id="javascript_file_url"
                                            v-model="form.javascript_file_url"
                                            type="url"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.javascript_file_url }"
                                            required
                                        />
                                        <div v-if="form.errors.javascript_file_url" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.javascript_file_url }}
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">URL to the JavaScript file containing the game code</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Settings Section -->
                            <div class="mb-8">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Additional Settings</h3>
                                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                    <div>
                                        <label for="tags" class="block text-sm font-medium text-gray-700">Tags (comma-separated)</label>
                                        <input
                                            id="tags"
                                            v-model="form.tags_string"
                                            type="text"
                                            placeholder="multiplayer, strategy, turn-based"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.tags_string }"
                                        />
                                        <div v-if="form.errors.tags_string" class="mt-2 text-sm text-red-600">{{ form.errors.tags_string }}</div>
                                    </div>

                                    <div>
                                        <label for="controls" class="block text-sm font-medium text-gray-700">Controls (comma-separated)</label>
                                        <input
                                            id="controls"
                                            v-model="form.controls_string"
                                            type="text"
                                            placeholder="mouse, keyboard, touch"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.controls_string }"
                                        />
                                        <div v-if="form.errors.controls_string" class="mt-2 text-sm text-red-600">
                                            {{ form.errors.controls_string }}
                                        </div>
                                    </div>

                                    <div>
                                        <label for="age_rating" class="block text-sm font-medium text-gray-700">Age Rating</label>
                                        <select
                                            id="age_rating"
                                            v-model="form.age_rating"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            :class="{ 'border-red-300': form.errors.age_rating }"
                                        >
                                            <option value="">Select Age Rating</option>
                                            <option value="E">Everyone</option>
                                            <option value="E10+">Everyone 10+</option>
                                            <option value="T">Teen</option>
                                            <option value="M">Mature</option>
                                        </select>
                                        <div v-if="form.errors.age_rating" class="mt-2 text-sm text-red-600">{{ form.errors.age_rating }}</div>
                                    </div>

                                    <div class="flex items-center">
                                        <input
                                            id="is_featured"
                                            v-model="form.is_featured"
                                            type="checkbox"
                                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                        />
                                        <label for="is_featured" class="ml-2 block text-sm text-gray-900"> Featured Game </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="flex items-center justify-end space-x-4">
                                <Link
                                    :href="route('admin.games')"
                                    class="inline-flex items-center rounded-md border border-transparent bg-gray-300 px-4 py-2 text-xs font-semibold tracking-widest text-gray-700 uppercase transition duration-150 ease-in-out hover:bg-gray-400 focus:bg-gray-400 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 focus:outline-none active:bg-gray-500"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition duration-150 ease-in-out hover:bg-indigo-700 focus:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:outline-none active:bg-indigo-900 disabled:opacity-25"
                                >
                                    <span v-if="form.processing">Creating...</span>
                                    <span v-else>Create Game</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    how_to_play: '',
    category: '',
    difficulty: '',
    status: 'draft',
    min_players: 1,
    max_players: 1,
    estimated_duration: null,
    thumbnail_url: '',
    featured_image_url: '',
    javascript_file_url: '',
    tags_string: '',
    controls_string: '',
    age_rating: '',
    is_featured: false,
});

const submit = () => {
    // Convert comma-separated strings to arrays
    const formData = {
        ...form.data(),
        tags: form.tags_string
            ? form.tags_string
                  .split(',')
                  .map((tag) => tag.trim())
                  .filter((tag) => tag)
            : [],
        controls: form.controls_string
            ? form.controls_string
                  .split(',')
                  .map((control) => control.trim())
                  .filter((control) => control)
            : [],
    };

    // Remove the string versions from the data being sent
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { tags_string, controls_string, ...finalData } = formData;

    form.transform(() => finalData).post(route('admin.games.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>
