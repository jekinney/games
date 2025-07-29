<script setup lang="ts">
import MainNavigation from './partials/MainNavigation.vue';
import MainFooter from './partials/MainFooter.vue';
import FlashMessage from './partials/FlashMessage.vue';

defineProps<{
    title?: string;
}>();
</script>

<template>
    <div class="flex min-h-screen flex-col bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900">
        <MainNavigation />
        
        <FlashMessage />
        
        <main class="flex-1">
            <slot />
        </main>
        
        <MainFooter />
    </div>
</template>