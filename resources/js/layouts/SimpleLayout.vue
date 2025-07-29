<script setup lang="ts">
interface Props {
    title?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'Games Hub',
});
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <!-- Navigation Placeholder -->
        <nav class="border-b border-white/10 bg-black/20 backdrop-blur-sm">
            <div class="px-6 py-4">
                <h1 class="text-white font-bold">Games Hub</h1>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>

        <!-- Footer Placeholder -->
        <footer class="border-t border-white/10 bg-black/40 py-4 text-center text-white">
            <p>&copy; 2025 Games Hub</p>
        </footer>
    </div>
</template>
